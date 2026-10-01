<?php
declare(strict_types=1);

namespace App\Services\Document;

use RuntimeException;

final class DocumentStorageService
{
    /** Dossier de stockage (hors web root) */
    private const STORAGE_DIR = 'documents';

    /** Taille max en octets (50 Mo) */
    public const MAX_SIZE = 50 * 1024 * 1024;

    /** Types MIME autorisés */
    public const ALLOWED_MIMES = [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        // Archives
        'application/zip',
        'application/x-zip-compressed',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/gzip',
    ];

    /** Extensions autorisées (sécurité supplémentaire) */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp',
        'zip', 'rar', '7z', 'gz',
    ];

    public function __construct(
        private readonly string $projectRoot = '',
    ) {}

    // ============================================================
    // VALIDATION
    // ============================================================

    /**
     * Valide un fichier uploadé. Lance une exception si invalide.
     */
    public function validate(array $file): void
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Paramètres de fichier invalides.');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Le fichier dépasse la taille maximale autorisée (50 Mo).');
            case UPLOAD_ERR_PARTIAL:
                throw new RuntimeException('L\'upload a été interrompu.');
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Aucun fichier n\'a été envoyé.');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new RuntimeException('Dossier temporaire manquant.');
            case UPLOAD_ERR_CANT_WRITE:
                throw new RuntimeException('Impossible d\'écrire le fichier sur le disque.');
            default:
                throw new RuntimeException('Erreur inconnue lors de l\'upload.');
        }

        if (($file['size'] ?? 0) > self::MAX_SIZE) {
            throw new RuntimeException('Le fichier dépasse 50 Mo.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException("Extension .{$ext} non autorisée.");
        }

        // Vérifie le MIME réel
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if ($realMime === false) {
            throw new RuntimeException('Impossible de détecter le type MIME.');
        }

        if (!in_array($realMime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException("Type MIME {$realMime} non autorisé.");
        }
    }

    // ============================================================
    // STOCKAGE
    // ============================================================

    /**
     * Enregistre un fichier uploadé. Retourne le chemin relatif
     * (à stocker dans documents.file_path).
     */
    public function store(array $file, ?string $subDir = null): string
    {
        $this->validate($file);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uuid     = bin2hex(random_bytes(8));
        $fileName = date('Ymd_His') . '_' . $uuid . '.' . $ext;

        // Sous-dossier (optionnel)
        $subPath = $subDir !== null ? '/' . trim($subDir, '/') : '';

        // Chemin relatif final
        $relativePath = self::STORAGE_DIR . $subPath . '/' . $fileName;

        // Chemin absolu
        $fullPath = $this->projectRoot() . '/' . 'storage' . '/' . $relativePath;
        $dir      = dirname($fullPath);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new RuntimeException("Impossible de créer le dossier : {$dir}");
            }
        }

        if (!is_writable($dir)) {
            throw new RuntimeException("Le dossier n'est pas accessible en écriture : {$dir}");
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new RuntimeException('Échec de l\'enregistrement du fichier.');
        }

        return $relativePath;
    }

    /**
     * Supprime un fichier du disque.
     */
    public function delete(string $relativePath): bool
    {
        $fullPath = $this->getFullPath($relativePath);

        if (!is_file($fullPath)) {
            return false;
        }

        return @unlink($fullPath);
    }

    /**
     * Retourne le chemin absolu complet.
     */
    public function getFullPath(string $relativePath): string
    {
        return $this->projectRoot() . '/' . 'storage' . '/' . ltrim($relativePath, '/\\');
    }

    /**
     * Vérifie que le fichier existe sur le disque.
     */
    public function exists(string $relativePath): bool
    {
        return is_file($this->getFullPath($relativePath));
    }

    /**
     * Retourne la taille réelle du fichier en octets.
     */
    public function size(string $relativePath): int
    {
        $fullPath = $this->getFullPath($relativePath);
        return is_file($fullPath) ? (int) filesize($fullPath) : 0;
    }

    /**
     * Détecte le MIME d'un fichier déjà stocké.
     */
    public function mimeType(string $relativePath): ?string
    {
        $fullPath = $this->getFullPath($relativePath);
        if (!is_file($fullPath)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($fullPath);

        return $mime === false ? null : $mime;
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function projectRoot(): string
    {
        return $this->projectRoot !== ''
            ? $this->projectRoot
            : dirname(__DIR__, 3);
    }
}