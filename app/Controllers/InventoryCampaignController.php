<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

final class InventoryCampaignController
{
    /**
     * Liste des campagnes
     */
    public function index(Request $request): Response
    {
        $pdo = Database::getInstance();

        $campaigns = $pdo->query("
            SELECT c.*, 
                   s.name AS site_name,
                   sv.name AS service_name,
                   u.username AS created_by_name,
                   (SELECT COUNT(*) FROM inventory_campaign_items WHERE campaign_id = c.id) AS total_items,
                   (SELECT COUNT(*) FROM inventory_campaign_items WHERE campaign_id = c.id AND status != 'pending') AS done_items,
                   (SELECT COUNT(*) FROM inventory_campaign_items WHERE campaign_id = c.id AND status = 'found') AS found_items,
                   (SELECT COUNT(*) FROM inventory_campaign_items WHERE campaign_id = c.id AND status = 'missing') AS missing_items
            FROM inventory_campaigns c
            LEFT JOIN sites s ON s.id = c.site_id
            LEFT JOIN services sv ON sv.id = c.service_id
            LEFT JOIN users u ON u.id = c.created_by
            ORDER BY c.created_at DESC
        ")->fetchAll();

        return $this->view('campaigns/index', [
            'title'     => 'Campagnes d\'inventaire',
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request): Response
    {
        $pdo = Database::getInstance();

        $sites    = $pdo->query("SELECT id, name FROM sites ORDER BY name")->fetchAll();
        $services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

        return $this->view('campaigns/create', [
            'title'    => 'Nouvelle campagne',
            'sites'    => $sites,
            'services' => $services,
        ]);
    }

    /**
     * Enregistrer une nouvelle campagne
     */
    public function store(Request $request): Response
    {
        $pdo = Database::getInstance();

        try {
            $name        = trim((string)($request->body['name'] ?? ''));
            $startDate   = trim((string)($request->body['start_date'] ?? ''));
            $endDate     = trim((string)($request->body['end_date'] ?? ''));
            $siteId      = $request->body['site_id'] ?? null;
            $serviceId   = $request->body['service_id'] ?? null;
            $description = trim((string)($request->body['description'] ?? ''));

            // Validation
            if ($name === '') {
                throw new \InvalidArgumentException('Le nom est obligatoire.');
            }
            if ($startDate === '') {
                throw new \InvalidArgumentException('La date de début est obligatoire.');
            }

            // Générer une référence unique
            $year = date('Y', strtotime($startDate));
            $count = (int)$pdo->query("SELECT COUNT(*) FROM inventory_campaigns WHERE YEAR(created_at) = $year")->fetchColumn() + 1;
            $reference = 'INV-' . $year . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);

            // Insérer la campagne
            $stmt = $pdo->prepare("
                INSERT INTO inventory_campaigns 
                (reference, name, site_id, service_id, start_date, end_date, status, description, created_by)
                VALUES (?, ?, ?, ?, ?, ?, 'planned', ?, ?)
            ");
            $stmt->execute([
                $reference,
                $name,
                $siteId ?: null,
                $serviceId ?: null,
                $startDate,
                $endDate ?: null,
                $description,
                auth_id(),
            ]);

            $campaignId = (int)$pdo->lastInsertId();

            // Générer automatiquement les items
            $sql = "SELECT id FROM equipment WHERE deleted_at IS NULL";
            $params = [];
            if ($siteId) {
                $sql .= " AND site_id = ?";
                $params[] = $siteId;
            }
            if ($serviceId) {
                $sql .= " AND service_id = ?";
                $params[] = $serviceId;
            }

            $equipStmt = $pdo->prepare($sql);
            $equipStmt->execute($params);
            $equipmentIds = $equipStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($equipmentIds)) {
                $itemStmt = $pdo->prepare("
                    INSERT INTO inventory_campaign_items (campaign_id, equipment_id, status)
                    VALUES (?, ?, 'pending')
                ");
                foreach ($equipmentIds as $eqId) {
                    $itemStmt->execute([$campaignId, $eqId]);
                }
            }

            if (function_exists('logAction')) {
                logAction('CREATE_CAMPAIGN', "Campagne créée : $reference ($name) - " . count($equipmentIds) . " équipements");
            }

            flash('success', "✅ Campagne '$name' créée avec " . count($equipmentIds) . " équipement(s) à vérifier.");
            return Response::redirect(url('campaigns/' . $campaignId));
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
            return Response::redirect(url('campaigns/create'));
        }
    }

    /**
     * Détail d'une campagne + pointage
     */
    public function show(Request $request, string $id): Response
    {
        $id = (int)$id;
        $pdo = Database::getInstance();

        $campaign = $pdo->prepare("
            SELECT c.*, 
                   s.name AS site_name, 
                   sv.name AS service_name,
                   u.username AS created_by_name
            FROM inventory_campaigns c
            LEFT JOIN sites s ON s.id = c.site_id
            LEFT JOIN services sv ON sv.id = c.service_id
            LEFT JOIN users u ON u.id = c.created_by
            WHERE c.id = ?
        ");
        $campaign->execute([$id]);
        $campaign = $campaign->fetch();

        if (!$campaign) {
            flash('error', 'Campagne introuvable.');
            return Response::redirect(url('campaigns'));
        }

        // Items avec équipement
        $items = $pdo->prepare("
            SELECT i.*, 
                   e.inventory_number, e.designation,
                   u.username AS verified_by_name
            FROM inventory_campaign_items i
            LEFT JOIN equipment e ON e.id = i.equipment_id
            LEFT JOIN users u ON u.id = i.verified_by
            WHERE i.campaign_id = ?
            ORDER BY 
                CASE i.status
                    WHEN 'pending' THEN 1
                    WHEN 'missing' THEN 2
                    WHEN 'damaged' THEN 3
                    WHEN 'moved' THEN 4
                    WHEN 'found' THEN 5
                END,
                e.inventory_number
        ");
        $items->execute([$id]);
        $items = $items->fetchAll();

        // Statistiques
        $stats = [
            'total'    => count($items),
            'pending'  => 0,
            'found'    => 0,
            'missing'  => 0,
            'moved'    => 0,
            'damaged'  => 0,
        ];
        foreach ($items as $item) {
            if (isset($stats[$item['status']])) {
                $stats[$item['status']]++;
            }
        }

        // ✅ FIX : Localisations - ne pas assumer la colonne `name`
        // On récupère toutes les colonnes et on laisse la vue s'adapter
        try {
            $locations = $pdo->query("SELECT * FROM locations ORDER BY id")->fetchAll();
        } catch (\Throwable $e) {
            $locations = [];
        }

        return $this->view('campaigns/show', [
            'title'     => 'Campagne : ' . $campaign['name'],
            'campaign'  => $campaign,
            'items'     => $items,
            'stats'     => $stats,
            'locations' => $locations,
        ]);
    }

    /**
     * Pointer un équipement (AJAX)
     */
    public function verifyItem(Request $request, string $campaignId, string $itemId): Response
    {
        $campaignId = (int)$campaignId;
        $itemId     = (int)$itemId;
        $pdo = Database::getInstance();

        try {
            $status       = (string)($request->body['status'] ?? 'found');
            $observations = trim((string)($request->body['observations'] ?? ''));
            $locationId   = $request->body['location_id'] ?? null;

            $allowedStatuses = ['pending', 'found', 'missing', 'moved', 'damaged'];
            if (!in_array($status, $allowedStatuses, true)) {
                throw new \InvalidArgumentException('Statut invalide.');
            }

            $stmt = $pdo->prepare("
                UPDATE inventory_campaign_items
                SET status = ?, 
                    verified_at = NOW(),
                    verified_by = ?,
                    location_id = ?,
                    observations = ?
                WHERE id = ? AND campaign_id = ?
            ");
            $stmt->execute([
                $status,
                auth_id(),
                $locationId ?: null,
                $observations,
                $itemId,
                $campaignId,
            ]);

            return Response::json([
                'success' => true,
                'message' => 'Item pointé',
                'status'  => $status,
            ]);
        } catch (\Throwable $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Démarrer une campagne
     */
    public function start(Request $request, string $id): Response
    {
        $id = (int)$id;
        $pdo = Database::getInstance();

        try {
            $pdo->prepare("UPDATE inventory_campaigns SET status = 'in_progress', updated_at = NOW() WHERE id = ?")
                ->execute([$id]);

            if (function_exists('logAction')) {
                logAction('START_CAMPAIGN', "Campagne démarrée (ID: $id)");
            }

            flash('success', 'Campagne démarrée. Bon courage pour le pointage ! 🚀');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('campaigns/' . $id));
    }

    /**
     * Clôturer une campagne
     */
    public function complete(Request $request, string $id): Response
    {
        $id = (int)$id;
        $pdo = Database::getInstance();

        try {
            $pdo->prepare("UPDATE inventory_campaigns SET status = 'completed', updated_at = NOW() WHERE id = ?")
                ->execute([$id]);

            if (function_exists('logAction')) {
                logAction('COMPLETE_CAMPAIGN', "Campagne clôturée (ID: $id)");
            }

            flash('success', '✅ Campagne clôturée avec succès !');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('campaigns/' . $id));
    }

    /**
     * Supprimer une campagne (uniquement si planned ou cancelled)
     */
    public function destroy(Request $request, string $id): Response
    {
        $id = (int)$id;
        $pdo = Database::getInstance();

        try {
            $campaign = $pdo->prepare("SELECT status FROM inventory_campaigns WHERE id = ?");
            $campaign->execute([$id]);
            $status = $campaign->fetchColumn();

            if (!in_array($status, ['planned', 'cancelled'], true)) {
                throw new \RuntimeException('Impossible de supprimer une campagne en cours ou terminée.');
            }

            $pdo->prepare("DELETE FROM inventory_campaign_items WHERE campaign_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM inventory_campaigns WHERE id = ?")->execute([$id]);

            if (function_exists('logAction')) {
                logAction('DELETE_CAMPAIGN', "Campagne supprimée (ID: $id)");
            }

            flash('success', 'Campagne supprimée.');
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }

        return Response::redirect(url('campaigns'));
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