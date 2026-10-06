<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Database;
use App\Core\Response;
use App\Services\System\MaintenanceModeService;

/**
 * Middleware de gestion du mode maintenance.
 *
 * ⚠️ VERSION ROBUSTE : la vérification admin se fait DIRECTEMENT en BDD,
 * sans dépendre de $_SESSION['roles'] (qui peut être vide).
 */
final class MaintenanceMiddleware
{
    /**
     * URLs exemptées du mode maintenance.
     */
    private const EXCLUDED_URLS = [
        '/login',
        '/logout',
        '/two-factor',
        '/profile',
        '/maintenance-mode',
        '/admin/system/health',
        '/admin/system/maintenance',
    ];

    /**
     * Slugs de rôles autorisés à bypasser le mode maintenance.
     */
    private const ALLOWED_ROLE_SLUGS = ['admin', 'superadmin', 'administrateur'];

    public function handle(): ?Response
    {
        $maintenance = new MaintenanceModeService();

        // 1. Mode maintenance inactif → OK
        if (!$maintenance->isActive()) {
            return null;
        }

        $currentUri = $_SERVER['REQUEST_URI'] ?? '';

        // 2. URL exemptée → OK
        if ($this->isExcluded($currentUri)) {
            return null;
        }

        // 3. Vérification admin DIRECTEMENT en BDD
        if ($this->isUserAdminInDatabase()) {
            return null;
        }

        // 4. Garde-fou anti-boucle
        $maintenanceUri    = url('maintenance-mode');
        $parsedMaintenance = parse_url($maintenanceUri, PHP_URL_PATH);
        if ($parsedMaintenance && str_contains($currentUri, $parsedMaintenance)) {
            return null;
        }

        // 5. Redirection
        return Response::redirect(url('maintenance-mode'));
    }

    private function isExcluded(string $uri): bool
    {
        foreach (self::EXCLUDED_URLS as $excluded) {
            if (str_contains($uri, $excluded)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 🎯 Vérifie si l'utilisateur connecté a un rôle admin,
     * en interrogeant la BDD directement.
     *
     * Cette méthode ne dépend PAS de $_SESSION['roles'].
     */
    private function isUserAdminInDatabase(): bool
    {
        // Récupérer l'ID utilisateur depuis la session
        $userId = $_SESSION['user_id'] ?? ($_SESSION['auth_user_id'] ?? null);

        // Pas de session utilisateur → pas admin
        if (!$userId) {
            return false;
        }

        try {
            $pdo = Database::getInstance();

            // Construire les placeholders pour les slugs autorisés
            $slugs = self::ALLOWED_ROLE_SLUGS;
            $placeholders = implode(',', array_fill(0, count($slugs), '?'));

            $sql = "
                SELECT COUNT(*)
                FROM user_roles ur
                JOIN roles r ON r.id = ur.role_id
                WHERE ur.user_id = ?
                  AND LOWER(r.slug) IN ({$placeholders})
            ";

            $params = array_merge([(int)$userId], $slugs);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $isAdmin = (int)$stmt->fetchColumn() > 0;

            // Log temporaire pour debug (à retirer plus tard)
            error_log(sprintf(
                '[MaintenanceMiddleware] user_id=%d, is_admin=%s',
                $userId,
                $isAdmin ? 'YES' : 'NO'
            ));

            return $isAdmin;
        } catch (\Throwable $e) {
            error_log('[MaintenanceMiddleware] ERREUR : ' . $e->getMessage());
            return false;
        }
    }
}