<?php
declare(strict_types=1);

namespace App\Models;

final class Maintenance
{
    public function __construct(
        public readonly int $id,
        public readonly int $equipmentId,
        public readonly string $type,              // preventive, corrective, curative, upgrade
        public readonly string $status,            // open, in_progress, waiting_parts, completed, cancelled
        public readonly string $reportedAt,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
        public readonly ?int $reportedBy,
        public readonly ?string $technician,
        public readonly ?int $supplierId,
        public readonly string $problemDescription,
        public readonly ?string $diagnosis,
        public readonly ?string $workDone,
        public readonly ?string $result,           // fixed, unfixed, replaced, pending
        public readonly float $cost,
        public readonly int $downtimeHours,
        public readonly ?string $invoiceNumber,
        public readonly ?string $notes,
        public readonly ?int $createdBy,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        // Champs joints
        public readonly ?string $equipmentInventoryNumber = null,
        public readonly ?string $equipmentDesignation = null,
        public readonly ?string $equipmentCategoryName = null,
        public readonly ?string $reportedByName = null,
        public readonly ?string $createdByName = null,
        public readonly ?string $supplierName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                       (int) $data['id'],
            equipmentId:              (int) $data['equipment_id'],
            type:                     $data['type'],
            status:                   $data['status'],
            reportedAt:               $data['reported_at'],
            startedAt:                $data['started_at'] ?? null,
            completedAt:              $data['completed_at'] ?? null,
            reportedBy:               isset($data['reported_by']) ? (int) $data['reported_by'] : null,
            technician:               $data['technician'] ?? null,
            supplierId:               isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            problemDescription:       $data['problem_description'],
            diagnosis:                $data['diagnosis'] ?? null,
            workDone:                 $data['work_done'] ?? null,
            result:                   $data['result'] ?? null,
            cost:                     (float) ($data['cost'] ?? 0),
            downtimeHours:            (int) ($data['downtime_hours'] ?? 0),
            invoiceNumber:            $data['invoice_number'] ?? null,
            notes:                    $data['notes'] ?? null,
            createdBy:                isset($data['created_by']) ? (int) $data['created_by'] : null,
            createdAt:                $data['created_at'] ?? null,
            updatedAt:                $data['updated_at'] ?? null,
            equipmentInventoryNumber: $data['equipment_inventory_number'] ?? null,
            equipmentDesignation:     $data['equipment_designation'] ?? null,
            equipmentCategoryName:    $data['equipment_category_name'] ?? null,
            reportedByName:           $data['reported_by_name'] ?? null,
            createdByName:            $data['created_by_name'] ?? null,
            supplierName:             $data['supplier_name'] ?? null,
        );
    }

    /**
     * Le ticket est-il en cours (non clôturé) ?
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['open', 'in_progress', 'waiting_parts'], true);
    }

    /**
     * Le ticket est-il clôturé ?
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['completed', 'cancelled'], true);
    }

    /**
     * Libellé du type lisible
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'preventive' => 'Préventive',
            'corrective' => 'Corrective',
            'curative'   => 'Curative',
            'upgrade'    => 'Mise à niveau',
            default      => ucfirst($this->type),
        };
    }

    /**
     * Libellé du statut lisible
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'open'          => 'Ouvert',
            'in_progress'   => 'En cours',
            'waiting_parts' => 'En attente de pièces',
            'completed'     => 'Terminé',
            'cancelled'     => 'Annulé',
            default         => ucfirst($this->status),
        };
    }

    /**
     * Badge HTML pour le statut
     */
    public function getStatusBadge(): string
    {
        [$bg, $label] = match ($this->status) {
            'open'          => ['#f59e0b', 'Ouvert'],
            'in_progress'   => ['#3b82f6', 'En cours'],
            'waiting_parts' => ['#8b5cf6', 'En attente'],
            'completed'     => ['#22c55e', 'Terminé'],
            'cancelled'     => ['#6b7280', 'Annulé'],
            default         => ['#6c757d', ucfirst($this->status)],
        };

        return '<span class="badge" style="background-color:' . $bg . ';">' . $label . '</span>';
    }

    /**
     * Badge HTML pour le type
     */
    public function getTypeBadge(): string
    {
        $color = match ($this->type) {
            'preventive' => '#06b6d4',
            'corrective' => '#ef4444',
            'curative'   => '#f97316',
            'upgrade'    => '#8b5cf6',
            default      => '#6c757d',
        };

        return '<span class="badge" style="background-color:' . $color . ';">' . $this->getTypeLabel() . '</span>';
    }

    /**
     * Badge pour le résultat (si terminé)
     */
    public function getResultBadge(): string
    {
        if ($this->result === null) {
            return '<span class="text-muted">—</span>';
        }

        return match ($this->result) {
            'fixed'    => '<span class="badge bg-success">Réparé</span>',
            'unfixed'  => '<span class="badge bg-danger">Non réparé</span>',
            'replaced' => '<span class="badge bg-info text-dark">Remplacé</span>',
            'pending'  => '<span class="badge bg-warning text-dark">En attente</span>',
            default    => '<span class="text-muted">—</span>',
        };
    }

    /**
     * Coût formaté
     */
    public function getFormattedCost(): string
    {
        return number_format($this->cost, 2, ',', ' ') . ' DA';
    }

    /**
     * Durée en jours depuis la déclaration
     */
    public function getDurationInDays(): int
    {
        $start = new \DateTime($this->reportedAt);
        $end = $this->completedAt ? new \DateTime($this->completedAt) : new \DateTime();

        return (int) $start->diff($end)->days;
    }
}