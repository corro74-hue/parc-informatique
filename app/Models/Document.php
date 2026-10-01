<?php
declare(strict_types=1);

namespace App\Models;

final class Document
{
    // ============================================================
    // TYPES DE DOCUMENTS (enum `type` en BDD)
    // ============================================================
    public const TYPE_PRV_REFORM    = 'prv_reform';
    public const TYPE_EXIT_VOUCHER  = 'exit_voucher';
    public const TYPE_INVOICE       = 'invoice';
    public const TYPE_REPORT        = 'report';
    public const TYPE_OTHER         = 'other';

    // ============================================================
    // TYPES D'ENTITÉS LIÉES (polymorphe)
    // ============================================================
    public const ENTITY_EQUIPMENT   = 'equipment';
    public const ENTITY_MAINTENANCE = 'maintenance';
    public const ENTITY_REFORMATION = 'reformation';
    public const ENTITY_EMPLOYEE    = 'employee';
    public const ENTITY_ASSIGNMENT  = 'assignment';
    public const ENTITY_CAMPAIGN    = 'inventory_campaign';
    public const ENTITY_USER        = 'user';

    // ============================================================
    // PROPRIÉTÉS
    // ============================================================
    public ?int $id = null;
    public ?string $reference = null;
    public string $type = self::TYPE_OTHER;
    public string $title = '';
    public ?string $entityType = null;
    public ?int $entityId = null;
    public int $currentVersion = 1;
    public string $filePath = '';
    public ?string $mimeType = null;
    public ?int $sizeBytes = null;
    public bool $isSigned = false;
    public ?int $createdBy = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    // Champs joints
    public ?string $createdByName = null;
    public ?int $versionsCount = null;

    // ============================================================
    // FACTORY
    // ============================================================
    public static function fromArray(array $data): self
    {
        $d = new self();
        $d->id             = isset($data['id']) ? (int) $data['id'] : null;
        $d->reference      = $data['reference'] ?? null;
        $d->type           = (string) ($data['type'] ?? self::TYPE_OTHER);
        $d->title          = (string) ($data['title'] ?? '');
        $d->entityType     = $data['entity_type'] ?? null;
        $d->entityId       = isset($data['entity_id']) ? (int) $data['entity_id'] : null;
        $d->currentVersion = (int) ($data['current_version'] ?? 1);
        $d->filePath       = (string) ($data['file_path'] ?? '');
        $d->mimeType       = $data['mime_type'] ?? null;
        $d->sizeBytes      = isset($data['size_bytes']) ? (int) $data['size_bytes'] : null;
        $d->isSigned       = (bool) ($data['is_signed'] ?? false);
        $d->createdBy      = isset($data['created_by']) ? (int) $data['created_by'] : null;
        $d->createdAt      = $data['created_at'] ?? null;
        $d->updatedAt      = $data['updated_at'] ?? null;
        $d->createdByName  = $data['created_by_name'] ?? null;
        $d->versionsCount  = isset($data['versions_count']) ? (int) $data['versions_count'] : null;

        return $d;
    }

    // ============================================================
    // LIBELLÉS & BADGES
    // ============================================================

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_PRV_REFORM   => 'PV de réforme',
            self::TYPE_EXIT_VOUCHER => 'Bon de sortie',
            self::TYPE_INVOICE      => 'Facture',
            self::TYPE_REPORT       => 'Rapport',
            self::TYPE_OTHER        => 'Autre',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function entityTypeLabels(): array
    {
        return [
            self::ENTITY_EQUIPMENT   => 'Équipement',
            self::ENTITY_MAINTENANCE => 'Maintenance',
            self::ENTITY_REFORMATION => 'Réforme',
            self::ENTITY_EMPLOYEE    => 'Employé',
            self::ENTITY_ASSIGNMENT  => 'Affectation',
            self::ENTITY_CAMPAIGN    => 'Campagne d\'inventaire',
            self::ENTITY_USER        => 'Utilisateur',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst($this->type);
    }

    public function getEntityTypeLabel(): string
    {
        if ($this->entityType === null) {
            return '—';
        }
        return self::entityTypeLabels()[$this->entityType] ?? ucfirst($this->entityType);
    }

    public function getTypeBadge(): string
    {
        [$bg, $label] = match ($this->type) {
            self::TYPE_PRV_REFORM   => ['#8b5cf6', 'PV'],
            self::TYPE_EXIT_VOUCHER => ['#06b6d4', 'Bon sortie'],
            self::TYPE_INVOICE      => ['#f59e0b', 'Facture'],
            self::TYPE_REPORT       => ['#3b82f6', 'Rapport'],
            default                 => ['#6b7280', 'Autre'],
        };

        return '<span class="badge" style="background-color:' . $bg . ';">' . $label . '</span>';
    }

    // ============================================================
    // FORMATAGE
    // ============================================================

    /**
     * Taille formatée (ex: "2.5 Mo", "150 Ko", "1.2 Go")
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->sizeBytes ?? 0;
        if ($bytes < 1024) {
            return $bytes . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', ' ') . ' Ko';
        }
        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 2, ',', ' ') . ' Mo';
        }
        return number_format($bytes / (1024 * 1024 * 1024), 2, ',', ' ') . ' Go';
    }

    /**
     * Nom de fichier extrait du chemin
     */
    public function getFileName(): string
    {
        return basename($this->filePath);
    }

    /**
     * Extension du fichier
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));
    }

    // ============================================================
    // ICÔNES & TYPE MIME
    // ============================================================

    /**
     * Icône Bootstrap selon le type MIME
     */
    public function getIcon(): string
    {
        $mime = strtolower($this->mimeType ?? '');
        $ext  = $this->getExtension();

        if (str_contains($mime, 'pdf') || $ext === 'pdf') {
            return 'bi-file-earmark-pdf';
        }
        if (str_contains($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return 'bi-file-earmark-image';
        }
        if (str_contains($mime, 'word') || in_array($ext, ['doc', 'docx'], true)) {
            return 'bi-file-earmark-word';
        }
        if (str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet') || in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
            return 'bi-file-earmark-excel';
        }
        if (str_contains($mime, 'zip') || str_contains($mime, 'compressed') || in_array($ext, ['zip', 'rar', '7z'], true)) {
            return 'bi-file-earmark-zip';
        }
        if (str_contains($mime, 'text/') || $ext === 'txt') {
            return 'bi-file-earmark-text';
        }
        return 'bi-file-earmark';
    }

    public function isPdf(): bool
    {
        return str_contains(strtolower($this->mimeType ?? ''), 'pdf')
            || $this->getExtension() === 'pdf';
    }

    public function isImage(): bool
    {
        $mime = strtolower($this->mimeType ?? '');
        return str_contains($mime, 'image/')
            || in_array($this->getExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function isOffice(): bool
    {
        $mime = strtolower($this->mimeType ?? '');
        return str_contains($mime, 'word')
            || str_contains($mime, 'excel')
            || str_contains($mime, 'spreadsheet')
            || str_contains($mime, 'presentation')
            || in_array($this->getExtension(), ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'], true);
    }

    public function isArchive(): bool
    {
        $mime = strtolower($this->mimeType ?? '');
        return str_contains($mime, 'zip')
            || str_contains($mime, 'compressed')
            || in_array($this->getExtension(), ['zip', 'rar', '7z', 'tar', 'gz'], true);
    }

    /**
     * Peut-on prévisualiser dans le navigateur ?
     */
    public function canPreview(): bool
    {
        return $this->isPdf() || $this->isImage();
    }

    // ============================================================
    // HELPERS MÉTIER
    // ============================================================

    public function isAttached(): bool
    {
        return $this->entityType !== null && $this->entityId !== null;
    }

    public function hasMultipleVersions(): bool
    {
        return $this->currentVersion > 1;
    }

    /**
     * URL vers l'entité liée (si elle existe)
     */
    public function getEntityUrl(): ?string
    {
        if (!$this->isAttached()) {
            return null;
        }

        $base = match ($this->entityType) {
            self::ENTITY_EQUIPMENT   => 'equipment/',
            self::ENTITY_MAINTENANCE => 'maintenance/',
            self::ENTITY_REFORMATION => 'reformations/',
            self::ENTITY_EMPLOYEE    => 'employees/',
            self::ENTITY_ASSIGNMENT  => 'assignments/',
            self::ENTITY_CAMPAIGN    => 'campaigns/',
            self::ENTITY_USER        => 'users/',
            default                  => null,
        };

        return $base === null ? null : $base . $this->entityId;
    }
}