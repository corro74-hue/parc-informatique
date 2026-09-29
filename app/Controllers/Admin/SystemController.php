<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\System\SystemHealthService;
use App\Services\System\MaintenanceModeService;
use App\Services\System\CacheService;

/**
 * Contrôleur d'administration du système.
 *
 * 🎯 RÔLE :
 *    Gère les actions d'administration liées au système :
 *    - Santé du système (PHP, disque, permissions, extensions)
 *    - Mode maintenance (activer/désactiver)
 *    - Nettoyage du cache, logs, sessions
 *
 * 🛠️ UTILISATION :
 *    Accessible uniquement aux utilisateurs avec la permission "settings.manage".
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Le mode maintenance bloque l'accès aux visiteurs
 *    - Le nettoyage des logs est irréversible
 *
 * 💡 BONNES PRATIQUES :
 *    - Vérifier la santé du système 1 fois par mois
 *    - Faire une sauvegarde AVANT toute modification
 *    - Désactiver le mode maintenance immédiatement après
 */
final class SystemController extends Controller
{
    private SystemHealthService $health;
    private MaintenanceModeService $maintenance;
    private CacheService $cache;

    public function __construct()
    {
        $this->health      = new SystemHealthService();
        $this->maintenance = new MaintenanceModeService();
        $this->cache       = new CacheService();
    }

    // ============================================
    // PAGE SANTÉ DU SYSTÈME
    // ============================================

    /**
     * Affiche la page de santé du système.
     */
    public function health(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $info            = $this->health->getSystemInfo();
        $disk            = $this->health->getDiskSpace();
        $permissions     = $this->health->getPermissions();
        $extensions      = $this->health->getExtensions();
        $folderSizes     = $this->health->getFolderSizes();
        $recommendations = $this->health->getRecommendations();
        $cacheStats      = $this->cache->getCacheStats();
        $maintenance     = $this->maintenance->getInfo();

        return $this->view('admin.system.health', [
            'title'           => 'Santé du système',
            'info'            => $info,
            'disk'            => $disk,
            'permissions'     => $permissions,
            'extensions'      => $extensions,
            'folderSizes'     => $folderSizes,
            'recommendations' => $recommendations,
            'cacheStats'      => $cacheStats,
            'maintenance'     => $maintenance,
        ]);
    }

    // ============================================
    // MODE MAINTENANCE
    // ============================================

    /**
     * Active le mode maintenance.
     */
    public function enableMaintenance(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $reason = trim((string) $request->input('reason', ''));
        $endAt  = trim((string) $request->input('end_at', ''));

        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $result = $this->maintenance->enable($reason, $userId, $endAt ?: null);

        if ($result['success']) {
            flash('success', '✅ ' . $result['message']);
        } else {
            flash('error', '❌ ' . $result['message']);
        }

        return $this->redirect(url('admin/system/health'));
    }

    /**
     * Désactive le mode maintenance.
     */
    public function disableMaintenance(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $result = $this->maintenance->disable($userId);

        if ($result['success']) {
            flash('success', '✅ ' . $result['message']);
        } else {
            flash('error', '❌ ' . $result['message']);
        }

        return $this->redirect(url('admin/system/health'));
    }

    // ============================================
    // NETTOYAGE
    // ============================================

    /**
     * Nettoie le cache applicatif.
     */
    public function clearCache(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->cache->clearCache();

        if ($result['deleted_files'] > 0) {
            flash('success', '✅ Cache nettoyé : ' . $result['deleted_files'] . ' fichier(s) supprimé(s) (' . $result['freed_formatted'] . ' libérés).');
        } else {
            flash('info', 'Le cache était déjà vide.');
        }

        return $this->redirect(url('admin/system/health'));
    }

    /**
     * Purge les vieux logs.
     */
    public function clearLogs(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->cache->clearLogs();

        if ($result['deleted_files'] > 0) {
            flash('success', '✅ Logs purgés : ' . $result['deleted_files'] . ' fichier(s) supprimé(s) (' . $result['freed_formatted'] . ' libérés).');
        } else {
            flash('info', 'Aucun log à purger (tous récents).');
        }

        return $this->redirect(url('admin/system/health'));
    }

    /**
     * Nettoie les sessions expirées.
     */
    public function clearSessions(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->cache->clearSessions();

        $message = '✅ Sessions nettoyées : ' . $result['deleted_files'] . ' fichier(s) et ' . $result['deleted_db_rows'] . ' entrée(s) BDD supprimée(s) (' . $result['freed_formatted'] . ' libérés).';

        flash('success', $message);

        return $this->redirect(url('admin/system/health'));
    }

    /**
     * Nettoyage complet (cache + logs + sessions).
     */
    public function clearAll(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->cache->clearAll();

        $total = $result['total_freed_formatted'];
        $message = '✅ Nettoyage complet effectué : ' . $total . ' libérés au total.';

        flash('success', $message);

        return $this->redirect(url('admin/system/health'));
    }
}