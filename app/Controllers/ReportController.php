<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

final class ReportController
{
    /**
     * Tableau de bord des rapports (avec graphiques)
     */
    public function index(Request $request): Response
    {
        $pdo = Database::getInstance();

        // ============================================
        // STATS GLOBALES
        // ============================================
        $stats = [
            'total_equipment'   => (int)$pdo->query("SELECT COUNT(*) FROM equipment WHERE deleted_at IS NULL")->fetchColumn(),
            'in_service'        => (int)$pdo->query("SELECT COUNT(*) FROM equipment e JOIN equipment_statuses s ON s.id = e.status_id WHERE s.code = 'in_service' AND e.deleted_at IS NULL")->fetchColumn(),
            'in_stock'          => (int)$pdo->query("SELECT COUNT(*) FROM equipment e JOIN equipment_statuses s ON s.id = e.status_id WHERE s.code = 'in_stock' AND e.deleted_at IS NULL")->fetchColumn(),
            'in_maintenance'    => (int)$pdo->query("SELECT COUNT(*) FROM equipment e JOIN equipment_statuses s ON s.id = e.status_id WHERE s.code = 'maintenance' AND e.deleted_at IS NULL")->fetchColumn(),
            'reformed'          => (int)$pdo->query("SELECT COUNT(*) FROM equipment e JOIN equipment_statuses s ON s.id = e.status_id WHERE s.code IN ('reformed','to_reform') AND e.deleted_at IS NULL")->fetchColumn(),
            'total_value'       => (float)$pdo->query("SELECT COALESCE(SUM(acquisition_value), 0) FROM equipment WHERE deleted_at IS NULL")->fetchColumn(),
            'total_employees'   => (int)$pdo->query("SELECT COUNT(*) FROM employees WHERE is_active = 1")->fetchColumn(),
            'total_assignments' => (int)$pdo->query("SELECT COUNT(*) FROM assignments WHERE end_date IS NULL")->fetchColumn(),
            'total_maintenance' => (int)$pdo->query("SELECT COUNT(*) FROM maintenance")->fetchColumn(),
            'total_reforms'     => (int)$pdo->query("SELECT COUNT(*) FROM reformations")->fetchColumn(),
        ];

        // Équipements par catégorie
        $byCategory = $pdo->query("
            SELECT c.name AS label, COUNT(e.id) AS count
            FROM equipment e
            JOIN equipment_categories c ON c.id = e.category_id
            WHERE e.deleted_at IS NULL
            GROUP BY c.id, c.name
            ORDER BY count DESC
        ")->fetchAll();

        // Équipements par statut
        $byStatus = $pdo->query("
            SELECT s.name AS label, s.color AS color, COUNT(e.id) AS count
            FROM equipment e
            JOIN equipment_statuses s ON s.id = e.status_id
            WHERE e.deleted_at IS NULL
            GROUP BY s.id, s.name, s.color, s.sort_order
            ORDER BY s.sort_order ASC
        ")->fetchAll();

        // Équipements par service
        $byService = $pdo->query("
            SELECT sv.name AS label, COUNT(e.id) AS count
            FROM equipment e
            LEFT JOIN services sv ON sv.id = e.service_id
            WHERE e.deleted_at IS NULL AND sv.name IS NOT NULL
            GROUP BY sv.id, sv.name
            ORDER BY count DESC
            LIMIT 10
        ")->fetchAll();

        // Équipements par marque
        $byBrand = $pdo->query("
            SELECT b.name AS label, COUNT(e.id) AS count
            FROM equipment e
            LEFT JOIN brands b ON b.id = e.brand_id
            WHERE e.deleted_at IS NULL AND b.name IS NOT NULL
            GROUP BY b.id, b.name
            ORDER BY count DESC
            LIMIT 10
        ")->fetchAll();

        // Évolution mensuelle
        $evolution = $pdo->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') AS mois,
                COUNT(*) AS count
            FROM equipment
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY mois ASC
        ")->fetchAll();

        // Alertes garanties
        $warrantyExpiring = (int)$pdo->query("
            SELECT COUNT(*) FROM equipment 
            WHERE warranty_end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
            AND deleted_at IS NULL
        ")->fetchColumn();

        $warrantyExpired = (int)$pdo->query("
            SELECT COUNT(*) FROM equipment 
            WHERE warranty_end_date < NOW()
            AND warranty_end_date IS NOT NULL
            AND deleted_at IS NULL
        ")->fetchColumn();

        return $this->view('reports/index', [
            'title'            => 'Rapports et Statistiques',
            'stats'            => $stats,
            'byCategory'       => $byCategory,
            'byStatus'         => $byStatus,
            'byService'        => $byService,
            'byBrand'          => $byBrand,
            'evolution'        => $evolution,
            'warrantyExpiring' => $warrantyExpiring,
            'warrantyExpired'  => $warrantyExpired,
        ]);
    }

    /**
     * Rapport détaillé Équipements
     */
    public function equipment(Request $request): Response
    {
        $pdo = Database::getInstance();

        $equipment = $pdo->query("
            SELECT 
                e.inventory_number,
                e.designation,
                e.serial_number,
                e.acquisition_date,
                e.acquisition_value,
                e.warranty_end_date,
                c.name AS category_name,
                b.name AS brand_name,
                s.name AS status_name,
                s.color AS status_color,
                sv.name AS service_name,
                site.name AS site_name
            FROM equipment e
            LEFT JOIN equipment_categories c ON c.id = e.category_id
            LEFT JOIN brands b ON b.id = e.brand_id
            LEFT JOIN equipment_statuses s ON s.id = e.status_id
            LEFT JOIN services sv ON sv.id = e.service_id
            LEFT JOIN sites site ON site.id = e.site_id
            WHERE e.deleted_at IS NULL
            ORDER BY e.inventory_number ASC
        ")->fetchAll();

        return $this->view('reports/equipment', [
            'title'     => 'Rapport Équipements',
            'equipment' => $equipment,
        ]);
    }

    /**
     * Rapport Maintenance
     */
    public function maintenance(Request $request): Response
    {
        $pdo = Database::getInstance();

        $maintenances = $pdo->query("
            SELECT 
                m.id,
                m.type,
                m.status,
                m.reported_at,
                m.completed_at,
                m.technician,
                m.problem_description,
                m.diagnosis,
                m.work_done,
                m.result,
                m.cost,
                m.downtime_hours,
                m.invoice_number,
                e.inventory_number,
                e.designation,
                u.username AS reported_by_name
            FROM maintenance m
            LEFT JOIN equipment e ON e.id = m.equipment_id
            LEFT JOIN users u ON u.id = m.reported_by
            ORDER BY m.reported_at DESC
            LIMIT 100
        ")->fetchAll();

        $totalCost = (float)$pdo->query("SELECT COALESCE(SUM(cost), 0) FROM maintenance")->fetchColumn();

        // Stats par type
        $byType = $pdo->query("
            SELECT type AS label, COUNT(*) AS count, COALESCE(SUM(cost), 0) AS total_cost
            FROM maintenance
            GROUP BY type
            ORDER BY count DESC
        ")->fetchAll();

        // Stats par statut
        $byStatus = $pdo->query("
            SELECT status AS label, COUNT(*) AS count
            FROM maintenance
            GROUP BY status
        ")->fetchAll();

        return $this->view('reports/maintenance', [
            'title'        => 'Rapport Maintenance',
            'maintenances' => $maintenances,
            'totalCost'    => $totalCost,
            'byType'       => $byType,
            'byStatus'     => $byStatus,
        ]);
    }

    /**
     * Rendu d'une vue
     */
    private function view(string $view, array $data = []): Response
    {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . '/resources/views/' . $view . '.php';

        if (!is_file($viewPath)) {
            return new Response("Vue introuvable : $view", 500);
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = dirname(__DIR__, 2) . '/resources/views/layouts/app.php';
        if (is_file($layoutPath)) {
            ob_start();
            require $layoutPath;
            $content = ob_get_clean();
        }

        return new Response($content);
    }
}