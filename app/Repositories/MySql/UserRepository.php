<?php
declare(strict_types=1);

namespace App\Repositories\MySql;

use App\Core\Database;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use PDO;

final class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? $this->hydrate($row) : null;
    }

    public function updateLastLogin(int $userId, string $ip): void
    {
        $stmt = $this->db->prepare('UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id');
        $stmt->execute(['id' => $userId, 'ip' => $ip]);
    }

    public function incrementFailedAttempts(int $userId): int
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        $stmt = $this->db->prepare('SELECT failed_attempts FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function resetFailedAttempts(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function lockAccount(int $userId, int $minutes): void
    {
        $stmt = $this->db->prepare('UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL :min MINUTE) WHERE id = :id');
        $stmt->execute(['id' => $userId, 'min' => $minutes]);
    }

    public function logLoginAttempt(string $username, string $ip, string $userAgent, bool $success): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO login_attempts (username, ip_address, user_agent, success) VALUES (:username, :ip, :ua, :success)'
        );
        $stmt->execute([
            'username' => $username,
            'ip'       => $ip,
            'ua'       => mb_substr($userAgent, 0, 255),
            'success'  => $success ? 1 : 0,
        ]);
    }

    public function countRecentFailedAttempts(string $username, int $minutes): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE username = :username
               AND success = 0
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL :min MINUTE)'
        );
        $stmt->execute(['username' => $username, 'min' => $minutes]);

        return (int) $stmt->fetchColumn();
    }

    // ============================================
    // NOUVELLES MÉTHODES - CRUD UTILISATEURS
    // ============================================

    public function findAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        // Filtre : recherche (username, email, first_name, last_name)
        if (!empty($filters['search'])) {
            $where[] = '(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Filtre : rôle
        if (!empty($filters['role'])) {
            $where[] = 'EXISTS (
                SELECT 1 FROM user_roles ur
                JOIN roles r ON r.id = ur.role_id
                WHERE ur.user_id = users.id AND r.slug = :role
            )';
            $params['role'] = $filters['role'];
        }

        // Filtre : statut
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = 'is_active = 1';
            } elseif ($filters['status'] === 'inactive') {
                $where[] = 'is_active = 0';
            } elseif ($filters['status'] === 'locked') {
                $where[] = 'locked_until IS NOT NULL AND locked_until > NOW()';
            }
        }

        $whereSql = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $sql = "SELECT * FROM users
                WHERE {$whereSql}
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $rows);
    }

    public function countAll(array $filters = []): int
    {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(username LIKE :search OR email LIKE :search OR first_name LIKE :search OR last_name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['role'])) {
            $where[] = 'EXISTS (
                SELECT 1 FROM user_roles ur
                JOIN roles r ON r.id = ur.role_id
                WHERE ur.user_id = users.id AND r.slug = :role
            )';
            $params['role'] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = 'is_active = 1';
            } elseif ($filters['status'] === 'inactive') {
                $where[] = 'is_active = 0';
            } elseif ($filters['status'] === 'locked') {
                $where[] = 'locked_until IS NOT NULL AND locked_until > NOW()';
            }
        }

        $whereSql = implode(' AND ', $where);
        $stmt     = $this->db->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (
                username, email, password_hash, first_name, last_name,
                phone, is_active, must_change_password, created_at, updated_at
             ) VALUES (
                :username, :email, :password_hash, :first_name, :last_name,
                :phone, :is_active, :must_change_password, NOW(), NOW()
             )'
        );

        $stmt->execute([
            'username'             => $data['username'],
            'email'                => $data['email'],
            'password_hash'        => $data['password_hash'],
            'first_name'           => $data['first_name'],
            'last_name'            => $data['last_name'],
            'phone'                => $data['phone'] ?? null,
            'is_active'            => $data['is_active'] ?? 1,
            'must_change_password' => $data['must_change_password'] ?? 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields  = [];
        $params  = ['id' => $id];

        $allowedFields = [
            'username', 'email', 'first_name', 'last_name',
            'phone', 'is_active', 'must_change_password',
        ];

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
        $sql      = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id AND deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
        return $stmt->execute(['id' => $id]);
    }

    public function restore(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET deleted_at = NULL WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function updatePassword(int $id, string $passwordHash, bool $mustChange = false): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password_hash = :hash, must_change_password = :must_change, updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            'id'          => $id,
            'hash'        => $passwordHash,
            'must_change' => $mustChange ? 1 : 0,
        ]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id'
        );

        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // RÔLES ET PERMISSIONS
    // ============================================

    public function findAllRoles(): array
    {
        $stmt = $this->db->query('SELECT id, name, slug FROM roles ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function findAllPermissions(): array
    {
        $stmt = $this->db->query('SELECT id, name, slug, description FROM permissions ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        // Supprime tous les rôles existants
        $stmt = $this->db->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        // Insère les nouveaux rôles
        if (!empty($roleIds)) {
            $stmt = $this->db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');

            foreach ($roleIds as $roleId) {
                $stmt->execute([
                    'user_id' => $userId,
                    'role_id' => (int) $roleId,
                ]);
            }
        }
    }

    // ============================================
    // HISTORIQUE ET SESSIONS
    // ============================================

    public function getLoginHistory(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            'SELECT la.*, u.username
             FROM login_attempts la
             JOIN users u ON u.username = la.username
             WHERE u.id = :user_id
             ORDER BY la.attempted_at DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getActiveSessions(int $userId): array
    {
        // Vérifie si la table sessions existe
        try {
            $stmt = $this->db->prepare(
                'SELECT * FROM sessions WHERE user_id = :user_id ORDER BY last_activity DESC'
            );
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // La table n'existe pas encore, on retourne un tableau vide
            return [];
        }
    }

    public function revokeAllSessions(int $userId): void
    {
        try {
            $stmt = $this->db->prepare('DELETE FROM sessions WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);
        } catch (\PDOException $e) {
            // La table n'existe pas encore, on ignore
        }
    }

    // ============================================
    // MÉTHODES PRIVÉES
    // ============================================

    private function hydrate(array $row): User
    {
        $roles       = $this->loadRoles((int) $row['id']);
        $permissions = $this->loadPermissions((int) $row['id']);

        return User::fromArray(array_merge($row, [
            'roles'       => $roles,
            'permissions' => $permissions,
        ]));
    }

    private function loadRoles(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT r.id, r.name, r.slug
             FROM roles r
             JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    private function loadPermissions(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT p.name
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = :user_id'
        );
        $stmt->execute(['user_id' => $userId]);

        return array_column($stmt->fetchAll(), 'name');
    }
}