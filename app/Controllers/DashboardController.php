<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Auth\AuthService;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        $auth = new AuthService();
        $user = $auth->user();

        // ----- Alertes garantie -----
        $equipmentRepo = new EquipmentRepository();

        $expiringWarranties       = $equipmentRepo->findExpiringWarranties(30);
        $recentlyExpiredWarranties = $equipmentRepo->findRecentlyExpiredWarranties(30);

        return $this->view('dashboard.index', [
            'title'                     => 'Tableau de bord',
            'user'                      => $user,
            'expiringWarranties'        => $expiringWarranties,
            'recentlyExpiredWarranties' => $recentlyExpiredWarranties,
        ], 'app');
    }
}