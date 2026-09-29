<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Employee;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use PDO;

final class EmployeeRepository implements EmployeeRepositoryInterface
{
    private PDO $db;

    /**
     * Sélection standard avec jointure service.
     */
    private const BASE_SELECT = '
        SELECT e.*,
               s.name AS service_name,
               s.code AS service_code
        FROM employees e
        LEFT JOIN services s ON s.id = e.service_id
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function findById(int $id): ?Employee
    {
        $sql = self::BASE_SELECT . ' WHERE e.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Employee::fromArray($row) : null;
    }

    public function findByMatricule(string $matricule): ?Employee
    {
        $sql = self::BASE_SELECT . ' WHERE e.matricule = :matricule LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['matricule' => $matricule]);
        $row = $stmt->fetch();

        return $row ? Employee::fromArray($row) : null;
    }

    public function findByEmail(string $email): ?Employee
    {
        $sql = self::BASE_SELECT . ' WHERE e.email = :email LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? Employee::fromArray($row) : null;
    }

    /**
     * Récupère TOUS les employés actifs (utilisé pour les listes déroulantes).
     *
     * @return Employee[]
     */
    public function findAllActive(): array
    {
        $sql = self::BASE_SELECT . '
                WHERE e.is_active = 1
                ORDER BY e.last_name ASC, e.first_name ASC';

        $stmt = $this->db->query($sql);
        return array_map(fn($r) => Employee::fromArray($r), $stmt->fetchAll());
    }

    /**
     * Récupère les employés d'un service donné.
     *
     * @return Employee[]
     */
    public function findByService(int $serviceId): array
    {
        $sql = self::BASE_SELECT . '
                WHERE e.service_id = :service_id
                  AND e.is_active = 1
                ORDER BY e.last_name ASC, e.first_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['service_id' => $serviceId]);

        return array_map(fn($r) => Employee::fromArray($r), $stmt->fetchAll());
    }

    /**
     * Recherche des employés par nom / prénom / matricule.
     *
     * @return Employee[]
     */
    public function search(string $term, int $limit = 20): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $sql = self::BASE_SELECT . '
                WHERE e.is_active = 1
                  AND (
                        e.first_name LIKE :s1
                     OR e.last_name  LIKE :s2
                     OR e.matricule  LIKE :s3
                     OR e.email      LIKE :s4
                  )
                ORDER BY e.last_name ASC, e.first_name ASC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $like = '%' . $term . '%';
        $stmt->bindValue(':s1', $like);
        $stmt->bindValue(':s2', $like);
        $stmt->bindValue(':s3', $like);
        $stmt->bindValue(':s4', $like);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Employee::fromArray($r), $stmt->fetchAll());
    }

    /**
     * Liste paginée avec filtres.
     *
     * @return array{data: Employee[], total: int, page: int, per_page: int, last_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1 = 1'];
        $params = [];

        // Recherche multi-champs
        if (!empty($filters['search'])) {
            $where[] = '(e.first_name LIKE :s1
                        OR e.last_name  LIKE :s2
                        OR e.matricule  LIKE :s3
                        OR e.email      LIKE :s4
                        OR e.function_title LIKE :s5)';
            $like = '%' . $filters['search'] . '%';
            $params['s1'] = $like;
            $params['s2'] = $like;
            $params['s3'] = $like;
            $params['s4'] = $like;
            $params['s5'] = $like;
        }

        // Filtre service
        if (!empty($filters['service_id'])) {
            $where[] = 'e.service_id = :service_id';
            $params['service_id'] = (int) $filters['service_id'];
        }

        // Filtre statut actif/inactif
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = 'e.is_active = :is_active';
            $params['is_active'] = (int) $filters['is_active'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Compter
        $countSql = 'SELECT COUNT(DISTINCT e.id) FROM employees e' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = max(1, min($page, $lastPage));
        $offset   = ($page - 1) * $perPage;

        // Récupérer les données
        $sql = self::BASE_SELECT . $whereSql . '
                ORDER BY e.last_name ASC, e.first_name ASC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'      => array_map(fn($r) => Employee::fromArray($r), $stmt->fetchAll()),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int
    {
        $sql = 'INSERT INTO employees
                    (service_id, first_name, last_name, matricule, email, phone,
                     function_title, is_active, created_at, updated_at)
                VALUES
                    (:service_id, :first_name, :last_name, :matricule, :email, :phone,
                     :function_title, :is_active, NOW(), NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->prepareData($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE employees SET
                    service_id     = :service_id,
                    first_name     = :first_name,
                    last_name      = :last_name,
                    matricule      = :matricule,
                    email          = :email,
                    phone          = :phone,
                    function_title = :function_title,
                    is_active      = :is_active,
                    updated_at     = NOW()
                WHERE id = :id';

        $params = $this->prepareData($data);
        $params['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM employees WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countActive(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM employees WHERE is_active = 1');
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM employees');
        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================

    /**
     * Nettoie les données : convertit les chaînes vides en NULL.
     */
    private function prepareData(array $data): array
    {
        $fields = [
            'service_id', 'first_name', 'last_name', 'matricule',
            'email', 'phone', 'function_title', 'is_active',
        ];

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