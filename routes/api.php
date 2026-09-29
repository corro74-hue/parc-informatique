<?php
declare(strict_types=1);

use App\Core\Response;
use App\Controllers\EquipmentController;

/** @var App\Core\Router $router */

// ============================================
// PING (test de connectivité)
// ============================================
$router->get('/api/ping', function () {
    return Response::json([
        'success' => true,
        'message' => 'pong',
        'time'    => date('c'),
    ]);
});

// ============================================
// API — Recherche d'équipements (autocomplete)
// ============================================
$router->get('/api/equipment/search', [EquipmentController::class, 'searchAjax']);