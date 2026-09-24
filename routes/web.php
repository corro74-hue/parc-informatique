<?php
declare(strict_types=1);

use App\Core\Response;
use App\Core\Request;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EquipmentController;

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
// DASHBOARD (protégé)
// ============================================
$router->get('/dashboard', [DashboardController::class, 'index']);

// ============================================
// RECHERCHE GLOBALE (AJAX)
// ============================================
$router->get('/search', [EquipmentController::class, 'searchAjax']);

// ============================================
// JOURNAL D'AUDIT (Administration)  ← NOUVEAU
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