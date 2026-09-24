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
    // NOUVELLES MÉTHODES - CRUD UTILISATEURS
    // ============================================

    /**
     * Récupère tous les utilisateurs avec filtres et pagination.
     *
     * @param array $filters ['search' => string, 'role' => string, 'status' => 'active'|'inactive'|'locked']
     * @param int $page
     * @param int $perPage
     * @return array<int, User>
     */
    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array;

    /**
     * Compte le nombre total d'utilisateurs (pour la pagination).
     */
    public function countAll(array $filters = []): int;

    /**
     * Crée un nouvel utilisateur.
     *
     * @param array $data
     * @return int L'ID du nouvel utilisateur
     */
    public function create(array $data): int;

    /**
     * Met à jour un utilisateur existant.
     */
    public function update(int $id, array $data): bool;

    /**
     * Suppression logique (soft delete).
     */
    public function softDelete(int $id): bool;

    /**
     * Restaure un utilisateur supprimé.
     */
    public function restore(int $id): bool;

    /**
     * Change le mot de passe d'un utilisateur.
     */
    public function updatePassword(int $id, string $passwordHash, bool $mustChange = false): bool;

    /**
     * Active ou désactive un compte utilisateur.
     */
    public function toggleActive(int $id): bool;

    // ============================================
    // RÔLES ET PERMISSIONS
    // ============================================

    /**
     * Récupère la liste de tous les rôles disponibles.
     */
    public function findAllRoles(): array;

    /**
     * Récupère la liste de toutes les permissions disponibles.
     */
    public function findAllPermissions(): array;

    /**
     * Synchronise les rôles d'un utilisateur (remplace les anciens).
     *
     * @param int $userId
     * @param array<int> $roleIds
     */
    public function syncRoles(int $userId, array $roleIds): void;

    // ============================================
    // HISTORIQUE ET SESSIONS
    // ============================================

    /**
     * Récupère l'historique des connexions d'un utilisateur.
     */
    public function getLoginHistory(int $userId, int $limit = 20): array;

    /**
     * Récupère les sessions actives d'un utilisateur (si table sessions en BDD).
     */
    public function getActiveSessions(int $userId): array;

    /**
     * Révoque toutes les sessions d'un utilisateur (force la déconnexion).
     */
    public function revokeAllSessions(int $userId): void;
}