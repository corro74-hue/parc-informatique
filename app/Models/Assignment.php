<?php
declare(strict_types=1);

namespace App\Models;

final class Assignment
{
    public function __construct(
        public readonly int $id,
        public readonly int $equipmentId,
        public readonly ?int $siteId,
        public readonly ?int $serviceId,
        public readonly ?int $locationId,
        public readonly ?int $employeeId,
        public readonly string $startDate,
        public readonly ?string $endDate,
        public readonly ?string $reason,
        public readonly ?string $documentPath,
        public readonly ?int $createdBy,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        // Champs joints (JOIN avec tables de référence)
        public readonly ?string $equipmentInventoryNumber = null,
        public readonly ?string $equipmentDesignation = null,
        public readonly ?string $equipmentCategoryName = null,
        public readonly ?string $employeeFirstName = null,
        public readonly ?string $employeeLastName = null,
        public readonly ?string $employeeMatricule = null,
        public readonly ?string $employeeFunctionTitle = null,
        public readonly ?string $siteName = null,
        public readonly ?string $serviceName = null,
        public readonly ?string $locationName = null,
        public readonly ?string $createdByName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                         (int) $data['id'],
            equipmentId:                (int) $data['equipment_id'],
            siteId:                     isset($data['site_id']) ? (int) $data['site_id'] : null,
            serviceId:                  isset($data['service_id']) ? (int) $data['service_id'] : null,
            locationId:                 isset($data['location_id']) ? (int) $data['location_id'] : null,
            employeeId:                 isset($data['employee_id']) ? (int) $data['employee_id'] : null,
            startDate:                  $data['start_date'],
            endDate:                    $data['end_date'] ?? null,
            reason:                     $data['reason'] ?? null,
            documentPath:               $data['document_path'] ?? null,
            createdBy:                  isset($data['created_by']) ? (int) $data['created_by'] : null,
            createdAt:                  $data['created_at'] ?? null,
            updatedAt:                  $data['updated_at'] ?? null,
            equipmentInventoryNumber:   $data['equipment_inventory_number'] ?? null,
            equipmentDesignation:       $data['equipment_designation'] ?? null,
            equipmentCategoryName:      $data['equipment_category_name'] ?? null,
            employeeFirstName:          $data['employee_first_name'] ?? null,
            employeeLastName:           $data['employee_last_name'] ?? null,
            employeeMatricule:          $data['employee_matricule'] ?? null,
            employeeFunctionTitle:      $data['employee_function_title'] ?? null,
            siteName:                   $data['site_name'] ?? null,
            serviceName:                $data['service_name'] ?? null,
            locationName:               $data['location_name'] ?? null,
            createdByName:              $data['created_by_name'] ?? null,
        );
    }

    /**
     * L'affectation est-elle active (pas encore retournée) ?
     */
    public function isActive(): bool
    {
        return $this->endDate === null;
    }

    /**
     * Nom complet de l'employé affecté.
     */
    public function getEmployeeFullName(): string
    {
        if ($this->employeeFirstName === null && $this->employeeLastName === null) {
            return 'Non attribué';
        }
        return trim(($this->employeeFirstName ?? '') . ' ' . ($this->employeeLastName ?? ''));
    }

    /**
     * Durée en jours depuis le début de l'affectation.
     */
    public function getDurationInDays(): int
    {
        $start = new \DateTime($this->startDate);
        $end = $this->endDate ? new \DateTime($this->endDate) : new \DateTime();
        return (int) $start->diff($end)->days;
    }

    /**
     * Badge HTML pour l'état (Actif / Retourné).
     */
    public function getStatusBadge(): string
    {
        if ($this->isActive()) {
            return '<span class="badge" style="background-color:#3b82f6">Actif</span>';
        }
        return '<span class="badge" style="background-color:#6c757d">Retourné</span>';
    }
}