<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Security\RateLimitService;

final class RateLimitMiddleware
{
    /**
     * Vérifie le rate limit pour la requête courante.
     *
     * @param string $route       Route cible (ex: '/login', '/two-factor')
     * @param int    $maxHits     Nombre maximum de requêtes autorisées
     * @param int    $windowSec   Fenêtre de temps (secondes)
     * @param int    $blockSec    Durée de blocage (secondes) si dépassement
     * @return Response|null      Null si OK, sinon une réponse 429
     */
    public function handle(
        string $route,
        int $maxHits = 60,
        int $windowSec = 60,
        int $blockSec = 300
    ): ?Response {
        $identifier = $this->getIdentifier();
        $service    = new RateLimitService();

        $result = $service->check($identifier, $route, $maxHits, $windowSec, $blockSec);

        if (!$result['allowed']) {
            return $this->tooManyRequests($result['retry_after'] ?? 0);
        }

        return null;
    }

    /**
     * Réinitialise le rate limit (à appeler après un login réussi par ex).
     */
    public function reset(string $route): void
    {
        $identifier = $this->getIdentifier();
        $service    = new RateLimitService();
        $service->reset($identifier, $route);
    }

    /**
     * Retourne l'identifiant unique pour le rate limiting.
     * On utilise l'IP (pour les visiteurs) ou l'IP + user_id (pour les connectés).
     */
    private function getIdentifier(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            return 'user:' . $userId . '|ip:' . $ip;
        }

        return 'ip:' . $ip;
    }

    /**
     * Réponse 429 — Too Many Requests.
     */
    private function tooManyRequests(int $retryAfter): Response
    {
        // Réponse JSON si AJAX
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        if ($isAjax) {
            http_response_code(429);
            header('Retry-After: ' . $retryAfter);
            return Response::json([
                'success' => false,
                'message' => 'Trop de requêtes. Veuillez réessayer dans ' . $retryAfter . ' secondes.',
            ], 429);
        }

        // Réponse HTML
        $minutes = ceil($retryAfter / 60);
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>429 — Trop de requêtes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; }
        .error-box { text-align: center; padding: 40px; }
        .error-code { font-size: 6rem; font-weight: 800; color: #f59e0b; line-height: 1; }
        .error-title { font-size: 1.5rem; color: #1e293b; margin: 15px 0 10px; }
        .error-message { color: #64748b; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-code">429</div>
        <div class="error-title">Trop de requêtes</div>
        <p class="error-message">
            Vous avez effectué trop de requêtes en peu de temps.<br>
            Veuillez réessayer dans <strong>' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '</strong>.
        </p>
        <a href="' . htmlspecialchars(url('dashboard'), ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">
            ← Retour au tableau de bord
        </a>
    </div>
</body>
</html>';

        http_response_code(429);
        header('Retry-After: ' . $retryAfter);

        return new Response($html, 429);
    }
}