<?php
declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function updateLastLogin(int $userId, string $ip): void;
    public function incrementFailedAttempts(int $userId): int;
    public function resetFailedAttempts(int $userId): void;
    public function lockAccount(int $userId, int $minutes): void;
    public function logLoginAttempt(string $username, string $ip, string $userAgent, bool $success): void;
    public function countRecentFailedAttempts(string $username, int $minutes): int;
}