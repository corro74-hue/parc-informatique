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
$router->post('/login',   [AuthController::class, 'login'], [
    // Rate limit : 5 tentatives par 15 min, blocage 15 min en cas de dépassement
    'rate_limit'       => ['max' => 5, 'window' => 900, 'block' => 900],
    'rate_limit_route' => '/login',
]);
$router->post('/logout',  [AuthController::class, 'logout']);
$router->get('/logout',   [AuthController::class, 'logout']);

// ============================================
// AUTHENTIFICATION 2FA
// ============================================
$router->get('/two-factor',          [AuthController::class, 'showTwoFactor']);
$router->post('/two-factor/verify',  [AuthController::class, 'verifyTwoFactor'], [
    // Rate limit : 5 tentatives par 5 min, blocage 15 min en cas de dépassement
    'rate_limit'       => ['max' => 5, 'window' => 300, 'block' => 900],
    'rate_limit_route' => '/two-factor/verify',
]);
$router->post('/two-factor/cancel',  [AuthController::class, 'cancelTwoFactor']);

// ============================================
// UTILISATEURS (Administration)
// ============================================
// IMPORTANT : les routes spécifiques doivent être AVANT /users/{id}
$router->get('/users',                       [UserController::class, 'index'],           [
    'permission'       => 'users.view',
    // Rate limit : 100 requêtes par minute (anti-scraping)
    'rate_limit'       => ['max' => 100, 'window' => 60, 'block' => 300],
    'rate_limit_route' => '/users',
]);
$router->get('/users/create',                [UserController::class, 'create'],          ['permission' => 'users.create']);

// Routes POST (création)
$router->post('/users',                      [UserController::class, 'store'],           ['permission' => 'users.create']);

// Routes avec ID dynamique
$router->get('/users/{id}',                  [UserController::class, 'show'],            ['permission' => 'users.view']);
$router->get('/users/{id}/edit',             [UserController::class, 'edit'],            ['permission' => 'users.edit']);

// Routes POST avec ID
$router->post('/users/{id}',                 [UserController::class, 'update'],          ['permission' => 'users.edit']);
$router->post('/users/{id}/delete',          [UserController::class, 'destroy'],         ['permission' => 'users.delete']);
$router->post('/users/{id}/reset-password',  [UserController::class, 'resetPassword'],   [
    'permission'       => 'users.edit',
    // Rate limit : 3 réinitialisations par heure (protection anti-spam)
    'rate_limit'       => ['max' => 3, 'window' => 3600, 'block' => 1800],
    'rate_limit_route' => '/users/reset-password',
]);
$router->post('/users/{id}/toggle-active',   [UserController::class, 'toggleActive'],    ['permission' => 'users.edit']);

// ============================================
// PROFIL PERSONNEL (accessible à tous les connectés)
// ============================================
$router->get('/profile',                     [UserController::class, 'profile']);
$router->post('/profile/change-password',    [UserController::class, 'changePassword']);

// ============================================
// SÉCURITÉ / 2FA (accessible à tous les connectés)
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
// ADMINISTRATION — BASE DE DONNÉES
// ============================================
// IMPORTANT : les routes spécifiques doivent être AVANT /admin/database
$router->get('/admin/database',                            [\App\Controllers\Admin\DatabaseController::class, 'index'],   ['permission' => 'settings.manage']);
$router->get('/admin/database/health',                     [\App\Controllers\Admin\DatabaseController::class, 'health'],  ['permission' => 'settings.manage']);

// Actions POST (création, suppression, nettoyage)
$router->post('/admin/database/backup',                    [\App\Controllers\Admin\DatabaseController::class, 'createBackup'],   ['permission' => 'settings.manage']);
$router->post('/admin/database/clean',                     [\App\Controllers\Admin\DatabaseController::class, 'cleanBackups'],   ['permission' => 'settings.manage']);
$router->post('/admin/database/check',                     [\App\Controllers\Admin\DatabaseController::class, 'checkIntegrity'], ['permission' => 'settings.manage']);
$router->post('/admin/database/optimize',                  [\App\Controllers\Admin\DatabaseController::class, 'optimize'],       ['permission' => 'settings.manage']);

// Routes avec ID dynamique (À LA FIN)
$router->get('/admin/database/backup/{id}/download',       [\App\Controllers\Admin\DatabaseController::class, 'downloadBackup'], ['permission' => 'settings.manage']);
$router->post('/admin/database/backup/{id}/delete',        [\App\Controllers\Admin\DatabaseController::class, 'deleteBackup'],   ['permission' => 'settings.manage']);

// ============================================
// ADMINISTRATION — SYSTÈME
// ============================================
$router->get('/admin/system/health',                       [\App\Controllers\Admin\SystemController::class, 'health'],              ['permission' => 'settings.manage']);

// Mode maintenance
$router->post('/admin/system/maintenance/enable',          [\App\Controllers\Admin\SystemController::class, 'enableMaintenance'],   ['permission' => 'settings.manage']);
$router->post('/admin/system/maintenance/disable',         [\App\Controllers\Admin\SystemController::class, 'disableMaintenance'],  ['permission' => 'settings.manage']);

// Nettoyage
$router->post('/admin/system/clear/cache',                 [\App\Controllers\Admin\SystemController::class, 'clearCache'],          ['permission' => 'settings.manage']);
$router->post('/admin/system/clear/logs',                  [\App\Controllers\Admin\SystemController::class, 'clearLogs'],           ['permission' => 'settings.manage']);
$router->post('/admin/system/clear/sessions',              [\App\Controllers\Admin\SystemController::class, 'clearSessions'],       ['permission' => 'settings.manage']);
$router->post('/admin/system/clear/all',                   [\App\Controllers\Admin\SystemController::class, 'clearAll'],            ['permission' => 'settings.manage']);

// ============================================
// PAGE DE MAINTENANCE PUBLIQUE (mode système)
// ============================================
// ⚠️ URL renommée en /maintenance-mode car /maintenance est utilisé par le module Maintenance
$router->get('/maintenance-mode', function (Request $request) {
    $maintenance = new \App\Services\System\MaintenanceModeService();
    $info = $maintenance->getInfo();

    // Si le mode maintenance n'est PAS actif, on redirige
    if (!$info['active']) {
        return \App\Core\Response::redirect(url('dashboard'));
    }

    // Formater la date de fin
    $endAt = null;
    if (!empty($info['end_at'])) {
        $endAt = date('d/m/Y à H:i', strtotime($info['end_at']));
    }

    // Afficher la vue de maintenance
    $viewPath = dirname(__DIR__) . '/resources/views/errors/maintenance.php';
    $content = (function () use ($viewPath, $info, $endAt) {
        $reason = $info['reason'];
        ob_start();
        require $viewPath;
        return ob_get_clean();
    })();

    return new \App\Core\Response($content, 503);
});

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
$router->post('/equipment/import/store',      [EquipmentController::class, 'importStore'],    [
    'permission'       => 'equipment.import',
    // Rate limit : 10 imports par heure (anti-spam)
    'rate_limit'       => ['max' => 10, 'window' => 3600, 'block' => 1800],
    'rate_limit_route' => '/equipment/import',
]);
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

// ============================================
// AFFECTATIONS D'ÉQUIPEMENTS
// ============================================
// IMPORTANT : /assignments/create doit être AVANT /assignments/{id}
$router->get('/assignments',                       [\App\Controllers\AssignmentController::class, 'index'],           ['permission' => 'equipment.view']);
$router->get('/assignments/create',                [\App\Controllers\AssignmentController::class, 'create'],          ['permission' => 'equipment.edit']);
$router->post('/assignments',                      [\App\Controllers\AssignmentController::class, 'store'],           ['permission' => 'equipment.edit']);

$router->get('/assignments/{id}',                  [\App\Controllers\AssignmentController::class, 'show'],            ['permission' => 'equipment.view']);
$router->get('/assignments/{id}/edit',             [\App\Controllers\AssignmentController::class, 'edit'],            ['permission' => 'equipment.edit']);

$router->post('/assignments/{id}',                 [\App\Controllers\AssignmentController::class, 'update'],          ['permission' => 'equipment.edit']);
$router->post('/assignments/{id}/return',          [\App\Controllers\AssignmentController::class, 'returnEquipment'], ['permission' => 'equipment.edit']);
$router->post('/assignments/{id}/delete',          [\App\Controllers\AssignmentController::class, 'destroy'],         ['permission' => 'equipment.delete']);

// ============================================
// EMPLOYÉS
// ============================================
// IMPORTANT : /employees/create doit être AVANT /employees/{id}
$router->get('/employees',                       [\App\Controllers\EmployeeController::class, 'index'],   ['permission' => 'equipment.view']);
$router->get('/employees/create',                [\App\Controllers\EmployeeController::class, 'create'],  ['permission' => 'equipment.edit']);
$router->post('/employees',                      [\App\Controllers\EmployeeController::class, 'store'],   ['permission' => 'equipment.edit']);

$router->get('/employees/{id}',                  [\App\Controllers\EmployeeController::class, 'show'],    ['permission' => 'equipment.view']);
$router->get('/employees/{id}/edit',             [\App\Controllers\EmployeeController::class, 'edit'],    ['permission' => 'equipment.edit']);

$router->post('/employees/{id}',                 [\App\Controllers\EmployeeController::class, 'update'],  ['permission' => 'equipment.edit']);
$router->post('/employees/{id}/delete',          [\App\Controllers\EmployeeController::class, 'destroy'], ['permission' => 'equipment.delete']);

// ============================================
// MAINTENANCE (module Interventions)
// ============================================
// IMPORTANT : /maintenance/create doit être AVANT /maintenance/{id}
$router->get('/maintenance',                       [\App\Controllers\MaintenanceController::class, 'index'],    ['permission' => 'equipment.view']);
$router->get('/maintenance/create',                [\App\Controllers\MaintenanceController::class, 'create'],   ['permission' => 'equipment.edit']);
$router->post('/maintenance',                      [\App\Controllers\MaintenanceController::class, 'store'],    ['permission' => 'equipment.edit']);

$router->get('/maintenance/{id}',                  [\App\Controllers\MaintenanceController::class, 'show'],     ['permission' => 'equipment.view']);
$router->get('/maintenance/{id}/edit',             [\App\Controllers\MaintenanceController::class, 'edit'],     ['permission' => 'equipment.edit']);

$router->post('/maintenance/{id}',                 [\App\Controllers\MaintenanceController::class, 'update'],   ['permission' => 'equipment.edit']);
$router->post('/maintenance/{id}/delete',          [\App\Controllers\MaintenanceController::class, 'destroy'],  ['permission' => 'equipment.delete']);