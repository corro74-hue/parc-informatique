<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Maintenance;

interface MaintenanceRepositoryInterface
{
    // ============================================
    // LECTURE
    // ============================================

    public function findById(int $id): ?Maintenance;

    /**
     * Liste paginée avec filtres.
     *
     * Filtres : search, status, type, equipment_id
     *
     * @return array{data: Maintenance[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array;

    /**
     * Récupère les maintenances d'un équipement.
     *
     * @return Maintenance[]
     */
    public function findByEquipment(int $equipmentId): array;

    /**
     * Récupère la maintenance ACTIVE d'un équipement (s'il y en a une).
     */
    public function findActiveByEquipment(int $equipmentId): ?Maintenance;

    /**
     * Récupère les N dernières maintenances.
     *
     * @return Maintenance[]
     */
    public function findRecent(int $limit = 5): array;

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    // ============================================
    // STATISTIQUES
    // ============================================

    /**
     * Compte les maintenances par statut.
     *
     * @return array<string, int> ['open' => 5, 'in_progress' => 3, ...]
     */
    public function countByStatus(): array;

    public function countActive(): int;

    public function countAll(): int;

    /**
     * Somme des coûts des maintenances terminées sur une période.
     */
    public function getTotalCost(?string $dateFrom = null, ?string $dateTo = null): float;
}