<?php
declare(strict_types=1);

namespace App\Services\Document;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Document;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use RuntimeException;

final class DocumentService
{
    public function __construct(
        private readonly DocumentRepositoryInterface $repo,
        private readonly DocumentStorageService $storage,
    ) {}

    // ============================================================
    // CRÉATION
    // ============================================================

    /**
     * Upload d'un nouveau document.
     *
     * @return int ID du document créé
     */
    public function create(array $data, array $file, int $userId): int
    {
        // 1. Validation métier
        if (empty(trim($data['title'] ?? ''))) {
            throw new ValidationException('Le titre est obligatoire.');
        }

        // 2. Validation + stockage du fichier
        $this->storage->validate($file);
        $relativePath = $this->storage->store($file, date('Y/m'));

        // 3. Référence auto
        $reference = $this->repo->generateReference();

        // 4. Détection MIME + taille
        $mimeType = $this->storage->mimeType($relativePath);
        $size     = $this->storage->size($relativePath);

        // 5. Insertion en BDD
        try {
            $id = $this->repo->create([
                'reference'   => $reference,
                'type'        => $data['type'] ?? 'other',
                'title'       => trim($data['title']),
                'entity_type' => $data['entity_type'] ?? null,
                'entity_id'   => $data['entity_id'] ?? null,
                'file_path'   => $relativePath,
                'mime_type'   => $mimeType,
                'size_bytes'  => $size,
                'is_signed'   => 0,
                'created_by'  => $userId,
            ]);
        } catch (\Throwable $e) {
            // Rollback : supprime le fichier si l'insertion échoue
            $this->storage->delete($relativePath);
            throw $e;
        }

        // 6. Créer la version 1
        $this->repo->createVersion([
            'document_id'  => $id,
            'version'      => 1,
            'file_path'    => $relativePath,
            'change_notes' => 'Version initiale',
            'created_by'   => $userId,
        ]);

        return $id;
    }

    // ============================================================
    // MISE À JOUR DES MÉTADONNÉES
    // ============================================================

    public function update(int $id, array $data): bool
    {
        $document = $this->loadOrFail($id);

        if (empty(trim($data['title'] ?? ''))) {
            throw new ValidationException('Le titre est obligatoire.');
        }

        return $this->repo->update($id, [
            'title'       => trim($data['title']),
            'type'        => $data['type'] ?? $document->type,
            'entity_type' => $data['entity_type'] ?? $document->entityType,
            'entity_id'   => $data['entity_id'] ?? $document->entityId,
            'is_signed'   => isset($data['is_signed']) ? (int) $data['is_signed'] : (int) $document->isSigned,
        ]);
    }

    // ============================================================
    // NOUVELLE VERSION
    // ============================================================

    /**
     * Upload d'une nouvelle version d'un document existant.
     */
    public function addVersion(int $id, array $file, ?string $changeNotes, int $userId): int
    {
        $document = $this->loadOrFail($id);

        // Stocker le nouveau fichier
        $this->storage->validate($file);
        $relativePath = $this->storage->store($file, date('Y/m'));

        // Incrémenter la version
        $newVersion = $this->repo->incrementVersion($id);

        // Créer la nouvelle version
        $this->repo->createVersion([
            'document_id'  => $id,
            'version'      => $newVersion,
            'file_path'    => $relativePath,
            'change_notes' => $changeNotes ?? "Version {$newVersion}",
            'created_by'   => $userId,
        ]);

        // Mettre à jour le chemin du fichier courant
        $mime = $this->storage->mimeType($relativePath);
        $size = $this->storage->size($relativePath);

        $this->repo->update($id, [
            'file_path'  => $relativePath,
            'mime_type'  => $mime,
            'size_bytes' => $size,
        ]);

        return $newVersion;
    }

    /**
     * Restaure une version antérieure comme version courante.
     */
    public function restoreVersion(int $documentId, int $versionId, int $userId): bool
    {
        $document = $this->loadOrFail($documentId);
        $version  = $this->repo->findVersion($versionId);

        if (!$version || $version->documentId !== $documentId) {
            throw new NotFoundException('Version introuvable.');
        }

        if ($version->version === $document->currentVersion) {
            throw new ValidationException('Cette version est déjà la version courante.');
        }

        // On ne supprime PAS l'ancien fichier — on crée une nouvelle version
        // qui pointe vers le même fichier que la version restaurée.
        $newVersion = $this->repo->incrementVersion($documentId);

        $this->repo->createVersion([
            'document_id'  => $documentId,
            'version'      => $newVersion,
            'file_path'    => $version->filePath,
            'change_notes' => "Restauration de la version {$version->version}",
            'created_by'   => $userId,
        ]);

        $mime = $this->storage->mimeType($version->filePath);
        $size = $this->storage->size($version->filePath);

        $this->repo->update($documentId, [
            'file_path'  => $version->filePath,
            'mime_type'  => $mime,
            'size_bytes' => $size,
        ]);

        return true;
    }

    // ============================================================
    // SUPPRESSION
    // ============================================================

    /**
     * Supprime un document et tous ses fichiers physiques.
     */
    public function delete(int $id): bool
    {
        $document = $this->loadOrFail($id);

        // Récupère toutes les versions (fichiers à supprimer)
        $versions = $this->repo->findVersions($id);

        // Supprime d'abord en BDD (cascade sur document_versions)
        $ok = $this->repo->delete($id);

        // Puis supprime les fichiers physiques
        if ($ok) {
            foreach ($versions as $version) {
                $this->storage->delete($version->filePath);
            }
        }

        return $ok;
    }

    // ============================================================
    // SIGNATURE
    // ============================================================

    public function toggleSigned(int $id): bool
    {
        $document = $this->loadOrFail($id);
        $newValue = !$document->isSigned;

        return $this->repo->update($id, ['is_signed' => (int) $newValue]);
    }

    // ============================================================
    // ACTIONS GROUPÉES
    // ============================================================

    /**
     * Supprime plusieurs documents d'un coup (BDD + fichiers physiques).
     *
     * @param int[] $ids
     * @return int Nombre de documents supprimés
     */
    public function bulkDelete(array $ids): int
    {
        // Récupérer tous les documents (pour connaître leurs versions/fichiers)
        $documents = $this->repo->findByIds($ids);
        if (empty($documents)) {
            return 0;
        }

        // Récupérer tous les chemins de fichiers à supprimer
        $filesToDelete = [];
        foreach ($documents as $doc) {
            $versions = $this->repo->findVersions($doc->id);
            foreach ($versions as $version) {
                $filesToDelete[] = $version->filePath;
            }
        }

        // Supprimer en BDD (cascade sur document_versions)
        $deletedCount = $this->repo->deleteByIds($ids);

        // Supprimer les fichiers physiques
        foreach ($filesToDelete as $path) {
            $this->storage->delete($path);
        }

        return $deletedCount;
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function loadOrFail(int $id): Document
    {
        $document = $this->repo->findById($id);
        if (!$document) {
            throw new NotFoundException("Document #{$id} introuvable.");
        }
        return $document;
    }
}