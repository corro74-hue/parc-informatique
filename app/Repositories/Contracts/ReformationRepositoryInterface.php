<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Reformation;
use App\Models\ReformationDecision;
use App\Models\ReformationItem;

interface ReformationRepositoryInterface
{
    // ============================================
    // RÉFORMES (ticket principal)
    // ============================================

    public function findById(int $id): ?Reformation;

    /**
     * @return array{data: Reformation[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array;

    /** @return Reformation[] */
    public function findRecent(int $limit = 5): array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    /**
     * Soft delete (met deleted_at).
     */
    public function delete(int $id): bool;

    public function generateReference(): string;

    // ============================================
    // ITEMS (équipements concernés)
    // ============================================

    /** @return ReformationItem[] */
    public function findItems(int $reformationId): array;

    public function findItem(int $itemId): ?ReformationItem;

    public function addItem(array $data): int;

    public function removeItem(int $itemId): bool;

    public function removeAllItems(int $reformationId): bool;

    public function recomputeTotalValue(int $reformationId): float;

    // ============================================
    // DÉCISIONS DE LA COMMISSION
    // ============================================

    /** @return ReformationDecision[] */
    public function findDecisions(int $reformationId): array;

    public function createDecision(array $data): int;

    // ============================================
    // WORKFLOW (historique des transitions)
    // ============================================

    /** @return array<int, array<string, mixed>> */
    public function findWorkflowLogs(int $reformationId): array;

    public function logWorkflow(array $data): void;

    // ============================================
    // STATISTIQUES
    // ============================================

    /** @return array<string, int> */
    public function countByStatus(): array;

    public function countAll(): int;

    public function countActive(): int;
}