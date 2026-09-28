<?php
declare(strict_types=1);

namespace App\Services\Database;

use App\Core\Database;
use PDO;

/**
 * Service de monitoring et de maintenance de la base de données.
 *
 * 🎯 RÔLE :
 *    Surveille l'état de santé de la base de données : taille, nombre de tables,
 *    intégrité, performances, et fournit des recommandations d'optimisation.
 *
 * 🛠️ UTILISATION :
 *    Appelé par le contrôleur Admin\DatabaseController (page "Santé BDD").
 *
 * ⚠️ PRÉCAUTIONS :
 *    - L'optimisation (OPTIMIZE TABLE) peut prendre du temps sur une grosse BDD
 *    - À exécuter de préférence en période de faible activité
 *
 * 💡 BONNES PRATIQUES :
 *    - Vérifier l'intégrité tous les mois (CHECK TABLE)
 *    - Optimiser les tables tous les 3 mois
 *    - Surveiller la taille pour anticiper les problèmes de disque
 */
final class DatabaseHealthService
{
    private PDO $db;
    private string $database;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $config = require dirname(__DIR__, 3) . '/config/database.php';
        $this->database = $config['database'] ?? 'parc_informatique';
    }

    // ============================================
    // INFORMATIONS GÉNÉRALES
    // ============================================

    /**
     * Récupère les informations générales de la base de données.
     *
     * @return array{
     *     size_bytes: int,
     *     size_formatted: string,
     *     table_count: int,
     *     version: string,
     *     charset: string,
     *     collation: string,
     *     uptime_seconds: int,
     *     uptime_formatted: string
     * }
     */
    public function getGeneralInfo(): array
    {
        // Taille totale
        $stmt = $this->db->prepare(
            'SELECT 
                COALESCE(SUM(data_length + index_length), 0) AS size_bytes,
                COUNT(*) AS table_count
             FROM information_schema.TABLES
             WHERE table_schema = :db'
        );
        $stmt->execute(['db' => $this->database]);
        $sizeInfo = $stmt->fetch();

        // Version MySQL
        $version = $this->db->query('SELECT VERSION()')->fetchColumn();

        // Charset et collation
        $stmt = $this->db->prepare(
            'SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
             FROM information_schema.SCHEMATA
             WHERE SCHEMA_NAME = :db'
        );
        $stmt->execute(['db' => $this->database]);
        $charsetInfo = $stmt->fetch();

        // Uptime
        $uptime = (int) $this->db->query('SHOW GLOBAL STATUS LIKE "Uptime"')->fetch(PDO::FETCH_ASSOC)['Value'];

        return [
            'size_bytes'       => (int) $sizeInfo['size_bytes'],
            'size_formatted'   => $this->formatSize((int) $sizeInfo['size_bytes']),
            'table_count'      => (int) $sizeInfo['table_count'],
            'version'          => (string) $version,
            'charset'          => $charsetInfo['DEFAULT_CHARACTER_SET_NAME'] ?? 'utf8mb4',
            'collation'        => $charsetInfo['DEFAULT_COLLATION_NAME'] ?? 'utf8mb4_unicode_ci',
            'uptime_seconds'   => $uptime,
            'uptime_formatted' => $this->formatDuration($uptime),
        ];
    }

    // ============================================
    // INFORMATIONS PAR TABLE
    // ============================================

    /**
     * Récupère les informations détaillées de chaque table.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTablesInfo(): array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                TABLE_NAME AS table_name,
                ENGINE AS engine,
                TABLE_ROWS AS row_count,
                ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024, 2) AS size_kb,
                ROUND(DATA_LENGTH / 1024, 2) AS data_kb,
                ROUND(INDEX_LENGTH / 1024, 2) AS index_kb,
                TABLE_COLLATION AS collation,
                CREATE_TIME AS created_at,
                UPDATE_TIME AS updated_at
             FROM information_schema.TABLES
             WHERE table_schema = :db
             ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC'
        );
        $stmt->execute(['db' => $this->database]);

        return $stmt->fetchAll();
    }

    // ============================================
    // VÉRIFICATION D'INTÉGRITÉ
    // ============================================

    /**
     * Vérifie l'intégrité de toutes les tables (CHECK TABLE).
     *
     * @return array{total: int, ok: int, warnings: int, errors: int, details: array}
     */
    public function checkIntegrity(): array
    {
        $tables = $this->getTablesInfo();

        $total = 0;
        $ok = 0;
        $warnings = 0;
        $errors = 0;
        $details = [];

        foreach ($tables as $table) {
            $tableName = $table['table_name'];
            $total++;

            try {
                $stmt = $this->db->query("CHECK TABLE `{$tableName}`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $msgType = $result['Msg_type'] ?? 'error';
                $msgText = $result['Msg_text'] ?? '';

                if ($msgType === 'status' && stripos($msgText, 'OK') !== false) {
                    $ok++;
                    $details[] = [
                        'table'   => $tableName,
                        'status'  => 'ok',
                        'message' => $msgText,
                    ];
                } elseif ($msgType === 'warning') {
                    $warnings++;
                    $details[] = [
                        'table'   => $tableName,
                        'status'  => 'warning',
                        'message' => $msgText,
                    ];
                } else {
                    $errors++;
                    $details[] = [
                        'table'   => $tableName,
                        'status'  => 'error',
                        'message' => $msgText,
                    ];
                }
            } catch (\Throwable $e) {
                $errors++;
                $details[] = [
                    'table'   => $tableName,
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'total'    => $total,
            'ok'       => $ok,
            'warnings' => $warnings,
            'errors'   => $errors,
            'details'  => $details,
        ];
    }

    // ============================================
    // OPTIMISATION
    // ============================================

    /**
     * Optimise toutes les tables (OPTIMIZE TABLE).
     *
     * ⚠️ Cette opération peut prendre plusieurs secondes/minutes.
     *
     * @return array{total: int, optimized: int, failed: int, freed_bytes: int, details: array}
     */
    public function optimizeTables(): array
    {
        $tables = $this->getTablesInfo();

        $total = 0;
        $optimized = 0;
        $failed = 0;
        $freedBytes = 0;
        $details = [];

        // Taille avant optimisation
        $sizeBefore = (int) $this->getGeneralInfo()['size_bytes'];

        foreach ($tables as $table) {
            $tableName = $table['table_name'];
            $total++;

            try {
                $stmt = $this->db->query("OPTIMIZE TABLE `{$tableName}`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($result)) {
                    $optimized++;
                    $details[] = [
                        'table'   => $tableName,
                        'status'  => 'ok',
                        'message' => $result['Msg_text'] ?? 'Optimisée',
                    ];
                }
            } catch (\Throwable $e) {
                $failed++;
                $details[] = [
                    'table'   => $tableName,
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Taille après optimisation
        $sizeAfter = (int) $this->getGeneralInfo()['size_bytes'];
        $freedBytes = max(0, $sizeBefore - $sizeAfter);

        return [
            'total'       => $total,
            'optimized'   => $optimized,
            'failed'      => $failed,
            'freed_bytes' => $freedBytes,
            'details'     => $details,
        ];
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    /**
     * Récupère les statistiques globales de la BDD.
     *
     * @return array{
     *     tables: int,
     *     total_rows: int,
     *     total_size: int,
     *     avg_row_size: int,
     *     biggest_table: ?array,
     *     engine_breakdown: array
     * }
     */
    public function getStatistics(): array
    {
        $tables = $this->getTablesInfo();

        $totalRows = 0;
        $totalSize = 0;
        $biggest = null;
        $engines = [];

        foreach ($tables as $table) {
            $totalRows += (int) $table['row_count'];
            $sizeBytes = ((float) $table['size_kb']) * 1024;
            $totalSize += $sizeBytes;

            if ($biggest === null || $sizeBytes > ((float) $biggest['size_kb']) * 1024) {
                $biggest = $table;
            }

            $engine = $table['engine'] ?? 'Unknown';
            if (!isset($engines[$engine])) {
                $engines[$engine] = 0;
            }
            $engines[$engine]++;
        }

        return [
            'tables'           => count($tables),
            'total_rows'       => $totalRows,
            'total_size'       => (int) $totalSize,
            'avg_row_size'     => $totalRows > 0 ? (int) ($totalSize / $totalRows) : 0,
            'biggest_table'    => $biggest,
            'engine_breakdown' => $engines,
        ];
    }

    // ============================================
    // RECOMMANDATIONS
    // ============================================

    /**
     * Génère des recommandations automatiques basées sur l'état de la BDD.
     *
     * @return array<int, array{level: string, title: string, message: string}>
     */
    public function getRecommendations(): array
    {
        $recommendations = [];
        $info = $this->getGeneralInfo();
        $stats = $this->getStatistics();

        // Recommandation 1 : Vérifier l'intégrité si > 30 jours
        $recommendations[] = [
            'level'   => 'info',
            'title'   => 'Vérification d\'intégrité',
            'message' => 'Lancez la vérification d\'intégrité tous les mois pour détecter les corruptions de table avant qu\'elles ne deviennent critiques.',
        ];

        // Recommandation 2 : Optimiser les tables si > 100 Mo
        if ($stats['total_size'] > 100 * 1024 * 1024) {
            $recommendations[] = [
                'level'   => 'warning',
                'title'   => 'Optimisation recommandée',
                'message' => 'La base de données dépasse 100 Mo. Lancez une optimisation pour libérer de l\'espace et améliorer les performances.',
            ];
        }

        // Recommandation 3 : Sauvegardes régulières
        $backupService = new DatabaseBackupService();
        $backupStats = $backupService->getStats();

        if ($backupStats['last_backup'] === null) {
            $recommendations[] = [
                'level'   => 'danger',
                'title'   => 'Aucune sauvegarde',
                'message' => 'Vous n\'avez aucune sauvegarde. Créez-en une immédiatement depuis la page "Sauvegardes".',
            ];
        } else {
            $lastBackupDate = strtotime($backupStats['last_backup']['created_at']);
            $daysSinceLastBackup = (int) floor((time() - $lastBackupDate) / 86400);

            if ($daysSinceLastBackup > 7) {
                $recommendations[] = [
                    'level'   => 'warning',
                    'title'   => 'Dernière sauvegarde ancienne',
                    'message' => 'Votre dernière sauvegarde date de ' . $daysSinceLastBackup . ' jour(s). Créez-en une nouvelle rapidement.',
                ];
            }
        }

        // Recommandation 4 : Moteur MyISAM
        if (isset($stats['engine_breakdown']['MyISAM'])) {
            $recommendations[] = [
                'level'   => 'warning',
                'title'   => 'Tables MyISAM détectées',
                'message' => $stats['engine_breakdown']['MyISAM'] . ' table(s) utilisent MyISAM (ancien moteur). Envisagez de migrer vers InnoDB pour la sécurité et les transactions.',
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
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' Go';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' Mo';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' Ko';
        }
        return $bytes . ' o';
    }

    /**
     * Formate une durée en secondes en format lisible.
     */
    private function formatDuration(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = $days . 'j';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'min';

        return implode(' ', $parts) ?: $seconds . 's';
    }
}