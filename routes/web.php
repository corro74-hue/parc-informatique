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
// AUTHENTIFICATION 2FA
// ============================================
$router->get('/two-factor',          [AuthController::class, 'showTwoFactor']);
$router->post('/two-factor/verify',  [AuthController::class, 'verifyTwoFactor']);
$router->post('/two-factor/cancel',  [AuthController::class, 'cancelTwoFactor']);

// ============================================
// UTILISATEURS (Administration)
// ============================================
// IMPORTANT : les routes spécifiques doivent être AVANT /users/{id}
$router->get('/users',                       [UserController::class, 'index'],           ['permission' => 'users.view']);
$router->get('/users/create',                [UserController::class, 'create'],          ['permission' => 'users.create']);

// Routes POST (création)
$router->post('/users',                      [UserController::class, 'store'],           ['permission' => 'users.create']);

// Routes avec ID dynamique
$router->get('/users/{id}',                  [UserController::class, 'show'],            ['permission' => 'users.view']);
$router->get('/users/{id}/edit',             [UserController::class, 'edit'],            ['permission' => 'users.edit']);

// Routes POST avec ID
$router->post('/users/{id}',                 [UserController::class, 'update'],          ['permission' => 'users.edit']);
$router->post('/users/{id}/delete',          [UserController::class, 'destroy'],         ['permission' => 'users.delete']);
$router->post('/users/{id}/reset-password',  [UserController::class, 'resetPassword'],   ['permission' => 'users.edit']);
$router->post('/users/{id}/toggle-active',   [UserController::class, 'toggleActive'],    ['permission' => 'users.edit']);

// ============================================
// PROFIL PERSONNEL (accessible à tous les connectés)
// ============================================
$router->get('/profile',                     [UserController::class, 'profile']);
$router->post('/profile/change-password',    [UserController::class, 'changePassword']);

// ============================================
// SÉCURITÉ / 2FA (accessible à tous les connectés)  ← NOUVEAU
// ============================================
$router->get('/profile/security',              [UserController::class, 'security']);
$router->get('/profile/security/enable',       [UserController::class, 'enableTwoFactor']);
$router->post('/profile/security/confirm',     [UserController::class, 'confirmTwoFactor']);
$router->get('/profile/security/backup-codes', [UserController::class, 'showBackupCodes']);
$router->post('/profile/security/disable',     [UserController::class, 'disableTwoFactor']);

// ============================================
// RÔLES ET PERMISSIONS (Administration)
// ============================================
$router->get('/roles',                       [RoleController::class, 'index'],           ['permission' => 'settings.manage']);
$router->get('/roles/create',                [RoleController::class, 'create'],          ['permission' => 'settings.manage']);
$router->post('/roles',                      [RoleController::class, 'store'],           ['permission' => 'settings.manage']);
$router->get('/roles/{id}',                  [RoleController::class, 'show'],            ['permission' => 'settings.manage']);
$router->get('/roles/{id}/edit',             [RoleController::class, 'edit'],            ['permission' => 'settings.manage']);
$router->post('/roles/{id}',                 [RoleController::class, 'update'],          ['permission' => 'settings.manage']);
$router->post('/roles/{id}/delete',          [RoleController::class, 'destroy'],         ['permission' => 'settings.manage']);

// ============================================
// DASHBOARD (protégé - accessible à tous les connectés)
// ============================================
$router->get('/dashboard', [DashboardController::class, 'index']);

// ============================================
// RECHERCHE GLOBALE (AJAX - accessible à tous les connectés)
// ============================================
$router->get('/search', [EquipmentController::class, 'searchAjax']);

// ============================================
// JOURNAL D'AUDIT (Administration)
// ============================================
$router->get('/audit', [\App\Controllers\AuditController::class, 'index'], ['permission' => 'audit.view']);

// ============================================
// ÉQUIPEMENTS (module Inventaire)
// ============================================
$router->get('/equipment',                    [EquipmentController::class, 'index'],      ['permission' => 'equipment.view']);
$router->get('/equipment/create',             [EquipmentController::class, 'create'],     ['permission' => 'equipment.create']);
$router->get('/equipment/trash',              [EquipmentController::class, 'trash'],      ['permission' => 'equipment.view']);
$router->get('/equipment/export-csv',         [EquipmentController::class, 'exportCsv'],  ['permission' => 'equipment.export']);
$router->get('/equipment/export-pdf',         [EquipmentController::class, 'exportPdf'],  ['permission' => 'equipment.export']);

$router->post('/equipment/bulk/status',       [EquipmentController::class, 'bulkUpdateStatus'], ['permission' => 'equipment.edit']);
$router->post('/equipment/bulk/delete',       [EquipmentController::class, 'bulkDelete'],       ['permission' => 'equipment.delete']);
$router->get('/equipment/bulk/export',        [EquipmentController::class, 'bulkExportCsv'],    ['permission' => 'equipment.export']);

$router->get('/equipment/import',             [EquipmentController::class, 'importForm'],     ['permission' => 'equipment.import']);
$router->post('/equipment/import/preview',    [EquipmentController::class, 'importPreview'],  ['permission' => 'equipment.import']);
$router->post('/equipment/import/store',      [EquipmentController::class, 'importStore'],    ['permission' => 'equipment.import']);
$router->get('/equipment/import/template',    [EquipmentController::class, 'importTemplate'], ['permission' => 'equipment.import']);

$router->post('/equipment',                   [EquipmentController::class, 'store'],          ['permission' => 'equipment.create']);

$router->get('/equipment/{id}',               [EquipmentController::class, 'show'],           ['permission' => 'equipment.view']);
$router->get('/equipment/{id}/edit',          [EquipmentController::class, 'edit'],           ['permission' => 'equipment.edit']);
$router->get('/equipment/{id}/qrcode',        [EquipmentController::class, 'qrcode'],         ['permission' => 'equipment.view']);
$router->get('/equipment/{id}/duplicate',     [EquipmentController::class, 'duplicate'],      ['permission' => 'equipment.create']);
$router->get('/equipment/{id}/show-pdf',      [EquipmentController::class, 'showPdf'],        ['permission' => 'equipment.view']);
$router->get('/equipment/{id}/history',       [EquipmentController::class, 'history'],        ['permission' => 'equipment.view']);

$router->post('/equipment/{id}',              [EquipmentController::class, 'update'],         ['permission' => 'equipment.edit']);
$router->post('/equipment/{id}/delete',       [EquipmentController::class, 'destroy'],        ['permission' => 'equipment.delete']);
$router->post('/equipment/{id}/restore',      [EquipmentController::class, 'restore'],        ['permission' => 'equipment.delete']);
$router->post('/equipment/{id}/force-delete', [EquipmentController::class, 'forceDelete'],    ['permission' => 'equipment.delete']);
$router->post('/equipment/{id}/update-status', [EquipmentController::class, 'updateStatusAjax'], ['permission' => 'equipment.edit']);

$router->post('/equipment/{id}/attachments',                        [EquipmentController::class, 'uploadAttachment'],   ['permission' => 'equipment.edit']);
$router->post('/equipment/{id}/attachments/{attachmentId}/delete',  [EquipmentController::class, 'deleteAttachment'],   ['permission' => 'equipment.edit']);
$router->get('/equipment/{id}/attachments/{attachmentId}/download', [EquipmentController::class, 'downloadAttachment'], ['permission' => 'equipment.view']);