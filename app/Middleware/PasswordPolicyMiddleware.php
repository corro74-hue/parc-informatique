<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth\AuthService;
use App\Services\Security\PasswordPolicyService;

final class PasswordPolicyMiddleware
{
    /**
     * Vérifie si le mot de passe de l'utilisateur a expiré.
     * Si oui, redirige vers une page de changement obligatoire.
     */
    public function handle(): ?Response
    {
        $auth = new AuthService();

        // Utilisateur non connecté → pas de vérification
        if (!$auth->check()) {
            return null;
        }

        // ============================================
        // Liste des URLs à NE PAS bloquer
        // ============================================
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';

        $excludedUris = [
            '/login',
            '/logout',
            '/two-factor',
            '/profile',              // Toute la page profil (incluant change-password)
            '/profile/change-password',
            '/profile/security',
        ];

        foreach ($excludedUris as $uri) {
            if (str_contains($currentUri, $uri)) {
                return null;
            }
        }

        // Récupère l'utilisateur
        $user = $auth->user();
        if (!$user) {
            return null;
        }

        // ============================================
        // Vérification 1 : Flag "must_change_password"
        // ============================================
        if ($user->mustChangePassword) {
            flash('warning', 'Vous devez changer votre mot de passe avant de continuer.');
            return Response::redirect(url('profile?force_change=1'));
        }

        // ============================================
        // Vérification 2 : Expiration du mot de passe
        // ============================================
        $policy = new PasswordPolicyService();
        $expiration = $policy->isExpired($user->passwordChangedAt ?? null);

        if ($expiration['expired']) {
            flash('warning', 'Votre mot de passe a expiré. Veuillez le changer.');
            return Response::redirect(url('profile?force_change=1'));
        }

        // ============================================
        // Avertissement si proche de l'expiration (7 jours)
        // ============================================
        if ($expiration['days_remaining'] <= 7 && $expiration['days_remaining'] > 0) {
            $_SESSION['_password_expiry_warning'] = $expiration['days_remaining'];
        }

        return null;
    }
}