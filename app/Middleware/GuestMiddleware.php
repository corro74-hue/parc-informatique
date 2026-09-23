<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth\AuthService;

final class GuestMiddleware
{
    public function handle(): ?Response
    {
        $auth = new AuthService();

        // ============================================
        // Si l'utilisateur est déjà connecté,
        // on le redirige vers le tableau de bord
        // (il n'a rien à faire sur /login)
        // ============================================
        if ($auth->check()) {
            return Response::redirect(url('dashboard'));
        }

        return null;
    }
}