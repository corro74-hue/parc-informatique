<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Auth\AuthService;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $auth = new AuthService();
        $user = $auth->user();

        // ----- Alertes garantie -----
        $equipmentRepo = new EquipmentRepository();

        $expiringWarranties        = $equipmentRepo->findExpiringWarranties(30);
        $recentlyExpiredWarranties = $equipmentRepo->findRecentlyExpiredWarranties(30);

        // ----- Statistiques dynamiques -----
        $stats = $this->getStats();

        return $this->view('dashboard.index', [
            'title'                     => 'Tableau de bord',
            'user'                      => $user,
            'expiringWarranties'        => $expiringWarranties,
            'recentlyExpiredWarranties' => $recentlyExpiredWarranties,
            'stats'                     => $stats,
        ], 'app');
    }

    // ============================================================
    // STATISTIQUES
    // ============================================================

    /**
     * Récupère les statistiques clés du tableau de bord.
     *
     * @return array{
     *     equipment_total: int,
     *     equipment_active: int,
     *     equipment_reformed: int,
     *     equipment_to_reform: int,
     *     maintenance_open: int,
     *     reforms_pending: int,
     *     documents_total: int,
     *     users_total: int,
     *     employees_total: int,
     *     assignments_active: int,
     *     documents_recent: array
     * }
     */
    private function getStats(): array
    {
        $db = Database::getInstance();

        // Équipements (non supprimés)
        $equipmentTotal = (int) $db->query(
            "SELECT COUNT(*) FROM equipment WHERE deleted_at IS NULL"
        )->fetchColumn();

        // Équipements "actifs" (En service / En stock / Affecté) = statuts 1, 2, 10
        $equipmentActive = (int) $db->query(
            "SELECT COUNT(*) FROM equipment 
             WHERE deleted_at IS NULL 
               AND status_id IN (1, 2, 10)"
        )->fetchColumn();

        // ✅ NOUVEAU : Équipements réformés (statut 7)
        $equipmentReformed = (int) $db->query(
            "SELECT COUNT(*) FROM equipment 
             WHERE deleted_at IS NULL 
               AND status_id = 7"
        )->fetchColumn();

        // ✅ NOUVEAU : Équipements à réformer (statut 6)
        $equipmentToReform = (int) $db->query(
            "SELECT COUNT(*) FROM equipment 
             WHERE deleted_at IS NULL 
               AND status_id = 6"
        )->fetchColumn();

        // Maintenance ouverte (Ouvert / En cours / Attente pièces)
        $maintenanceOpen = (int) $db->query(
            "SELECT COUNT(*) FROM maintenance 
             WHERE status IN ('open', 'in_progress', 'waiting_parts')"
        )->fetchColumn();

        // Réformes en attente (Brouillon / Soumise / En examen / Approuvée)
        $reformsPending = (int) $db->query(
            "SELECT COUNT(*) FROM reformations 
             WHERE deleted_at IS NULL 
               AND status IN ('draft', 'proposed', 'under_review', 'approved')"
        )->fetchColumn();

        // Documents totaux
        $documentsTotal = (int) $db->query(
            "SELECT COUNT(*) FROM documents"
        )->fetchColumn();

        // Utilisateurs actifs
        $usersTotal = (int) $db->query(
            "SELECT COUNT(*) FROM users WHERE is_active = 1"
        )->fetchColumn();

        // Employés — colonne `is_active` (pas de deleted_at dans cette table)
        $employeesTotal = (int) $db->query(
            "SELECT COUNT(*) FROM employees WHERE is_active = 1"
        )->fetchColumn();

        // Affectations en cours — `end_date IS NULL` (pas de returned_at dans cette table)
        $assignmentsActive = (int) $db->query(
            "SELECT COUNT(*) FROM assignments WHERE end_date IS NULL"
        )->fetchColumn();

        // Derniers documents ajoutés (pour raccourci rapide)
        $documentsRecent = $db->query(
            "SELECT id, reference, title, type, created_at 
             FROM documents 
             ORDER BY created_at DESC 
             LIMIT 5"
        )->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'equipment_total'    => $equipmentTotal,
            'equipment_active'   => $equipmentActive,
            'equipment_reformed' => $equipmentReformed,
            'equipment_to_reform' => $equipmentToReform,
            'maintenance_open'   => $maintenanceOpen,
            'reforms_pending'    => $reformsPending,
            'documents_total'    => $documentsTotal,
            'users_total'        => $usersTotal,
            'employees_total'    => $employeesTotal,
            'assignments_active' => $assignmentsActive,
            'documents_recent'   => $documentsRecent,
        ];
    }
}