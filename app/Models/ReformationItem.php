<?php
declare(strict_types=1);

namespace App\Models;

final class ReformationItem
{
    public function __construct(
        public readonly int $id,
        public readonly int $reformationId,
        public readonly int $equipmentId,
        public readonly int $quantity,
        public readonly float $estimatedValue,
        public readonly ?string $conditionNotes,
        public readonly ?string $photos,
        public readonly ?string $createdAt,
        // Champs joints équipement
        public readonly ?string $equipmentInventoryNumber = null,
        public readonly ?string $equipmentDesignation = null,
        public readonly ?string $equipmentCategoryName = null,
        public readonly ?string $equipmentStatusName = null,
        public readonly ?string $equipmentStatusColor = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                        (int) $data['id'],
            reformationId:             (int) $data['reformation_id'],
            equipmentId:               (int) $data['equipment_id'],
            quantity:                  (int) ($data['quantity'] ?? 1),
            estimatedValue:            (float) ($data['estimated_value'] ?? 0),
            conditionNotes:            $data['condition_notes'] ?? null,
            photos:                    $data['photos'] ?? null,
            createdAt:                 $data['created_at'] ?? null,
            equipmentInventoryNumber:  $data['equipment_inventory_number'] ?? null,
            equipmentDesignation:      $data['equipment_designation'] ?? null,
            equipmentCategoryName:     $data['equipment_category_name'] ?? null,
            equipmentStatusName:       $data['equipment_status_name'] ?? null,
            equipmentStatusColor:      $data['equipment_status_color'] ?? null,
        );
    }

    public function getFormattedEstimatedValue(): string
    {
        return number_format($this->estimatedValue, 2, ',', ' ') . ' DA';
    }

    public function getStatusBadge(): string
    {
        $color = $this->equipmentStatusColor ?? '#6c757d';
        $label = $this->equipmentStatusName ?? '—';

        return '<span class="badge" style="background-color:' . $color . ';">' . $label . '</span>';
    }
}