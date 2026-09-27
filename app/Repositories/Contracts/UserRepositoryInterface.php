<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    // ============================================
    // MÉTHODES EXISTANTES (AUTHENTIFICATION)
    // ============================================
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function updateLastLogin(int $userId, string $ip): void;
    public function incrementFailedAttempts(int $userId): int;
    public function resetFailedAttempts(int $userId): void;
    public function lockAccount(int $userId, int $minutes): void;
    public function logLoginAttempt(string $username, string $ip, string $userAgent, bool $success): void;
    public function countRecentFailedAttempts(string $username, int $minutes): int;

    // ============================================
    // MÉTHODES CRUD UTILISATEURS
    // ============================================
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;
    public function countAll(array $filters = []): int;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function softDelete(int $id): bool;
    public function restore(int $id): bool;
    public function updatePassword(int $id, string $passwordHash, bool $mustChange = false): bool;
    public function toggleActive(int $id): bool;

    // ============================================
    // RÔLES ET PERMISSIONS
    // ============================================
    public function findAllRoles(): array;
    public function findAllPermissions(): array;
    public function syncRoles(int $userId, array $roleIds): void;

    // ============================================
    // HISTORIQUE ET SESSIONS
    // ============================================
    public function getLoginHistory(int $userId, int $limit = 20): array;
    public function getActiveSessions(int $userId): array;
    public function revokeAllSessions(int $userId): void;

    // ============================================
    // NOUVELLES MÉTHODES - 2FA
    // ============================================

    /**
     * Active la 2FA pour un utilisateur.
     */
    public function enableTwoFactor(int $userId, string $secret, array $hashedBackupCodes): void;

    /**
     * Désactive la 2FA pour un utilisateur.
     */
    public function disableTwoFactor(int $userId): void;

    /**
     * Récupère les codes de secours hashés d'un utilisateur.
     */
    public function getBackupCodes(int $userId): array;

    /**
     * Supprime un code de secours après utilisation.
     */
    public function removeBackupCode(int $userId, int $index): void;

    /**
     * Met à jour le secret 2FA d'un utilisateur (sans activer).
     */
    public function updateTwoFactorSecret(int $userId, string $secret): void;
}