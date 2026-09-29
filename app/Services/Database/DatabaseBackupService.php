<?php
declare(strict_types=1);

namespace App\Services\Database;

use App\Core\Database;
use PDO;

/**
 * Service de sauvegarde et gestion des backups de la base de données.
 *
 * 🎯 RÔLE :
 *    Permet de créer, lister, télécharger et supprimer des sauvegardes SQL
 *    de la base de données, ainsi que de nettoyer automatiquement les vieux
 *    backups selon une politique de rétention.
 *
 * 🛠️ UTILISATION :
 *    Ce service est appelé par le contrôleur Admin\DatabaseController
 *    et par le script CRON scripts/cron.php.
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Les sauvegardes sont stockées dans storage/backups/database/
 *    - Cette méthode utilise la commande `mysqldump` de MySQL
 *    - Sous Windows : chemin vers mysqldump = C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe
 *
 * 💡 BONNES PRATIQUES :
 *    - Effectuer une sauvegarde AVANT toute modification importante
 *    - Tester une restauration de temps en temps (pas juste la sauvegarde)
 *    - Conserver une copie hors serveur (cloud, disque externe)
 */
final class DatabaseBackupService
{
    /**
     * Nombre de jours de rétention (garde les 7 derniers jours).
     */
    private const RETENTION_DAYS = 7;

    /**
     * Nombre de semaines de rétention (garde les 4 dernières semaines).
     */
    private const RETENTION_WEEKS = 4;

    /**
     * Nombre de mois de rétention (garde les 12 derniers mois).
     */
    private const RETENTION_MONTHS = 12;

    private PDO $db;
    private string $backupDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->backupDir = dirname(__DIR__, 3) . '/storage/backups/database';

        // Créer le dossier si nécessaire
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Crée une nouvelle sauvegarde SQL de la base de données.
     *
     * @param string $triggeredBy Origine : 'manual', 'cron', 'system'
     * @param int|null $userId ID de l'utilisateur qui déclenche (null si cron)
     * @return array{success: bool, filename: ?string, size: int, message: string}
     */
    public function createBackup(string $triggeredBy = 'manual', ?int $userId = null): array
    {
        $startTime = microtime(true);

        // Nom du fichier : backup_2026-09-28_143000.sql
        $timestamp = date('Y-m-d_His');
        $filename = 'backup_' . $timestamp . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        // Configuration de la BDD
        $config = require dirname(__DIR__, 3) . '/config/database.php';

        // Chemin vers mysqldump (WampServer sur Windows)
        $mysqldump = 'C:\\wamp64\\bin\\mysql\\mysql8.4.7\\bin\\mysqldump.exe';

        // Construction de la commande
        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction --routines --triggers --default-character-set=utf8mb4 %s > "%s"',
            $mysqldump,
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '3306',
            $config['username'] ?? 'root',
            !empty($config['password']) ? '--password="' . $config['password'] . '"' : '',
            $config['database'] ?? 'parc_informatique',
            $filepath
        );

        // Exécution de la commande
        exec($command . ' 2>&1', $output, $returnCode);

        $duration = round(microtime(true) - $startTime, 2);
        $size = is_file($filepath) ? filesize($filepath) : 0;

        // Gestion des erreurs
        if ($returnCode !== 0 || $size === 0) {
            $errorMsg = implode("\n", $output);

            $this->logBackup([
                'type'             => 'database',
                'filename'         => $filename,
                'filepath'         => $filepath,
                'size_bytes'       => $size,
                'status'           => 'failed',
                'error_message'    => $errorMsg,
                'triggered_by'     => $triggeredBy,
                'user_id'          => $userId,
                'duration_seconds' => $duration,
            ]);

            return [
                'success'  => false,
                'filename' => null,
                'size'     => 0,
                'message'  => 'Erreur lors de la sauvegarde : ' . $errorMsg,
            ];
        }

        // Log de succès
        $this->logBackup([
            'type'             => 'database',
            'filename'         => $filename,
            'filepath'         => $filepath,
            'size_bytes'       => $size,
            'status'           => 'success',
            'error_message'    => null,
            'triggered_by'     => $triggeredBy,
            'user_id'          => $userId,
            'duration_seconds' => $duration,
        ]);

        return [
            'success'  => true,
            'filename' => $filename,
            'size'     => $size,
            'message'  => 'Sauvegarde créée avec succès (' . $this->formatSize($size) . ')',
        ];
    }

    /**
     * Liste toutes les sauvegardes existantes (du plus récent au plus ancien).
     *
     * @param int $limit Nombre maximum de sauvegardes à retourner
     * @return array<int, array<string, mixed>>
     */
    public function listBackups(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM backup_logs
             WHERE type = :type
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':type', 'database');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Récupère une sauvegarde par son ID.
     */
    public function getBackup(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM backup_logs WHERE id = :id AND type = :type LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'type' => 'database']);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Supprime une sauvegarde (fichier + log).
     *
     * @return array{success: bool, message: string}
     */
    public function deleteBackup(int $id): array
    {
        $backup = $this->getBackup($id);

        if (!$backup) {
            return ['success' => false, 'message' => 'Sauvegarde introuvable.'];
        }

        // Protection : empêche la suppression de la dernière sauvegarde
        $count = $this->countBackups();
        if ($count <= 1) {
            return [
                'success' => false,
                'message' => '⚠️ Impossible de supprimer la dernière sauvegarde. Créez-en une nouvelle d\'abord.',
            ];
        }

        // Suppression du fichier physique
        if (is_file($backup['filepath'])) {
            @unlink($backup['filepath']);
        }

        // Suppression du log
        $stmt = $this->db->prepare('DELETE FROM backup_logs WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return ['success' => true, 'message' => 'Sauvegarde supprimée.'];
    }

    /**
     * Compte le nombre total de sauvegardes.
     */
    public function countBackups(): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM backup_logs WHERE type = :type AND status = :status'
        );
        $stmt->execute(['type' => 'database', 'status' => 'success']);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupère les statistiques globales des sauvegardes.
     *
     * @return array{total: int, size_total: int, last_backup: ?array}
     */
    public function getStats(): array
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) AS total, COALESCE(SUM(size_bytes), 0) AS size_total
             FROM backup_logs
             WHERE type = "database" AND status = "success"'
        );
        $stats = $stmt->fetch();

        $lastStmt = $this->db->query(
            'SELECT * FROM backup_logs
             WHERE type = "database" AND status = "success"
             ORDER BY created_at DESC LIMIT 1'
        );
        $lastBackup = $lastStmt->fetch();

        return [
            'total'       => (int) $stats['total'],
            'size_total'  => (int) $stats['size_total'],
            'last_backup' => $lastBackup ?: null,
        ];
    }

    /**
     * Nettoie les anciennes sauvegardes selon la politique de rétention.
     *
     * Politique :
     *   - Garde les 7 derniers jours (journalier)
     *   - Garde les 4 dernières semaines (hebdomadaire)
     *   - Garde les 12 derniers mois (mensuel)
     *
     * @return array{deleted: int, freed_bytes: int}
     */
    public function cleanOldBackups(): array
    {
        $deleted = 0;
        $freedBytes = 0;

        // On garde toujours les sauvegardes récentes
        $cutoffDaily   = date('Y-m-d H:i:s', strtotime('-' . self::RETENTION_DAYS . ' days'));
        $cutoffWeekly  = date('Y-m-d H:i:s', strtotime('-' . self::RETENTION_WEEKS . ' weeks'));
        $cutoffMonthly = date('Y-m-d H:i:s', strtotime('-' . self::RETENTION_MONTHS . ' months'));

        // Récupère toutes les sauvegardes (sauf les plus récentes journalières)
        $stmt = $this->db->prepare(
            'SELECT * FROM backup_logs
             WHERE type = :type AND status = :status
               AND created_at < :cutoff_daily
             ORDER BY created_at DESC'
        );
        $stmt->execute([
            'type'          => 'database',
            'status'        => 'success',
            'cutoff_daily'  => $cutoffDaily,
        ]);
        $oldBackups = $stmt->fetchAll();

        foreach ($oldBackups as $backup) {
            $createdAt = strtotime($backup['created_at']);
            $shouldDelete = false;

            // Supprimer si > 12 mois
            if ($createdAt < strtotime($cutoffMonthly)) {
                $shouldDelete = true;
            }
            // Supprimer si > 4 semaines ET pas un backup hebdomadaire (dimanche)
            elseif ($createdAt < strtotime($cutoffWeekly) && date('N', $createdAt) !== '7') {
                $shouldDelete = true;
            }
            // Supprimer si > 7 jours ET pas un backup quotidien récent
            // (les backups journaliers sont déjà gardés par le cutoff)

            if ($shouldDelete) {
                // Suppression du fichier
                if (is_file($backup['filepath'])) {
                    $freedBytes += filesize($backup['filepath']);
                    @unlink($backup['filepath']);
                }

                // Suppression du log
                $delStmt = $this->db->prepare('DELETE FROM backup_logs WHERE id = :id');
                $delStmt->execute(['id' => $backup['id']]);

                $deleted++;
            }
        }

        return [
            'deleted'     => $deleted,
            'freed_bytes' => $freedBytes,
        ];
    }

    /**
     * Enregistre un événement de sauvegarde dans la table backup_logs.
     */
    private function logBackup(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO backup_logs
                (type, filename, filepath, size_bytes, status, error_message,
                 triggered_by, user_id, duration_seconds, created_at)
             VALUES
                (:type, :filename, :filepath, :size_bytes, :status, :error_message,
                 :triggered_by, :user_id, :duration_seconds, NOW())'
        );

        $stmt->execute([
            'type'             => $data['type'],
            'filename'         => $data['filename'],
            'filepath'         => $data['filepath'],
            'size_bytes'       => $data['size_bytes'],
            'status'           => $data['status'],
            'error_message'    => $data['error_message'],
            'triggered_by'     => $data['triggered_by'],
            'user_id'          => $data['user_id'],
            'duration_seconds' => $data['duration_seconds'],
        ]);
    }

    /**
     * Formate une taille en octets en format lisible (Ko, Mo, Go).
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
}