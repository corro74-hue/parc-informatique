<?php
declare(strict_types=1);

namespace App\Models;

final class Reformation
{
    public function __construct(
        public readonly int $id,
        public readonly string $reference,
        public readonly string $title,
        public readonly string $reason,
        public readonly ?string $reasonDetails,
        public readonly string $status,
        public readonly ?int $proposedBy,
        public readonly ?string $proposedAt,
        public readonly ?string $submittedAt,
        public readonly ?string $decidedAt,
        public readonly ?int $decidedBy,
        public readonly ?string $decisionNotes,
        public readonly ?string $commissionReference,
        public readonly ?string $meetingDate,
        public readonly float $totalValue,
        public readonly ?string $pvPath,
        public readonly ?string $pvGeneratedAt,
        public readonly ?string $exitVoucherPath,
        public readonly ?string $exitVoucherGeneratedAt,
        public readonly ?int $createdBy,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $deletedAt,
        public readonly ?string $proposedByName = null,
        public readonly ?string $decidedByName = null,
        public readonly ?string $createdByName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                       (int) $data['id'],
            reference:                $data['reference'],
            title:                    $data['title'],
            reason:                   $data['reason'],
            reasonDetails:            $data['reason_details'] ?? null,
            status:                   $data['status'],
            proposedBy:               isset($data['proposed_by']) ? (int) $data['proposed_by'] : null,
            proposedAt:               $data['proposed_at'] ?? null,
            submittedAt:              $data['submitted_at'] ?? null,
            decidedAt:                $data['decided_at'] ?? null,
            decidedBy:                isset($data['decided_by']) ? (int) $data['decided_by'] : null,
            decisionNotes:            $data['decision_notes'] ?? null,
            commissionReference:      $data['commission_reference'] ?? null,
            meetingDate:              $data['meeting_date'] ?? null,
            totalValue:               (float) ($data['total_value'] ?? 0),
            pvPath:                   $data['pv_path'] ?? null,
            pvGeneratedAt:            $data['pv_generated_at'] ?? null,
            exitVoucherPath:          $data['exit_voucher_path'] ?? null,
            exitVoucherGeneratedAt:   $data['exit_voucher_generated_at'] ?? null,
            createdBy:                isset($data['created_by']) ? (int) $data['created_by'] : null,
            createdAt:                $data['created_at'] ?? null,
            updatedAt:                $data['updated_at'] ?? null,
            deletedAt:                $data['deleted_at'] ?? null,
            proposedByName:           $data['proposed_by_name'] ?? null,
            decidedByName:            $data['decided_by_name'] ?? null,
            createdByName:            $data['created_by_name'] ?? null,
        );
    }

    // ============================================================
    // RÈGLES MÉTIER
    // ============================================================

    /**
     * Peut-on modifier les infos générales (titre, motif, etc.) ?
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'proposed', 'under_review'], true);
    }

    /**
     * Peut-on encore ajouter/retirer des équipements ?
     */
    public function canModifyItems(): bool
    {
        return in_array($this->status, ['draft', 'proposed', 'under_review'], true);
    }

    /**
     * Le workflow est-il terminé ?
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['completed', 'cancelled', 'rejected'], true);
    }

    // ============================================================
    // LIBELLÉS
    // ============================================================

    public function getReasonLabel(): string
    {
        return match ($this->reason) {
            'obsolescence' => 'Obsolescence',
            'breakdown'    => 'Panne irréparable',
            'wear'         => 'Usure',
            'end_of_life'  => 'Fin de vie',
            'other'        => 'Autre',
            default        => ucfirst($this->reason),
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft'                   => 'Brouillon',
            'proposed'                => 'Soumise',
            'under_review'            => 'En examen',
            'approved'                => 'Approuvée',
            'rejected'                => 'Rejetée',
            'pv_generated'            => 'PV généré',
            'exit_voucher_generated'  => 'Bon de sortie généré',
            'completed'               => 'Terminée',
            'cancelled'               => 'Annulée',
            default                   => ucfirst($this->status),
        };
    }

    public function getStatusBadge(): string
    {
        [$bg, $label] = match ($this->status) {
            'draft'                  => ['#6b7280', 'Brouillon'],
            'proposed'               => ['#f59e0b', 'Soumise'],
            'under_review'           => ['#3b82f6', 'En examen'],
            'approved'               => ['#22c55e', 'Approuvée'],
            'rejected'               => ['#ef4444', 'Rejetée'],
            'pv_generated'           => ['#8b5cf6', 'PV généré'],
            'exit_voucher_generated' => ['#06b6d4', 'Bon de sortie'],
            'completed'              => ['#10b981', 'Terminée'],
            'cancelled'              => ['#6c757d', 'Annulée'],
            default                  => ['#6c757d', ucfirst($this->status)],
        };

        return '<span class="badge" style="background-color:' . $bg . ';">' . $label . '</span>';
    }

    public function getReasonBadge(): string
    {
        $color = match ($this->reason) {
            'obsolescence' => '#6b7280',
            'breakdown'    => '#ef4444',
            'wear'         => '#f97316',
            'end_of_life'  => '#8b5cf6',
            'other'        => '#0ea5e9',
            default        => '#6c757d',
        };

        return '<span class="badge" style="background-color:' . $color . ';">' . $this->getReasonLabel() . '</span>';
    }

    public function getFormattedTotalValue(): string
    {
        return number_format($this->totalValue, 2, ',', ' ') . ' DA';
    }

    // ============================================================
    // TRANSITIONS WORKFLOW
    // ============================================================

    /**
     * @return array<string, string> ['action_code' => 'Libellé action']
     */
    public function getAvailableTransitions(): array
    {
        return match ($this->status) {
            'draft' => [
                'propose' => 'Soumettre pour examen',
                'cancel'  => 'Annuler',
            ],
            'proposed' => [
                'review' => 'Prendre en examen',
                'cancel' => 'Annuler',
            ],
            'under_review' => [
                'approve'  => 'Approuver',
                'reject'   => 'Rejeter',
                'postpone' => 'Reporter',
                'cancel'   => 'Annuler',
            ],
            'approved' => [
                'generate_pv' => 'Générer le PV',
                'cancel'      => 'Annuler',
            ],
            'pv_generated' => [
                'generate_exit_voucher' => 'Générer le bon de sortie',
            ],
            'exit_voucher_generated' => [
                'complete' => 'Terminer (réformer les équipements)',
            ],
            default => [],
        };
    }

    // ============================================================
    // LISTES STATIQUES (pour l'UI)
    // ============================================================

    public static function statusLabels(): array
    {
        return [
            'draft'                   => 'Brouillon',
            'proposed'                => 'Soumise',
            'under_review'            => 'En examen',
            'approved'                => 'Approuvée',
            'rejected'                => 'Rejetée',
            'pv_generated'            => 'PV généré',
            'exit_voucher_generated'  => 'Bon de sortie généré',
            'completed'               => 'Terminée',
            'cancelled'               => 'Annulée',
        ];
    }

    public static function reasonLabels(): array
    {
        return [
            'obsolescence' => 'Obsolescence',
            'breakdown'    => 'Panne irréparable',
            'wear'         => 'Usure',
            'end_of_life'  => 'Fin de vie',
            'other'        => 'Autre',
        ];
    }
}