<?php
declare(strict_types=1);

namespace App\Models;

final class DocumentVersion
{
    public ?int $id = null;
    public int $documentId = 0;
    public int $version = 1;
    public string $filePath = '';
    public ?string $changeNotes = null;
    public ?int $createdBy = null;
    public ?string $createdAt = null;

    // Champs joints
    public ?string $createdByName = null;

    public static function fromArray(array $data): self
    {
        $v = new self();
        $v->id            = isset($data['id']) ? (int) $data['id'] : null;
        $v->documentId    = (int) ($data['document_id'] ?? 0);
        $v->version       = (int) ($data['version'] ?? 1);
        $v->filePath      = (string) ($data['file_path'] ?? '');
        $v->changeNotes   = $data['change_notes'] ?? null;
        $v->createdBy     = isset($data['created_by']) ? (int) $data['created_by'] : null;
        $v->createdAt     = $data['created_at'] ?? null;
        $v->createdByName = $data['created_by_name'] ?? null;

        return $v;
    }

    /**
     * Libellé "v1", "v2", etc.
     */
    public function getVersionLabel(): string
    {
        return 'v' . $this->version;
    }

    /**
     * Date relative ("il y a 2 heures", "hier", "le 01/10/2026")
     */
    public function getRelativeDate(): string
    {
        if ($this->createdAt === null) {
            return '—';
        }

        $timestamp = strtotime($this->createdAt);
        if ($timestamp === false) {
            return '—';
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'à l\'instant';
        }
        if ($diff < 3600) {
            return 'il y a ' . floor($diff / 60) . ' min';
        }
        if ($diff < 86400) {
            return 'il y a ' . floor($diff / 3600) . ' h';
        }
        if ($diff < 172800) {
            return 'hier';
        }
        return 'le ' . date('d/m/Y', $timestamp);
    }

    /**
     * Nom du fichier extrait du chemin
     */
    public function getFileName(): string
    {
        return basename($this->filePath);
    }
}