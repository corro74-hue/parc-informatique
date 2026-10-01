<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Services\Auth\AuthService;
use App\Services\Auth\TwoFactorService;

final class AuthController extends Controller
{
    // ============================================
    // AFFICHER LE FORMULAIRE DE CONNEXION
    // ============================================
    public function showLogin(Request $request): Response
    {
        if ($r = (new GuestMiddleware())->handle()) return $r;

        return $this->view('auth.login', [
            'title' => 'Connexion',
        ], 'auth');
    }

    // ============================================
    // TRAITER LA CONNEXION
    // ============================================
    public function login(Request $request): Response
    {
        if ($r = (new GuestMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if ($username === '' || $password === '') {
            $_SESSION['_old']['username'] = $username;
            flash('error', 'Veuillez remplir tous les champs.');
            return $this->redirect(url('login'));
        }

        $auth      = new AuthService();
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $result = $auth->attempt($username, $password, $ip, $userAgent);

        if (!$result['success']) {
            $_SESSION['_old']['username'] = $username;
            flash('error', $result['message']);
            return $this->redirect(url('login'));
        }

        if (!empty($result['requires_2fa'])) {
            flash('info', $result['message']);
            return $this->redirect(url('two-factor'));
        }

        $auth->login($result['user']);
        flash('success', 'Bienvenue, ' . $result['user']->getFullName() . ' !');

        return $this->redirectAfterLogin();
    }

    // ============================================
    // DÉCONNEXION
    // ============================================
    public function logout(Request $request): Response
    {
        $auth = new AuthService();
        $auth->logout();

        session_start();
        flash('success', 'Vous êtes déconnecté.');

        return $this->redirect(url('login'));
    }

    // ============================================
    // AFFICHER LA PAGE DE SAISIE DU CODE 2FA
    // ============================================
    public function showTwoFactor(Request $request): Response
    {
        $auth = new AuthService();

        if ($auth->isAuthenticated()) {
            return $this->redirect(url('dashboard'));
        }

        if (!$auth->isTwoFactorPending()) {
            flash('error', 'Aucune vérification en cours. Veuillez vous reconnecter.');
            return $this->redirect(url('login'));
        }

        $user = $auth->getTwoFactorUser();
        if (!$user) {
            $auth->cancelTwoFactorChallenge();
            flash('error', 'Utilisateur introuvable.');
            return $this->redirect(url('login'));
        }

        return $this->view('auth.two-factor', [
            'title' => 'Vérification 2FA',
            'user'  => $user,
        ], 'auth');
    }

    // ============================================
    // VÉRIFIER LE CODE 2FA
    // ============================================
    public function verifyTwoFactor(Request $request): Response
    {
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $auth = new AuthService();

        if (!$auth->isTwoFactorPending()) {
            flash('error', 'Session expirée. Veuillez vous reconnecter.');
            return $this->redirect(url('login'));
        }

        $user = $auth->getTwoFactorUser();
        if (!$user) {
            $auth->cancelTwoFactorChallenge();
            flash('error', 'Utilisateur introuvable.');
            return $this->redirect(url('login'));
        }

        $code = trim((string) $request->input('code', ''));

        if ($code === '') {
            flash('error', 'Veuillez saisir un code.');
            return $this->redirect(url('two-factor'));
        }

        $twoFactor = new TwoFactorService();
        $isValid   = false;

        // 1. Essayer comme TOTP (6 chiffres)
        $digitsOnly = preg_replace('/\s+/', '', $code);
        if (preg_match('/^\d{6}$/', $digitsOnly)) {
            if (!empty($user->twoFactorSecret)) {
                $isValid = $twoFactor->verifyCode($user->twoFactorSecret, $digitsOnly);
            }
        }

        // 2. Si échec, essayer comme backup code
        if (!$isValid) {
            $isValid = $twoFactor->verifyBackupCode($user->id, $code);
        }

        if (!$isValid) {
            flash('error', 'Code invalide. Vérifiez votre application d\'authentification.');
            return $this->redirect(url('two-factor'));
        }

        // 3. Code valide → connexion complète
        $auth->login($user);
        flash('success', 'Bienvenue, ' . $user->getFullName() . ' !');

        return $this->redirectAfterLogin();
    }

    // ============================================
    // ANNULER LA 2FA
    // ============================================
    public function cancelTwoFactor(Request $request): Response
    {
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $auth = new AuthService();
        $auth->cancelTwoFactorChallenge();

        flash('info', 'Vérification annulée. Vous pouvez vous reconnecter.');
        return $this->redirect(url('login'));
    }

    // ============================================
    // HELPER PRIVÉ : Redirection post-login
    // ============================================
    /**
     * Redirige vers l'URL "intended" (mémorisée par AuthMiddleware)
     * ou vers le dashboard. Sécurité : refuse les redirections externes.
     */
    private function redirectAfterLogin(): Response
    {
        $intended = $_SESSION['_intended_url'] ?? url('dashboard');
        unset($_SESSION['_intended_url']);

        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if ($basePath !== '' && !str_starts_with($intended, $basePath)) {
            $intended = url('dashboard');
        }

        return $this->redirect($intended);
    }
}