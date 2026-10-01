<?php
declare(strict_types=1);

namespace App\Services\Reform;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Reformation;
use App\Repositories\Contracts\ReformationRepositoryInterface;

final class ReformationService
{
    /** ID du statut "Réformé" dans equipment_statuses */
    public const REFORMED_STATUS_ID = 7;

    public function __construct(
        private readonly ReformationRepositoryInterface $repo,
        private readonly ReformationPdfService $pdf,
    ) {}

    // ============================================================
    // CRUD DE BASE
    // ============================================================

    public function create(array $data, int $userId): int
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new ValidationException('Le titre est obligatoire.');
        }

        $reference = $this->repo->generateReference();

        return $this->repo->create([
            'reference'            => $reference,
            'title'                => trim($data['title']),
            'reason'               => $data['reason'] ?? 'obsolescence',
            'reason_details'       => $data['reason_details'] ?? null,
            'status'               => 'draft',
            'commission_reference' => $data['commission_reference'] ?? null,
            'meeting_date'         => $data['meeting_date'] ?? null,
            'created_by'           => $userId,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $r = $this->loadOrFail($id);

        if (!$r->isEditable()) {
            throw new ValidationException('Cette réforme ne peut plus être modifiée.');
        }

        return $this->repo->update($id, $this->mergeState($r, [
            'title'                => $data['title'] ?? $r->title,
            'reason'               => $data['reason'] ?? $r->reason,
            'reason_details'       => $data['reason_details'] ?? $r->reasonDetails,
            'commission_reference' => $data['commission_reference'] ?? $r->commissionReference,
            'meeting_date'         => $data['meeting_date'] ?? $r->meetingDate,
        ]));
    }

    public function delete(int $id): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->isClosed()) {
            throw new ValidationException('Impossible de supprimer une réforme clôturée.');
        }
        return $this->repo->delete($id);
    }

    // ============================================================
    // ITEMS (équipements concernés)
    // ============================================================

    public function addItem(int $reformationId, array $data, int $userId): int
    {
        $r = $this->loadOrFail($reformationId);

        if (!$r->canModifyItems()) {
            throw new ValidationException('Impossible d\'ajouter des équipements à ce stade.');
        }

        $itemId = $this->repo->addItem([
            'reformation_id'  => $reformationId,
            'equipment_id'    => (int) $data['equipment_id'],
            'quantity'        => (int) ($data['quantity'] ?? 1),
            'estimated_value' => (float) ($data['estimated_value'] ?? 0),
            'condition_notes' => $data['condition_notes'] ?? null,
        ]);

        $this->repo->recomputeTotalValue($reformationId);
        $this->log($r, 'add_item', null, null, "Ajout équipement #{$data['equipment_id']}", $userId);

        return $itemId;
    }

    public function removeItem(int $itemId, int $reformationId, int $userId): bool
    {
        $r = $this->loadOrFail($reformationId);
        if (!$r->canModifyItems()) {
            throw new ValidationException('Impossible de retirer des équipements à ce stade.');
        }

        $ok = $this->repo->removeItem($itemId);
        $this->repo->recomputeTotalValue($reformationId);
        $this->log($r, 'remove_item', null, null, "Retrait item #{$itemId}", $userId);

        return $ok;
    }

    // ============================================================
    // WORKFLOW — TRANSITIONS
    // ============================================================

    public function propose(int $id, int $userId): bool
    {
        $r = $this->loadOrFail($id);

        if ($r->status !== 'draft') {
            throw new ValidationException('Seul un brouillon peut être soumis.');
        }
        if (empty($this->repo->findItems($id))) {
            throw new ValidationException('Ajoutez au moins un équipement avant de soumettre.');
        }

        $now = date('Y-m-d H:i:s');
        $ok = $this->repo->update($id, $this->mergeState($r, [
            'status'       => 'proposed',
            'proposed_by'  => $userId,
            'proposed_at'  => $now,
            'submitted_at' => $now,
        ]));

        $this->log($r, 'propose', 'draft', 'proposed', null, $userId);
        return $ok;
    }

    public function review(int $id, int $userId): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'proposed') {
            throw new ValidationException('Transition invalide.');
        }

        $ok = $this->repo->update($id, $this->mergeState($r, ['status' => 'under_review']));
        $this->log($r, 'review', 'proposed', 'under_review', null, $userId);
        return $ok;
    }

    public function approve(int $id, int $userId, array $decisionData = []): bool
    {
        return $this->decide($id, $userId, 'approved', $decisionData);
    }

    public function reject(int $id, int $userId, array $decisionData = []): bool
    {
        return $this->decide($id, $userId, 'rejected', $decisionData);
    }

    public function postpone(int $id, int $userId, string $comment): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'under_review') {
            throw new ValidationException('Transition invalide.');
        }

        $this->repo->createDecision([
            'reformation_id'     => $id,
            'decision'           => 'postponed',
            'decision_date'      => date('Y-m-d'),
            'commission_members' => null,
            'notes'              => $comment,
            'created_by'         => $userId,
        ]);

        $this->log($r, 'postpone', 'under_review', 'under_review', $comment, $userId);
        return true;
    }

    public function generatePv(int $id, int $userId): string
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'approved') {
            throw new ValidationException('Le PV ne peut être généré qu\'après approbation.');
        }

        $items     = $this->repo->findItems($id);
        $decisions = $this->repo->findDecisions($id);

        $relativePath = $this->pdf->generatePv($r, $items, $decisions);

        $this->repo->update($id, $this->mergeState($r, [
            'status'          => 'pv_generated',
            'pv_path'         => $relativePath,
            'pv_generated_at' => date('Y-m-d H:i:s'),
        ]));

        $this->log($r, 'generate_pv', 'approved', 'pv_generated', $relativePath, $userId);
        return $relativePath;
    }

    public function generateExitVoucher(int $id, int $userId): string
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'pv_generated') {
            throw new ValidationException('Générez d\'abord le PV.');
        }

        $items = $this->repo->findItems($id);
        $relativePath = $this->pdf->generateExitVoucher($r, $items);

        $this->repo->update($id, $this->mergeState($r, [
            'status'                    => 'exit_voucher_generated',
            'exit_voucher_path'         => $relativePath,
            'exit_voucher_generated_at' => date('Y-m-d H:i:s'),
        ]));

        $this->log($r, 'generate_exit_voucher', 'pv_generated', 'exit_voucher_generated', $relativePath, $userId);
        return $relativePath;
    }

    public function complete(int $id, int $userId): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'exit_voucher_generated') {
            throw new ValidationException('Le bon de sortie doit être généré avant de clôturer.');
        }

        $items = $this->repo->findItems($id);
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            foreach ($items as $item) {
                $this->updateEquipmentStatus($item->equipmentId, self::REFORMED_STATUS_ID);
            }

            $this->repo->update($id, $this->mergeState($r, ['status' => 'completed']));
            $this->log($r, 'complete', 'exit_voucher_generated', 'completed',
                'Équipements passés en "Réformé"', $userId);

            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function cancel(int $id, int $userId, string $reason = ''): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->isClosed()) {
            throw new ValidationException('Cette réforme est déjà clôturée.');
        }

        $ok = $this->repo->update($id, $this->mergeState($r, ['status' => 'cancelled']));
        $this->log($r, 'cancel', $r->status, 'cancelled', $reason, $userId);
        return $ok;
    }

    // ============================================================
    // HELPERS PRIVÉS
    // ============================================================

    private function decide(int $id, int $userId, string $decision, array $data): bool
    {
        $r = $this->loadOrFail($id);
        if ($r->status !== 'under_review') {
            throw new ValidationException('Transition invalide.');
        }

        $notes = $data['notes'] ?? null;

        $this->repo->createDecision([
            'reformation_id'     => $id,
            'decision'           => $decision,
            'decision_date'      => $data['decision_date'] ?? date('Y-m-d'),
            'commission_members' => $data['commission_members'] ?? null,
            'notes'              => $notes,
            'created_by'         => $userId,
        ]);

        $this->repo->update($id, $this->mergeState($r, [
            'status'              => $decision,
            'decided_at'          => date('Y-m-d H:i:s'),
            'decided_by'          => $userId,
            'decision_notes'      => $notes,
        ]));

        $this->log($r, $decision, 'under_review', $decision, $notes, $userId);
        return true;
    }

    private function loadOrFail(int $id): Reformation
    {
        $r = $this->repo->findById($id);
        if (!$r) {
            throw new NotFoundException("Réforme #{$id} introuvable.");
        }
        return $r;
    }

    /** Fusionne l'état actuel + les changements (car repo.update() écrit tous les champs) */
    private function mergeState(Reformation $r, array $changes): array
    {
        return array_merge([
            'title'                     => $r->title,
            'reason'                    => $r->reason,
            'reason_details'            => $r->reasonDetails,
            'status'                    => $r->status,
            'proposed_by'               => $r->proposedBy,
            'proposed_at'               => $r->proposedAt,
            'submitted_at'              => $r->submittedAt,
            'decided_at'                => $r->decidedAt,
            'decided_by'                => $r->decidedBy,
            'decision_notes'            => $r->decisionNotes,
            'commission_reference'      => $r->commissionReference,
            'meeting_date'              => $r->meetingDate,
            'pv_path'                   => $r->pvPath,
            'pv_generated_at'           => $r->pvGeneratedAt,
            'exit_voucher_path'         => $r->exitVoucherPath,
            'exit_voucher_generated_at' => $r->exitVoucherGeneratedAt,
        ], $changes);
    }

    private function log(Reformation $r, string $action, ?string $from, ?string $to, ?string $comment, int $userId): void
    {
        $this->repo->logWorkflow([
            'reformation_id' => $r->id,
            'from_status'    => $from,
            'to_status'      => $to ?? $r->status,
            'action'         => $action,
            'comment'        => $comment,
            'user_id'        => $userId,
        ]);
    }

    private function updateEquipmentStatus(int $equipmentId, int $statusId): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare(
            'UPDATE equipment SET status_id = :status_id, updated_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['status_id' => $statusId, 'id' => $equipmentId]);
    }
}