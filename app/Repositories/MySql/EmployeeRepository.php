<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Employee;
use PDO;

final class EmployeeRepository
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
}