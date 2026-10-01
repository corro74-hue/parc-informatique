<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Document;
use App\Models\DocumentVersion;

interface DocumentRepositoryInterface
{
    // ============================================
    // DOCUMENTS (table principale)
    // ============================================

    public function findById(int $id): ?Document;

    /**
     * @return array{data: Document[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array;

    /** @return Document[] */
    public function findRecent(int $limit = 5): array;

    /** @return Document[] */
    public function findByEntity(string $entityType, int $entityId): array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function generateReference(): string;

    public function incrementVersion(int $id): int;

    // ============================================
    // VERSIONS
    // ============================================

    /** @return DocumentVersion[] */
    public function findVersions(int $documentId): array;

    public function findVersion(int $versionId): ?DocumentVersion;

    public function createVersion(array $data): int;

    public function deleteVersion(int $versionId): bool;

    // ============================================
    // STATISTIQUES
    // ============================================

    /** @return array<string, int> */
    public function countByType(): array;

    public function countAll(): int;

    public function countByEntity(string $entityType, int $entityId): int;

    public function totalSize(): int;

    // ============================================
    // ACTIONS GROUPÉES
    // ============================================

    /**
     * Récupère plusieurs documents par leurs IDs.
     *
     * @param int[] $ids
     * @return Document[]
     */
    public function findByIds(array $ids): array;

    /**
     * Supprime plusieurs documents par leurs IDs.
     *
     * @param int[] $ids
     * @return int Nombre de documents supprimés
     */
    public function deleteByIds(array $ids): int;
}