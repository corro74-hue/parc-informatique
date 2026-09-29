<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\EmployeeRepository;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Assignment\AssignmentService;
use App\Services\Auth\AuthService;

final class AssignmentController extends Controller
{
    private AssignmentService $service;
    private EquipmentRepository $equipment;
    private EmployeeRepository $employees;

    public function __construct()
    {
        $this->service   = new AssignmentService();
        $this->equipment = new EquipmentRepository();
        $this->employees = new EmployeeRepository();
    }

    // ============================================
    // LISTE
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'      => (string) $request->input('search', ''),
            'status'      => (string) $request->input('status', ''),
            'employee_id' => (string) $request->input('employee_id', ''),
            'service_id'  => (string) $request->input('service_id', ''),
            'site_id'     => (string) $request->input('site_id', ''),
        ];

        $page = max(1, (int) $request->input('page', 1));

        $result = $this->service->list($filters, $page, 15);
        $stats  = $this->service->stats();

        return $this->view('assignment.index', [
            'title'       => 'Affectations',
            'assignments' => $result['data'],
            'total'       => $result['total'],
            'page'        => $result['page'],
            'lastPage'    => $result['last_page'],
            'perPage'     => $result['per_page'],
            'filters'     => $filters,
            'stats'       => $stats,
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

        return $this->view('assignment.create', [
            'title'     => 'Nouvelle affectation',
            'equipment' => $preselectedEquipment,
            'employees' => $this->employees->findAllActive(),
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
            $assignmentId = $this->service->create($request->body, $user->id);

            unset($_SESSION['_old'], $_SESSION['_errors']);
            flash('success', 'Affectation créée avec succès.');

            return Response::redirect(url('assignments/' . $assignmentId));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('assignments/create'));
        }
    }

    // ============================================
    // DÉTAIL
    // ============================================
    public function show(Request $request, int $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $assignment = $this->service->find($id);
        if ($assignment === null) {
            return $this->notFound();
        }

        return $this->view('assignment.show', [
            'title'      => 'Affectation #' . $assignment->id,
            'assignment' => $assignment,
        ], 'app');
    }

    // ============================================
    // MODIFICATION — Formulaire
    // ============================================
    public function edit(Request $request, int $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $assignment = $this->service->find($id);
        if ($assignment === null) {
            return $this->notFound();
        }

        if (!$assignment->isActive()) {
            flash('warning', 'Cette affectation est déjà clôturée et ne peut plus être modifiée.');
            return Response::redirect(url('assignments/' . $id));
        }

        return $this->view('assignment.edit', [
            'title'      => 'Modifier l\'affectation #' . $assignment->id,
            'assignment' => $assignment,
            'employees'  => $this->employees->findAllActive(),
            'old'        => $_SESSION['_old']    ?? [],
            'errors'     => $_SESSION['_errors'] ?? [],
        ], 'app');
    }

    // ============================================
    // MODIFICATION — Traitement
    // ============================================
    public function update(Request $request, int $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        try {
            $this->service->update($id, $request->body, $user->id);

            unset($_SESSION['_old'], $_SESSION['_errors']);
            flash('success', 'Affectation mise à jour avec succès.');

            return Response::redirect(url('assignments/' . $id));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('assignments/' . $id . '/edit'));
        }
    }

    // ============================================
    // RETOUR D'AFFECTATION
    // ============================================
    public function returnEquipment(Request $request, int $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        $endDate = (string) $request->input('end_date', '');

        try {
            $this->service->returnEquipment($id, $endDate ?: null, $user->id);
            flash('success', 'Équipement retourné et remis en stock.');

        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }

        return Response::redirect(url('assignments/' . $id));
    }

    // ============================================
    // SUPPRESSION
    // ============================================
    public function destroy(Request $request, int $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = (new AuthService())->user();
        if ($user === null) {
            return Response::redirect(url('login'));
        }

        try {
            $this->service->delete($id, $user->id);
            flash('success', 'Affectation supprimée avec succès.');
            return Response::redirect(url('assignments'));

        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
            return Response::redirect(url('assignments/' . $id));
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