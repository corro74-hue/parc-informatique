<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\Database\DatabaseBackupService;
use App\Services\Database\DatabaseHealthService;

/**
 * Contrôleur d'administration pour la base de données.
 *
 * 🎯 RÔLE :
 *    Gère les actions d'administration liées à la BDD :
 *    - Liste et gestion des sauvegardes
 *    - Monitoring de la santé de la BDD
 *    - Optimisation et vérification d'intégrité
 *
 * 🛠️ UTILISATION :
 *    Accessible uniquement aux utilisateurs avec la permission "settings.manage".
 *
 * ⚠️ PRÉCAUTIONS :
 *    - Toutes les actions destructives nécessitent une confirmation
 *    - Les sauvegardes sont stockées dans storage/backups/database/
 *    - La suppression de la dernière sauvegarde est bloquée
 *
 * 💡 BONNES PRATIQUES :
 *    - Sauvegarder AVANT toute manipulation importante
 *    - Tester régulièrement la restauration
 *    - Surveiller la taille de la BDD
 */
final class DatabaseController extends Controller
{
    private DatabaseBackupService $backupService;
    private DatabaseHealthService $healthService;

    public function __construct()
    {
        $this->backupService = new DatabaseBackupService();
        $this->healthService = new DatabaseHealthService();
    }

    // ============================================
    // LISTE DES SAUVEGARDES (page principale)
    // ============================================

    /**
     * Affiche la page principale de gestion des sauvegardes.
     */
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $backups = $this->backupService->listBackups(50);
        $stats   = $this->backupService->getStats();
        $info    = $this->healthService->getGeneralInfo();
        $recos   = $this->healthService->getRecommendations();

        return $this->view('admin.database.index', [
            'title'           => 'Sauvegardes de la base de données',
            'backups'         => $backups,
            'stats'           => $stats,
            'info'            => $info,
            'recommendations' => $recos,
        ]);
    }

    // ============================================
    // CRÉATION D'UNE SAUVEGARDE
    // ============================================

    /**
     * Crée une nouvelle sauvegarde SQL.
     */
    public function createBackup(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $userId = (int) ($_SESSION['user_id'] ?? 0);

        $result = $this->backupService->createBackup('manual', $userId);

        if ($result['success']) {
            flash('success', '✅ ' . $result['message']);
        } else {
            flash('error', '❌ ' . $result['message']);
        }

        return $this->redirect(url('admin/database'));
    }

    // ============================================
    // TÉLÉCHARGEMENT D'UNE SAUVEGARDE
    // ============================================

    /**
     * Télécharge une sauvegarde SQL.
     */
    public function downloadBackup(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $id = (int) $id;
        $backup = $this->backupService->getBackup($id);

        if (!$backup) {
            flash('error', 'Sauvegarde introuvable.');
            return $this->redirect(url('admin/database'));
        }

        if (!is_file($backup['filepath'])) {
            flash('error', 'Fichier introuvable sur le disque.');
            return $this->redirect(url('admin/database'));
        }

        // Envoi du fichier
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($backup['filename']) . '"');
        header('Content-Length: ' . filesize($backup['filepath']));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($backup['filepath']);
        exit;
    }

    // ============================================
    // SUPPRESSION D'UNE SAUVEGARDE
    // ============================================

    /**
     * Supprime une sauvegarde (fichier + log).
     */
    public function deleteBackup(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $id = (int) $id;
        $result = $this->backupService->deleteBackup($id);

        if ($result['success']) {
            flash('success', '✅ ' . $result['message']);
        } else {
            flash('error', '❌ ' . $result['message']);
        }

        return $this->redirect(url('admin/database'));
    }

    // ============================================
    // PAGE DE SANTÉ DE LA BDD
    // ============================================

    /**
     * Affiche la page de monitoring de la santé de la BDD.
     */
    public function health(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $info     = $this->healthService->getGeneralInfo();
        $tables   = $this->healthService->getTablesInfo();
        $stats    = $this->healthService->getStatistics();
        $recos    = $this->healthService->getRecommendations();

        return $this->view('admin.database.health', [
            'title'           => 'Santé de la base de données',
            'info'            => $info,
            'tables'          => $tables,
            'stats'           => $stats,
            'recommendations' => $recos,
        ]);
    }

    // ============================================
    // VÉRIFICATION D'INTÉGRITÉ
    // ============================================

    /**
     * Lance une vérification d'intégrité (CHECK TABLE) sur toutes les tables.
     */
    public function checkIntegrity(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->healthService->checkIntegrity();

        if ($result['errors'] > 0) {
            flash('error', '❌ ' . $result['errors'] . ' table(s) en erreur. Consultez les détails.');
        } elseif ($result['warnings'] > 0) {
            flash('warning', '⚠️ ' . $result['warnings'] . ' avertissement(s) détecté(s).');
        } else {
            flash('success', '✅ Toutes les tables sont saines (' . $result['ok'] . '/' . $result['total'] . ').');
        }

        $_SESSION['_integrity_result'] = $result;

        return $this->redirect(url('admin/database/health'));
    }

    // ============================================
    // OPTIMISATION
    // ============================================

    /**
     * Lance une optimisation (OPTIMIZE TABLE) sur toutes les tables.
     */
    public function optimize(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->healthService->optimizeTables();

        if ($result['failed'] > 0) {
            flash('warning', '⚠️ ' . $result['optimized'] . ' table(s) optimisée(s), ' . $result['failed'] . ' échec(s).');
        } else {
            $freed = $result['freed_bytes'] > 0 ? ' (' . $this->formatSize($result['freed_bytes']) . ' libérés)' : '';
            flash('success', '✅ ' . $result['optimized'] . ' table(s) optimisée(s)' . $freed . '.');
        }

        return $this->redirect(url('admin/database/health'));
    }

    // ============================================
    // NETTOYAGE DES VIEILLES SAUVEGARDES
    // ============================================

    /**
     * Nettoie les vieilles sauvegardes selon la politique de rétention.
     */
    public function cleanBackups(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $result = $this->backupService->cleanOldBackups();

        if ($result['deleted'] > 0) {
            $freed = $result['freed_bytes'] > 0 ? ' (' . $this->formatSize($result['freed_bytes']) . ' libérés)' : '';
            flash('success', '✅ ' . $result['deleted'] . ' ancienne(s) sauvegarde(s) supprimée(s)' . $freed . '.');
        } else {
            flash('info', 'Aucune sauvegarde à nettoyer. Tout est à jour.');
        }

        return $this->redirect(url('admin/database'));
    }

    // ============================================
    // HELPERS
    // ============================================

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' Go';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' Mo';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' Ko';
        return $bytes . ' o';
    }
}