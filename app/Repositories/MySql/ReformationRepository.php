<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Reformation;
use App\Models\ReformationDecision;
use App\Models\ReformationItem;
use App\Repositories\Contracts\ReformationRepositoryInterface;
use PDO;

final class ReformationRepository implements ReformationRepositoryInterface
{
    private PDO $db;

    private const BASE_SELECT = '
        SELECT r.*,
               CONCAT(u1.first_name, " ", u1.last_name) AS created_by_name,
               CONCAT(u2.first_name, " ", u2.last_name) AS proposed_by_name,
               CONCAT(u3.first_name, " ", u3.last_name) AS decided_by_name
        FROM reformations r
        LEFT JOIN users u1 ON u1.id = r.created_by
        LEFT JOIN users u2 ON u2.id = r.proposed_by
        LEFT JOIN users u3 ON u3.id = r.decided_by
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // RÉFORMES
    // ============================================

    public function findById(int $id): ?Reformation
    {
        $sql = self::BASE_SELECT . ' WHERE r.id = :id AND r.deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Reformation::fromArray($row) : null;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['r.deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(r.reference LIKE :s1 OR r.title LIKE :s2 OR r.commission_reference LIKE :s3)';
            $like = '%' . $filters['search'] . '%';
            $params['s1'] = $like;
            $params['s2'] = $like;
            $params['s3'] = $like;
        }

        if (!empty($filters['status'])) {
            $where[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['reason'])) {
            $where[] = 'r.reason = :reason';
            $params['reason'] = $filters['reason'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $countSql = 'SELECT COUNT(*) FROM reformations r' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = max(1, min($page, $lastPage));
        $offset   = ($page - 1) * $perPage;

        $sql = self::BASE_SELECT . $whereSql . '
                ORDER BY r.created_at DESC, r.id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'      => array_map(fn($r) => Reformation::fromArray($r), $stmt->fetchAll()),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    public function findRecent(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . '
                WHERE r.deleted_at IS NULL
                ORDER BY r.created_at DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Reformation::fromArray($r), $stmt->fetchAll());
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO reformations (
                    reference, title, reason, reason_details, status,
                    proposed_by, proposed_at, commission_reference, meeting_date,
                    total_value, created_by, created_at
                ) VALUES (
                    :reference, :title, :reason, :reason_details, :status,
                    :proposed_by, :proposed_at, :commission_reference, :meeting_date,
                    0, :created_by, NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reference'             => $data['reference'],
            'title'                 => $data['title'],
            'reason'                => $data['reason'],
            'reason_details'        => $data['reason_details'] ?? null,
            'status'                => $data['status'] ?? 'draft',
            'proposed_by'           => $data['proposed_by'] ?? null,
            'proposed_at'           => $data['proposed_at'] ?? null,
            'commission_reference'  => $data['commission_reference'] ?? null,
            'meeting_date'          => $data['meeting_date'] ?? null,
            'created_by'            => $data['created_by'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE reformations SET
                    title                = :title,
                    reason               = :reason,
                    reason_details       = :reason_details,
                    status               = :status,
                    proposed_by          = :proposed_by,
                    submitted_at         = :submitted_at,
                    decided_at           = :decided_at,
                    decided_by           = :decided_by,
                    decision_notes       = :decision_notes,
                    commission_reference = :commission_reference,
                    meeting_date         = :meeting_date,
                    pv_path              = :pv_path,
                    pv_generated_at      = :pv_generated_at,
                    exit_voucher_path    = :exit_voucher_path,
                    exit_voucher_generated_at = :exit_voucher_generated_at,
                    updated_at           = NOW()
                WHERE id = :id AND deleted_at IS NULL';

        $fields = [
            'title', 'reason', 'reason_details', 'status',
            'proposed_by', 'submitted_at', 'decided_at', 'decided_by',
            'decision_notes', 'commission_reference', 'meeting_date',
            'pv_path', 'pv_generated_at', 'exit_voucher_path', 'exit_voucher_generated_at',
        ];

        $params = ['id' => $id];
        foreach ($fields as $field) {
            $params[$field] = $data[$field] ?? null;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE reformations SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute(['id' => $id]);
    }

    public function generateReference(): string
    {
        $year = date('Y');
        $prefix = 'REF-' . $year . '-';

        $stmt = $this->db->prepare(
            'SELECT reference FROM reformations
             WHERE reference LIKE :pattern
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['pattern' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $parts = explode('-', $last);
            $next = ((int) end($parts)) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s%04d', $prefix, $next);
    }

    // ============================================
    // ITEMS
    // ============================================

    public function findItems(int $reformationId): array
    {
        $sql = 'SELECT ri.*,
                       e.inventory_number AS equipment_inventory_number,
                       e.designation      AS equipment_designation,
                       ec.name            AS equipment_category_name,
                       es.name            AS equipment_status_name,
                       es.color           AS equipment_status_color
                FROM reformation_items ri
                LEFT JOIN equipment e             ON e.id  = ri.equipment_id
                LEFT JOIN equipment_categories ec ON ec.id = e.category_id
                LEFT JOIN equipment_statuses es   ON es.id = e.status_id
                WHERE ri.reformation_id = :reformation_id
                ORDER BY ri.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reformation_id' => $reformationId]);

        return array_map(fn($r) => ReformationItem::fromArray($r), $stmt->fetchAll());
    }

    public function findItem(int $itemId): ?ReformationItem
    {
        $sql = 'SELECT ri.*,
                       e.inventory_number AS equipment_inventory_number,
                       e.designation      AS equipment_designation
                FROM reformation_items ri
                LEFT JOIN equipment e ON e.id = ri.equipment_id
                WHERE ri.id = :id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $itemId]);
        $row = $stmt->fetch();

        return $row ? ReformationItem::fromArray($row) : null;
    }

    public function addItem(array $data): int
    {
        $sql = 'INSERT INTO reformation_items
                    (reformation_id, equipment_id, quantity, estimated_value, condition_notes, created_at)
                VALUES
                    (:reformation_id, :equipment_id, :quantity, :estimated_value, :condition_notes, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reformation_id'  => (int) $data['reformation_id'],
            'equipment_id'    => (int) $data['equipment_id'],
            'quantity'        => (int) ($data['quantity'] ?? 1),
            'estimated_value' => (float) ($data['estimated_value'] ?? 0),
            'condition_notes' => $data['condition_notes'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function removeItem(int $itemId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM reformation_items WHERE id = :id');
        return $stmt->execute(['id' => $itemId]);
    }

    public function removeAllItems(int $reformationId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM reformation_items WHERE reformation_id = :id');
        return $stmt->execute(['id' => $reformationId]);
    }

    public function recomputeTotalValue(int $reformationId): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(estimated_value * quantity), 0)
             FROM reformation_items
             WHERE reformation_id = :id'
        );
        $stmt->execute(['id' => $reformationId]);
        $total = (float) $stmt->fetchColumn();

        $update = $this->db->prepare(
            'UPDATE reformations SET total_value = :total, updated_at = NOW() WHERE id = :id'
        );
        $update->execute(['total' => $total, 'id' => $reformationId]);

        return $total;
    }

    // ============================================
    // DÉCISIONS
    // ============================================

    public function findDecisions(int $reformationId): array
    {
        $sql = 'SELECT rd.*,
                       CONCAT(u.first_name, " ", u.last_name) AS created_by_name
                FROM reformation_decisions rd
                LEFT JOIN users u ON u.id = rd.created_by
                WHERE rd.reformation_id = :reformation_id
                ORDER BY rd.decision_date DESC, rd.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reformation_id' => $reformationId]);

        return array_map(fn($r) => ReformationDecision::fromArray($r), $stmt->fetchAll());
    }

    public function createDecision(array $data): int
    {
        $sql = 'INSERT INTO reformation_decisions
                    (reformation_id, decision, decision_date, commission_members, notes, document_path, created_by, created_at)
                VALUES
                    (:reformation_id, :decision, :decision_date, :commission_members, :notes, :document_path, :created_by, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reformation_id'      => (int) $data['reformation_id'],
            'decision'            => $data['decision'],
            'decision_date'       => $data['decision_date'],
            'commission_members'  => $data['commission_members'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'document_path'       => $data['document_path'] ?? null,
            'created_by'          => $data['created_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ============================================
    // WORKFLOW LOGS
    // ============================================

    public function findWorkflowLogs(int $reformationId): array
    {
        $sql = 'SELECT wl.*,
                       CONCAT(u.first_name, " ", u.last_name) AS user_name
                FROM reformation_workflow_logs wl
                LEFT JOIN users u ON u.id = wl.user_id
                WHERE wl.reformation_id = :reformation_id
                ORDER BY wl.created_at DESC, wl.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reformation_id' => $reformationId]);

        return $stmt->fetchAll();
    }

    public function logWorkflow(array $data): void
    {
        $sql = 'INSERT INTO reformation_workflow_logs
                    (reformation_id, from_status, to_status, action, comment, user_id, created_at)
                VALUES
                    (:reformation_id, :from_status, :to_status, :action, :comment, :user_id, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reformation_id' => (int) $data['reformation_id'],
            'from_status'    => $data['from_status'] ?? null,
            'to_status'      => $data['to_status'],
            'action'         => $data['action'],
            'comment'        => $data['comment'] ?? null,
            'user_id'        => $data['user_id'] ?? null,
        ]);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countByStatus(): array
    {
        $stmt = $this->db->query(
            'SELECT status, COUNT(*) as total
             FROM reformations
             WHERE deleted_at IS NULL
             GROUP BY status'
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['status']] = (int) $row['total'];
        }
        return $result;
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM reformations WHERE deleted_at IS NULL');
        return (int) $stmt->fetchColumn();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM reformations
             WHERE deleted_at IS NULL
               AND status NOT IN ("completed", "cancelled", "rejected")'
        );
        return (int) $stmt->fetchColumn();
    }
}