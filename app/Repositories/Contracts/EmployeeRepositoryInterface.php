<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Employee;

interface EmployeeRepositoryInterface
{
    public function findById(int $id): ?Employee;
    public function findByMatricule(string $matricule): ?Employee;
    public function findByEmail(string $email): ?Employee;

    /** @return Employee[] */
    public function findAllActive(): array;

    /** @return Employee[] */
    public function findByService(int $serviceId): array;

    /** @return Employee[] */
    public function search(string $term, int $limit = 20): array;

    /**
     * Liste paginée avec filtres.
     * @return array{data: Employee[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array;

    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;

    public function countActive(): int;
    public function countAll(): int;
}