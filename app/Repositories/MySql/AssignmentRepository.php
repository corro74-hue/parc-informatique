<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Assignment;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use PDO;

final class AssignmentRepository implements AssignmentRepositoryInterface
{
    private PDO $db;

    /**
     * Sélection standard avec jointures pour affichage complet.
     */
    private const BASE_SELECT = '
        SELECT a.*,
               e.inventory_number AS equipment_inventory_number,
               e.designation      AS equipment_designation,
               ec.name            AS equipment_category_name,
               emp.first_name     AS employee_first_name,
               emp.last_name      AS employee_last_name,
               emp.matricule      AS employee_matricule,
               emp.function_title AS employee_function_title,
               st.name            AS site_name,
               sv.name            AS service_name,
               l.full_name        AS location_name,
               CONCAT(u.first_name, " ", u.last_name) AS created_by_name
        FROM assignments a
        LEFT JOIN equipment e             ON e.id  = a.equipment_id
        LEFT JOIN equipment_categories ec ON ec.id = e.category_id
        LEFT JOIN employees emp           ON emp.id = a.employee_id
        LEFT JOIN sites st                ON st.id  = a.site_id
        LEFT JOIN services sv             ON sv.id  = a.service_id
        LEFT JOIN locations l             ON l.id   = a.location_id
        LEFT JOIN users u                 ON u.id   = a.created_by
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function findById(int $id): ?Assignment
    {
        $sql = self::BASE_SELECT . ' WHERE a.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Assignment::fromArray($row) : null;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1 = 1'];
        $params = [];

        // ----- Recherche multi-champs -----
        if (!empty($filters['search'])) {
            $where[] = '(
                e.inventory_number LIKE :search1
                OR e.designation    LIKE :search2
                OR emp.first_name   LIKE :search3
                OR emp.last_name    LIKE :search4
                OR emp.matricule    LIKE :search5
                OR a.reason         LIKE :search6
            )';
            $term = '%' . $filters['search'] . '%';
            $params['search1'] = $term;
            $params['search2'] = $term;
            $params['search3'] = $term;
            $params['search4'] = $term;
            $params['search5'] = $term;
            $params['search6'] = $term;
        }

        // ----- Filtre statut -----
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = 'a.end_date IS NULL';
            } elseif ($filters['status'] === 'returned') {
                $where[] = 'a.end_date IS NOT NULL';
            }
        }

        // ----- Filtres ciblés -----
        if (!empty($filters['employee_id'])) {
            $where[] = 'a.employee_id = :employee_id';
            $params['employee_id'] = (int) $filters['employee_id'];
        }

        if (!empty($filters['equipment_id'])) {
            $where[] = 'a.equipment_id = :equipment_id';
            $params['equipment_id'] = (int) $filters['equipment_id'];
        }

        if (!empty($filters['service_id'])) {
            $where[] = 'a.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        if (!empty($filters['site_id'])) {
            $where[] = 'a.site_id = :site_id';
            $params['site_id'] = (int) $filters['site_id'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // ----- Compter le total -----
        $countSql = 'SELECT COUNT(DISTINCT a.id)
                     FROM assignments a
                     LEFT JOIN equipment e   ON e.id  = a.equipment_id
                     LEFT JOIN employees emp ON emp.id = a.employee_id'
                     . $whereSql;

        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // ----- Pagination -----
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = max(1, min($page, $lastPage));
        $offset   = ($page - 1) * $perPage;

        // ----- Récupérer les données -----
        $sql = self::BASE_SELECT . $whereSql . '
                ORDER BY
                    CASE WHEN a.end_date IS NULL THEN 0 ELSE 1 END ASC,
                    a.start_date DESC,
                    a.id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'data'      => array_map(fn($r) => Assignment::fromArray($r), $rows),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    public function findByEquipment(int $equipmentId): array
    {
        $sql = self::BASE_SELECT . '
                WHERE a.equipment_id = :equipment_id
                ORDER BY a.start_date DESC, a.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['equipment_id' => $equipmentId]);

        return array_map(fn($r) => Assignment::fromArray($r), $stmt->fetchAll());
    }

    public function findByEmployee(int $employeeId): array
    {
        $sql = self::BASE_SELECT . '
                WHERE a.employee_id = :employee_id
                ORDER BY a.start_date DESC, a.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['employee_id' => $employeeId]);

        return array_map(fn($r) => Assignment::fromArray($r), $stmt->fetchAll());
    }

    public function findActiveByEquipment(int $equipmentId): ?Assignment
    {
        $sql = self::BASE_SELECT . '
                WHERE a.equipment_id = :equipment_id
                  AND a.end_date IS NULL
                ORDER BY a.start_date DESC
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['equipment_id' => $equipmentId]);
        $row = $stmt->fetch();

        return $row ? Assignment::fromArray($row) : null;
    }

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int
    {
        $sql = 'INSERT INTO assignments (
                    equipment_id, site_id, service_id, location_id, employee_id,
                    start_date, end_date, reason, document_path, created_by, created_at
                ) VALUES (
                    :equipment_id, :site_id, :service_id, :location_id, :employee_id,
                    :start_date, :end_date, :reason, :document_path, :created_by, NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->prepareData($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE assignments SET
                    site_id       = :site_id,
                    service_id    = :service_id,
                    location_id   = :location_id,
                    employee_id   = :employee_id,
                    start_date    = :start_date,
                    end_date      = :end_date,
                    reason        = :reason,
                    document_path = :document_path,
                    updated_at    = NOW()
                WHERE id = :id';

        $params = $this->prepareData($data);
        unset($params['equipment_id']); // on ne change pas l'équipement
        unset($params['created_by']);   // on ne change pas le créateur
        $params['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function markAsReturned(int $id, string $endDate): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE assignments
             SET end_date = :end_date, updated_at = NOW()
             WHERE id = :id AND end_date IS NULL'
        );

        return $stmt->execute([
            'id'       => $id,
            'end_date' => $endDate,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM assignments WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countActive(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM assignments WHERE end_date IS NULL');
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM assignments');
        return (int) $stmt->fetchColumn();
    }

    public function findRecent(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . '
                ORDER BY a.created_at DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Assignment::fromArray($r), $stmt->fetchAll());
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    /**
     * Convertit les chaînes vides en NULL pour MySQL.
     */
    private function prepareData(array $data): array
    {
        $fields = [
            'equipment_id', 'site_id', 'service_id', 'location_id', 'employee_id',
            'start_date', 'end_date', 'reason', 'document_path', 'created_by',
        ];

        $result = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            if ($value === '' || $value === null) {
                $result[$field] = null;
            } else {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}