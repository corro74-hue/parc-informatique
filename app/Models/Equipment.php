<?php
declare(strict_types=1);

namespace App\Models;

final class Equipment
{
    public function __construct(
        public readonly int $id,
        public readonly string $inventoryNumber,
        public readonly int $categoryId,
        public readonly string $designation,
        public readonly ?int $brandId,
        public readonly ?int $modelId,
        public readonly ?string $modelText,
        public readonly ?string $serialNumber,
        public readonly ?int $siteId,
        public readonly ?int $serviceId,
        public readonly ?int $locationId,
        public readonly ?int $responsibleId,
        public readonly ?int $supplierId,
        public readonly int $statusId,
        public readonly ?string $acquisitionDate,
        public readonly ?string $commissioningDate,
        public readonly float $acquisitionValue,
        public readonly float $residualValue,
        public readonly ?int $amortizationYears,
        public readonly ?string $invoiceNumber,
        public readonly ?string $warrantyEndDate,
        public readonly ?string $qrCodePath,
        public readonly ?string $barcode,
        public readonly ?string $technicalSpecs,
        public readonly ?string $notes,
        public readonly ?string $photoPath,
        public readonly ?int $createdBy,
        public readonly ?int $updatedBy,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $deletedAt,
        // Champs joints (issus de JOIN avec les tables de référence)
        public readonly ?string $categoryName = null,
        public readonly ?string $categoryCode = null,
        public readonly ?string $brandName = null,
        public readonly ?string $statusName = null,
        public readonly ?string $statusCode = null,
        public readonly ?string $statusColor = null,
        public readonly ?string $serviceName = null,
        public readonly ?string $siteName = null,
        public readonly ?string $locationName = null,
        public readonly ?string $responsibleName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                 (int) $data['id'],
            inventoryNumber:    $data['inventory_number'],
            categoryId:         (int) $data['category_id'],
            designation:        $data['designation'],
            brandId:            isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            modelId:            isset($data['model_id']) ? (int) $data['model_id'] : null,
            modelText:          $data['model_text'] ?? null,
            serialNumber:       $data['serial_number'] ?? null,
            siteId:             isset($data['site_id']) ? (int) $data['site_id'] : null,
            serviceId:          isset($data['service_id']) ? (int) $data['service_id'] : null,
            locationId:         isset($data['location_id']) ? (int) $data['location_id'] : null,
            responsibleId:      isset($data['responsible_id']) ? (int) $data['responsible_id'] : null,
            supplierId:         isset($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            statusId:           (int) $data['status_id'],
            acquisitionDate:    $data['acquisition_date'] ?? null,
            commissioningDate:  $data['commissioning_date'] ?? null,
            acquisitionValue:   (float) ($data['acquisition_value'] ?? 0),
            residualValue:      (float) ($data['residual_value'] ?? 0),
            amortizationYears:  isset($data['amortization_years']) ? (int) $data['amortization_years'] : null,
            invoiceNumber:      $data['invoice_number'] ?? null,
            warrantyEndDate:    $data['warranty_end_date'] ?? null,
            qrCodePath:         $data['qr_code_path'] ?? null,
            barcode:            $data['barcode'] ?? null,
            technicalSpecs:     $data['technical_specs'] ?? null,
            notes:              $data['notes'] ?? null,
            photoPath:          $data['photo_path'] ?? null,
            createdBy:          isset($data['created_by']) ? (int) $data['created_by'] : null,
            updatedBy:          isset($data['updated_by']) ? (int) $data['updated_by'] : null,
            createdAt:          $data['created_at'],
            updatedAt:          $data['updated_at'] ?? null,
            deletedAt:          $data['deleted_at'] ?? null,
            categoryName:       $data['category_name'] ?? null,
            categoryCode:       $data['category_code'] ?? null,
            brandName:          $data['brand_name'] ?? null,
            statusName:         $data['status_name'] ?? null,
            statusCode:         $data['status_code'] ?? null,
            statusColor:        $data['status_color'] ?? null,
            serviceName:        $data['service_name'] ?? null,
            siteName:           $data['site_name'] ?? null,
            locationName:       $data['location_name'] ?? null,
            responsibleName:    $data['responsible_name'] ?? null,
        );
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function isUnderWarranty(): bool
    {
        if ($this->warrantyEndDate === null) {
            return false;
        }
        return strtotime($this->warrantyEndDate) > time();
    }

    /**
     * Calcule la valeur nette comptable (amortissement linéaire)
     */
    public function getBookValue(): float
    {
        if ($this->acquisitionDate === null || $this->amortizationYears === null || $this->amortizationYears <= 0) {
            return $this->acquisitionValue;
        }

        $start = new \DateTime($this->acquisitionDate);
        $now   = new \DateTime();
        $years = $start->diff($now)->y + ($start->diff($now)->m / 12);

        if ($years >= $this->amortizationYears) {
            return 0.0;
        }

        $annualDepreciation = $this->acquisitionValue / $this->amortizationYears;
        $accumulated = $annualDepreciation * $years;
        $bookValue = $this->acquisitionValue - $accumulated;

        return max(0.0, round($bookValue, 2));
    }

    public function getFormattedValue(): string
    {
        return number_format($this->acquisitionValue, 2, ',', ' ') . ' DA';
    }

    public function getFormattedBookValue(): string
    {
        return number_format($this->getBookValue(), 2, ',', ' ') . ' DA';
    }

    public function getStatusBadge(): string
    {
        $color = $this->statusColor ?? '#6c757d';
        $label = $this->statusName ?? 'Inconnu';
        return '<span class="badge" style="background-color:' . htmlspecialchars($color) . '">' . htmlspecialchars($label) . '</span>';
    }
}