<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

interface RoleRepositoryInterface
{
    /**
     * Récupère tous les rôles.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array;

    /**
     * Récupère un rôle par son ID.
     */
    public function findById(int $id): ?array;

    /**
     * Récupère un rôle par son slug.
     */
    public function findBySlug(string $slug): ?array;

    /**
     * Crée un nouveau rôle.
     */
    public function create(array $data): int;

    /**
     * Met à jour un rôle.
     */
    public function update(int $id, array $data): bool;

    /**
     * Supprime un rôle (uniquement si is_system = 0).
     */
    public function delete(int $id): bool;

    /**
     * Récupère les permissions d'un rôle.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPermissions(int $roleId): array;

    /**
     * Récupère les IDs des permissions d'un rôle.
     *
     * @return array<int>
     */
    public function getPermissionIds(int $roleId): array;

    /**
     * Synchronise les permissions d'un rôle.
     *
     * @param array<int> $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): void;

    /**
     * Compte les utilisateurs ayant ce rôle.
     */
    public function countUsers(int $roleId): int;

    /**
     * Vérifie si un slug existe déjà.
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool;

    /**
     * Récupère toutes les permissions groupées par module.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getPermissionsGroupedByModule(): array;
}