<?php
declare(strict_types=1);

namespace App\Models;

final class Employee
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $serviceId,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $matricule,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $functionTitle,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        // Champs joints
        public readonly ?string $serviceName = null,
        public readonly ?string $serviceCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:            (int) $data['id'],
            serviceId:     isset($data['service_id']) ? (int) $data['service_id'] : null,
            firstName:     $data['first_name'],
            lastName:      $data['last_name'],
            matricule:     $data['matricule'] ?? null,
            email:         $data['email'] ?? null,
            phone:         $data['phone'] ?? null,
            functionTitle: $data['function_title'] ?? null,
            isActive:      (bool) ($data['is_active'] ?? true),
            createdAt:     $data['created_at'] ?? null,
            updatedAt:     $data['updated_at'] ?? null,
            serviceName:   $data['service_name'] ?? null,
            serviceCode:   $data['service_code'] ?? null,
        );
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    /**
     * Affichage complet : "NOM Prénom (MATRICULE)"
     */
    public function getDisplayName(): string
    {
        $name = strtoupper($this->lastName) . ' ' . $this->firstName;
        return $this->matricule ? $name . ' (' . $this->matricule . ')' : $name;
    }
}