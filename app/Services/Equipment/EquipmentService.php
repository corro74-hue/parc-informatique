<?php
declare(strict_types=1);

namespace App\Services\Equipment;

use App\Exceptions\ValidationException;
use App\Models\Equipment;
use App\Repositories\Contracts\EquipmentRepositoryInterface;
use App\Repositories\MySql\EquipmentRepository;

final class EquipmentService
{
    private EquipmentRepositoryInterface $repo;

    public function __construct(?EquipmentRepositoryInterface $repo = null)
    {
        $this->repo = $repo ?? new EquipmentRepository();
    }

    /**
     * Liste paginée avec filtres
     */
    public function getPaginatedList(array $filters, int $page = 1, int $perPage = 15, string $sortBy = 'created_at', string $sortDir = 'DESC'): array
    {
        // Nettoyer les filtres (ignorer les valeurs vides)
        $cleanFilters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        return $this->repo->paginate($cleanFilters, $page, $perPage, $sortBy, $sortDir);
    }

    /**
     * Récupère un équipement par son ID
     */
    public function find(int $id): ?Equipment
    {
        return $this->repo->findById($id);
    }

    /**
     * Valide les données d'un équipement
     *
     * @throws ValidationException si des champs sont invalides
     */
    public function validate(array $data, ?int $excludeId = null): void
    {
        $errors = [];

        // Désignation obligatoire
        if (empty(trim($data['designation'] ?? ''))) {
            $errors['designation'] = 'La désignation est obligatoire.';
        } elseif (mb_strlen($data['designation']) > 180) {
            $errors['designation'] = 'La désignation ne peut pas dépasser 180 caractères.';
        }

        // Catégorie obligatoire
        if (empty($data['category_id'])) {
            $errors['category_id'] = 'La catégorie est obligatoire.';
        }

        // Statut obligatoire
        if (empty($data['status_id'])) {
            $errors['status_id'] = 'Le statut est obligatoire.';
        }

        // Numéro de série unique (si renseigné)
        if (!empty($data['serial_number'])) {
            $existing = $this->repo->findBySerialNumber($data['serial_number']);
            if ($existing && ($excludeId === null || $existing->id !== $excludeId)) {
                $errors['serial_number'] = 'Ce numéro de série existe déjà pour un autre équipement.';
            }
        }

        // Valeur d'acquisition
        if (isset($data['acquisition_value']) && $data['acquisition_value'] !== '' && $data['acquisition_value'] !== null) {
            if (!is_numeric($data['acquisition_value']) || (float) $data['acquisition_value'] < 0) {
                $errors['acquisition_value'] = 'La valeur d\'acquisition doit être un nombre positif.';
            }
        }

        // Date d'acquisition
        if (!empty($data['acquisition_date']) && !$this->isValidDate($data['acquisition_date'])) {
            $errors['acquisition_date'] = 'La date d\'acquisition est invalide.';
        }

        // Année d'amortissement
        if (!empty($data['amortization_years'])) {
            $years = (int) $data['amortization_years'];
            if ($years < 1 || $years > 50) {
                $errors['amortization_years'] = 'L\'amortissement doit être entre 1 et 50 ans.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Données invalides', $errors);
        }
    }

    /**
     * Crée un nouvel équipement
     *
     * @throws ValidationException
     */
    public function create(array $data, int $userId): Equipment
    {
        // Validation
        $this->validate($data);

        // Générer le numéro d'inventaire automatiquement
        $data['inventory_number'] = $this->repo->generateNextInventoryNumber();
        $data['created_by'] = $userId;

        // Valeurs par défaut
        $data['residual_value'] = $data['residual_value'] ?? $data['acquisition_value'] ?? 0;

        // Insertion
        $id = $this->repo->create($data);

        $equipment = $this->repo->findById($id);
        if (!$equipment) {
            throw new \RuntimeException("Erreur lors de la création de l'équipement.");
        }

        return $equipment;
    }

    /**
     * Met à jour un équipement
     *
     * @throws ValidationException
     */
    public function update(int $id, array $data, int $userId): Equipment
    {
        $equipment = $this->repo->findById($id);
        if (!$equipment) {
            throw new \RuntimeException("Équipement introuvable.");
        }

        // Validation (en excluant l'ID actuel pour l'unicité du serial)
        $this->validate($data, $id);

        $data['updated_by'] = $userId;

        $this->repo->update($id, $data);

        $updated = $this->repo->findById($id);
        if (!$updated) {
            throw new \RuntimeException("Erreur lors de la mise à jour.");
        }

        return $updated;
    }

    /**
     * Supprime logiquement un équipement
     */
    public function delete(int $id, int $userId): bool
    {
        return $this->repo->softDelete($id, $userId);
    }

    /**
     * Statistiques pour le dashboard
     */
    public function getStats(): array
    {
        return [
            'by_status'   => $this->repo->countByStatus(),
            'by_category' => $this->repo->countByCategory(),
            'total_value' => $this->repo->getTotalValue(),
        ];
    }

    /**
     * Derniers équipements ajoutés
     */
    public function getRecent(int $limit = 5): array
    {
        return $this->repo->getRecent($limit);
    }

    /**
     * Vérifie si une chaîne est une date valide (format Y-m-d)
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}