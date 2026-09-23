<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;

final class CsrfMiddleware
{
    public function handle(): ?Response
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!verify_csrf($token)) {
            return Response::html(
                '<h1>419 — Session expirée</h1><p>Veuillez recharger la page et réessayer.</p>',
                419
            );
        }

        return null;
    }
}