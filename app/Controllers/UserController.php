<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\ValidationException;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\Auth\AuthService;
use App\Services\User\UserService;

final class UserController extends Controller
{
    private UserService $users;

    public function __construct()
    {
        $this->users = new UserService();
    }

    // ============================================
    // LISTE DES UTILISATEURS
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'role'   => trim((string) $request->input('role', '')),
            'status' => trim((string) $request->input('status', '')),
        ];

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 20;

        $result = $this->users->paginate($filters, $page, $perPage);
        $roles  = $this->users->getAllRoles();

        return $this->view('users.index', [
            'title'   => 'Utilisateurs',
            'users'   => $result['data'],
            'total'   => $result['total'],
            'page'    => $result['page'],
            'lastPage' => $result['lastPage'],
            'perPage' => $result['perPage'],
            'roles'   => $roles,
            'filters' => $filters,
        ]);
    }

    // ============================================
    // FORMULAIRE DE CRÉATION
    // ============================================
    public function create(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $roles = $this->users->getAllRoles();

        return $this->view('users.create', [
            'title' => 'Nouvel utilisateur',
            'roles' => $roles,
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
            'username'             => trim((string) $request->input('username', '')),
            'email'                => trim((string) $request->input('email', '')),
            'first_name'           => trim((string) $request->input('first_name', '')),
            'last_name'            => trim((string) $request->input('last_name', '')),
            'phone'                => trim((string) $request->input('phone', '')),
            'is_active'            => (int) $request->input('is_active', 1),
            'must_change_password' => (int) $request->input('must_change_password', 0),
        ];

        $password = (string) $request->input('password', '');
        $roleIds  = (array) $request->input('roles', []);

        try {
            $userId = $this->users->create($data, $password, $roleIds);

            flash('success', 'Utilisateur créé avec succès.');
            return $this->redirect(url('users/' . $userId));
        } catch (ValidationException $e) {
            $_SESSION['_old']    = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());
            return $this->redirect(url('users/create'));
        }
    }

    // ============================================
    // FICHE UTILISATEUR
    // ============================================
    public function show(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = $this->users->find($id);
        if (!$user) {
            flash('error', 'Utilisateur introuvable.');
            return $this->redirect(url('users'));
        }

        $loginHistory = $this->users->getLoginHistory($id, 20);

        return $this->view('users.show', [
            'title'        => 'Utilisateur : ' . $user->getFullName(),
            'user'         => $user,
            'loginHistory' => $loginHistory,
        ]);
    }

    // ============================================
    // FORMULAIRE D'ÉDITION
    // ============================================
    public function edit(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = $this->users->find($id);
        if (!$user) {
            flash('error', 'Utilisateur introuvable.');
            return $this->redirect(url('users'));
        }

        $roles = $this->users->getAllRoles();
        $userRoleIds = array_column($user->roles, 'id');

        return $this->view('users.edit', [
            'title'        => 'Modifier : ' . $user->getFullName(),
            'user'         => $user,
            'roles'        => $roles,
            'userRoleIds'  => $userRoleIds,
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
            'username'             => trim((string) $request->input('username', '')),
            'email'                => trim((string) $request->input('email', '')),
            'first_name'           => trim((string) $request->input('first_name', '')),
            'last_name'            => trim((string) $request->input('last_name', '')),
            'phone'                => trim((string) $request->input('phone', '')),
            'is_active'            => (int) $request->input('is_active', 1),
            'must_change_password' => (int) $request->input('must_change_password', 0),
        ];

        $roleIds = (array) $request->input('roles', []);

        try {
            $this->users->update($id, $data, $roleIds);

            flash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirect(url('users/' . $id));
        } catch (ValidationException $e) {
            $_SESSION['_old']    = $data;
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());
            return $this->redirect(url('users/' . $id . '/edit'));
        }
    }

    // ============================================
    // SUPPRESSION (SOFT DELETE)
    // ============================================
    public function destroy(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        try {
            $this->users->delete($id, (int) $_SESSION['user_id']);
            flash('success', 'Utilisateur supprimé avec succès.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }

        return $this->redirect(url('users'));
    }

    // ============================================
    // RÉINITIALISATION DU MOT DE PASSE
    // ============================================
    public function resetPassword(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        try {
            $temporaryPassword = $this->users->resetPassword($id);
            flash('success', 'Mot de passe réinitialisé. Mot de passe temporaire : ' . $temporaryPassword);
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }

        return $this->redirect(url('users/' . $id));
    }

    // ============================================
    // ACTIVER / DÉSACTIVER
    // ============================================
    public function toggleActive(Request $request, string $id): Response
    {
        $id = (int) $id;
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        try {
            $this->users->find($id);
            flash('success', 'Statut du compte modifié.');
        } catch (\Exception $e) {
            flash('error', $e->getMessage());
        }

        return $this->redirect(url('users/' . $id));
    }

    // ============================================
    // PROFIL PERSONNEL
    // ============================================
    public function profile(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $user = $this->users->find((int) $_SESSION['user_id']);
        if (!$user) {
            return $this->redirect(url('login'));
        }

        return $this->view('users.profile', [
            'title' => 'Mon profil',
            'user'  => $user,
        ]);
    }

    // ============================================
    // CHANGEMENT DE MOT DE PASSE PERSONNEL
    // ============================================
    public function changePassword(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;
        if ($r = (new CsrfMiddleware())->handle()) return $r;

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword     = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        $user = $this->users->find((int) $_SESSION['user_id']);
        if (!$user) {
            return $this->redirect(url('login'));
        }

        if (!password_verify($currentPassword, $user->passwordHash)) {
            flash('error', 'Le mot de passe actuel est incorrect.');
            return $this->redirect(url('profile'));
        }

        if ($newPassword !== $confirmPassword) {
            flash('error', 'Les deux mots de passe ne correspondent pas.');
            return $this->redirect(url('profile'));
        }

        try {
            $this->users->changePassword($user->id, $newPassword, false);
            flash('success', 'Mot de passe changé avec succès.');
        } catch (ValidationException $e) {
            $_SESSION['_errors'] = $e->getErrors();
            flash('error', $e->getMessage());
        }

        return $this->redirect(url('profile'));
    }
}