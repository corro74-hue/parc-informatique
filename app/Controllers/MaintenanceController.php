<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Auth\AuthService;
use App\Services\Maintenance\MaintenanceService;

final class MaintenanceController extends Controller
{
    private MaintenanceService $service;
    private EquipmentRepository $equipment;

    public function __construct()
    {
        $this->service   = new MaintenanceService();
        $this->equipment = new EquipmentRepository();
    }

    // ============================================
    // LISTE
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'       => (string) $request->input('search', ''),
            'status'       => (string) $request->input('status', ''),
            'type'         => (string) $request->input('type', ''),
            'equipment_id' => (string) $request->input('equipment_id', ''),
        ];

        $page = max(1, (int) $request->input('page', 1));

        $result = $this->service->list($filters, $page, 15);
        $stats  = $this->service->stats();

        return $this->view('maintenance.index', [
            'title'        => 'Maintenance',
            'maintenances' => $result['data'],
            'total'        => $result['total'],
            'page'         => $result['page'],
            'lastPage'     => $result['last_page'],
            'perPage'      => $result['per_page'],
            'filters'      => $filters,
            'stats'        => $stats,
        ], 'app');
    }

    // ============================================
    // CRÉATION — Formulaire
    // ============================================
    public function create(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $preselectedEquipmentId = (int) $request->input('equipment_id', 0);
        $preselectedEquipment   = null;

        if ($preselectedEquipmentId > 0) {
            $preselectedEquipment = $this->equipment->findById($preselectedEquipmentId);
        }

        return $this->view('maintenance.create', [
            'title'     => 'Nouvelle maintenance',
            'equipment' => $preselectedEquipment,
            'old'       => $_SESSION['_old']    ?? [],
            'errors'    => $_SESSION['_errors'] ?? [],
        ], 'app');
    }

    // ============================================
    // CRÉATION — Traitement
    // ============================================
    public function store(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        try {
            $maintenanceId = $this->service->create($request->body, $user->id);

            unset($_SESSION['_old'], $_SESSION['_errors']);
            flash('success', 'Maintenance créée avec succès.');

            return Response::redirect(url('maintenance/' . $maintenanceId));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('maintenance/create'));
        }
    }

    // ============================================
    // DÉTAIL
    // ============================================
    public function show(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $maintenance = $this->service->find((int) $id);
        if ($maintenance === null) {
            return $this->notFound();
        }

        return $this->view('maintenance.show', [
            'title'       => 'Maintenance #' . $maintenance->id,
            'maintenance' => $maintenance,
        ], 'app');
    }

    // ============================================
    // MODIFICATION — Formulaire
    // ============================================
    public function edit(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $maintenance = $this->service->find((int) $id);
        if ($maintenance === null) {
            return $this->notFound();
        }

        if ($maintenance->isClosed()) {
            flash('warning', 'Cette maintenance est déjà clôturée et ne peut plus être modifiée.');
            return Response::redirect(url('maintenance/' . $id));
        }

        return $this->view('maintenance.edit', [
            'title'       => 'Modifier la maintenance #' . $maintenance->id,
            'maintenance' => $maintenance,
            'old'         => $_SESSION['_old']    ?? [],
            'errors'      => $_SESSION['_errors'] ?? [],
        ], 'app');
    }

    // ============================================
    // MODIFICATION — Traitement
    // ============================================
    public function update(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        try {
            $this->service->update((int) $id, $request->body, $user->id);

            unset($_SESSION['_old'], $_SESSION['_errors']);
            flash('success', 'Maintenance mise à jour avec succès.');

            return Response::redirect(url('maintenance/' . $id));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('maintenance/' . $id . '/edit'));
        }
    }

    // ============================================
    // SUPPRESSION
    // ============================================
    public function destroy(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        try {
            $this->service->delete((int) $id, $user->id);
            flash('success', 'Maintenance supprimée avec succès.');
            return Response::redirect(url('maintenance'));

        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
            return Response::redirect(url('maintenance/' . $id));
        }
    }

    // ============================================
    // HELPER
    // ============================================
    private function notFound(): Response
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/errors/404.php';
        ob_start();
        require $viewPath;
        return new Response(ob_get_clean(), 404);
    }
}