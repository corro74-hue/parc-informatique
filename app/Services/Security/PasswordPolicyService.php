<?php
declare(strict_types=1);

namespace App\Services\Security;

use App\Core\Database;
use PDO;

final class PasswordPolicyService
{
    /**
     * Configuration de la politique
     */
    private const MIN_LENGTH = 12;
    private const REQUIRE_UPPERCASE = true;
    private const REQUIRE_LOWERCASE = true;
    private const REQUIRE_DIGIT = true;
    private const REQUIRE_SPECIAL = true;
    private const HISTORY_SIZE = 5;         // Empêche la réutilisation des 5 derniers
    private const EXPIRATION_DAYS = 90;     // Expiration après 90 jours

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Valide un mot de passe selon la politique.
     *
     * @param string $password Mot de passe en clair
     * @param int|null $userId  ID utilisateur (pour vérifier l'historique)
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(string $password, ?int $userId = null): array
    {
        $errors = [];

        // Longueur minimale
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = 'Le mot de passe doit contenir au moins ' . self::MIN_LENGTH . ' caractères.';
        }

        // Majuscule
        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }

        // Minuscule
        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
        }

        // Chiffre
        if (self::REQUIRE_DIGIT && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }

        // Caractère spécial
        if (self::REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*...).';
        }

        // Vérifie qu'il n'est pas déjà utilisé (historique)
        if ($userId !== null && $this->isInHistory($userId, $password)) {
            $errors[] = 'Ce mot de passe a déjà été utilisé récemment. Choisissez-en un autre.';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Vérifie si un mot de passe est dans l'historique d'un utilisateur.
     */
    public function isInHistory(int $userId, string $plainPassword): bool
    {
        $stmt = $this->db->prepare(
            'SELECT password_hash FROM password_history
             WHERE user_id = :user_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::HISTORY_SIZE, PDO::PARAM_INT);
        $stmt->execute();

        $history = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($history as $hash) {
            if (password_verify($plainPassword, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enregistre un mot de passe dans l'historique.
     * Appelé APRÈS chaque changement de mot de passe.
     */
    public function addToHistory(int $userId, string $passwordHash): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO password_history (user_id, password_hash, created_at)
             VALUES (:user_id, :password_hash, NOW())'
        );
        $stmt->execute([
            'user_id'       => $userId,
            'password_hash' => $passwordHash,
        ]);

        // Nettoie l'historique (garde uniquement les X derniers)
        $this->cleanupHistory($userId);
    }

    /**
     * Nettoie l'historique pour ne garder que les X derniers.
     */
    private function cleanupHistory(int $userId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM password_history
             WHERE user_id = :user_id
               AND id NOT IN (
                   SELECT id FROM (
                       SELECT id FROM password_history
                       WHERE user_id = :user_id2
                       ORDER BY created_at DESC
                       LIMIT :limit
                   ) AS recent
               )'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id2', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', self::HISTORY_SIZE, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Vérifie si un mot de passe doit être changé (expiration).
     *
     * @param string|null $lastChangedAt Date du dernier changement
     * @return array{expired: bool, days_remaining: int}
     */
    public function isExpired(?string $lastChangedAt): array
    {
        if ($lastChangedAt === null) {
            return ['expired' => true, 'days_remaining' => 0];
        }

        $daysSinceChange = (int) floor((time() - strtotime($lastChangedAt)) / 86400);
        $daysRemaining = max(0, self::EXPIRATION_DAYS - $daysSinceChange);

        return [
            'expired'        => $daysSinceChange >= self::EXPIRATION_DAYS,
            'days_remaining' => $daysRemaining,
        ];
    }

    /**
     * Retourne les règles de la politique (pour affichage dans les vues).
     */
    public static function getRules(): array
    {
        return [
            'min_length'       => self::MIN_LENGTH,
            'require_uppercase' => self::REQUIRE_UPPERCASE,
            'require_lowercase' => self::REQUIRE_LOWERCASE,
            'require_digit'    => self::REQUIRE_DIGIT,
            'require_special'  => self::REQUIRE_SPECIAL,
            'history_size'     => self::HISTORY_SIZE,
            'expiration_days'  => self::EXPIRATION_DAYS,
        ];
    }
}