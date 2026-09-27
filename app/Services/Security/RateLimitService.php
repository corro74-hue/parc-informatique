<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Database;
use PDO;

final class RateLimitService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vérifie si une action est autorisée.
     *
     * @param string $identifier Identifiant (IP, user_id, etc.)
     * @param string $route      Route cible (ex: '/login')
     * @param int    $maxHits    Nombre maximum de requêtes autorisées
     * @param int    $windowSec  Fenêtre de temps en secondes
     * @param int    $blockSec   Durée de blocage si dépassement (en secondes)
     * @return array{allowed: bool, remaining: int, blocked_until: ?int, retry_after: ?int}
     */
    public function check(
        string $identifier,
        string $route,
        int $maxHits = 60,
        int $windowSec = 60,
        int $blockSec = 300
    ): array {
        $now = time();

        // Récupère l'enregistrement existant
        $stmt = $this->db->prepare(
            'SELECT * FROM rate_limits
             WHERE identifier = :identifier AND route = :route
             LIMIT 1'
        );
        $stmt->execute(['identifier' => $identifier, 'route' => $route]);
        $row = $stmt->fetch();

        // Si l'enregistrement n'existe pas, on le crée
        if (!$row) {
            $this->createEntry($identifier, $route, $now);

            return [
                'allowed'       => true,
                'remaining'     => $maxHits - 1,
                'blocked_until' => null,
                'retry_after'   => null,
            ];
        }

        // Vérifie si l'IP est actuellement bloquée
        if (!empty($row['blocked_until']) && $row['blocked_until'] > $now) {
            return [
                'allowed'       => false,
                'remaining'     => 0,
                'blocked_until' => (int) $row['blocked_until'],
                'retry_after'   => (int) $row['blocked_until'] - $now,
            ];
        }

        // Vérifie si la fenêtre de temps est dépassée → réinitialise
        $windowExpired = ($now - (int) $row['first_hit_at']) > $windowSec;

        if ($windowExpired) {
            // Réinitialise le compteur
            $stmt = $this->db->prepare(
                'UPDATE rate_limits
                 SET hits = 1,
                     first_hit_at = :first_hit,
                     last_hit_at = :last_hit,
                     blocked_until = NULL
                 WHERE id = :id'
            );
            $stmt->execute([
                'id'         => $row['id'],
                'first_hit'  => $now,
                'last_hit'   => $now,
            ]);

            return [
                'allowed'       => true,
                'remaining'     => $maxHits - 1,
                'blocked_until' => null,
                'retry_after'   => null,
            ];
        }

        // Vérifie si la limite est atteinte
        if ((int) $row['hits'] >= $maxHits) {
            // Bloque l'identifiant
            $blockedUntil = $now + $blockSec;
            $stmt = $this->db->prepare(
                'UPDATE rate_limits
                 SET blocked_until = :blocked_until, last_hit_at = :last_hit
                 WHERE id = :id'
            );
            $stmt->execute([
                'id'            => $row['id'],
                'blocked_until' => $blockedUntil,
                'last_hit'      => $now,
            ]);

            return [
                'allowed'       => false,
                'remaining'     => 0,
                'blocked_until' => $blockedUntil,
                'retry_after'   => $blockSec,
            ];
        }

        // Incrémente le compteur
        $stmt = $this->db->prepare(
            'UPDATE rate_limits
             SET hits = hits + 1, last_hit_at = :last_hit
             WHERE id = :id'
        );
        $stmt->execute(['id' => $row['id'], 'last_hit' => $now]);

        return [
            'allowed'       => true,
            'remaining'     => $maxHits - (int) $row['hits'] - 1,
            'blocked_until' => null,
            'retry_after'   => null,
        ];
    }

    /**
     * Crée une nouvelle entrée pour un identifiant/route.
     */
    private function createEntry(string $identifier, string $route, int $now): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO rate_limits (identifier, route, hits, first_hit_at, last_hit_at)
             VALUES (:identifier, :route, 1, :first_hit, :last_hit)'
        );
        $stmt->execute([
            'identifier' => $identifier,
            'route'      => $route,
            'first_hit'  => $now,
            'last_hit'   => $now,
        ]);
    }

    /**
     * Réinitialise les compteurs pour un identifiant (après login réussi par ex).
     */
    public function reset(string $identifier, string $route): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM rate_limits WHERE identifier = :identifier AND route = :route'
        );
        $stmt->execute(['identifier' => $identifier, 'route' => $route]);
    }

    /**
     * Nettoie les entrées expirées (à appeler périodiquement ou aléatoirement).
     *
     * @param int $olderThanSecondes Supprime les entrées plus vieilles que ce délai
     */
    public function cleanOldEntries(int $olderThanSecondes = 86400): int
    {
        $threshold = time() - $olderThanSecondes;
        $now       = time();

        $stmt = $this->db->prepare(
            'DELETE FROM rate_limits
             WHERE last_hit_at < :threshold
               AND (blocked_until IS NULL OR blocked_until < :now)'
        );
        $stmt->execute([
            'threshold' => $threshold,
            'now'       => $now,
        ]);

        return $stmt->rowCount();
    }

    /**
     * Récupère les statistiques d'un identifiant.
     */
    public function getStats(string $identifier): array
    {
        $stmt = $this->db->prepare(
            'SELECT route, hits, first_hit_at, last_hit_at, blocked_until
             FROM rate_limits
             WHERE identifier = :identifier
             ORDER BY last_hit_at DESC'
        );
        $stmt->execute(['identifier' => $identifier]);

        return $stmt->fetchAll();
    }
}