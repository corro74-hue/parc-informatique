<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Maintenance;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use PDO;

final class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    private PDO $db;

    /**
     * Sélection standard avec jointures pour affichage.
     */
    private const BASE_SELECT = '
        SELECT m.*,
               e.inventory_number AS equipment_inventory_number,
               e.designation      AS equipment_designation,
               ec.name            AS equipment_category_name,
               CONCAT(u.first_name, " ", u.last_name) AS created_by_name,
               s.name             AS supplier_name
        FROM maintenance m
        LEFT JOIN equipment e             ON e.id  = m.equipment_id
        LEFT JOIN equipment_categories ec ON ec.id = e.category_id
        LEFT JOIN users u                 ON u.id  = m.created_by
        LEFT JOIN suppliers s             ON s.id  = m.supplier_id
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function findById(int $id): ?Maintenance
    {
        $sql = self::BASE_SELECT . ' WHERE m.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Maintenance::fromArray($row) : null;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1 = 1'];
        $params = [];

        // Recherche multi-champs
        if (!empty($filters['search'])) {
            $where[] = '(
                e.inventory_number LIKE :s1
                OR e.designation    LIKE :s2
                OR m.problem_description LIKE :s3
                OR m.technician     LIKE :s4
                OR m.invoice_number LIKE :s5
            )';
            $term = '%' . $filters['search'] . '%';
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
            $params['s5'] = $term;
        }

        // Filtre statut
        if (!empty($filters['status'])) {
            $where[] = 'm.status = :status';
            $params['status'] = $filters['status'];
        }

        // Filtre type
        if (!empty($filters['type'])) {
            $where[] = 'm.type = :type';
            $params['type'] = $filters['type'];
        }

        // Filtre équipement
        if (!empty($filters['equipment_id'])) {
            $where[] = 'm.equipment_id = :equipment_id';
            $params['equipment_id'] = (int) $filters['equipment_id'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Compter le total
        $countSql = 'SELECT COUNT(DISTINCT m.id)
                     FROM maintenance m
                     LEFT JOIN equipment e ON e.id = m.equipment_id'
                     . $whereSql;

        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // Pagination
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = max(1, min($page, $lastPage));
        $offset   = ($page - 1) * $perPage;

        // Récupérer les données
        $sql = self::BASE_SELECT . $whereSql . '
                ORDER BY
                    CASE m.status
                        WHEN "open" THEN 1
                        WHEN "in_progress" THEN 2
                        WHEN "waiting_parts" THEN 3
                        WHEN "completed" THEN 4
                        WHEN "cancelled" THEN 5
                        ELSE 6
                    END ASC,
                    m.reported_at DESC,
                    m.id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'      => array_map(fn($r) => Maintenance::fromArray($r), $stmt->fetchAll()),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    public function findByEquipment(int $equipmentId): array
    {
        $sql = self::BASE_SELECT . '
                WHERE m.equipment_id = :equipment_id
                ORDER BY m.reported_at DESC, m.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['equipment_id' => $equipmentId]);

        return array_map(fn($r) => Maintenance::fromArray($r), $stmt->fetchAll());
    }

    public function findActiveByEquipment(int $equipmentId): ?Maintenance
    {
        $sql = self::BASE_SELECT . '
                WHERE m.equipment_id = :equipment_id
                  AND m.status IN ("open", "in_progress", "waiting_parts")
                ORDER BY m.reported_at DESC
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['equipment_id' => $equipmentId]);
        $row = $stmt->fetch();

        return $row ? Maintenance::fromArray($row) : null;
    }

    public function findRecent(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . '
                ORDER BY m.reported_at DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Maintenance::fromArray($r), $stmt->fetchAll());
    }

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int
    {
        $sql = 'INSERT INTO maintenance (
                    equipment_id, type, status, reported_at, started_at, completed_at,
                    reported_by, technician, supplier_id, problem_description, diagnosis,
                    work_done, result, cost, downtime_hours, invoice_number, notes,
                    created_by, created_at
                ) VALUES (
                    :equipment_id, :type, :status, :reported_at, :started_at, :completed_at,
                    :reported_by, :technician, :supplier_id, :problem_description, :diagnosis,
                    :work_done, :result, :cost, :downtime_hours, :invoice_number, :notes,
                    :created_by, NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->prepareData($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE maintenance SET
                    type                = :type,
                    status              = :status,
                    started_at          = :started_at,
                    completed_at        = :completed_at,
                    technician          = :technician,
                    supplier_id         = :supplier_id,
                    problem_description = :problem_description,
                    diagnosis           = :diagnosis,
                    work_done           = :work_done,
                    result              = :result,
                    cost                = :cost,
                    downtime_hours      = :downtime_hours,
                    invoice_number      = :invoice_number,
                    notes               = :notes,
                    updated_at          = NOW()
                WHERE id = :id';

        $params = $this->prepareData($data, false);
        $params['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM maintenance WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            'SELECT status, COUNT(*) as total FROM maintenance GROUP BY status'
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
        }
        return $result;
    }

    public function countActive(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM maintenance
             WHERE status IN ("open", "in_progress", "waiting_parts")'
        );
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM maintenance');
        return (int) $stmt->fetchColumn();
    }

    public function getTotalCost(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $where = ['status = "completed"'];
        $params = [];

        if ($dateFrom !== null) {
            $where[] = 'completed_at >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $where[] = 'completed_at <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $sql = 'SELECT COALESCE(SUM(cost), 0) FROM maintenance WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (float) $stmt->fetchColumn();
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    private function prepareData(array $data, bool $includeCreatedFields = true): array
    {
        $fields = [
            'equipment_id', 'type', 'status', 'reported_at', 'started_at', 'completed_at',
            'reported_by', 'technician', 'supplier_id', 'problem_description', 'diagnosis',
            'work_done', 'result', 'cost', 'downtime_hours', 'invoice_number', 'notes',
        ];

        if ($includeCreatedFields) {
            $fields[] = 'created_by';
        }

        $result = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            $result[$field] = ($value === '' || $value === null) ? null : $value;
        }

        return $result;
    }
}