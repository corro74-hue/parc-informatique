<?php
declare(strict_types=1);

namespace App\Services\System;

use App\Core\Database;
use PDO;

/**
 * Service de nettoyage du cache et des fichiers temporaires.
 *
 * 🎯 RÔLE :
 *    Permet de vider les caches applicatifs, les logs, les sessions expirées
 *    et les fichiers temporaires pour libérer de l'espace disque et améliorer
 *    les performances.
 *
 * 🛠️ UTILISATION :
 *    Appelé par Admin\SystemController (actions de nettoyage manuel).
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Le nettoyage des sessions déconnecte les utilisateurs inactifs
 *    - Le nettoyage des logs est irréversible
 *    - Toujours vérifier la taille AVANT de supprimer
 *
 * 💡 BONNES PRATIQUES :
 *    - Nettoyer le cache tous les 15 jours
 *    - Purger les logs tous les 3 mois
 *    - Surveiller la taille des dossiers régulièrement
 */
final class CacheService
{
    /**
     * Âge maximum des logs (en jours) avant purge.
     */
    private const LOGS_MAX_AGE_DAYS = 90;

    /**
     * Âge maximum des sessions (en secondes) — 7 jours.
     */
    private const SESSIONS_MAX_AGE = 604800;

    private string $basePath;
    private PDO $db;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 3);
        $this->db = Database::getInstance();
    }

    // ============================================
    // NETTOYAGE DU CACHE
    // ============================================

    /**
     * Vide les caches applicatifs.
     *
     * @return array{deleted_files: int, freed_bytes: int, freed_formatted: string}
     */
    public function clearCache(): array
    {
        $cacheDir = $this->basePath . '/storage/cache';

        return $this->clearDirectory($cacheDir, true, 'cache');
    }

    // ============================================
    // NETTOYAGE DES LOGS
    // ============================================

    /**
     * Purge les vieux logs (plus de 90 jours).
     *
     * @param int $maxAgeDays Âge maximum en jours (par défaut 90)
     * @return array{deleted_files: int, freed_bytes: int, freed_formatted: string}
     */
    public function clearLogs(int $maxAgeDays = self::LOGS_MAX_AGE_DAYS): array
    {
        $logsDir = $this->basePath . '/storage/logs';

        return $this->purgeOldFiles($logsDir, $maxAgeDays, 'logs');
    }

    // ============================================
    // NETTOYAGE DES SESSIONS EXPIRÉES
    // ============================================

    /**
     * Supprime les sessions expirées (fichiers + BDD).
     *
     * @return array{deleted_files: int, deleted_db_rows: int, freed_bytes: int, freed_formatted: string}
     */
    public function clearSessions(): array
    {
        // 1. Nettoyage des fichiers de session PHP
        $sessionsDir = $this->basePath . '/storage/sessions';
        $result = $this->purgeOldFiles($sessionsDir, 7, 'sessions');

        // 2. Nettoyage de la table sessions (sessions en BDD)
        $deletedDbRows = 0;
        try {
            $stmt = $this->db->prepare(
                'DELETE FROM sessions WHERE last_activity < :cutoff'
            );
            $stmt->execute(['cutoff' => time() - self::SESSIONS_MAX_AGE]);
            $deletedDbRows = $stmt->rowCount();
        } catch (\Throwable $e) {
            // Silencieux
        }

        $this->logAction('cache_cleared', 'success', 'Sessions nettoyées', [
            'files'      => $result['deleted_files'],
            'db_rows'    => $deletedDbRows,
            'freed'      => $result['freed_formatted'],
        ]);

        return [
            'deleted_files'     => $result['deleted_files'],
            'deleted_db_rows'   => $deletedDbRows,
            'freed_bytes'       => $result['freed_bytes'],
            'freed_formatted'   => $result['freed_formatted'],
        ];
    }

    // ============================================
    // NETTOYAGE COMPLET
    // ============================================

    /**
     * Effectue un nettoyage complet (cache + logs + sessions).
     *
     * @return array{
     *     cache: array,
     *     logs: array,
     *     sessions: array,
     *     total_freed: int,
     *     total_freed_formatted: string
     * }
     */
    public function clearAll(): array
    {
        $cache    = $this->clearCache();
        $logs     = $this->clearLogs();
        $sessions = $this->clearSessions();

        $totalFreed = $cache['freed_bytes'] + $logs['freed_bytes'] + $sessions['freed_bytes'];

        $this->logAction('cache_cleared', 'success', 'Nettoyage complet effectué', [
            'cache'    => $cache,
            'logs'     => $logs,
            'sessions' => $sessions,
        ]);

        return [
            'cache'                 => $cache,
            'logs'                  => $logs,
            'sessions'              => $sessions,
            'total_freed'           => $totalFreed,
            'total_freed_formatted' => $this->formatSize($totalFreed),
        ];
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    /**
     * Récupère la taille actuelle des dossiers de cache/logs/sessions.
     *
     * @return array<string, array{size: int, formatted: string, file_count: int}>
     */
    public function getCacheStats(): array
    {
        $folders = [
            'Cache'    => 'storage/cache',
            'Logs'     => 'storage/logs',
            'Sessions' => 'storage/sessions',
        ];

        $results = [];

        foreach ($folders as $label => $path) {
            $fullPath = $this->basePath . '/' . $path;
            $size = 0;
            $count = 0;

            if (is_dir($fullPath)) {
                try {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($fullPath, \FilesystemIterator::SKIP_DOTS)
                    );

                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $size += $file->getSize();
                            $count++;
                        }
                    }
                } catch (\Throwable $e) {
                    // Silencieux
                }
            }

            $results[$label] = [
                'size'       => $size,
                'formatted'  => $this->formatSize($size),
                'file_count' => $count,
            ];
        }

        return $results;
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    /**
     * Vide complètement un dossier (sans le supprimer).
     */
    private function clearDirectory(string $dir, bool $keepDir = true, string $context = ''): array
    {
        $deletedFiles = 0;
        $freedBytes = 0;

        if (!is_dir($dir)) {
            return [
                'deleted_files'   => 0,
                'freed_bytes'     => 0,
                'freed_formatted' => '0 o',
            ];
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size = $file->getSize();
                    if (@unlink($file->getPathname())) {
                        $deletedFiles++;
                        $freedBytes += $size;
                    }
                } elseif ($file->isDir() && !$keepDir) {
                    @rmdir($file->getPathname());
                }
            }

            // Supprimer les sous-dossiers vides si on ne garde pas le dossier
            if (!$keepDir) {
                // Sous-dossiers déjà supprimés par CHILD_FIRST
            } else {
                // Nettoyer les sous-dossiers vides
                $this->removeEmptySubdirs($dir);
            }
        } catch (\Throwable $e) {
            $this->logAction('cache_cleared', 'error', 'Erreur nettoyage ' . $context . ' : ' . $e->getMessage());
        }

        if ($deletedFiles > 0) {
            $this->logAction('cache_cleared', 'success', $context . ' nettoyé', [
                'files' => $deletedFiles,
                'freed' => $this->formatSize($freedBytes),
            ]);
        }

        return [
            'deleted_files'   => $deletedFiles,
            'freed_bytes'     => $freedBytes,
            'freed_formatted' => $this->formatSize($freedBytes),
        ];
    }

    /**
     * Supprime les fichiers plus vieux que N jours.
     */
    private function purgeOldFiles(string $dir, int $maxAgeDays, string $context = ''): array
    {
        $deletedFiles = 0;
        $freedBytes = 0;

        if (!is_dir($dir)) {
            return [
                'deleted_files'   => 0,
                'freed_bytes'     => 0,
                'freed_formatted' => '0 o',
            ];
        }

        $cutoff = time() - ($maxAgeDays * 86400);

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) continue;

                if ($file->getMTime() < $cutoff) {
                    $size = $file->getSize();
                    if (@unlink($file->getPathname())) {
                        $deletedFiles++;
                        $freedBytes += $size;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logAction('cache_cleared', 'error', 'Erreur purge ' . $context . ' : ' . $e->getMessage());
        }

        return [
            'deleted_files'   => $deletedFiles,
            'freed_bytes'     => $freedBytes,
            'freed_formatted' => $this->formatSize($freedBytes),
        ];
    }

    /**
     * Supprime les sous-dossiers vides.
     */
    private function removeEmptySubdirs(string $dir): void
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getPathname()); // rmdir échoue silencieusement si non vide
                }
            }
        } catch (\Throwable $e) {
            // Silencieux
        }
    }

    /**
     * Log une action dans system_logs.
     */
    private function logAction(string $action, string $status, string $message, array $details = []): void
    {
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO system_logs (action, status, message, details, user_id, ip_address, created_at)
                 VALUES (:action, :status, :message, :details, :user_id, :ip, NOW())'
            );

            $stmt->execute([
                'action'  => $action,
                'status'  => $status,
                'message' => $message,
                'details' => !empty($details) ? json_encode($details) : null,
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Silencieux
        }
    }

    /**
     * Formate une taille en octets.
     */
    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' Ko';
        return $bytes . ' o';
    }
}