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

    private function hydrate(array $row): User
    {
        $roles = $this->loadRoles((int) $row['id']);
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