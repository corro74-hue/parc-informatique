<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Equipment;

interface EquipmentRepositoryInterface
{
    /**
     * Trouver un équipement par son ID (avec jointures)
     */
    public function findById(int $id): ?Equipment;

    /**
     * Trouver un équipement par son numéro d'inventaire
     */
    public function findByInventoryNumber(string $inventoryNumber): ?Equipment;

    /**
     * Trouver un équipement par son numéro de série
     */
    public function findBySerialNumber(string $serialNumber): ?Equipment;

    /**
     * Liste paginée avec filtres et recherche
     *
     * @param array{
     *     search?: string,
     *     category_id?: int,
     *     status_id?: int,
     *     service_id?: int,
     *     site_id?: int,
     *     brand_id?: int
     * } $filters
     * @return array{data: Equipment[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15, string $sortBy = 'created_at', string $sortDir = 'DESC'): array;

    /**
     * Créer un nouvel équipement
     *
     * @return int L'ID du nouvel équipement
     */
    public function create(array $data): int;

    /**
     * Mettre à jour un équipement
     */
    public function update(int $id, array $data): bool;

    /**
     * Suppression logique (soft delete)
     */
    public function softDelete(int $id, int $userId): bool;

    /**
     * Restaurer un équipement supprimé
     */
    public function restore(int $id): bool;

    /**
     * Génère le prochain numéro d'inventaire
     * Format : INF-YYYY-NNNN (ex: INF-2026-0001)
     */
    public function generateNextInventoryNumber(): string;

    /**
     * Statistiques par statut
     *
     * @return array<string, int>  [code_status => count]
     */
    public function countByStatus(): array;

    /**
     * Statistiques par catégorie
     *
     * @return array<int, array{name: string, count: int}>
     */
    public function countByCategory(): array;

    /**
     * Valeur totale du parc (hors supprimés)
     */
    public function getTotalValue(): float;

    /**
     * Derniers équipements ajoutés (pour le dashboard)
     *
     * @return Equipment[]
     */
    public function getRecent(int $limit = 5): array;
}