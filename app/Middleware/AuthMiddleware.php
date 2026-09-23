<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth\AuthService;

final class AuthMiddleware
{
    public function handle(): ?Response
    {
        $auth = new AuthService();

        // ============================================
        // 1. Vérifier que l'utilisateur est connecté
        // ============================================
        if (!$auth->check()) {
            // Mémoriser l'URL demandée pour y revenir après connexion
            $_SESSION['_intended_url'] = $_SERVER['REQUEST_URI'] ?? url('dashboard');

            return Response::redirect(url('login'));
        }

        // ============================================
        // 2. Vérifier que la session n'a pas expiré
        // ============================================
        $lifetime = (int) ($_ENV['SESSION_LIFETIME'] ?? 1800);

        if (!$auth->checkSessionExpiry($lifetime)) {
            $_SESSION['_flash']['error'] = 'Votre session a expiré. Veuillez vous reconnecter.';
            return Response::redirect(url('login'));
        }

        // Tout est OK, on continue
        return null;
    }
}