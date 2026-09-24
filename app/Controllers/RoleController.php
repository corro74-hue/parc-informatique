<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\Role\RoleService;

final class RoleController extends Controller
{
    private RoleService $roles;

    public function __construct()
    {
        $this->roles = new RoleService();
    }

    // ============================================
    // LISTE DES RÔLES
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $roles = $this->roles->getAllWithStats();

        return $this->view('roles.index', [
            'title' => 'Rôles et permissions',
            'roles' => $roles,
        ]);
    }

    // ============================================
    // FORMULAIRE DE CRÉATION
    // ============================================
    public function create(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $permissionsByModule = $this->roles->getPermissionsGroupedByModule();

        return $this->view('roles.create', [
            'title'               => 'Nouveau rôle',
            'permissionsByModule' => $permissionsByModule,
        ]);
    }

    // ============================================
    // TRAITEMENT DE LA CRÉATION
    // ============================================
    public function store(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $data = [
            'name'        => trim((string) $request->input('name', '')),
            'slug'        => trim((string) $request->input('slug', '')),
            'description' => trim((string) $request->input('description', '')),
        ];

        $permissionIds = (array) $request->input('permissions', []);

        try {
            $roleId = $this->roles->create($data, $permissionIds);

            flash('success', 'Rôle créé avec succès.');
            return $this->redirect(url('roles/' . $roleId));
        } catch (ValidationException $e) {
            $_SESSION['_old']    = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());
            return $this->redirect(url('roles/create'));
        }
    }

    // ============================================
    // FICHE DÉTAILLÉE D'UN RÔLE
    // ============================================
    public function show(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $role = $this->roles->find($id);
        if (!$role) {
            flash('error', 'Rôle introuvable.');
            return $this->redirect(url('roles'));
        }

        $permissions = $this->roles->getPermissions($id);
        $canDelete   = $this->roles->canDeleteCheck($id);

        // Groupe les permissions assignées par module
        $permissionsByModule = [];
        foreach ($permissions as $perm) {
            $module = $perm['module'] ?? 'autre';
            if (!isset($permissionsByModule[$module])) {
                $permissionsByModule[$module] = [];
            }
            $permissionsByModule[$module][] = $perm;
        }

        return $this->view('roles.show', [
            'title'               => 'Rôle : ' . $role['name'],
            'role'                => $role,
            'permissionsByModule' => $permissionsByModule,
            'canDelete'           => $canDelete,
        ]);
    }

    // ============================================
    // FORMULAIRE D'ÉDITION
    // ============================================
    public function edit(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $role = $this->roles->find($id);
        if (!$role) {
            flash('error', 'Rôle introuvable.');
            return $this->redirect(url('roles'));
        }

        $permissionsByModule = $this->roles->getPermissionsGroupedByModule();
        $assignedPermissionIds = $this->roles->getPermissionIds($id);

        return $this->view('roles.edit', [
            'title'                 => 'Modifier : ' . $role['name'],
            'role'                  => $role,
            'permissionsByModule'   => $permissionsByModule,
            'assignedPermissionIds' => $assignedPermissionIds,
        ]);
    }

    // ============================================
    // TRAITEMENT DE L'ÉDITION
    // ============================================
    public function update(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $data = [
            'name'        => trim((string) $request->input('name', '')),
            'slug'        => trim((string) $request->input('slug', '')),
            'description' => trim((string) $request->input('description', '')),
        ];

        $permissionIds = (array) $request->input('permissions', []);

        try {
            $this->roles->update($id, $data, $permissionIds);

            flash('success', 'Rôle mis à jour avec succès.');
            return $this->redirect(url('roles/' . $id));
        } catch (ValidationException $e) {
            $_SESSION['_old']    = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());
            return $this->redirect(url('roles/' . $id . '/edit'));
        }
    }

    // ============================================
    // SUPPRESSION
    // ============================================
    public function destroy(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        try {
            $this->roles->delete($id);
            flash('success', 'Rôle supprimé avec succès.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }

        return $this->redirect(url('roles'));
    }
}