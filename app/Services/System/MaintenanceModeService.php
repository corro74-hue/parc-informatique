<?php
declare(strict_types=1);

namespace App\Services\System;

use App\Core\Database;
use PDO;

/**
 * Service de gestion du mode maintenance.
 *
 * 🎯 RÔLE :
 *    Permet d'activer/désactiver le mode maintenance de l'application.
 *    Quand le mode est actif, les visiteurs voient une page dédiée tandis
 *    que les administrateurs peuvent continuer à travailler.
 *
 * 🛠️ UTILISATION :
 *    - Appelé par Admin\SystemController (activer/désactiver)
 *    - Appelé par MaintenanceMiddleware (vérifier l'état à chaque requête)
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Ne pas oublier de désactiver après l'intervention
 *    - Vérifier que les admins peuvent toujours accéder
 *    - Communiquer la durée prévue aux utilisateurs
 *
 * 💡 BONNES PRATIQUES :
 *    - Prévenir les utilisateurs à l'avance
 *    - Faire une sauvegarde AVANT l'intervention
 *    - Désactiver le mode maintenance immédiatement après
 */
final class MaintenanceModeService
{
    /**
     * Clé de stockage du mode maintenance dans la table settings.
     */
    private const SETTING_KEY = 'maintenance_mode';

    /**
     * Clé de stockage de la raison.
     */
    private const SETTING_REASON_KEY = 'maintenance_reason';

    /**
     * Clé de stockage de la date de fin prévue.
     */
    private const SETTING_END_KEY = 'maintenance_end_at';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // ÉTAT DU MODE MAINTENANCE
    // ============================================

    /**
     * Vérifie si le mode maintenance est actif.
     */
    public function isActive(): bool
    {
        $value = $this->getSetting(self::SETTING_KEY, '0');

        return $value === '1';
    }

    /**
     * Récupère les informations complètes du mode maintenance.
     *
     * @return array{
     *     active: bool,
     *     reason: string,
     *     end_at: ?string,
     *     activated_by: ?int,
     *     activated_at: ?string,
     *     duration_minutes: ?int
     * }
     */
    public function getInfo(): array
    {
        $active    = $this->isActive();
        $reason    = $this->getSetting(self::SETTING_REASON_KEY, '');
        $endAt     = $this->getSetting(self::SETTING_END_KEY, null);
        $activatedBy = $this->getSetting('maintenance_activated_by', null);
        $activatedAt = $this->getSetting('maintenance_activated_at', null);

        $durationMinutes = null;
        if ($activatedAt) {
            $durationMinutes = (int) round((time() - strtotime($activatedAt)) / 60);
        }

        return [
            'active'           => $active,
            'reason'           => $reason,
            'end_at'           => $endAt,
            'activated_by'     => $activatedBy ? (int) $activatedBy : null,
            'activated_at'     => $activatedAt,
            'duration_minutes' => $durationMinutes,
        ];
    }

    // ============================================
    // ACTIVER / DÉSACTIVER
    // ============================================

    /**
     * Active le mode maintenance.
     *
     * @param string $reason Raison de la maintenance (visible par les visiteurs)
     * @param int|null $userId ID de l'utilisateur qui active
     * @param string|null $endAt Date de fin prévue (format Y-m-d H:i:s)
     * @return array{success: bool, message: string}
     */
    public function enable(string $reason = '', ?int $userId = null, ?string $endAt = null): array
    {
        try {
            $this->setSetting(self::SETTING_KEY, '1');
            $this->setSetting(self::SETTING_REASON_KEY, $reason);
            $this->setSetting(self::SETTING_END_KEY, $endAt ?? '');
            $this->setSetting('maintenance_activated_by', (string) ($userId ?? ''));
            $this->setSetting('maintenance_activated_at', date('Y-m-d H:i:s'));

            // Log dans system_logs
            $this->logAction('maintenance_enabled', 'success', 'Mode maintenance activé', $userId, [
                'reason' => $reason,
                'end_at' => $endAt,
            ]);

            return [
                'success' => true,
                'message' => 'Mode maintenance activé. Les visiteurs voient la page de maintenance.',
            ];
        } catch (\Throwable $e) {
            $this->logAction('maintenance_enabled', 'error', 'Échec activation : ' . $e->getMessage(), $userId);

            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Désactive le mode maintenance.
     *
     * @param int|null $userId ID de l'utilisateur qui désactive
     * @return array{success: bool, message: string}
     */
    public function disable(?int $userId = null): array
    {
        try {
            $this->setSetting(self::SETTING_KEY, '0');
            $this->setSetting(self::SETTING_REASON_KEY, '');
            $this->setSetting(self::SETTING_END_KEY, '');

            // Log dans system_logs
            $this->logAction('maintenance_disabled', 'success', 'Mode maintenance désactivé', $userId);

            return [
                'success' => true,
                'message' => 'Mode maintenance désactivé. L\'application est de nouveau accessible.',
            ];
        } catch (\Throwable $e) {
            $this->logAction('maintenance_disabled', 'error', 'Échec désactivation : ' . $e->getMessage(), $userId);

            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ============================================
    // HELPERS SETTINGS
    // ============================================

    /**
     * Récupère une valeur depuis la table settings.
     */
    private function getSetting(string $key, ?string $default = null): ?string
    {
        try {
            $stmt = $this->db->prepare('SELECT value FROM settings WHERE `key` = :key LIMIT 1');
            $stmt->execute(['key' => $key]);
            $value = $stmt->fetchColumn();

            return $value !== false ? (string) $value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Enregistre une valeur dans la table settings.
     */
    private function setSetting(string $key, string $value): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO settings (`key`, `value`, `group`, `type`, `updated_at`)
             VALUES (:key, :value, "system", "string", NOW())
             ON DUPLICATE KEY UPDATE value = :value2, updated_at = NOW()'
        );
        $stmt->execute(['key' => $key, 'value' => $value, 'value2' => $value]);
    }

    /**
     * Enregistre une action dans system_logs.
     */
    private function logAction(string $action, string $status, string $message, ?int $userId, array $details = []): void
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
                'user_id' => $userId,
                'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Silencieux : ne pas bloquer si le log échoue
        }
    }
}