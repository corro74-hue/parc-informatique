<?php
declare(strict_types=1);

use App\Core\Response;

/** @var App\Core\Router $router */

$router->get('/api/ping', function () {
    return Response::json([
        'success' => true,
        'message' => 'pong',
        'time'    => date('c'),
    ]);
});