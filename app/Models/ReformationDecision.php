<?php
declare(strict_types=1);

namespace App\Models;

final class ReformationDecision
{
    public function __construct(
        public readonly int $id,
        public readonly int $reformationId,
        public readonly string $decision,         // approved, rejected, postponed
        public readonly string $decisionDate,
        public readonly ?string $commissionMembers,
        public readonly ?string $notes,
        public readonly ?string $documentPath,
        public readonly ?int $createdBy,
        public readonly ?string $createdAt,
        // Champs joints
        public readonly ?string $createdByName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                 (int) $data['id'],
            reformationId:      (int) $data['reformation_id'],
            decision:           $data['decision'],
            decisionDate:       $data['decision_date'],
            commissionMembers:  $data['commission_members'] ?? null,
            notes:              $data['notes'] ?? null,
            documentPath:       $data['document_path'] ?? null,
            createdBy:          isset($data['created_by']) ? (int) $data['created_by'] : null,
            createdAt:          $data['created_at'] ?? null,
            createdByName:      $data['created_by_name'] ?? null,
        );
    }

    public function getDecisionLabel(): string
    {
        return match ($this->decision) {
            'approved'  => 'Approuvée',
            'rejected'  => 'Rejetée',
            'postponed' => 'Reportée',
            default     => ucfirst($this->decision),
        };
    }

    public function getDecisionBadge(): string
    {
        [$bg, $label] = match ($this->decision) {
            'approved'  => ['#22c55e', 'Approuvée'],
            'rejected'  => ['#ef4444', 'Rejetée'],
            'postponed' => ['#f59e0b', 'Reportée'],
            default     => ['#6c757d', ucfirst($this->decision)],
        };

        return '<span class="badge" style="background-color:' . $bg . ';">' . $label . '</span>';
    }

    /**
     * Liste des membres de la commission (tableau)
     *
     * @return string[]
     */
    public function getCommissionMembersList(): array
    {
        if (empty($this->commissionMembers)) {
            return [];
        }

        return array_map('trim', explode(',', $this->commissionMembers));
    }
}