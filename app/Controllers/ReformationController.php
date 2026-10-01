<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Middleware\CsrfMiddleware;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use App\Repositories\Contracts\ReformationRepositoryInterface;
use App\Repositories\MySql\EquipmentRepository;
use App\Repositories\MySql\ReformationRepository;
use App\Services\Reform\ReformationPdfService;
use App\Services\Reform\ReformationService;

final class ReformationController extends Controller
{
    private ReformationRepositoryInterface $repo;
    private ReformationService $service;
    private EquipmentRepositoryInterface $equipmentRepo;

    public function __construct()
    {
        $this->repo          = new ReformationRepository();
        $this->service       = new ReformationService(
            $this->repo,
            new ReformationPdfService(),
        );
        $this->equipmentRepo = new EquipmentRepository();
    }

    // ============================================================
    // LISTE
    // ============================================================

    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => (string) $request->get('status', ''),
            'reason' => (string) $request->get('reason', ''),
        ];

        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 15;

        $paginated = $this->repo->paginate(array_filter($filters), $page, $perPage);
        $stats     = $this->repo->countByStatus();

        return $this->view('reformation/index', [
            'title'       => 'Réformes',
            'paginated'   => $paginated,
            'filters'     => $filters,
            'stats'       => $stats,
            'totalAll'    => $this->repo->countAll(),
            'totalActive' => $this->repo->countActive(),
        ]);
    }

    // ============================================================
    // CRÉATION
    // ============================================================

    public function create(Request $request): Response
    {
        return $this->view('reformation/create', [
            'title'  => 'Nouvelle réforme',
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function store(Request $request): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $data = [
            'title'                => trim((string) $request->post('title', '')),
            'reason'               => (string) $request->post('reason', 'obsolescence'),
            'reason_details'       => trim((string) $request->post('reason_details', '')),
            'commission_reference' => trim((string) $request->post('commission_reference', '')),
            'meeting_date'         => $request->post('meeting_date') ?: null,
        ];

        $errors = $this->validate($data);
        if (!empty($errors)) {
            return $this->view('reformation/create', [
                'title'  => 'Nouvelle réforme',
                'errors' => $errors,
                'old'    => $data,
            ]);
        }

        try {
            $id = $this->service->create($data, (int) auth_id());
            flash('success', 'Réforme créée. Ajoutez maintenant les équipements concernés.');
            return $this->redirect(url("reformations/{$id}"));
        } catch (ValidationException $e) {
            return $this->view('reformation/create', [
                'title'  => 'Nouvelle réforme',
                'errors' => ['global' => $e->getMessage()],
                'old'    => $data,
            ]);
        }
    }

    // ============================================================
    // AFFICHAGE
    // ============================================================

    public function show(Request $request, string $id): Response
    {
        $id = (int) $id;
        $reformation = $this->repo->findById($id);
        if (!$reformation) {
            throw new NotFoundException('Réforme introuvable.');
        }

        // ✅ NOUVEAU : Charger les documents liés à cette réforme
        $documentRepo = new \App\Repositories\MySql\DocumentRepository();
        $documents = $documentRepo->findByEntity('reformation', $id);

        return $this->view('reformation/show', [
            'title'              => 'Réforme ' . $reformation->reference,
            'reformation'        => $reformation,
            'items'              => $this->repo->findItems($id),
            'decisions'          => $this->repo->findDecisions($id),
            'logs'               => $this->repo->findWorkflowLogs($id),
            'availableEquipment' => $this->getAvailableEquipment(),
            'documents'          => $documents,
        ]);
    }

    // ============================================================
    // ÉDITION
    // ============================================================

    public function edit(Request $request, string $id): Response
    {
        $id = (int) $id;
        $reformation = $this->repo->findById($id);
        if (!$reformation) {
            throw new NotFoundException('Réforme introuvable.');
        }
        if (!$reformation->isEditable()) {
            flash('error', 'Cette réforme ne peut plus être modifiée.');
            return $this->redirect(url("reformations/{$id}"));
        }

        return $this->view('reformation/edit', [
            'title'              => 'Modifier ' . $reformation->reference,
            'reformation'        => $reformation,
            'items'              => $this->repo->findItems($id),
            'errors'             => [],
            'old'                => [],
            'availableEquipment' => $this->getAvailableEquipment(),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $id = (int) $id;
        $data = [
            'title'                => trim((string) $request->post('title', '')),
            'reason'               => (string) $request->post('reason', 'obsolescence'),
            'reason_details'       => trim((string) $request->post('reason_details', '')),
            'commission_reference' => trim((string) $request->post('commission_reference', '')),
            'meeting_date'         => $request->post('meeting_date') ?: null,
        ];

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $reformation = $this->repo->findById($id);
            return $this->view('reformation/edit', [
                'title'              => 'Modifier ' . $reformation->reference,
                'reformation'        => $reformation,
                'items'              => $this->repo->findItems($id),
                'errors'             => $errors,
                'old'                => $data,
                'availableEquipment' => $this->getAvailableEquipment(),
            ]);
        }

        try {
            $this->service->update($id, $data);
            flash('success', 'Réforme mise à jour.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    // ============================================================
    // SUPPRESSION
    // ============================================================

    public function destroy(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        try {
            $this->service->delete((int) $id);
            flash('success', 'Réforme supprimée.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url('reformations'));
    }

    // ============================================================
    // ITEMS
    // ============================================================

    public function addItem(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $id = (int) $id;
        $data = [
            'equipment_id'    => (int) $request->post('equipment_id', 0),
            'quantity'        => max(1, (int) $request->post('quantity', 1)),
            'estimated_value' => (float) $request->post('estimated_value', 0),
            'condition_notes' => trim((string) $request->post('condition_notes', '')),
        ];

        if ($data['equipment_id'] <= 0) {
            flash('error', 'Sélectionnez un équipement.');
            return $this->redirect(url("reformations/{$id}"));
        }

        try {
            $this->service->addItem($id, $data, (int) auth_id());
            flash('success', 'Équipement ajouté à la réforme.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    public function removeItem(Request $request, string $id, string $itemId): Response
    {
        if ($r = $this->guardCsrf()) return $r;

        $id     = (int) $id;
        $itemId = (int) $itemId;

        try {
            $this->service->removeItem($itemId, $id, (int) auth_id());
            flash('success', 'Équipement retiré.');
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    // ============================================================
    // WORKFLOW
    // ============================================================

    public function propose(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        return $this->runTransition(fn() => $this->service->propose((int) $id, (int) auth_id()),
            'Réforme soumise pour examen.', (int) $id);
    }

    public function review(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        return $this->runTransition(fn() => $this->service->review((int) $id, (int) auth_id()),
            'Réforme prise en examen.', (int) $id);
    }

    public function approve(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        $data = [
            'notes'              => trim((string) $request->post('notes', '')),
            'commission_members' => trim((string) $request->post('commission_members', '')),
            'decision_date'      => $request->post('decision_date') ?: date('Y-m-d'),
        ];
        return $this->runTransition(fn() => $this->service->approve((int) $id, (int) auth_id(), $data),
            'Réforme approuvée.', (int) $id);
    }

    public function reject(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        $data = [
            'notes'              => trim((string) $request->post('notes', '')),
            'commission_members' => trim((string) $request->post('commission_members', '')),
            'decision_date'      => $request->post('decision_date') ?: date('Y-m-d'),
        ];
        return $this->runTransition(fn() => $this->service->reject((int) $id, (int) auth_id(), $data),
            'Réforme rejetée.', (int) $id);
    }

    public function postpone(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        $comment = trim((string) $request->post('notes', ''));
        return $this->runTransition(fn() => $this->service->postpone((int) $id, (int) auth_id(), $comment),
            'Réforme reportée.', (int) $id);
    }

    // ============================================================
    // GÉNÉRATION PDF
    // ============================================================

    public function generatePv(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        try {
            $this->service->generatePv((int) $id, (int) auth_id());
            flash('success', 'Procès-verbal généré avec succès.');
        } catch (\Throwable $e) {
            flash('error', 'Erreur génération PV : ' . $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    public function generateExitVoucher(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        try {
            $this->service->generateExitVoucher((int) $id, (int) auth_id());
            flash('success', 'Bon de sortie généré avec succès.');
        } catch (\Throwable $e) {
            flash('error', 'Erreur génération bon de sortie : ' . $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    public function complete(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        return $this->runTransition(fn() => $this->service->complete((int) $id, (int) auth_id()),
            'Réforme terminée. Les équipements sont passés en "Réformé".', (int) $id);
    }

    public function cancel(Request $request, string $id): Response
    {
        if ($r = $this->guardCsrf()) return $r;
        $reason = trim((string) $request->post('reason', ''));
        return $this->runTransition(fn() => $this->service->cancel((int) $id, (int) auth_id(), $reason),
            'Réforme annulée.', (int) $id);
    }

    // ============================================================
    // TÉLÉCHARGEMENT PDF
    // ============================================================

    public function downloadPv(Request $request, string $id): Response
    {
        $r = $this->repo->findById((int) $id);
        if (!$r || !$r->pvPath) {
            throw new NotFoundException('PV non disponible.');
        }
        return $this->downloadFile(storage_path($r->pvPath), "PV-{$r->reference}.pdf");
    }

    public function downloadExitVoucher(Request $request, string $id): Response
    {
        $r = $this->repo->findById((int) $id);
        if (!$r || !$r->exitVoucherPath) {
            throw new NotFoundException('Bon de sortie non disponible.');
        }
        return $this->downloadFile(storage_path($r->exitVoucherPath), "BonSortie-{$r->reference}.pdf");
    }

    // ============================================================
    // HELPERS PRIVÉS
    // ============================================================

    private function guardCsrf(): ?Response
    {
        return (new CsrfMiddleware())->handle();
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['title'])) {
            $errors['title'] = 'Le titre est obligatoire.';
        }
        if (!in_array($data['reason'], ['obsolescence', 'breakdown', 'wear', 'end_of_life', 'other'], true)) {
            $errors['reason'] = 'Motif invalide.';
        }
        return $errors;
    }

    private function runTransition(callable $fn, string $successMsg, int $id): Response
    {
        try {
            $fn();
            flash('success', $successMsg);
        } catch (ValidationException $e) {
            flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            flash('error', 'Erreur : ' . $e->getMessage());
        }
        return $this->redirect(url("reformations/{$id}"));
    }

    private function getAvailableEquipment(): array
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("
            SELECT id, inventory_number, designation
            FROM equipment
            WHERE deleted_at IS NULL
              AND status_id NOT IN (7, 8, 9)
            ORDER BY designation ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}