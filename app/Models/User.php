<?php
declare(strict_types=1);

namespace App\Models;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $phone,
        public readonly ?string $avatar,
        public readonly bool $isActive,
        public readonly bool $mustChangePassword,
        public readonly ?string $lastLoginAt,
        public readonly int $failedAttempts,
        public readonly ?string $lockedUntil,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly array $roles = [],
        public readonly array $permissions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                   (int) $data['id'],
            username:             $data['username'],
            email:                $data['email'],
            passwordHash:         $data['password_hash'],
            firstName:            $data['first_name'],
            lastName:             $data['last_name'],
            phone:                $data['phone'] ?? null,
            avatar:               $data['avatar'] ?? null,
            isActive:             (bool) $data['is_active'],
            mustChangePassword:   (bool) $data['must_change_password'],
            lastLoginAt:          $data['last_login_at'] ?? null,
            failedAttempts:       (int) ($data['failed_attempts'] ?? 0),
            lockedUntil:          $data['locked_until'] ?? null,
            createdAt:            $data['created_at'],
            updatedAt:            $data['updated_at'] ?? null,
            roles:                $data['roles'] ?? [],
            permissions:          $data['permissions'] ?? [],
        );
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getInitials(): string
    {
        return strtoupper(
            mb_substr($this->firstName, 0, 1) . mb_substr($this->lastName, 0, 1)
        );
    }

    public function isLocked(): bool
    {
        if ($this->lockedUntil === null) {
            return false;
        }
        return strtotime($this->lockedUntil) > time();
    }

    public function hasRole(string $slug): bool
    {
        return in_array($slug, array_column($this->roles, 'slug'), true);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}