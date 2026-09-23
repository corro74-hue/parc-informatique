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

    private UserRepositoryInterface $users;

    public function __construct(?UserRepositoryInterface $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    /**
     * Tentative de connexion
     *
     * @return array{success: bool, user?: User, message: string}
     */
    public function attempt(string $username, string $password, string $ip, string $userAgent): array
    {
        // 1. Vérifier le rate limiting global (par username)
        $recentFailures = $this->users->countRecentFailedAttempts($username, self::ATTEMPT_WINDOW_MINUTES);
        if ($recentFailures >= self::MAX_ATTEMPTS) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return [
                'success' => false,
                'message' => 'Trop de tentatives échouées. Veuillez réessayer dans ' . self::LOCK_MINUTES . ' minutes.',
            ];
        }

        // 2. Chercher l'utilisateur
        $user = $this->users->findByUsername($username);

        if (!$user) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return [
                'success' => false,
                'message' => 'Identifiants invalides.',
            ];
        }

        // 3. Compte actif ?
        if (!$user->isActive) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return [
                'success' => false,
                'message' => 'Ce compte est désactivé. Contactez l\'administrateur.',
            ];
        }

        // 4. Compte verrouillé ?
        if ($user->isLocked()) {
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);
            return [
                'success' => false,
                'message' => 'Ce compte est temporairement verrouillé. Réessayez plus tard.',
            ];
        }

        // 5. Vérifier le mot de passe
        if (!password_verify($password, $user->passwordHash)) {
            $this->users->incrementFailedAttempts($user->id);
            $this->users->logLoginAttempt($username, $ip, $userAgent, false);

            // Verrouiller si trop d'échecs
            $attempts = $this->users->countRecentFailedAttempts($username, self::ATTEMPT_WINDOW_MINUTES);
            if ($attempts >= self::MAX_ATTEMPTS) {
                $this->users->lockAccount($user->id, self::LOCK_MINUTES);
            }

            return [
                'success' => false,
                'message' => 'Identifiants invalides.',
            ];
        }

        // 6. Connexion réussie
        $this->users->resetFailedAttempts($user->id);
        $this->users->updateLastLogin($user->id, $ip);
        $this->users->logLoginAttempt($username, $ip, $userAgent, true);

        // Recharger l'utilisateur pour avoir les rôles/permissions à jour
        $user = $this->users->findById($user->id) ?? $user;

        return [
            'success' => true,
            'user'    => $user,
            'message' => 'Connexion réussie.',
        ];
    }

    /**
     * Connecte l'utilisateur (stocke en session)
     */
    public function login(User $user): void
    {
        // Protection contre la fixation de session
        session_regenerate_id(true);

        $_SESSION['user_id']       = $user->id;
        $_SESSION['username']      = $user->username;
        $_SESSION['full_name']     = $user->getFullName();
        $_SESSION['roles']         = array_column($user->roles, 'slug');
        $_SESSION['permissions']   = $user->permissions;
        $_SESSION['logged_in_at']  = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['must_change_password'] = $user->mustChangePassword;

        // Rotation du token CSRF
        unset($_SESSION['_csrf_token']);
    }

    /**
     * Déconnecte l'utilisateur
     */
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

    /**
     * Vérifie si un utilisateur est connecté
     */
    public function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Retourne l'utilisateur connecté
     */
    public function user(): ?User
    {
        if (!$this->check()) {
            return null;
        }

        return $this->users->findById((int) $_SESSION['user_id']);
    }

    /**
     * Vérifie l'expiration de la session
     */
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

    /**
     * Vérifie une permission
     */
    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }
        return in_array($permission, $_SESSION['permissions'] ?? [], true);
    }

    /**
     * Vérifie un rôle
     */
    public function hasRole(string $slug): bool
    {
        if (!$this->check()) {
            return false;
        }
        return in_array($slug, $_SESSION['roles'] ?? [], true);
    }
}