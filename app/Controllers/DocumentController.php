<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Middleware\CsrfMiddleware;
use App\Repositories\MySql\DocumentRepository;
use App\Services\Document\DocumentService;
use App\Services\Document\DocumentStorageService;

final class DocumentController extends Controller
{
    private DocumentRepository $repo;
    private DocumentService $service;
    private DocumentStorageService $storage;

    public function __construct()
    {
        $this->repo    = new DocumentRepository();
        $this->storage = new DocumentStorageService();
        $this->service = new DocumentService($this->repo, $this->storage);
    }

    // ============================================================
    // LISTE
    // ============================================================

    public function index(Request $request): Response
    {
        $filters = [
            'search'      => trim((string) $request->get('search', '')),
            'type'        => (string) $request->get('type', ''),
            'entity_type' => (string) $request->get('entity_type', ''),
            'is_signed'   => $request->get('is_signed', ''),
        ];

        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 15;

        $paginated = $this->repo->paginate(array_filter($filters, fn($v) => $v !== ''), $page, $perPage);
        $stats     = $this->repo->countByType();

        return $this->view('documents/index', [
            'title'       => 'Documents',
            'paginated'   => $paginated,
            'filters'     => $filters,
            'stats'       => $stats,
            'totalAll'    => $this->repo->countAll(),
            'totalSize'   => $this->repo->totalSize(),
        ]);
    }

    // ============================================================
    // CRÉATION
    // ============================================================

    public function create(Request $request): Response
    {
        return $this->view('documents/create', [
            'title'      => 'Nouveau document',
            'errors'     => [],
            'old'        => [],
            'entityType' => (string) $request->get('entity_type', ''),
            'entityId'   => (int) $request->get('entity_id', 0),
        ]);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $data = [
            'title'       => trim((string) $request->post('title', '')),
            'type'        => (string) $request->post('type', 'other'),
            'entity_type' => trim((string) $request->post('entity_type', '')) ?: null,
            'entity_id'   => (int) $request->post('entity_id', 0) ?: null,
        ];

        $file = $_FILES['file'] ?? null;

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'Vous devez sélectionner un fichier.');
            return $this->view('documents/create', [
                'title'      => 'Nouveau document',
                'errors'     => [],
                'old'        => $data,
                'entityType' => $data['entity_type'] ?? '',
                'entityId'   => $data['entity_id'] ?? 0,
            ]);
        }

        try {
            $id = $this->service->create($data, $file, (int) auth_id());
            flash('success', 'Document ajouté avec succès.');
            return $this->redirect(url("documents/{$id}"));
        } catch (ValidationException | \RuntimeException $e) {
            return $this->view('documents/create', [
                'title'      => 'Nouveau document',
                'errors'     => ['global' => $e->getMessage()],
                'old'        => $data,
                'entityType' => $data['entity_type'] ?? '',
                'entityId'   => $data['entity_id'] ?? 0,
            ]);
        }
    }

    // ============================================================
    // AFFICHAGE
    // ============================================================

    public function show(Request $request, string $id): Response
    {
        $id = (int) $id;
        $document = $this->repo->findById($id);
        if (!$document) {
            throw new NotFoundException('Document introuvable.');
        }

        return $this->view('documents/show', [
            'title'    => 'Document ' . ($document->reference ?? '#' . $id),
            'document' => $document,
            'versions' => $this->repo->findVersions($id),
        ]);
    }

    // ============================================================
    // ÉDITION
    // ============================================================

    public function edit(Request $request, string $id): Response
    {
        $id = (int) $id;
        $document = $this->repo->findById($id);
        if (!$document) {
            throw new NotFoundException('Document introuvable.');
        }

        return $this->view('documents/edit', [
            'title'    => 'Modifier ' . ($document->reference ?? '#' . $id),
            'document' => $document,
            'errors'   => [],
            'old'      => [],
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $id = (int) $id;
        $data = [
            'title'       => trim((string) $request->post('title', '')),
            'type'        => (string) $request->post('type', 'other'),
            'entity_type' => trim((string) $request->post('entity_type', '')) ?: null,
            'entity_id'   => (int) $request->post('entity_id', 0) ?: null,
            'is_signed'   => (int) $request->post('is_signed', 0),
        ];

        try {
            $this->service->update($id, $data);
            flash('success', 'Document mis à jour.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("documents/{$id}"));
    }

    // ============================================================
    // SUPPRESSION
    // ============================================================

    public function destroy(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        try {
            $this->service->delete((int) $id);
            flash('success', 'Document supprimé.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url('documents'));
    }

    // ============================================================
    // NOUVELLE VERSION
    // ============================================================

    public function addVersion(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $id   = (int) $id;
        $file = $_FILES['file'] ?? null;
        $notes = trim((string) $request->post('change_notes', ''));

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'Vous devez sélectionner un fichier.');
            return $this->redirect(url("documents/{$id}"));
        }

        try {
            $this->service->addVersion($id, $file, $notes ?: null, (int) auth_id());
            flash('success', 'Nouvelle version ajoutée.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("documents/{$id}"));
    }

    public function restoreVersion(Request $request, string $id, string $versionId): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        try {
            $this->service->restoreVersion((int) $id, (int) $versionId, (int) auth_id());
            flash('success', 'Version restaurée.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("documents/{$id}"));
    }

    // ============================================================
    // SIGNATURE
    // ============================================================

    public function toggleSigned(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        try {
            $this->service->toggleSigned((int) $id);
            flash('success', 'Statut de signature mis à jour.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("documents/{$id}"));
    }

    // ============================================================
    // TÉLÉCHARGEMENT
    // ============================================================

    public function download(Request $request, string $id): Response
    {
        $document = $this->repo->findById((int) $id);
        if (!$document) {
            throw new NotFoundException('Document introuvable.');
        }

        $fullPath = $this->storage->getFullPath($document->filePath);
        if (!$this->storage->exists($document->filePath)) {
            throw new NotFoundException('Fichier introuvable sur le disque.');
        }

        $downloadName = $this->sanitizeFileName($document->title) . '.' . $document->getExtension();

        return $this->downloadFile($fullPath, $downloadName);
    }

    // ============================================================
    // PRÉVISUALISATION (inline — pas de téléchargement)
    // ============================================================

    public function preview(Request $request, string $id): Response
    {
        $document = $this->repo->findById((int) $id);
        if (!$document) {
            throw new NotFoundException('Document introuvable.');
        }

        if (!$document->canPreview()) {
            throw new NotFoundException('Ce document ne peut pas être prévisualisé.');
        }

        $fullPath = $this->storage->getFullPath($document->filePath);
        if (!$this->storage->exists($document->filePath)) {
            throw new NotFoundException('Fichier introuvable.');
        }

        $mime = $document->mimeType ?? 'application/octet-stream';

        // Envoi inline
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($document->filePath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: private, max-age=3600');

        readfile($fullPath);
        exit;
    }

    // ============================================================
    // ACTIONS GROUPÉES
    // ============================================================

    /**
     * Télécharge plusieurs documents en une archive ZIP.
     */
    public function bulkDownload(Request $request): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $ids = $request->post('ids', []);
        if (!is_array($ids) || empty($ids)) {
            flash('error', 'Aucun document sélectionné.');
            return $this->redirect(url('documents'));
        }

        $documents = $this->repo->findByIds($ids);
        if (empty($documents)) {
            flash('error', 'Aucun document trouvé.');
            return $this->redirect(url('documents'));
        }

        // Créer le ZIP dans un fichier temporaire
        $tmpZip = tempnam(sys_get_temp_dir(), 'docs_') . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            flash('error', 'Impossible de créer l\'archive ZIP.');
            return $this->redirect(url('documents'));
        }

        $added = 0;
        foreach ($documents as $doc) {
            $fullPath = $this->storage->getFullPath($doc->filePath);
            if (!is_file($fullPath)) {
                continue;
            }

            // Nom du fichier dans le ZIP
            $downloadName = $this->sanitizeFileName($doc->title) . '.' . $doc->getExtension();

            // Éviter les doublons de nom dans le ZIP
            $finalName = $downloadName;
            $counter = 1;
            while ($zip->locateName($finalName) !== false) {
                $finalName = pathinfo($downloadName, PATHINFO_FILENAME)
                           . '_' . $counter++ . '.'
                           . pathinfo($downloadName, PATHINFO_EXTENSION);
            }

            $zip->addFile($fullPath, $finalName);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tmpZip);
            flash('error', 'Aucun fichier valide à compresser.');
            return $this->redirect(url('documents'));
        }

        // Envoi du ZIP
        $zipName = 'documents_' . date('Ymd_His') . '.zip';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($tmpZip));
        header('Cache-Control: private, max-age=0, must-revalidate');

        readfile($tmpZip);
        @unlink($tmpZip);
        exit;
    }

    /**
     * Supprime plusieurs documents d'un coup.
     */
    public function bulkDelete(Request $request): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $ids = $request->post('ids', []);
        if (!is_array($ids) || empty($ids)) {
            flash('error', 'Aucun document sélectionné.');
            return $this->redirect(url('documents'));
        }

        try {
            $count = $this->service->bulkDelete($ids);
            flash('success', $count . ' document(s) supprimé(s).');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect(url('documents'));
    }

    // ============================================================
    // HELPERS PRIVÉS
    // ============================================================

    private function guardCsrf(): ?Response
    {
        return (new CsrfMiddleware())->handle();
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9_\- ]/', '_', $name);
        $name = trim(preg_replace('/\s+/', '_', $name));
        return $name === '' ? 'document' : $name;
    }
}