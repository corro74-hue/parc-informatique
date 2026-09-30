<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Services\Auth\AuthService;
use App\Services\System\MaintenanceModeService;

/**
 * Middleware de gestion du mode maintenance.
 *
 * 🎯 RÔLE :
 *    Vérifie si le mode maintenance est actif. Si oui, redirige les
 *    visiteurs non-admin vers la page de maintenance. Les administrateurs
 *    peuvent continuer à utiliser l'application.
 *
 * 🛠️ UTILISATION :
 *    Appelé dans Router::dispatch() AVANT chaque requête.
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Les admins doivent pouvoir accéder au site pour désactiver le mode
 *    - Les routes critiques (login, logout, admin) doivent être exemptées
 *    - La page de maintenance doit être légère (pas de BDD)
 *
 * 💡 BONNES PRATIQUES :
 *    - Toujours tester la désactivation du mode maintenance
 *    - Ne pas activer le mode sans raison valable
 *    - Prévenir les utilisateurs à l'avance
 */
final class MaintenanceMiddleware
{
    /**
     * URLs exemptées du mode maintenance.
     */
    private const EXCLUDED_URLS = [
        '/login',
        '/logout',
        '/maintenance-mode',           // La page publique de maintenance (mode système)
        '/admin/system/health',        // Permet aux admins de désactiver
        '/admin/system/maintenance',   // Actions sur le mode maintenance
    ];

    /**
     * Vérifie si le mode maintenance doit bloquer la requête.
     *
     * @return Response|null Null si OK, sinon la réponse de redirection
     */
    public function handle(): ?Response
    {
        $maintenance = new MaintenanceModeService();

        // 1. Si le mode maintenance n'est pas actif → OK
        if (!$maintenance->isActive()) {
            return null;
        }

        // 2. Vérifier si l'URL actuelle est exemptée
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        if ($this->isExcluded($currentUri)) {
            return null;
        }

        // 3. Vérifier si l'utilisateur est admin connecté
        $auth = new AuthService();
        if ($auth->check() && $auth->hasRole('admin')) {
            // Les admins peuvent continuer à travailler
            return null;
        }

        // 4. Sinon → rediriger vers la page de maintenance (mode système)
        return Response::redirect(url('maintenance-mode'));
    }

    /**
     * Vérifie si l'URL actuelle est exemptée du mode maintenance.
     */
    private function isExcluded(string $uri): bool
    {
        foreach (self::EXCLUDED_URLS as $excluded) {
            if (str_contains($uri, $excluded)) {
                return true;
            }
        }

        return false;
    }
}