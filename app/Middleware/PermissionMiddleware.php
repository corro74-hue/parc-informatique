<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth\AuthService;

final class PermissionMiddleware
{
    /**
     * Vérifie que l'utilisateur connecté possède la permission demandée.
     *
     * @param string $permission Permission requise (ex: 'users.create')
     * @return Response|null Null si OK, sinon une réponse de redirection/erreur
     */
    public function handle(string $permission): ?Response
    {
        $auth = new AuthService();

        // 1. Utilisateur connecté ?
        if (!$auth->check()) {
            return Response::redirect(url('login'));
        }

        // 2. Permission accordée ?
        if (!$auth->can($permission)) {
            // Retourne une page 403 (accès refusé)
            return $this->forbidden($permission);
        }

        return null;
    }

    /**
     * Génère une réponse 403 personnalisée.
     */
    private function forbidden(string $permission): Response
    {
        // Si la requête est AJAX → réponse JSON
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        if ($isAjax) {
            return Response::json([
                'success' => false,
                'message' => 'Accès refusé. Permission requise : ' . $permission,
            ], 403);
        }

        // Sinon → page HTML 403
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>403 — Accès refusé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; }
        .error-box { text-align: center; padding: 40px; }
        .error-code { font-size: 6rem; font-weight: 800; color: #ef4444; line-height: 1; }
        .error-title { font-size: 1.5rem; color: #1e293b; margin: 15px 0 10px; }
        .error-message { color: #64748b; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-code">403</div>
        <div class="error-title">Accès refusé</div>
        <p class="error-message">
            Vous n\'avez pas la permission nécessaire pour accéder à cette page.<br>
            <small>Permission requise : <code>' . htmlspecialchars($permission, ENT_QUOTES, 'UTF-8') . '</code></small>
        </p>
        <a href="' . htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">
            ← Retour au tableau de bord
        </a>
    </div>
</body>
</html>';

        return new Response($html, 403);
    }
}