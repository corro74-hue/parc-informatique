<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Equipment\EquipmentService;

final class EquipmentController extends Controller
{
    private EquipmentService $service;
    private EquipmentRepository $repo;

    public function __construct()
    {
        $this->service = new EquipmentService();
        $this->repo    = new EquipmentRepository();
    }

    // ============================================
    // LISTE
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'category_id'    => $request->input('category_id'),
            'status_id'      => $request->input('status_id'),
            'service_id'     => $request->input('service_id'),
            'site_id'        => $request->input('site_id'),
            'brand_id'       => $request->input('brand_id'),
            // Filtres avancés
            'date_from'      => $request->input('date_from'),
            'date_to'        => $request->input('date_to'),
            'value_min'      => $request->input('value_min'),
            'value_max'      => $request->input('value_max'),
            'under_warranty' => $request->input('under_warranty'),
            'without_serial' => $request->input('without_serial'),
        ];

        $page    = max(1, (int) $request->input('page', 1));
        $sortBy  = (string) $request->input('sort', 'created_at');
        $sortDir = (string) $request->input('dir', 'DESC');

        $result = $this->service->getPaginatedList($filters, $page, 15, $sortBy, $sortDir);

        $categories = $this->loadCategories();
        $statuses   = $this->loadStatuses();
        $services   = $this->loadServices();
        $sites      = $this->loadSites();
        $brands     = $this->loadBrands();

        // Compteurs par statut pour la barre de badges
        $statusCounts = $this->repo->countByStatusDetailed();

        return $this->view('equipment.index', [
            'title'        => 'Équipements',
            'result'       => $result,
            'filters'      => $filters,
            'categories'   => $categories,
            'statuses'     => $statuses,
            'services'     => $services,
            'sites'        => $sites,
            'brands'       => $brands,
            'statusCounts' => $statusCounts,
        ], 'app');
    }

    // ============================================
    // FORMULAIRE DE CRÉATION
    // ============================================
    public function create(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        return $this->view('equipment.create', [
            'title'      => 'Nouvel équipement',
            'categories' => $this->loadCategories(),
            'statuses'   => $this->loadStatuses(),
            'services'   => $this->loadServices(),
            'sites'      => $this->loadSites(),
            'brands'     => $this->loadBrands(),
            'nextNumber' => $this->repo->generateNextInventoryNumber(),
        ], 'app');
    }

    // ============================================
    // TRAITEMENT DE LA CRÉATION
    // ============================================
    public function store(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $data = $this->extractFormData($request);
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        try {
            $equipment = $this->service->create($data, $userId);

            flash('success', "Équipement {$equipment->inventoryNumber} créé avec succès.");
            return $this->redirect(url('equipment'));

        } catch (ValidationException $e) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', 'Veuillez corriger les erreurs ci-dessous.');
            return $this->redirect(url('equipment/create'));
        }
    }

    // ============================================
    // FICHE DÉTAILLÉE
    // ============================================
    public function show(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $equipment = $this->service->find((int) $id);
        if (!$equipment) {
            flash('error', 'Équipement introuvable.');
            return $this->redirect(url('equipment'));
        }

        return $this->view('equipment.show', [
            'title'     => 'Fiche équipement — ' . $equipment->inventoryNumber,
            'equipment' => $equipment,
        ], 'app');
    }

    // ============================================
    // FORMULAIRE D'ÉDITION
    // ============================================
    public function edit(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $equipment = $this->service->find((int) $id);
        if (!$equipment) {
            flash('error', 'Équipement introuvable.');
            return $this->redirect(url('equipment'));
        }

        return $this->view('equipment.edit', [
            'title'      => 'Modifier — ' . $equipment->inventoryNumber,
            'equipment'  => $equipment,
            'categories' => $this->loadCategories(),
            'statuses'   => $this->loadStatuses(),
            'services'   => $this->loadServices(),
            'sites'      => $this->loadSites(),
            'brands'     => $this->loadBrands(),
        ], 'app');
    }

    // ============================================
    // TRAITEMENT DE LA MISE À JOUR
    // ============================================
    public function update(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $data = $this->extractFormData($request);
        $userId = (int) ($_SESSION['user_id'] ?? 0);

        try {
            $equipment = $this->service->update((int) $id, $data, $userId);

            flash('success', "Équipement {$equipment->inventoryNumber} mis à jour.");
            return $this->redirect(url('equipment/' . $id));

        } catch (ValidationException $e) {
            $_SESSION['_old'] = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', 'Veuillez corriger les erreurs ci-dessous.');
            return $this->redirect(url('equipment/' . $id . '/edit'));
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            return $this->redirect(url('equipment'));
        }
    }

    // ============================================
    // SUPPRESSION LOGIQUE
    // ============================================
    public function destroy(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $equipment = $this->service->find((int) $id);

        if (!$equipment) {
            flash('error', 'Équipement introuvable.');
            return $this->redirect(url('equipment'));
        }

        $this->service->delete((int) $id, $userId);
        flash('success', "Équipement {$equipment->inventoryNumber} supprimé.");

        return $this->redirect(url('equipment'));
    }

    // ============================================
    // CORBEILLE
    // ============================================
    public function trash(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search' => trim((string) $request->input('search', '')),
        ];

        $page = max(1, (int) $request->input('page', 1));

        $result = $this->repo->findTrashed($filters, $page, 15);

        return $this->view('equipment.trash', [
            'title'   => 'Corbeille',
            'result'  => $result,
            'filters' => $filters,
        ], 'app');
    }

    public function restore(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $success = $this->repo->restore((int) $id);

        if ($success) {
            flash('success', 'Équipement restauré avec succès.');
        } else {
            flash('error', 'Impossible de restaurer cet équipement.');
        }

        return $this->redirect(url('equipment/trash'));
    }

    public function forceDelete(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $stmt = \App\Core\Database::getInstance()->prepare(
            'SELECT id, inventory_number FROM equipment WHERE id = :id AND deleted_at IS NOT NULL'
        );
        $stmt->execute(['id' => (int) $id]);
        $trashed = $stmt->fetch();

        if (!$trashed) {
            flash('error', 'Cet équipement n\'est pas dans la corbeille ou n\'existe pas.');
            return $this->redirect(url('equipment/trash'));
        }

        $success = $this->repo->forceDelete((int) $id);

        if ($success) {
            flash('success', "Équipement {$trashed['inventory_number']} supprimé définitivement.");
        } else {
            flash('error', 'Impossible de supprimer définitivement cet équipement.');
        }

        return $this->redirect(url('equipment/trash'));
    }

    // ============================================
    // QR CODE
    // ============================================
    public function qrcode(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $equipment = $this->service->find((int) $id);
        if (!$equipment) {
            flash('error', 'Équipement introuvable.');
            return $this->redirect(url('equipment'));
        }

        $baseUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost'), '/');
        $equipmentUrl = $baseUrl . '/equipment/' . $equipment->id;

        $qrCodeService = new \App\Services\QrCode\QrCodeService();
        $qrCodeDataUri = $qrCodeService->generateBase64($equipmentUrl, 400);

        return $this->view('equipment.qrcode', [
            'title'         => 'Étiquette QR Code — ' . $equipment->inventoryNumber,
            'equipment'     => $equipment,
            'qrCodeDataUri' => $qrCodeDataUri,
            'equipmentUrl'  => $equipmentUrl,
        ], 'app');
    }

    // ============================================
    // DUPLIQUER UN ÉQUIPEMENT
    // ============================================
    public function duplicate(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $source = $this->service->find((int) $id);
        if (!$source) {
            flash('error', 'Équipement source introuvable.');
            return $this->redirect(url('equipment'));
        }

        $_SESSION['_old'] = [
            'category_id'         => $source->categoryId,
            'designation'         => $source->designation . ' (copie)',
            'brand_id'            => $source->brandId,
            'model_text'          => $source->modelText,
            'serial_number'       => null,
            'site_id'             => $source->siteId,
            'service_id'          => $source->serviceId,
            'status_id'           => $source->statusId,
            'acquisition_date'    => $source->acquisitionDate,
            'commissioning_date'  => $source->commissioningDate,
            'acquisition_value'   => $source->acquisitionValue,
            'amortization_years'  => $source->amortizationYears,
            'invoice_number'      => $source->invoiceNumber,
            'warranty_end_date'   => $source->warrantyEndDate,
            'technical_specs'     => $source->technicalSpecs,
            'notes'               => $source->notes,
        ];

        flash('info', "Formulaire pré-rempli depuis {$source->inventoryNumber}. Vérifiez et enregistrez.");
        return $this->redirect(url('equipment/create'));
    }

    // ============================================
    // EXPORT EXCEL / CSV
    // ============================================
    public function exportCsv(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'category_id'    => $request->input('category_id'),
            'status_id'      => $request->input('status_id'),
            'service_id'     => $request->input('service_id'),
            'site_id'        => $request->input('site_id'),
            'brand_id'       => $request->input('brand_id'),
            // Filtres avancés
            'date_from'      => $request->input('date_from'),
            'date_to'        => $request->input('date_to'),
            'value_min'      => $request->input('value_min'),
            'value_max'      => $request->input('value_max'),
            'under_warranty' => $request->input('under_warranty'),
            'without_serial' => $request->input('without_serial'),
        ];

        $equipments = $this->repo->findAllForExport($filters);

        $filename = 'equipements_' . date('Y-m-d_H-i') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Numero Inventaire',
            'Designation',
            'Categorie',
            'Marque',
            'Modele',
            'N° Serie',
            'Service',
            'Site',
            'Statut',
            'Valeur (DA)',
            'Date acquisition',
            'Fin garantie',
        ], ';');

        foreach ($equipments as $eq) {
            fputcsv($output, [
                $eq->inventoryNumber,
                $eq->designation,
                $eq->categoryName  ?? '—',
                $eq->brandName     ?? '—',
                $eq->modelText     ?? '—',
                $eq->serialNumber  ?? '—',
                $eq->serviceName   ?? '—',
                $eq->siteName      ?? '—',
                $eq->statusName    ?? '—',
                number_format($eq->acquisitionValue, 2, ',', ' '),
                $eq->acquisitionDate  ?? '—',
                $eq->warrantyEndDate  ?? '—',
            ], ';');
        }

        fclose($output);
        exit;
    }

    // ============================================
    // EXPORT PDF — LISTE
    // ============================================
    public function exportPdf(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'         => trim((string) $request->input('search', '')),
            'category_id'    => $request->input('category_id'),
            'status_id'      => $request->input('status_id'),
            'service_id'     => $request->input('service_id'),
            'site_id'        => $request->input('site_id'),
            'brand_id'       => $request->input('brand_id'),
            // Filtres avancés
            'date_from'      => $request->input('date_from'),
            'date_to'        => $request->input('date_to'),
            'value_min'      => $request->input('value_min'),
            'value_max'      => $request->input('value_max'),
            'under_warranty' => $request->input('under_warranty'),
            'without_serial' => $request->input('without_serial'),
        ];

        $equipments = $this->repo->findAllForExport($filters);

        $activeFilters = [];
        if (!empty($filters['search'])) {
            $activeFilters[] = 'Recherche : "' . htmlspecialchars($filters['search'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($filters['category_id'])) {
            foreach ($this->loadCategories() as $c) {
                if ((int) $c['id'] === (int) $filters['category_id']) {
                    $activeFilters[] = 'Catégorie : ' . htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8');
                }
            }
        }
        if (!empty($filters['status_id'])) {
            foreach ($this->loadStatuses() as $s) {
                if ((int) $s['id'] === (int) $filters['status_id']) {
                    $activeFilters[] = 'Statut : ' . htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8');
                }
            }
        }
        if (!empty($filters['service_id'])) {
            foreach ($this->loadServices() as $sv) {
                if ((int) $sv['id'] === (int) $filters['service_id']) {
                    $activeFilters[] = 'Service : ' . htmlspecialchars($sv['name'], ENT_QUOTES, 'UTF-8');
                }
            }
        }
        if (!empty($filters['site_id'])) {
            foreach ($this->loadSites() as $st) {
                if ((int) $st['id'] === (int) $filters['site_id']) {
                    $activeFilters[] = 'Site : ' . htmlspecialchars($st['name'], ENT_QUOTES, 'UTF-8');
                }
            }
        }
        if (!empty($filters['brand_id'])) {
            foreach ($this->loadBrands() as $b) {
                if ((int) $b['id'] === (int) $filters['brand_id']) {
                    $activeFilters[] = 'Marque : ' . htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8');
                }
            }
        }
        // Filtres avancés affichés dans le PDF
        if (!empty($filters['date_from'])) {
            $activeFilters[] = 'Acquisition à partir du : ' . htmlspecialchars($filters['date_from'], ENT_QUOTES, 'UTF-8');
        }
        if (!empty($filters['date_to'])) {
            $activeFilters[] = 'Acquisition jusqu\'au : ' . htmlspecialchars($filters['date_to'], ENT_QUOTES, 'UTF-8');
        }
        if (!empty($filters['value_min'])) {
            $activeFilters[] = 'Valeur min : ' . htmlspecialchars($filters['value_min'], ENT_QUOTES, 'UTF-8') . ' DA';
        }
        if (!empty($filters['value_max'])) {
            $activeFilters[] = 'Valeur max : ' . htmlspecialchars($filters['value_max'], ENT_QUOTES, 'UTF-8') . ' DA';
        }
        if (!empty($filters['under_warranty'])) {
            $activeFilters[] = 'Sous garantie uniquement';
        }
        if (!empty($filters['without_serial'])) {
            $activeFilters[] = 'Sans N° de série uniquement';
        }

        $userName = $_SESSION['user_name'] ?? 'Administrateur';
        $generatedAt = date('d/m/Y à H:i');

        header('Content-Type: text/html; charset=utf-8');

        $html  = '<!DOCTYPE html>';
        $html .= '<html lang="fr"><head><meta charset="UTF-8">';
        $html .= '<title>Liste des équipements — Parc Info</title>';
        $html .= '<style>
            * { box-sizing: border-box; }
            body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #1e293b; margin: 0; padding: 20px; background: #f1f5f9; }
            .toolbar { max-width: 1100px; margin: 0 auto 20px; display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
            .toolbar p { margin: 0; font-size: 14px; color: #64748b; }
            .btn { padding: 8px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
            .btn-primary { background: #4f46e5; color: #fff; }
            .btn-secondary { background: #e2e8f0; color: #1e293b; margin-right: 8px; }
            .page { max-width: 1100px; margin: 0 auto; background: #fff; padding: 30px 40px; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: 8px; }
            .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #4f46e5; padding-bottom: 15px; margin-bottom: 25px; }
            .logo { display: flex; align-items: center; gap: 12px; }
            .logo-icon { width: 42px; height: 42px; background: #4f46e5; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: bold; }
            .logo-text h1 { margin: 0; font-size: 20px; color: #1e293b; }
            .logo-text small { color: #64748b; font-size: 12px; }
            .meta { text-align: right; font-size: 12px; color: #64748b; line-height: 1.6; }
            .meta strong { color: #1e293b; }
            .title { margin: 0 0 6px 0; font-size: 22px; color: #1e293b; }
            .subtitle { margin: 0 0 20px 0; color: #64748b; font-size: 13px; }
            .filters { background: #f8fafc; border-left: 4px solid #4f46e5; padding: 10px 15px; margin-bottom: 20px; border-radius: 4px; font-size: 12px; color: #475569; }
            .filters strong { color: #1e293b; }
            table { width: 100%; border-collapse: collapse; font-size: 11px; }
            thead th { background: #4f46e5; color: #fff; padding: 10px 8px; text-align: left; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .3px; }
            tbody td { padding: 8px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
            tbody tr:nth-child(even) { background: #f8fafc; }
            tbody tr:hover { background: #f1f5f9; }
            .text-end { text-align: right; }
            .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
            .empty { text-align: center; padding: 40px; color: #94a3b8; font-style: italic; }
            .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; }
            @media print {
                body { background: #fff; padding: 0; }
                .toolbar { display: none !important; }
                .page { box-shadow: none; border-radius: 0; max-width: 100%; padding: 0; }
                @page { size: A4 landscape; margin: 12mm; }
                thead { display: table-header-group; }
                tbody tr { page-break-inside: avoid; }
            }
        </style></head><body>';

        $html .= '<div class="toolbar">';
        $html .= '<p>📄 Aperçu avant impression — Vérifiez le rendu, puis enregistrez en PDF.</p>';
        $html .= '<div>';
        $html .= '<a href="' . htmlspecialchars(url('equipment'), ENT_QUOTES, 'UTF-8') . '" class="btn btn-secondary">← Retour</a>';
        $html .= '<button class="btn btn-primary" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>';
        $html .= '</div></div>';

        $html .= '<div class="page">';

        $html .= '<div class="header">';
        $html .= '<div class="logo">';
        $html .= '<div class="logo-icon">P</div>';
        $html .= '<div class="logo-text"><h1>Parc Info</h1><small>Gestion de Parc Informatique</small></div>';
        $html .= '</div>';
        $html .= '<div class="meta">';
        $html .= '<div>Généré le <strong>' . $generatedAt . '</strong></div>';
        $html .= '<div>Par <strong>' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . '</strong></div>';
        $html .= '<div>Total : <strong>' . count($equipments) . ' équipement(s)</strong></div>';
        $html .= '</div></div>';

        $html .= '<h2 class="title">Liste des équipements</h2>';
        $html .= '<p class="subtitle">Document généré automatiquement depuis l\'application Parc Info.</p>';

        if (!empty($activeFilters)) {
            $html .= '<div class="filters"><strong>Filtres appliqués :</strong> ' . implode(' &nbsp;•&nbsp; ', $activeFilters) . '</div>';
        }

        if (empty($equipments)) {
            $html .= '<div class="empty">Aucun équipement à afficher.</div>';
        } else {
            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>N° Inventaire</th>';
            $html .= '<th>Désignation</th>';
            $html .= '<th>Catégorie</th>';
            $html .= '<th>Marque</th>';
            $html .= '<th>Modèle</th>';
            $html .= '<th>N° Série</th>';
            $html .= '<th>Service</th>';
            $html .= '<th>Site</th>';
            $html .= '<th>Statut</th>';
            $html .= '<th class="text-end">Valeur (DA)</th>';
            $html .= '<th>Acquisition</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($equipments as $eq) {
                $statusColor = $eq->statusColor ?? '#6c757d';
                $statusName  = $eq->statusName ?? 'Inconnu';

                $html .= '<tr>';
                $html .= '<td><strong>' . htmlspecialchars($eq->inventoryNumber, ENT_QUOTES, 'UTF-8') . '</strong></td>';
                $html .= '<td>' . htmlspecialchars($eq->designation, ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->categoryName ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->brandName ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->modelText ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->serialNumber ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->serviceName ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . htmlspecialchars($eq->siteName ?? '—', ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td><span class="badge" style="background:' . htmlspecialchars($statusColor, ENT_QUOTES, 'UTF-8') . ';color:#fff;">' . htmlspecialchars($statusName, ENT_QUOTES, 'UTF-8') . '</span></td>';
                $html .= '<td class="text-end">' . htmlspecialchars($eq->getFormattedValue(), ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td>' . ($eq->acquisitionDate ? date('d/m/Y', strtotime($eq->acquisitionDate)) : '—') . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        $html .= '<div class="footer">';
        $html .= '<div>Parc Info — Gestion de Parc Informatique</div>';
        $html .= '<div>Document confidentiel — Usage interne</div>';
        $html .= '</div>';

        $html .= '</div>';

        $html .= '<script>window.addEventListener("load", function () { setTimeout(function () { window.print(); }, 500); });</script>';

        $html .= '</body></html>';

        echo $html;
        exit;
    }

    // ============================================
    // EXPORT PDF — FICHE INDIVIDUELLE
    // ============================================
    public function showPdf(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $equipment = $this->service->find((int) $id);
        if (!$equipment) {
            flash('error', 'Équipement introuvable.');
            return $this->redirect(url('equipment'));
        }

        $baseUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost'), '/');
        $equipmentUrl = $baseUrl . '/equipment/' . $equipment->id;
        $qrCodeService = new \App\Services\QrCode\QrCodeService();
        $qrCodeDataUri = $qrCodeService->generateBase64($equipmentUrl, 200);

        $userName = $_SESSION['user_name'] ?? 'Administrateur';
        $generatedAt = date('d/m/Y à H:i');

        $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $fmtDate = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';

        $statusColor = $equipment->statusColor ?? '#6c757d';
        $statusName  = $equipment->statusName ?? 'Inconnu';

        header('Content-Type: text/html; charset=utf-8');

        $html  = '<!DOCTYPE html>';
        $html .= '<html lang="fr"><head><meta charset="UTF-8">';
        $html .= '<title>Fiche — ' . $esc($equipment->inventoryNumber) . '</title>';
        $html .= '<style>
            * { box-sizing: border-box; }
            body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #1e293b; margin: 0; padding: 20px; background: #f1f5f9; }
            .toolbar { max-width: 800px; margin: 0 auto 20px; display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 12px 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
            .toolbar p { margin: 0; font-size: 14px; color: #64748b; }
            .btn { padding: 8px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; }
            .btn-primary { background: #4f46e5; color: #fff; }
            .btn-secondary { background: #e2e8f0; color: #1e293b; margin-right: 8px; }
            .page { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px 40px; box-shadow: 0 1px 3px rgba(0,0,0,.08); border-radius: 8px; }
            .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #4f46e5; padding-bottom: 15px; margin-bottom: 20px; }
            .logo { display: flex; align-items: center; gap: 12px; }
            .logo-icon { width: 42px; height: 42px; background: #4f46e5; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: bold; }
            .logo-text h1 { margin: 0; font-size: 20px; color: #1e293b; }
            .logo-text small { color: #64748b; font-size: 12px; }
            .meta { text-align: right; font-size: 11px; color: #64748b; line-height: 1.6; }
            .meta strong { color: #1e293b; }
            .title-block { margin-bottom: 20px; padding: 15px 20px; background: #eef2ff; border-left: 5px solid #4f46e5; border-radius: 4px; }
            .title-block h2 { margin: 0 0 5px 0; font-size: 20px; color: #1e293b; }
            .title-block .designation { font-size: 14px; color: #475569; margin: 0; }
            .title-block .status-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; color: #fff; margin-left: 8px; vertical-align: middle; }
            .section { margin-bottom: 18px; page-break-inside: avoid; }
            .section h3 { margin: 0 0 10px 0; font-size: 13px; color: #4f46e5; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 25px; }
            .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px 20px; }
            .field { font-size: 12px; }
            .field .label { color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: .3px; display: block; margin-bottom: 2px; }
            .field .value { color: #1e293b; font-weight: 500; }
            .field .value.mono { font-family: ui-monospace, Consolas, monospace; font-size: 11px; }
            .field .value.highlight { color: #4f46e5; font-weight: 700; font-size: 14px; }
            .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; }
            .qr-block { text-align: center; margin: 20px 0; page-break-inside: avoid; }
            .qr-block img { border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px; background: #fff; }
            .qr-block p { font-size: 10px; color: #94a3b8; margin: 5px 0 0 0; }
            @media print {
                body { background: #fff; padding: 0; }
                .toolbar { display: none !important; }
                .page { box-shadow: none; border-radius: 0; max-width: 100%; padding: 0; }
                @page { size: A4 portrait; margin: 15mm; }
                .section { page-break-inside: avoid; }
            }
        </style></head><body>';

        $html .= '<div class="toolbar">';
        $html .= '<p>📄 Fiche équipement — Aperçu avant impression.</p>';
        $html .= '<div>';
        $html .= '<a href="' . htmlspecialchars(url('equipment/' . $equipment->id), ENT_QUOTES, 'UTF-8') . '" class="btn btn-secondary">← Retour</a>';
        $html .= '<button class="btn btn-primary" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>';
        $html .= '</div></div>';

        $html .= '<div class="page">';

        $html .= '<div class="header">';
        $html .= '<div class="logo">';
        $html .= '<div class="logo-icon">P</div>';
        $html .= '<div class="logo-text"><h1>Parc Info</h1><small>Gestion de Parc Informatique</small></div>';
        $html .= '</div>';
        $html .= '<div class="meta">';
        $html .= '<div>Fiche générée le <strong>' . $generatedAt . '</strong></div>';
        $html .= '<div>Par <strong>' . $esc($userName) . '</strong></div>';
        $html .= '<div>ID : <strong>#' . (int) $equipment->id . '</strong></div>';
        $html .= '</div></div>';

        $html .= '<div class="title-block">';
        $html .= '<h2>' . $esc($equipment->inventoryNumber)
               . '<span class="status-badge" style="background:' . $esc($statusColor) . ';">' . $esc($statusName) . '</span>'
               . '</h2>';
        $html .= '<p class="designation">' . $esc($equipment->designation) . '</p>';
        $html .= '</div>';

        $html .= '<div class="section">';
        $html .= '<h3>Informations générales</h3>';
        $html .= '<div class="grid">';
        $html .= '<div class="field"><span class="label">N° Inventaire</span><span class="value">' . $esc($equipment->inventoryNumber) . '</span></div>';
        $html .= '<div class="field"><span class="label">Désignation</span><span class="value">' . $esc($equipment->designation) . '</span></div>';
        $html .= '<div class="field"><span class="label">Catégorie</span><span class="value">' . $esc($equipment->categoryName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Statut</span><span class="value">' . $esc($equipment->statusName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Marque</span><span class="value">' . $esc($equipment->brandName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Modèle</span><span class="value">' . $esc($equipment->modelText ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">N° de série</span><span class="value mono">' . $esc($equipment->serialNumber ?? '—') . '</span></div>';
        $html .= '</div></div>';

        $html .= '<div class="section">';
        $html .= '<h3>Affectation</h3>';
        $html .= '<div class="grid">';
        $html .= '<div class="field"><span class="label">Site</span><span class="value">' . $esc($equipment->siteName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Service</span><span class="value">' . $esc($equipment->serviceName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Localisation</span><span class="value">' . $esc($equipment->locationName ?? '—') . '</span></div>';
        $html .= '<div class="field"><span class="label">Responsable</span><span class="value">' . $esc($equipment->responsibleName ?? '—') . '</span></div>';
        $html .= '</div></div>';

        $html .= '<div class="section">';
        $html .= '<h3>Informations financières</h3>';
        $html .= '<div class="grid-3">';
        $html .= '<div class="field"><span class="label">Valeur d\'acquisition</span><span class="value highlight">' . $esc($equipment->getFormattedValue()) . '</span></div>';
        $html .= '<div class="field"><span class="label">Valeur nette comptable</span><span class="value highlight">' . $esc($equipment->getFormattedBookValue()) . '</span></div>';
        $html .= '<div class="field"><span class="label">Amortissement</span><span class="value">' . (int) ($equipment->amortizationYears ?? 0) . ' an(s)</span></div>';
        $html .= '<div class="field"><span class="label">Date d\'acquisition</span><span class="value">' . $esc($fmtDate($equipment->acquisitionDate)) . '</span></div>';
        $html .= '<div class="field"><span class="label">Mise en service</span><span class="value">' . $esc($fmtDate($equipment->commissioningDate)) . '</span></div>';
        $html .= '<div class="field"><span class="label">Fin de garantie</span><span class="value">' . $esc($fmtDate($equipment->warrantyEndDate)) . '</span></div>';
        $html .= '<div class="field"><span class="label">N° de facture</span><span class="value mono">' . $esc($equipment->invoiceNumber ?? '—') . '</span></div>';
        $html .= '</div></div>';

        if (!empty($equipment->technicalSpecs) || !empty($equipment->notes)) {
            $html .= '<div class="section">';
            $html .= '<h3>Spécifications et notes</h3>';
            $html .= '<div class="grid">';
            if (!empty($equipment->technicalSpecs)) {
                $html .= '<div class="field" style="grid-column: 1 / -1;"><span class="label">Spécifications techniques</span><span class="value" style="white-space: pre-wrap;">' . $esc($equipment->technicalSpecs) . '</span></div>';
            }
            if (!empty($equipment->notes)) {
                $html .= '<div class="field" style="grid-column: 1 / -1;"><span class="label">Notes</span><span class="value" style="white-space: pre-wrap;">' . $esc($equipment->notes) . '</span></div>';
            }
            $html .= '</div></div>';
        }

        $html .= '<div class="qr-block">';
        $html .= '<img src="' . $esc($qrCodeDataUri) . '" alt="QR Code" width="140" height="140">';
        $html .= '<p>Scannez pour accéder à la fiche numérique</p>';
        $html .= '</div>';

        $html .= '<div class="footer">';
        $html .= '<div>Parc Info — Gestion de Parc Informatique</div>';
        $html .= '<div>Document confidentiel — Usage interne</div>';
        $html .= '</div>';

        $html .= '</div>';

        $html .= '<script>window.addEventListener("load", function () { setTimeout(function () { window.print(); }, 500); });</script>';

        $html .= '</body></html>';

        echo $html;
        exit;
    }

    // ============================================
    // CHANGEMENT RAPIDE DE STATUT (AJAX)
    // ============================================
    /**
     * Met à jour le statut d'un équipement en AJAX depuis la liste.
     * Retourne systématiquement une réponse JSON via Response::json().
     */
    public function updateStatusAjax(Request $request, string $id): Response
    {
        // ----- 1. Auth -----
        $authService = new \App\Services\Auth\AuthService();
        if (!$authService->check()) {
            return Response::json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // ----- 2. CSRF -----
        if (!\App\Core\Csrf::verify($_POST['_token'] ?? null)) {
            return Response::json([
                'success' => false,
                'message' => 'Jeton CSRF invalide.',
            ], 403);
        }

        // ----- 3. Validation des entrées -----
        $statusId = (int) $request->input('status_id', 0);
        if ($statusId <= 0) {
            return Response::json([
                'success' => false,
                'message' => 'Statut invalide.',
            ], 422);
        }

        // ----- 4. Vérification du statut en base -----
        $stmt = \App\Core\Database::getInstance()->prepare(
            'SELECT id, name, color FROM equipment_statuses WHERE id = :id'
        );
        $stmt->execute(['id' => $statusId]);
        $status = $stmt->fetch();

        if (!$status) {
            return Response::json([
                'success' => false,
                'message' => 'Statut introuvable.',
            ], 422);
        }

        // ----- 5. Vérification de l'équipement -----
        $equipment = $this->service->find((int) $id);
        if (!$equipment) {
            return Response::json([
                'success' => false,
                'message' => 'Équipement introuvable.',
            ], 404);
        }

        // ----- 6. Mise à jour -----
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $ok     = $this->repo->updateStatus((int) $id, $statusId, $userId);

        if (!$ok) {
            return Response::json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.',
            ], 500);
        }

        // ----- 7. Réponse succès -----
        return Response::json([
            'success'      => true,
            'message'      => 'Statut mis à jour.',
            'status_id'    => (int) $status['id'],
            'status_name'  => $status['name'],
            'status_color' => $status['color'],
        ]);
    }

    // ============================================
    // RECHERCHE GLOBALE (AJAX)
    // ============================================
    /**
     * Recherche rapide pour la barre de recherche du header.
     * Retourne du JSON : { success, results: [...] }
     */
    public function searchAjax(Request $request): Response
    {
        // ----- 1. Auth -----
        $authService = new \App\Services\Auth\AuthService();
        if (!$authService->check()) {
            return Response::json([
                'success' => false,
                'message' => 'Non authentifié.',
            ], 401);
        }

        // ----- 2. Terme -----
        $term = trim((string) $request->input('q', ''));
        if (mb_strlen($term) < 2) {
            return Response::json([
                'success' => true,
                'results' => [],
            ]);
        }

        // ----- 3. Recherche -----
        $equipments = $this->repo->searchGlobal($term, 10);

        // ----- 4. Formater la réponse -----
        $results = array_map(function ($eq) {
            return [
                'id'               => $eq->id,
                'inventory_number' => $eq->inventoryNumber,
                'designation'      => $eq->designation,
                'serial_number'    => $eq->serialNumber,
                'status_name'      => $eq->statusName,
                'status_color'     => $eq->statusColor,
                'category_name'    => $eq->categoryName,
                'url'              => url('equipment/' . $eq->id),
            ];
        }, $equipments);

        return Response::json([
            'success' => true,
            'query'   => $term,
            'count'   => count($results),
            'results' => $results,
        ]);
    }

    // ============================================
    // ACTIONS GROUPÉES (BULK - AJAX)
    // ============================================

    /**
     * Change le statut de PLUSIEURS équipements.
     */
    public function bulkUpdateStatus(Request $request): Response
    {
        // Auth
        $authService = new \App\Services\Auth\AuthService();
        if (!$authService->check()) {
            return Response::json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        // CSRF
        if (!\App\Core\Csrf::verify($_POST['_token'] ?? null)) {
            return Response::json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 403);
        }

        // Récupérer les IDs (envoyés en JSON ou POST classique)
        $ids = $this->extractBulkIds($request);
        if (empty($ids)) {
            return Response::json(['success' => false, 'message' => 'Aucun équipement sélectionné.'], 422);
        }

        // Statut
        $statusId = (int) $request->input('status_id', 0);
        if ($statusId <= 0) {
            return Response::json(['success' => false, 'message' => 'Statut invalide.'], 422);
        }

        // Vérifier le statut en base
        $stmt = \App\Core\Database::getInstance()->prepare(
            'SELECT id, name, color FROM equipment_statuses WHERE id = :id'
        );
        $stmt->execute(['id' => $statusId]);
        $status = $stmt->fetch();

        if (!$status) {
            return Response::json(['success' => false, 'message' => 'Statut introuvable.'], 422);
        }

        // Mettre à jour
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $count  = $this->repo->bulkUpdateStatus($ids, $statusId, $userId);

        return Response::json([
            'success'      => true,
            'message'      => $count . ' équipement(s) mis à jour.',
            'count'        => $count,
            'status_id'    => (int) $status['id'],
            'status_name'  => $status['name'],
            'status_color' => $status['color'],
        ]);
    }

    /**
     * Met à la corbeille PLUSIEURS équipements.
     */
    public function bulkDelete(Request $request): Response
    {
        // Auth
        $authService = new \App\Services\Auth\AuthService();
        if (!$authService->check()) {
            return Response::json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        // CSRF
        if (!\App\Core\Csrf::verify($_POST['_token'] ?? null)) {
            return Response::json(['success' => false, 'message' => 'Jeton CSRF invalide.'], 403);
        }

        $ids = $this->extractBulkIds($request);
        if (empty($ids)) {
            return Response::json(['success' => false, 'message' => 'Aucun équipement sélectionné.'], 422);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $count  = $this->repo->bulkSoftDelete($ids, $userId);

        return Response::json([
            'success' => true,
            'message' => $count . ' équipement(s) placé(s) dans la corbeille.',
            'count'   => $count,
        ]);
    }

    /**
     * Export CSV de la sélection uniquement.
     */
    public function bulkExportCsv(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $ids = $this->extractBulkIds($request);
        if (empty($ids)) {
            flash('error', 'Aucun équipement sélectionné pour l\'export.');
            return $this->redirect(url('equipment'));
        }

        $equipments = $this->repo->findByIds($ids);

        $filename = 'equipements_selection_' . date('Y-m-d_H-i') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'Numero Inventaire',
            'Designation',
            'Categorie',
            'Marque',
            'Modele',
            'N° Serie',
            'Service',
            'Site',
            'Statut',
            'Valeur (DA)',
            'Date acquisition',
            'Fin garantie',
        ], ';');

        foreach ($equipments as $eq) {
            fputcsv($output, [
                $eq->inventoryNumber,
                $eq->designation,
                $eq->categoryName  ?? '—',
                $eq->brandName     ?? '—',
                $eq->modelText     ?? '—',
                $eq->serialNumber  ?? '—',
                $eq->serviceName   ?? '—',
                $eq->siteName      ?? '—',
                $eq->statusName    ?? '—',
                number_format($eq->acquisitionValue, 2, ',', ' '),
                $eq->acquisitionDate  ?? '—',
                $eq->warrantyEndDate  ?? '—',
            ], ';');
        }

        fclose($output);
        exit;
    }

    // ============================================
    // IMPORT CSV  ← NOUVEAU
    // ============================================

    /**
     * Affiche le formulaire d'import.
     */
    public function importForm(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        return $this->view('equipment.import', [
            'title'   => 'Importer des équipements',
            'preview' => null,
            'report'  => null,
        ], 'app');
    }

    /**
     * Analyse le fichier uploadé et affiche un aperçu AVANT validation.
     */
    public function importPreview(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        // ----- 1. Vérifier l'upload -----
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Aucun fichier reçu ou erreur d\'upload.');
            return $this->redirect(url('equipment/import'));
        }

        $file = $_FILES['csv_file'];

        // ----- 2. Vérifier l'extension -----
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'], true)) {
            flash('error', 'Format non supporté. Utilisez un fichier .csv');
            return $this->redirect(url('equipment/import'));
        }

        // ----- 3. Vérifier la taille (max 5 Mo) -----
        if ($file['size'] > 5 * 1024 * 1024) {
            flash('error', 'Fichier trop volumineux (max 5 Mo).');
            return $this->redirect(url('equipment/import'));
        }

        // ----- 4. Sauvegarder le fichier dans storage/imports -----
        $uploadDir = dirname(__DIR__, 2) . '/storage/imports';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $storedName = 'import_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4)) . '.csv';
        $storedPath = $uploadDir . '/' . $storedName;

        if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
            flash('error', 'Impossible de sauvegarder le fichier.');
            return $this->redirect(url('equipment/import'));
        }

        // ----- 5. Analyser le fichier -----
        try {
            $analysis = $this->analyzeCsvFile($storedPath);
        } catch (\Throwable $e) {
            @unlink($storedPath);
            flash('error', 'Erreur d\'analyse : ' . $e->getMessage());
            return $this->redirect(url('equipment/import'));
        }

        // ----- 6. Stocker le chemin en session pour l'étape suivante -----
        $_SESSION['_import_file'] = $storedPath;

        return $this->view('equipment.import', [
            'title'      => 'Aperçu de l\'import',
            'preview'    => $analysis['rows'],
            'report'     => $analysis['report'],
            'filename'   => $file['name'],
            'total_rows' => count($analysis['rows']),
        ], 'app');
    }

    /**
     * Insère effectivement les données en base.
     */
    public function importStore(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $path = $_SESSION['_import_file'] ?? null;
        if (!$path || !is_file($path)) {
            flash('error', 'Fichier d\'import introuvable. Recommencez.');
            return $this->redirect(url('equipment/import'));
        }

        // ----- Analyser à nouveau (au cas où) -----
        try {
            $analysis = $this->analyzeCsvFile($path);
        } catch (\Throwable $e) {
            @unlink($path);
            unset($_SESSION['_import_file']);
            flash('error', 'Erreur : ' . $e->getMessage());
            return $this->redirect(url('equipment/import'));
        }

        // ----- Insérer les lignes valides -----
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $validRows = $analysis['valid_rows'];

        foreach ($validRows as &$row) {
            $row['created_by'] = $userId;
        }
        unset($row);

        $inserted = 0;
        try {
            $inserted = $this->repo->bulkInsert($validRows);
        } catch (\Throwable $e) {
            flash('error', 'Erreur lors de l\'insertion : ' . $e->getMessage());
            return $this->redirect(url('equipment/import'));
        }

        // ----- Nettoyer -----
        @unlink($path);
        unset($_SESSION['_import_file']);

        $errorCount = count($analysis['report']['errors']);
        flash('success', "{$inserted} équipement(s) importé(s) avec succès. " .
            ($errorCount > 0 ? "{$errorCount} erreur(s) ignorée(s)." : ""));

        return $this->redirect(url('equipment'));
    }

    /**
     * Télécharge un modèle CSV vierge.
     */
    public function importTemplate(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filename = 'modele_import_equipements.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // En-tête
        fputcsv($output, [
            'inventory_number',
            'designation',
            'category_code',
            'brand_name',
            'model_text',
            'serial_number',
            'service_name',
            'site_name',
            'status_code',
            'acquisition_date',
            'acquisition_value',
            'warranty_end_date',
        ], ';');

        // Exemples
        fputcsv($output, ['INF-2026-0001', 'PC Bureau Dell OptiPlex', 'PC', 'Dell', 'OptiPlex 3080', 'SN-ABC123', 'Informatique', 'Siège principal', 'EN_SERVICE', '2024-01-15', '95000', '2027-01-15'], ';');
        fputcsv($output, ['', 'Imprimante HP LaserJet', 'IMP', 'HP', 'LaserJet Pro M404', 'SN-XYZ789', 'Comptabilité', 'Siège principal', 'EN_SERVICE', '2024-03-01', '45000', '2026-03-01'], ';');

        fclose($output);
        exit;
    }

    // ============================================
    // HELPERS PRIVÉS
    // ============================================
    private function extractFormData(Request $request): array
    {
        $toNull = fn($v) => ($v === '' || $v === null) ? null : $v;

        return [
            'category_id'         => (int) $request->input('category_id', 0),
            'designation'         => trim((string) $request->input('designation', '')),
            'brand_id'            => $toNull($request->input('brand_id')) ? (int) $request->input('brand_id') : null,
            'model_id'            => null,
            'model_text'          => $toNull($request->input('model_text')),
            'serial_number'       => $toNull($request->input('serial_number')),
            'site_id'             => $toNull($request->input('site_id')) ? (int) $request->input('site_id') : null,
            'service_id'          => $toNull($request->input('service_id')) ? (int) $request->input('service_id') : null,
            'location_id'         => null,
            'responsible_id'      => null,
            'supplier_id'         => null,
            'status_id'           => (int) $request->input('status_id', 0),
            'acquisition_date'    => $toNull($request->input('acquisition_date')),
            'commissioning_date'  => $toNull($request->input('commissioning_date')),
            'acquisition_value'   => (float) ($request->input('acquisition_value') ?: 0),
            'residual_value'      => (float) ($request->input('acquisition_value') ?: 0),
            'amortization_years'  => $toNull($request->input('amortization_years')) ? (int) $request->input('amortization_years') : null,
            'invoice_number'      => $toNull($request->input('invoice_number')),
            'warranty_end_date'   => $toNull($request->input('warranty_end_date')),
            'technical_specs'     => $toNull($request->input('technical_specs')),
            'notes'               => $toNull($request->input('notes')),
        ];
    }

    /**
     * Extrait les IDs depuis plusieurs formats d'entrée (bulk actions).
     *
     * @return int[]
     */
    private function extractBulkIds(Request $request): array
    {
        // Format 1 : "ids[]" (array PHP classique)
        $ids = $request->input('ids', []);

        // Format 2 : "ids" en JSON string
        if (is_string($ids) && $ids !== '') {
            $decoded = json_decode($ids, true);
            if (is_array($decoded)) {
                $ids = $decoded;
            } else {
                // Format 3 : "1,2,3"
                $ids = array_map('trim', explode(',', $ids));
            }
        }

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));
    }

    /**
     * Analyse un fichier CSV d'import.
     * Retourne un rapport + les lignes valides prêtes à insérer.
     *
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     valid_rows: array<int, array<string, mixed>>,
     *     report: array{total: int, valid: int, errors: array<int, array{line: int, message: string}>}
     * }
     */
    private function analyzeCsvFile(string $path): array
    {
        // ----- Ouvrir le fichier -----
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \RuntimeException('Impossible d\'ouvrir le fichier.');
        }

        // ----- Détecter et retirer le BOM UTF-8 -----
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // ----- Lire l'en-tête -----
        $header = fgetcsv($handle, 0, ';');
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException('Fichier vide ou en-tête manquant.');
        }

        // Nettoyer les noms de colonnes
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);

        // ----- Colonnes attendues -----
        $expected = [
            'inventory_number', 'designation', 'category_code', 'brand_name',
            'model_text', 'serial_number', 'service_name', 'site_name',
            'status_code', 'acquisition_date', 'acquisition_value', 'warranty_end_date',
        ];

        $missing = array_diff($expected, $header);
        if (!empty($missing)) {
            fclose($handle);
            throw new \RuntimeException('Colonnes manquantes : ' . implode(', ', $missing));
        }

        $headerIndex = array_flip($header);

        // ----- Charger les données de référence -----
        $categories  = $this->repo->getCategoriesByCode();
        $statuses    = $this->repo->getStatusesByCode();
        $brands      = $this->repo->getBrandsByName();
        $services    = $this->repo->getServicesByName();
        $sites       = $this->repo->getSitesByName();
        $existingInv = $this->repo->getExistingInventoryNumbers();
        $existingSer = $this->repo->getExistingSerialNumbers();

        // ----- Parcourir les lignes -----
        $rows       = [];
        $validRows  = [];
        $errors     = [];
        $lineNumber = 1;

        while (($line = fgetcsv($handle, 0, ';')) !== false) {
            $lineNumber++;

            // Ignorer les lignes vides
            if (count($line) === 1 && trim((string) $line[0]) === '') {
                continue;
            }

            $row = [];
            foreach ($headerIndex as $col => $idx) {
                $row[$col] = isset($line[$idx]) ? trim((string) $line[$idx]) : '';
            }

            // ----- Validation -----
            $rowErrors = [];

            // designation (obligatoire)
            if ($row['designation'] === '') {
                $rowErrors[] = 'La désignation est obligatoire.';
            }

            // category_code → category_id
            $categoryId = null;
            if ($row['category_code'] !== '') {
                $key = strtolower($row['category_code']);
                if (isset($categories[$key])) {
                    $categoryId = $categories[$key];
                } else {
                    $rowErrors[] = "Catégorie inconnue : '{$row['category_code']}'";
                }
            } else {
                $rowErrors[] = 'La catégorie est obligatoire.';
            }

            // status_code → status_id
            $statusId = null;
            if ($row['status_code'] !== '') {
                $key = strtolower($row['status_code']);
                if (isset($statuses[$key])) {
                    $statusId = $statuses[$key];
                } else {
                    $rowErrors[] = "Statut inconnu : '{$row['status_code']}'";
                }
            } else {
                $rowErrors[] = 'Le statut est obligatoire.';
            }

            // brand_name → brand_id (optionnel)
            $brandId = null;
            if ($row['brand_name'] !== '') {
                $key = strtolower($row['brand_name']);
                $brandId = $brands[$key] ?? null;
                if ($brandId === null) {
                    $rowErrors[] = "Marque inconnue : '{$row['brand_name']}' (sera ignorée)";
                }
            }

            // service_name → service_id
            $serviceId = null;
            if ($row['service_name'] !== '') {
                $key = strtolower($row['service_name']);
                $serviceId = $services[$key] ?? null;
                if ($serviceId === null) {
                    $rowErrors[] = "Service inconnu : '{$row['service_name']}' (sera ignoré)";
                }
            }

            // site_name → site_id
            $siteId = null;
            if ($row['site_name'] !== '') {
                $key = strtolower($row['site_name']);
                $siteId = $sites[$key] ?? null;
                if ($siteId === null) {
                    $rowErrors[] = "Site inconnu : '{$row['site_name']}' (sera ignoré)";
                }
            }

            // inventory_number (doublon ?)
            $invNumber = $row['inventory_number'];
            if ($invNumber !== '' && isset($existingInv[strtoupper($invNumber)])) {
                $rowErrors[] = "N° d'inventaire déjà existant : '{$invNumber}'";
            }

            // serial_number (doublon ?)
            $serial = $row['serial_number'];
            if ($serial !== '' && isset($existingSer[strtoupper($serial)])) {
                $rowErrors[] = "N° de série déjà existant : '{$serial}'";
            }

            // Dates
            $acqDate = $row['acquisition_date'] !== '' ? $row['acquisition_date'] : null;
            $warrantyDate = $row['warranty_end_date'] !== '' ? $row['warranty_end_date'] : null;

            // Valeur
            $value = $row['acquisition_value'] !== '' ? (float) str_replace(',', '.', $row['acquisition_value']) : 0.0;

            // ----- Construire la ligne -----
            $data = [
                'inventory_number'    => $invNumber ?: null, // null = auto-généré
                'designation'         => $row['designation'],
                'category_id'         => $categoryId,
                'brand_id'            => $brandId,
                'model_text'          => $row['model_text'] ?: null,
                'serial_number'       => $serial ?: null,
                'service_id'          => $serviceId,
                'site_id'             => $siteId,
                'status_id'           => $statusId,
                'acquisition_date'    => $acqDate,
                'acquisition_value'   => $value,
                'warranty_end_date'   => $warrantyDate,
                'created_by'          => 0, // sera remplacé à l'insertion
            ];

            // ----- Ajouter à l'aperçu -----
            $rows[] = [
                'line'     => $lineNumber,
                'data'     => $data,
                'errors'   => $rowErrors,
                'is_valid' => empty($rowErrors),
            ];

            // ----- Ajouter aux valides si OK -----
            if (empty($rowErrors) && $categoryId && $statusId) {
                // Auto-générer un N° d'inventaire si vide
                if ($data['inventory_number'] === null) {
                    $data['inventory_number'] = $this->repo->generateNextInventoryNumber();
                    // Mettre à jour le registre pour éviter les doublons dans le même import
                    $existingInv[strtoupper($data['inventory_number'])] = true;
                }
                $validRows[] = $data;
            }
        }

        fclose($handle);

        return [
            'rows'       => $rows,
            'valid_rows' => $validRows,
            'report'     => [
                'total'  => count($rows),
                'valid'  => count($validRows),
                'errors' => array_values(array_filter(
                    array_map(function ($r) {
                        if (empty($r['errors'])) return null;
                        return ['line' => $r['line'], 'message' => implode(' | ', $r['errors'])];
                    }, $rows)
                )),
            ],
        ];
    }

    private function loadCategories(): array
    {
        $stmt = \App\Core\Database::getInstance()->query(
            'SELECT id, name, code FROM equipment_categories WHERE is_active = 1 ORDER BY name'
        );
        return $stmt->fetchAll();
    }

    private function loadStatuses(): array
    {
        $stmt = \App\Core\Database::getInstance()->query(
            'SELECT id, name, code, color FROM equipment_statuses ORDER BY sort_order'
        );
        return $stmt->fetchAll();
    }

    private function loadServices(): array
    {
        $stmt = \App\Core\Database::getInstance()->query(
            'SELECT id, name FROM services WHERE is_active = 1 ORDER BY name'
        );
        return $stmt->fetchAll();
    }

    private function loadSites(): array
    {
        $stmt = \App\Core\Database::getInstance()->query(
            'SELECT id, name FROM sites WHERE is_active = 1 ORDER BY name'
        );
        return $stmt->fetchAll();
    }

    private function loadBrands(): array
    {
        $stmt = \App\Core\Database::getInstance()->query(
            'SELECT id, name FROM brands ORDER BY name'
        );
        return $stmt->fetchAll();
    }
}