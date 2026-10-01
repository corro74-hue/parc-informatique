<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use PDO;

final class DocumentRepository implements DocumentRepositoryInterface
{
    private PDO $db;

    private const BASE_SELECT = '
        SELECT d.*,
               CONCAT(u.first_name, " ", u.last_name) AS created_by_name
        FROM documents d
        LEFT JOIN users u ON u.id = d.created_by
    ';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // DOCUMENTS
    // ============================================

    public function findById(int $id): ?Document
    {
        $sql = self::BASE_SELECT . ' WHERE d.id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Document::fromArray($row) : null;
    }

    public function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(d.title LIKE :s1 OR d.reference LIKE :s2)';
            $like = '%' . $filters['search'] . '%';
            $params['s1'] = $like;
            $params['s2'] = $like;
        }

        if (!empty($filters['type'])) {
            $where[] = 'd.type = :type';
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['entity_type'])) {
            $where[] = 'd.entity_type = :entity_type';
            $params['entity_type'] = $filters['entity_type'];
        }

        if (isset($filters['is_signed']) && $filters['is_signed'] !== '') {
            $where[] = 'd.is_signed = :is_signed';
            $params['is_signed'] = (int) $filters['is_signed'];
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        // Compter le total
        $countSql = 'SELECT COUNT(*) FROM documents d' . $whereSql;
        $stmt = $this->db->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $total = (int) $stmt->fetchColumn();

        $lastPage = max(1, (int) ceil($total / $perPage));
        $page     = max(1, min($page, $lastPage));
        $offset   = ($page - 1) * $perPage;

        // Récupérer la page
        $sql = self::BASE_SELECT . $whereSql . '
                ORDER BY d.created_at DESC, d.id DESC
                LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'      => array_map(fn($r) => Document::fromArray($r), $stmt->fetchAll()),
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => $lastPage,
        ];
    }

    public function findRecent(int $limit = 5): array
    {
        $sql = self::BASE_SELECT . '
                ORDER BY d.created_at DESC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn($r) => Document::fromArray($r), $stmt->fetchAll());
    }

    public function findByEntity(string $entityType, int $entityId): array
    {
        $sql = self::BASE_SELECT . '
                WHERE d.entity_type = :entity_type
                  AND d.entity_id = :entity_id
                ORDER BY d.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ]);

        return array_map(fn($r) => Document::fromArray($r), $stmt->fetchAll());
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO documents (
                    reference, type, title, entity_type, entity_id,
                    current_version, file_path, mime_type, size_bytes,
                    is_signed, created_by, created_at, updated_at
                ) VALUES (
                    :reference, :type, :title, :entity_type, :entity_id,
                    1, :file_path, :mime_type, :size_bytes,
                    :is_signed, :created_by, NOW(), NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reference'    => $data['reference'] ?? null,
            'type'         => $data['type'] ?? 'other',
            'title'        => $data['title'],
            'entity_type'  => $data['entity_type'] ?? null,
            'entity_id'    => $data['entity_id'] ?? null,
            'file_path'    => $data['file_path'],
            'mime_type'    => $data['mime_type'] ?? null,
            'size_bytes'   => $data['size_bytes'] ?? null,
            'is_signed'    => (int) ($data['is_signed'] ?? 0),
            'created_by'   => $data['created_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'title', 'type', 'entity_type', 'entity_id',
            'current_version', 'file_path', 'mime_type', 'size_bytes',
            'is_signed',
        ];

        $sets = [];
        $params = ['id' => $id];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        $sets[] = 'updated_at = NOW()';

        $sql = 'UPDATE documents SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function generateReference(): string
    {
        $year   = date('Y');
        $prefix = 'DOC-' . $year . '-';

        $stmt = $this->db->prepare(
            'SELECT reference FROM documents
             WHERE reference LIKE :pattern
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['pattern' => $prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $parts = explode('-', $last);
            $next  = ((int) end($parts)) + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s%04d', $prefix, $next);
    }

    public function incrementVersion(int $id): int
    {
        $stmt = $this->db->prepare(
            'UPDATE documents 
             SET current_version = current_version + 1,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $stmt = $this->db->prepare('SELECT current_version FROM documents WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // VERSIONS
    // ============================================

    public function findVersions(int $documentId): array
    {
        $sql = 'SELECT dv.*,
                       CONCAT(u.first_name, " ", u.last_name) AS created_by_name
                FROM document_versions dv
                LEFT JOIN users u ON u.id = dv.created_by
                WHERE dv.document_id = :document_id
                ORDER BY dv.version DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['document_id' => $documentId]);

        return array_map(fn($r) => DocumentVersion::fromArray($r), $stmt->fetchAll());
    }

    public function findVersion(int $versionId): ?DocumentVersion
    {
        $sql = 'SELECT dv.*,
                       CONCAT(u.first_name, " ", u.last_name) AS created_by_name
                FROM document_versions dv
                LEFT JOIN users u ON u.id = dv.created_by
                WHERE dv.id = :id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $versionId]);
        $row = $stmt->fetch();

        return $row ? DocumentVersion::fromArray($row) : null;
    }

    public function createVersion(array $data): int
    {
        $sql = 'INSERT INTO document_versions (
                    document_id, version, file_path, change_notes,
                    created_by, created_at
                ) VALUES (
                    :document_id, :version, :file_path, :change_notes,
                    :created_by, NOW()
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'document_id'  => (int) $data['document_id'],
            'version'      => (int) $data['version'],
            'file_path'    => $data['file_path'],
            'change_notes' => $data['change_notes'] ?? null,
            'created_by'   => $data['created_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteVersion(int $versionId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM document_versions WHERE id = :id');
        return $stmt->execute(['id' => $versionId]);
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countByType(): array
    {
        $stmt = $this->db->query(
            'SELECT type, COUNT(*) as total
             FROM documents
             GROUP BY type'
        );

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['type']] = (int) $row['total'];
        }
        return $result;
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) FROM documents');
        return (int) $stmt->fetchColumn();
    }

    public function countByEntity(string $entityType, int $entityId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM documents
             WHERE entity_type = :entity_type AND entity_id = :entity_id'
        );
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function totalSize(): int
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(size_bytes), 0) FROM documents');
        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // ACTIONS GROUPÉES
    // ============================================

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // Sécuriser les IDs (entiers positifs uniquement)
        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = self::BASE_SELECT . " WHERE d.id IN ({$placeholders})";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);

        return array_map(fn($r) => Document::fromArray($r), $stmt->fetchAll());
    }

    public function deleteByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "DELETE FROM documents WHERE id IN ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);

        return $stmt->rowCount();
    }
}