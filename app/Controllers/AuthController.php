<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;
use App\Services\Auth\AuthService;

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
        // Blocage si déjà connecté
        if ($r = (new GuestMiddleware())->handle()) return $r;

        // Vérification du token CSRF
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        // Validation basique
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

        // ============================================
        // CONNEXION RÉUSSIE
        // ============================================
        $auth->login($result['user']);
        flash('success', 'Bienvenue, ' . $result['user']->getFullName() . ' !');

        // Redirection vers la page initialement demandée
        // ou vers le dashboard par défaut
        $intended = $_SESSION['_intended_url'] ?? url('dashboard');
        unset($_SESSION['_intended_url']);

        // Sécurité : s'assurer que l'URL de destination
        // commence bien par BASE_PATH (évite les redirections malveillantes)
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        if ($basePath !== '' && !str_starts_with($intended, $basePath)) {
            $intended = url('dashboard');
        }

        return $this->redirect($intended);
    }

    // ============================================
    // DÉCONNEXION
    // ============================================
    public function logout(Request $request): Response
    {
        $auth = new AuthService();
        $auth->logout();

        // Relancer une session pour pouvoir afficher un message flash
        session_start();
        flash('success', 'Vous êtes déconnecté.');

        return $this->redirect(url('login'));
    }
}