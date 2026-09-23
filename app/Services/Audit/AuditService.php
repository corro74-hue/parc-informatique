<?php
declare(strict_types=1);

namespace App\Services\Audit;

use App\Core\Database;

/**
 * Service de journalisation (audit trail).
 *
 * Enregistre toutes les actions importantes de l'application dans la table audit_logs
 * pour permettre une traçabilité complète : qui, quoi, quand, avant/après.
 */
final class AuditService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string      $action      Type d'action (create, update, delete, restore, ...)
     * @param string      $entityType  Type d'entité (equipment, user, maintenance, ...)
     * @param int|null    $entityId    ID de l'entité concernée
     * @param array|null  $oldValues   Valeurs AVANT modification (null pour création)
     * @param array|null  $newValues   Valeurs APRÈS modification (null pour suppression)
     * @param string      $severity    info | warning | error | critical
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $severity = 'info'
    ): void {
        try {
            // ----- Utilisateur courant -----
            $userId   = $_SESSION['user_id'] ?? null;
            $userName = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? 'Système';

            // ----- Contexte HTTP -----
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $url       = $_SERVER['REQUEST_URI'] ?? null;
            $method    = $_SERVER['REQUEST_METHOD'] ?? null;

            // ----- Convertir les valeurs en JSON -----
            $oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

            // ----- Insérer dans audit_logs -----
            $sql = 'INSERT INTO audit_logs (
                        user_id, user_name, action, entity_type, entity_id,
                        old_values, new_values, ip_address, user_agent, url, method,
                        severity, created_at
                    ) VALUES (
                        :user_id, :user_name, :action, :entity_type, :entity_id,
                        :old_values, :new_values, :ip_address, :user_agent, :url, :method,
                        :severity, NOW()
                    )';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id'     => $userId,
                'user_name'   => mb_substr((string) $userName, 0, 180),
                'action'      => mb_substr($action, 0, 80),
                'entity_type' => mb_substr($entityType, 0, 80),
                'entity_id'   => $entityId,
                'old_values'  => $oldJson,
                'new_values'  => $newJson,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent ? mb_substr($userAgent, 0, 255) : null,
                'url'         => $url ? mb_substr($url, 0, 500) : null,
                'method'      => $method ? mb_substr($method, 0, 10) : null,
                'severity'    => $severity,
            ]);
        } catch (\Throwable $e) {
            // Ne JAMAIS faire planter l'application à cause de l'audit
            error_log('[AuditService] Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Raccourci : enregistre une création.
     */
    public function logCreate(string $entityType, int $entityId, array $newValues): void
    {
        $this->log('create', $entityType, $entityId, null, $newValues, 'info');
    }

    /**
     * Raccourci : enregistre une modification.
     * Calcule automatiquement les champs modifiés.
     */
    public function logUpdate(string $entityType, int $entityId, array $oldValues, array $newValues): void
    {
        // Détecter les champs qui ont réellement changé
        $changes = [];
        foreach ($newValues as $key => $newVal) {
            $oldVal = $oldValues[$key] ?? null;
            if ((string) $oldVal !== (string) $newVal) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        // Ne rien enregistrer si aucun changement
        if (empty($changes)) {
            return;
        }

        $this->log('update', $entityType, $entityId, $oldValues, $newValues, 'info');
    }

    /**
     * Raccourci : enregistre une suppression (soft delete).
     */
    public function logDelete(string $entityType, int $entityId, array $oldValues, string $severity = 'warning'): void
    {
        $this->log('delete', $entityType, $entityId, $oldValues, null, $severity);
    }

    /**
     * Raccourci : enregistre une restauration.
     */
    public function logRestore(string $entityType, int $entityId, array $newValues): void
    {
        $this->log('restore', $entityType, $entityId, null, $newValues, 'info');
    }

    /**
     * Récupère l'IP réelle du client (gère les proxies).
     */
    private function getClientIp(): ?string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}