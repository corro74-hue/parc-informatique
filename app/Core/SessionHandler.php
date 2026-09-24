<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use SessionHandlerInterface;

final class SessionHandler implements SessionHandlerInterface
{
    private PDO $db;
    private int $lifetime;

    public function __construct(?PDO $db = null, int $lifetime = 7200)
    {
        $this->db = $db ?? Database::getInstance();
        $this->lifetime = $lifetime;
    }

    /**
     * Appelé quand une session démarre.
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * Appelé quand une session se ferme.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Lit les données d'une session.
     */
    public function read(string $id): string|false
    {
        $stmt = $this->db->prepare(
            'SELECT payload FROM sessions WHERE id = :id AND last_activity >= :min_time LIMIT 1'
        );
        $stmt->execute([
            'id'       => $id,
            'min_time' => time() - $this->lifetime,
        ]);

        $payload = $stmt->fetchColumn();

        return $payload !== false ? (string) $payload : '';
    }

    /**
     * Écrit les données d'une session.
     */
    public function write(string $id, string $data): bool
    {
        // Récupère les métadonnées depuis la session courante
        $userId    = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $stmt = $this->db->prepare(
            'INSERT INTO sessions (id, user_id, ip_address, user_agent, payload, last_activity)
             VALUES (:id, :user_id, :ip, :ua, :payload, :last_activity)
             ON DUPLICATE KEY UPDATE
                user_id       = VALUES(user_id),
                ip_address    = VALUES(ip_address),
                user_agent    = VALUES(user_agent),
                payload       = VALUES(payload),
                last_activity = VALUES(last_activity)'
        );

        return $stmt->execute([
            'id'            => $id,
            'user_id'       => $userId,
            'ip'            => $ipAddress,
            'ua'            => $userAgent,
            'payload'       => $data,
            'last_activity' => time(),
        ]);
    }

    /**
     * Détruit une session.
     */
    public function destroy(string $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Nettoie les sessions expirées (appelé par PHP aléatoirement).
     */
    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->db->prepare(
            'DELETE FROM sessions WHERE last_activity < :min_time'
        );
        $stmt->execute(['min_time' => time() - $max_lifetime]);

        return $stmt->rowCount();
    }
}