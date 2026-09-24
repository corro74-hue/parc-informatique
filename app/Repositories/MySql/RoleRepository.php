<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Repositories\Contracts\RoleRepositoryInterface;
use PDO;

final class RoleRepository implements RoleRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function findAll(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM roles ORDER BY is_system DESC, name ASC'
        );
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    // ============================================
    // ÉCRITURE
    // ============================================

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO roles (name, slug, description, is_system, created_at, updated_at)
             VALUES (:name, :slug, :description, :is_system, NOW(), NOW())'
        );

        $stmt->execute([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system'   => $data['is_system'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        $allowedFields = ['name', 'slug', 'description'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = 'updated_at = NOW()';
        $sql = 'UPDATE roles SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        // Sécurité : ne supprime que les rôles non-système
        $stmt = $this->db->prepare('DELETE FROM roles WHERE id = :id AND is_system = 0');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // ============================================
    // PERMISSIONS
    // ============================================

    public function getPermissions(int $roleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :role_id
             ORDER BY p.module ASC, p.name ASC'
        );
        $stmt->execute(['role_id' => $roleId]);

        return $stmt->fetchAll();
    }

    public function getPermissionIds(int $roleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT permission_id FROM role_permissions WHERE role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);

        return array_map('intval', array_column($stmt->fetchAll(), 'permission_id'));
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        // Supprime toutes les permissions existantes
        $stmt = $this->db->prepare('DELETE FROM role_permissions WHERE role_id = :role_id');
        $stmt->execute(['role_id' => $roleId]);

        // Insère les nouvelles permissions
        if (!empty($permissionIds)) {
            $stmt = $this->db->prepare(
                'INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)'
            );

            foreach ($permissionIds as $permissionId) {
                $stmt->execute([
                    'role_id'       => $roleId,
                    'permission_id' => (int) $permissionId,
                ]);
            }
        }
    }

    public function getPermissionsGroupedByModule(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM permissions ORDER BY module ASC, name ASC'
        );

        $grouped = [];
        foreach ($stmt->fetchAll() as $permission) {
            $module = $permission['module'];
            if (!isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][] = $permission;
        }

        return $grouped;
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function countUsers(int $roleId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM user_roles WHERE role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);

        return (int) $stmt->fetchColumn();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM roles WHERE slug = :slug AND id != :id'
            );
            $stmt->execute(['slug' => $slug, 'id' => $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM roles WHERE slug = :slug'
            );
            $stmt->execute(['slug' => $slug]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }
}