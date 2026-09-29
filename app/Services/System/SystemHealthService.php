<?php
declare(strict_types=1);

namespace App\Services\System;

/**
 * Service de surveillance de la santé du système.
 *
 * 🎯 RÔLE :
 *    Vérifie l'état de santé de l'application : version PHP, extensions,
 *    espace disque, permissions dossiers, mémoire, et fournit des
 *    recommandations automatiques.
 *
 * 🛠️ UTILISATION :
 *    Appelé par le contrôleur Admin\SystemController (page "Santé Système").
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Les vérifications sont en LECTURE SEULE (aucune modification)
 *    - Peut être exécuté régulièrement sans risque
 *
 * 💡 BONNES PRATIQUES :
 *    - Vérifier la santé du système 1 fois par mois
 *    - Surveiller l'espace disque (alerte si < 1 Go)
 *    - Vérifier les permissions des dossiers sensibles
 */
final class SystemHealthService
{
    /**
     * Taille minimum d'espace disque recommandée (1 Go).
     */
    private const MIN_DISK_SPACE = 1073741824;

    /**
     * Version minimum de PHP recommandée.
     */
    private const MIN_PHP_VERSION = '8.1.0';

    /**
     * Extensions PHP requises par l'application.
     */
    private const REQUIRED_EXTENSIONS = [
        'pdo',
        'pdo_mysql',
        'mbstring',
        'json',
        'openssl',
        'curl',
        'fileinfo',
        'zip',
    ];

    /**
     * Extensions PHP optionnelles mais recommandées.
     */
    private const RECOMMENDED_EXTENSIONS = [
        'gd',
        'intl',
        'sodium',
        'imagick',
    ];

    private string $basePath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 3);
    }

    // ============================================
    // INFORMATIONS SYSTÈME
    // ============================================

    /**
     * Récupère les informations générales du système.
     *
     * @return array{
     *     php_version: string,
     *     php_version_ok: bool,
     *     os: string,
     *     server_software: string,
     *     memory_limit: string,
     *     memory_usage: string,
     *     max_execution_time: int,
     *     upload_max_filesize: string,
     *     post_max_size: string,
     *     timezone: string,
     *     app_debug: bool,
     *     app_env: string
     * }
     */
    public function getSystemInfo(): array
    {
        $phpVersion = PHP_VERSION;
        $phpVersionOk = version_compare($phpVersion, self::MIN_PHP_VERSION, '>=');

        // Mémoire
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return [
            'php_version'         => $phpVersion,
            'php_version_ok'      => $phpVersionOk,
            'php_version_min'     => self::MIN_PHP_VERSION,
            'os'                  => PHP_OS . ' (' . php_uname('r') . ')',
            'server_software'     => $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu',
            'memory_limit'        => $memoryLimit,
            'memory_usage'        => $this->formatSize($memoryUsage),
            'memory_peak'         => $this->formatSize($memoryPeak),
            'max_execution_time'  => (int) ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size'       => ini_get('post_max_size'),
            'timezone'            => date_default_timezone_get(),
            'app_debug'           => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'app_env'             => $_ENV['APP_ENV'] ?? 'production',
        ];
    }

    // ============================================
    // ESPACE DISQUE
    // ============================================

    /**
     * Récupère les informations d'espace disque.
     *
     * @return array{
     *     total: int,
     *     free: int,
     *     used: int,
     *     total_formatted: string,
     *     free_formatted: string,
     *     used_formatted: string,
     *     used_percent: float,
     *     alert: bool
     * }
     */
    public function getDiskSpace(): array
    {
        $total = disk_total_space($this->basePath);
        $free  = disk_free_space($this->basePath);

        $used = $total - $free;
        $usedPercent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'total'           => (int) $total,
            'free'            => (int) $free,
            'used'            => (int) $used,
            'total_formatted' => $this->formatSize((int) $total),
            'free_formatted'  => $this->formatSize((int) $free),
            'used_formatted'  => $this->formatSize((int) $used),
            'used_percent'    => $usedPercent,
            'alert'           => $free < self::MIN_DISK_SPACE,
        ];
    }

    // ============================================
    // PERMISSIONS DOSSIERS
    // ============================================

    /**
     * Vérifie les permissions des dossiers sensibles.
     *
     * @return array<int, array{path: string, exists: bool, writable: bool, status: string}>
     */
    public function getPermissions(): array
    {
        $paths = [
            ['path' => 'storage',                    'name' => 'Storage (général)'],
            ['path' => 'storage/backups',            'name' => 'Storage/backups'],
            ['path' => 'storage/backups/database',   'name' => 'Storage/backups/database'],
            ['path' => 'storage/logs',               'name' => 'Storage/logs'],
            ['path' => 'storage/cache',              'name' => 'Storage/cache'],
            ['path' => 'storage/sessions',           'name' => 'Storage/sessions'],
            ['path' => 'storage/attachments',        'name' => 'Storage/attachments'],
            ['path' => 'public/uploads',             'name' => 'Public/uploads'],
        ];

        $results = [];

        foreach ($paths as $item) {
            $fullPath = $this->basePath . '/' . $item['path'];
            $exists   = is_dir($fullPath);
            $writable = $exists && is_writable($fullPath);

            $results[] = [
                'path'     => $item['path'],
                'name'     => $item['name'],
                'exists'   => $exists,
                'writable' => $writable,
                'status'   => !$exists ? 'missing' : ($writable ? 'ok' : 'not_writable'),
            ];
        }

        return $results;
    }

    // ============================================
    // EXTENSIONS PHP
    // ============================================

    /**
     * Vérifie les extensions PHP requises et recommandées.
     *
     * @return array{
     *     required: array<int, array{name: string, loaded: bool}>,
     *     recommended: array<int, array{name: string, loaded: bool}>,
     *     missing_required: array<int, string>
     * }
     */
    public function getExtensions(): array
    {
        $required = [];
        $recommended = [];
        $missingRequired = [];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $loaded = extension_loaded($ext);
            $required[] = ['name' => $ext, 'loaded' => $loaded];

            if (!$loaded) {
                $missingRequired[] = $ext;
            }
        }

        foreach (self::RECOMMENDED_EXTENSIONS as $ext) {
            $recommended[] = ['name' => $ext, 'loaded' => extension_loaded($ext)];
        }

        return [
            'required'         => $required,
            'recommended'      => $recommended,
            'missing_required' => $missingRequired,
        ];
    }

    // ============================================
    // TAILLE DES DOSSIERS
    // ============================================

    /**
     * Calcule la taille des dossiers principaux.
     *
     * @return array<string, array{size: int, formatted: string, file_count: int}>
     */
    public function getFolderSizes(): array
    {
        $folders = [
            'storage/backups'     => 'Sauvegardes',
            'storage/logs'        => 'Logs',
            'storage/cache'       => 'Cache',
            'storage/attachments' => 'Pièces jointes',
            'storage/exports'     => 'Exports',
            'storage/qrcodes'     => 'QR Codes',
        ];

        $results = [];

        foreach ($folders as $path => $label) {
            $fullPath = $this->basePath . '/' . $path;

            if (!is_dir($fullPath)) {
                $results[$label] = [
                    'size'       => 0,
                    'formatted'  => '0 o',
                    'file_count' => 0,
                ];
                continue;
            }

            $size = 0;
            $count = 0;

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
                // Ignore les erreurs d'accès
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
    // RECOMMANDATIONS
    // ============================================

    /**
     * Génère des recommandations automatiques.
     *
     * @return array<int, array{level: string, title: string, message: string}>
     */
    public function getRecommendations(): array
    {
        $recommendations = [];

        // 1. Version PHP
        $info = $this->getSystemInfo();
        if (!$info['php_version_ok']) {
            $recommendations[] = [
                'level'   => 'warning',
                'title'   => 'Version PHP obsolète',
                'message' => 'Votre version PHP (' . $info['php_version'] . ') est inférieure à la version recommandée (' . $info['php_version_min'] . '). Pensez à mettre à jour.',
            ];
        }

        // 2. Espace disque
        $disk = $this->getDiskSpace();
        if ($disk['alert']) {
            $recommendations[] = [
                'level'   => 'danger',
                'title'   => 'Espace disque faible',
                'message' => 'Il ne reste que ' . $disk['free_formatted'] . ' d\'espace disque. Libérez de l\'espace ou augmentez la capacité du serveur.',
            ];
        } elseif ($disk['used_percent'] > 80) {
            $recommendations[] = [
                'level'   => 'warning',
                'title'   => 'Espace disque bientôt plein',
                'message' => 'Le disque est utilisé à ' . $disk['used_percent'] . '%. Surveillez l\'espace disponible.',
            ];
        }

        // 3. Extensions manquantes
        $ext = $this->getExtensions();
        if (!empty($ext['missing_required'])) {
            $recommendations[] = [
                'level'   => 'danger',
                'title'   => 'Extensions PHP manquantes',
                'message' => 'Extensions requises non chargées : ' . implode(', ', $ext['missing_required']) . '. Contactez votre hébergeur.',
            ];
        }

        // 4. Permissions dossiers
        $perms = $this->getPermissions();
        $notWritable = array_filter($perms, fn($p) => $p['status'] !== 'ok');

        if (!empty($notWritable)) {
            $paths = array_column($notWritable, 'path');
            $recommendations[] = [
                'level'   => 'warning',
                'title'   => 'Permissions dossiers',
                'message' => 'Les dossiers suivants ne sont pas accessibles en écriture : ' . implode(', ', $paths),
            ];
        }

        // 5. Mode debug en production
        if ($info['app_env'] === 'production' && $info['app_debug'] === true) {
            $recommendations[] = [
                'level'   => 'danger',
                'title'   => 'Mode debug activé en production',
                'message' => 'APP_DEBUG est activé alors que APP_ENV est défini sur "production". Désactivez le debug pour la sécurité.',
            ];
        }

        return $recommendations;
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Formate une taille en octets en format lisible.
     */
    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' Ko';
        return $bytes . ' o';
    }
}