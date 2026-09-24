<?php
declare(strict_types=1);

use App\Core\Response;
use App\Core\Request;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EquipmentController;
use App\Controllers\UserController;
use App\Controllers\RoleController;

/** @var App\Core\Router $router */

// ============================================
// PAGE D'ACCUEIL — Redirection intelligente
// ============================================
$router->get('/', function (Request $request) {
    $auth = new \App\Services\Auth\AuthService();
    if ($auth->check()) {
        return Response::redirect(url('dashboard'));
    }
    return Response::redirect(url('login'));
});

// ============================================
// AUTHENTIFICATION
// ============================================
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->post('/logout',  [AuthController::class, 'logout']);
$router->get('/logout',   [AuthController::class, 'logout']);

// ============================================
// UTILISATEURS (Administration)
// ============================================
// IMPORTANT : les routes spécifiques doivent être AVANT /users/{id}
$router->get('/users',                       [UserController::class, 'index']);
$router->get('/users/create',                [UserController::class, 'create']);

// Routes POST (création)
$router->post('/users',                      [UserController::class, 'store']);

// Routes avec ID dynamique
$router->get('/users/{id}',                  [UserController::class, 'show']);
$router->get('/users/{id}/edit',             [UserController::class, 'edit']);

// Routes POST avec ID
$router->post('/users/{id}',                 [UserController::class, 'update']);
$router->post('/users/{id}/delete',          [UserController::class, 'destroy']);
$router->post('/users/{id}/reset-password',  [UserController::class, 'resetPassword']);
$router->post('/users/{id}/toggle-active',   [UserController::class, 'toggleActive']);

// ============================================
// PROFIL PERSONNEL
// ============================================
$router->get('/profile',                     [UserController::class, 'profile']);
$router->post('/profile/change-password',    [UserController::class, 'changePassword']);

// ============================================
// RÔLES ET PERMISSIONS (Administration)  ← NOUVEAU
// ============================================
// IMPORTANT : les routes spécifiques doivent être AVANT /roles/{id}
$router->get('/roles',                       [RoleController::class, 'index']);
$router->get('/roles/create',                [RoleController::class, 'create']);

// Routes POST (création)
$router->post('/roles',                      [RoleController::class, 'store']);

// Routes avec ID dynamique
$router->get('/roles/{id}',                  [RoleController::class, 'show']);
$router->get('/roles/{id}/edit',             [RoleController::class, 'edit']);

// Routes POST avec ID
$router->post('/roles/{id}',                 [RoleController::class, 'update']);
$router->post('/roles/{id}/delete',          [RoleController::class, 'destroy']);

// ============================================
// DASHBOARD (protégé)
// ============================================
$router->get('/dashboard', [DashboardController::class, 'index']);

// ============================================
// RECHERCHE GLOBALE (AJAX)
// ============================================
$router->get('/search', [EquipmentController::class, 'searchAjax']);

// ============================================
// JOURNAL D'AUDIT (Administration)
// ============================================
$router->get('/audit', [\App\Controllers\AuditController::class, 'index']);

// ============================================
// ÉQUIPEMENTS (module Inventaire)
// ============================================
// Routes spécifiques (doivent être AVANT les routes avec {id})
$router->get('/equipment',                    [EquipmentController::class, 'index']);
$router->get('/equipment/create',             [EquipmentController::class, 'create']);
$router->get('/equipment/trash',              [EquipmentController::class, 'trash']);
$router->get('/equipment/export-csv',         [EquipmentController::class, 'exportCsv']);
$router->get('/equipment/export-pdf',         [EquipmentController::class, 'exportPdf']);

// ============================================
// ACTIONS GROUPÉES (BULK)
// ============================================
// IMPORTANT : ces routes doivent être AVANT /equipment/{id}
$router->post('/equipment/bulk/status',       [EquipmentController::class, 'bulkUpdateStatus']);
$router->post('/equipment/bulk/delete',       [EquipmentController::class, 'bulkDelete']);
$router->get('/equipment/bulk/export',        [EquipmentController::class, 'bulkExportCsv']);

// ============================================
// IMPORT CSV
// ============================================
// IMPORTANT : ces routes doivent être AVANT /equipment/{id}
$router->get('/equipment/import',             [EquipmentController::class, 'importForm']);
$router->post('/equipment/import/preview',    [EquipmentController::class, 'importPreview']);
$router->post('/equipment/import/store',      [EquipmentController::class, 'importStore']);
$router->get('/equipment/import/template',    [EquipmentController::class, 'importTemplate']);

// Routes POST (création, mise à jour, suppression)
$router->post('/equipment',                   [EquipmentController::class, 'store']);

// Routes avec ID dynamique
$router->get('/equipment/{id}',               [EquipmentController::class, 'show']);
$router->get('/equipment/{id}/edit',          [EquipmentController::class, 'edit']);
$router->get('/equipment/{id}/qrcode',        [EquipmentController::class, 'qrcode']);
$router->get('/equipment/{id}/duplicate',     [EquipmentController::class, 'duplicate']);
$router->get('/equipment/{id}/show-pdf',      [EquipmentController::class, 'showPdf']);
$router->get('/equipment/{id}/history',       [EquipmentController::class, 'history']);

// Routes POST avec ID
$router->post('/equipment/{id}',              [EquipmentController::class, 'update']);
$router->post('/equipment/{id}/delete',       [EquipmentController::class, 'destroy']);
$router->post('/equipment/{id}/restore',      [EquipmentController::class, 'restore']);
$router->post('/equipment/{id}/force-delete', [EquipmentController::class, 'forceDelete']);
$router->post('/equipment/{id}/update-status', [EquipmentController::class, 'updateStatusAjax']);

// ============================================
// PIÈCES JOINTES (ATTACHMENTS)
// ============================================
// IMPORTANT : ces routes doivent être APRÈS /equipment/{id}
$router->post('/equipment/{id}/attachments',                        [EquipmentController::class, 'uploadAttachment']);
$router->post('/equipment/{id}/attachments/{attachmentId}/delete',  [EquipmentController::class, 'deleteAttachment']);
$router->get('/equipment/{id}/attachments/{attachmentId}/download', [EquipmentController::class, 'downloadAttachment']);