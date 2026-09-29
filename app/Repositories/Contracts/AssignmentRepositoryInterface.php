<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Assignment;

interface AssignmentRepositoryInterface
{
    // ============================================
    // LECTURE
    // ============================================

    /**
     * Trouve une affectation par son ID (avec jointures).
     */
    public function findById(int $id): ?Assignment;

    /**
     * Liste paginée des affectations avec filtres.
     *
     * Filtres supportés :
     *  - search         (numéro inventaire, désignation, nom employé, matricule)
     *  - status         ('active' | 'returned')
     *  - employee_id    (employé spécifique)
     *  - equipment_id   (équipement spécifique)
     *  - service_id     (service)
     *  - site_id        (site)
     *
     * @return array{data: Assignment[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array;

    /**
     * Récupère toutes les affectations d'un équipement (historique).
     *
     * @return Assignment[]
     */
    public function findByEquipment(int $equipmentId): array;

    /**
     * Récupère toutes les affectations d'un employé (historique).
     *
     * @return Assignment[]
     */
    public function findByEmployee(int $employeeId): array;

    /**
     * Récupère l'affectation ACTIVE d'un équipement (s'il y en a une).
     * Retourne null si l'équipement n'est pas affecté actuellement.
     */
    public function findActiveByEquipment(int $equipmentId): ?Assignment;

    // ============================================
    // ÉCRITURE
    // ============================================

    /**
     * Crée une nouvelle affectation.
     * Retourne l'ID de l'affectation créée.
     */
    public function create(array $data): int;

    /**
     * Met à jour une affectation existante.
     */
    public function update(int $id, array $data): bool;

    /**
     * Marque une affectation comme retournée (remplit end_date).
     */
    public function markAsReturned(int $id, string $endDate): bool;

    /**
     * Suppression définitive (hard delete - usage admin uniquement).
     */
    public function delete(int $id): bool;

    // ============================================
    // STATISTIQUES
    // ============================================

    /**
     * Compte les affectations actives.
     */
    public function countActive(): int;

    /**
     * Compte le total d'affectations.
     */
    public function countAll(): int;

    /**
     * Récupère les N dernières affectations (pour dashboard).
     *
     * @return Assignment[]
     */
    public function findRecent(int $limit = 5): array;
}