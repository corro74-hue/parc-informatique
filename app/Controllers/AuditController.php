<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Repositories\MySql\AuditRepository;

final class AuditController extends Controller
{
    private AuditRepository $repo;

    public function __construct()
    {
        $this->repo = new AuditRepository();
    }

    // ============================================
    // JOURNAL D'AUDIT GLOBAL
    // ============================================
    public function index(Request $request): Response
    {
        if ($r = (new AuthMiddleware())->handle()) return $r;

        // ----- Filtres -----
        $filters = [
            'search'      => trim((string) $request->input('search', '')),
            'action'      => $request->input('action'),
            'entity_type' => $request->input('entity_type'),
            'severity'    => $request->input('severity'),
            'user_id'     => $request->input('user_id'),
        ];

        // ----- Pagination -----
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = 25;

        // ----- Récupérer les logs paginés -----
        $result = $this->repo->paginate($filters, $page, $perPage);

        // ----- Données pour les filtres -----
        $actions     = $this->repo->getDistinctActions();
        $entityTypes = $this->repo->getDistinctEntityTypes();
        $severities  = ['info', 'warning', 'error', 'critical'];
        $counts      = $this->repo->countBySeverity();

        return $this->view('audit.index', [
            'title'       => 'Journal d\'audit',
            'result'      => $result,
            'filters'     => $filters,
            'actions'     => $actions,
            'entityTypes' => $entityTypes,
            'severities'  => $severities,
            'counts'      => $counts,
        ], 'app');
    }
}