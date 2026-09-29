<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\ServiceRepository;
use App\Services\Auth\AuthService;
use App\Services\Employee\EmployeeService;

final class EmployeeController extends Controller
{
    private EmployeeService $service;

    public function __construct()
    {
        $this->service = new EmployeeService();
    }

    // ============================================
    // LISTE
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search'     => (string) $request->input('search', ''),
            'service_id' => (string) $request->input('service_id', ''),
            'is_active'  => (string) $request->input('is_active', ''),
        ];

        $page = max(1, (int) $request->input('page', 1));

        $result = $this->service->list($filters, $page, 15);
        $stats  = $this->service->stats();

        return $this->view('employees.index', [
            'title'     => 'Employés',
            'employees' => $result['data'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'lastPage'  => $result['last_page'],
            'perPage'   => $result['per_page'],
            'filters'   => $filters,
            'stats'     => $stats,
            'services'  => $this->loadServices(),
        ], 'app');
    }

    // ============================================
    // CRÉATION — Formulaire
    // ============================================
    public function create(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        return $this->view('employees.create', [
            'title'    => 'Nouvel employé',
            'services' => $this->loadServices(),
            'old'      => $_SESSION['_old']    ?? [],
            'errors'   => $_SESSION['_errors'] ?? [],
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
            $employeeId = $this->service->create($request->body, $user->id);

            unset($_SESSION['_old'], $_SESSION['_errors']);
            flash('success', 'Employé créé avec succès.');

            return Response::redirect(url('employees/' . $employeeId));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('employees/create'));
        }
    }

    // ============================================
    // DÉTAIL
    // ============================================
    public function show(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $employee = $this->service->find((int) $id);
        if ($employee === null) {
            return $this->notFound();
        }

        return $this->view('employees.show', [
            'title'    => 'Employé — ' . $employee->getFullName(),
            'employee' => $employee,
        ], 'app');
    }

    // ============================================
    // MODIFICATION — Formulaire
    // ============================================
    public function edit(Request $request, string $id): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $employee = $this->service->find((int) $id);
        if ($employee === null) {
            return $this->notFound();
        }

        return $this->view('employees.edit', [
            'title'    => 'Modifier — ' . $employee->getFullName(),
            'employee' => $employee,
            'services' => $this->loadServices(),
            'old'      => $_SESSION['_old']    ?? [],
            'errors'   => $_SESSION['_errors'] ?? [],
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
            flash('success', 'Employé mis à jour avec succès.');

            return Response::redirect(url('employees/' . $id));

        } catch (ValidationException $e) {
            $_SESSION['_old']    = $request->body;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());

            return Response::redirect(url('employees/' . $id . '/edit'));
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
            flash('success', 'Employé supprimé avec succès.');
            return Response::redirect(url('employees'));

        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
            return Response::redirect(url('employees/' . $id));
        }
    }

    // ============================================
    // HELPER
    // ============================================
    private function loadServices(): array
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query('SELECT id, name, code FROM services WHERE is_active = 1 ORDER BY name');
        return $stmt->fetchAll();
    }

    private function notFound(): Response
    {
        $viewPath = dirname(__DIR__, 2) . '/resources/views/errors/404.php';
        ob_start();
        require $viewPath;
        return new Response(ob_get_clean(), 404);
    }
}