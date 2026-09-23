<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use PDO;

/**
 * Repository pour lire le journal d'audit (audit_logs).
 */
final class AuditRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupère l'historique d'une entité donnée (ex: un équipement).
     *
     * @return array<int, array<string, mixed>>
     */
    public function findByEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        $sql = 'SELECT *
                FROM audit_logs
                WHERE entity_type = :entity_type
                  AND entity_id = :entity_id
                ORDER BY created_at DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':entity_type', $entityType);
        $stmt->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Liste paginée du journal global avec filtres.
     *
     * @param array{search?: string, action?: string, entity_type?: string, user_id?: int, severity?: string} $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, page: int, last_page: int, per_page: int}
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where = ['1 = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(user_name LIKE :search1
                        OR action LIKE :search2
                        OR entity_type LIKE :search3
                        OR url LIKE :search4)';
            $searchTerm = '%' . $filters['search'] . '%';
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
            $params['search4'] = $searchTerm;
        }

        if (!empty($filters['action'])) {
            $where[] = 'action = :action';
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'entity_type = :entity_type';
            $params['entity_type'] = $filters['entity_type'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = (int) $filters['user_id'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = :severity';
            $params['severity'] = $filters['severity'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Compter le total
        $countSql = 'SELECT COUNT(*) FROM audit_logs' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        // Pagination
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $lastPage));
        $offset = ($page - 1) * $perPage;

        // Récupérer les données
        $sql = 'SELECT * FROM audit_logs' . $whereSql
             . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'      => $stmt->fetchAll(),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    /**
     * Récupère les actions distinctes (pour les filtres).
     *
     * @return string[]
     */
    public function getDistinctActions(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT action FROM audit_logs ORDER BY action');
        return array_column($stmt->fetchAll(), 'action');
    }

    /**
     * Récupère les types d'entités distincts (pour les filtres).
     *
     * @return string[]
     */
    public function getDistinctEntityTypes(): array
    {
        $stmt = $this->db->query('SELECT DISTINCT entity_type FROM audit_logs WHERE entity_type IS NOT NULL ORDER BY entity_type');
        return array_column($stmt->fetchAll(), 'entity_type');
    }

    /**
     * Compte les entrées par sévérité (pour un petit widget).
     *
     * @return array<string, int>
     */
    public function countBySeverity(): array
    {
        $stmt = $this->db->query(
            'SELECT severity, COUNT(*) AS total FROM audit_logs GROUP BY severity'
        );
        $result = ['info' => 0, 'warning' => 0, 'error' => 0, 'critical' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['severity']] = (int) $row['total'];
        }
        return $result;
    }
}