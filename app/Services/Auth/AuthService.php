<?php
declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\MySql\UserRepository;

final class AuthService
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const ATTEMPT_WINDOW_MINUTES = 15;

    private const TWO_FACTOR_SESSION_KEY = '_2fa_user_id';
    private const TWO_FACTOR_TIME_KEY    = '_2fa_started_at';
    private const TWO_FACTOR_TIMEOUT     = 300;

    private UserRepositoryInterface $users;

    public function __construct(?UserRepositoryInterface $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    public function attempt(string $username, string $password, string $ip, string $userAgent): array
    {
        $recentFailures = $this->users->countRecentFailedAttempts($username, self::ATTEMPT_WINDOW_MINUTES);
        if ($recentFailures >= self::MAX_ATTEMPTS) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return [
                'success' => false,
                'message' => 'Trop de tentatives échouées. Veuillez réessayer dans ' . self::LOCK_MINUTES . ' minutes.',
            ];
        }

        $user = $this->users->findByUsername($username);

        if (!$user) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return ['success' => false, 'message' => 'Identifiants invalides.'];
        }

        if (!$user->isActive) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return ['success' => false, 'message' => 'Ce compte est désactivé. Contactez l\'administrateur.'];
        }

        if ($user->isLocked()) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return ['success' => false, 'message' => 'Ce compte est temporairement verrouillé. Réessayez plus tard.'];
        }

        if (!password_verify($password, $user->passwordHash)) {
            $this->users->incrementFailedAttempts($user->id);
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);

            $attempts = $this->users->countRecentFailedAttempts($username, self::ATTEMPT_WINDOW_MINUTES);
            if ($attempts >= self::MAX_ATTEMPTS) {
                $this->users->lockAccount($user->id, self::LOCK_MINUTES);
            }

            return ['success' => false, 'message' => 'Identifiants invalides.'];
        }

        $this->users->resetFailedAttempts($user->id);
        $this->users->logLoginAttempt($username, $ip, $userAgent, true);

        $user = $this->users->findById($user->id) ?? $user;

        if ($user->twoFactorEnabled) {
            $this->startTwoFactorChallenge($user->id);
            return [
                'success'      => true,
                'requires_2fa' => true,
                'user'         => $user,
                'message'      => 'Veuillez saisir votre code d\'authentification.',
            ];
        }

        return ['success' => true, 'user' => $user, 'message' => 'Connexion réussie.'];
    }

    public function login(User $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->id;
        $_SESSION['username']      = $user->username;
        $_SESSION['full_name']     = $user->getFullName();
        $_SESSION['roles']         = array_column($user->roles, 'slug');
        $_SESSION['permissions']   = $user->permissions;
        $_SESSION['logged_in_at']  = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['must_change_password'] = $user->mustChangePassword;

        unset($_SESSION['_csrf_token']);
        unset($_SESSION[self::TWO_FACTOR_SESSION_KEY]);
        unset($_SESSION[self::TWO_FACTOR_TIME_KEY]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function user(): ?User
    {
        if (!$this->check()) {
            return null;
        }
        return $this->users->findById((int) $_SESSION['user_id']);
    }

    public function checkSessionExpiry(int $lifetime): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }

        if (time() - $_SESSION['last_activity'] > $lifetime) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }
        return in_array($permission, $_SESSION['permissions'] ?? [], true);
    }

    public function hasRole(string $slug): bool
    {
        if (!$this->check()) {
            return false;
        }
        return in_array($slug, $_SESSION['roles'] ?? [], true);
    }

    // ============================================
    // NOUVELLES MÉTHODES - 2FA
    // ============================================

    public function startTwoFactorChallenge(int $userId): void
    {
        session_regenerate_id(true);

        $_SESSION[self::TWO_FACTOR_SESSION_KEY] = $userId;
        $_SESSION[self::TWO_FACTOR_TIME_KEY]    = time();

        unset($_SESSION['_csrf_token']);
    }

    public function isTwoFactorPending(): bool
    {
        if (empty($_SESSION[self::TWO_FACTOR_SESSION_KEY])) {
            return false;
        }

        $startedAt = $_SESSION[self::TWO_FACTOR_TIME_KEY] ?? 0;
        if (time() - $startedAt > self::TWO_FACTOR_TIMEOUT) {
            $this->cancelTwoFactorChallenge();
            return false;
        }

        return true;
    }

    public function getTwoFactorUser(): ?User
    {
        if (!$this->isTwoFactorPending()) {
            return null;
        }

        $userId = (int) $_SESSION[self::TWO_FACTOR_SESSION_KEY];
        return $this->users->findById($userId);
    }

    public function cancelTwoFactorChallenge(): void
    {
        unset($_SESSION[self::TWO_FACTOR_SESSION_KEY]);
        unset($_SESSION[self::TWO_FACTOR_TIME_KEY]);
    }
}