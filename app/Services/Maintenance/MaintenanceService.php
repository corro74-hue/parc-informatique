<?php
declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Exceptions\ValidationException;
use App\Models\Maintenance;
use App\Repositories\MySql\EquipmentRepository;
use App\Repositories\MySql\MaintenanceRepository;
use App\Services\Audit\AuditService;

final class MaintenanceService
{
    private MaintenanceRepository $maintenances;
    private EquipmentRepository $equipment;
    private AuditService $audit;

    /**
     * IDs des statuts d'équipement.
     */
    private const STATUS_IN_MAINTENANCE = 3;  // "En maintenance"
    private const STATUS_IN_STOCK       = 2;  // "En stock"

    /**
     * Statuts de maintenance considérés comme ACTIFS.
     */
    private const ACTIVE_STATUSES = ['open', 'in_progress', 'waiting_parts'];

    public function __construct()
    {
        $this->maintenances = new MaintenanceRepository();
        $this->equipment    = new EquipmentRepository();
        $this->audit        = new AuditService();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function find(int $id): ?Maintenance
    {
        return $this->maintenances->findById($id);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->maintenances->paginate($filters, $page, $perPage);
    }

    public function historyForEquipment(int $equipmentId): array
    {
        return $this->maintenances->findByEquipment($equipmentId);
    }

    public function findActiveForEquipment(int $equipmentId): ?Maintenance
    {
        return $this->maintenances->findActiveByEquipment($equipmentId);
    }

    public function recent(int $limit = 5): array
    {
        return $this->maintenances->findRecent($limit);
    }

    // ============================================
    // CRÉATION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function create(array $data, int $userId): int
    {
        $this->validate($data);

        $equipmentId = (int) $data['equipment_id'];

        // Vérifier l'équipement
        $equipment = $this->equipment->findById($equipmentId);
        if ($equipment === null) {
            throw new ValidationException('Équipement introuvable.');
        }

        // Vérifier qu'il n'y a pas déjà un ticket actif
        $existing = $this->maintenances->findActiveByEquipment($equipmentId);
        if ($existing !== null) {
            throw new ValidationException(
                'Cet équipement a déjà une maintenance en cours (ticket #' . $existing->id . ').'
            );
        }

        $payload = [
            'equipment_id'        => $equipmentId,
            'type'                => $data['type'],
            'status'              => $data['status'] ?? 'open',
            'reported_at'         => $data['reported_at'] ?? date('Y-m-d H:i:s'),
            'started_at'          => !empty($data['started_at']) ? $data['started_at'] : null,
            'completed_at'        => !empty($data['completed_at']) ? $data['completed_at'] : null,
            'reported_by'         => !empty($data['reported_by']) ? (int) $data['reported_by'] : $userId,
            'technician'          => !empty($data['technician']) ? trim((string) $data['technician']) : null,
            'supplier_id'         => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'problem_description' => trim((string) $data['problem_description']),
            'diagnosis'           => !empty($data['diagnosis']) ? trim((string) $data['diagnosis']) : null,
            'work_done'           => !empty($data['work_done']) ? trim((string) $data['work_done']) : null,
            'result'              => !empty($data['result']) ? $data['result'] : null,
            'cost'                => (float) ($data['cost'] ?? 0),
            'downtime_hours'      => (int) ($data['downtime_hours'] ?? 0),
            'invoice_number'      => !empty($data['invoice_number']) ? trim((string) $data['invoice_number']) : null,
            'notes'               => !empty($data['notes']) ? trim((string) $data['notes']) : null,
            'created_by'          => $userId,
        ];

        $maintenanceId = $this->maintenances->create($payload);

        // Mettre l'équipement en "En maintenance"
        $this->equipment->updateStatus($equipmentId, self::STATUS_IN_MAINTENANCE, $userId);

        // Audit
        $this->audit->log(
            action:     'create',
            entityType: 'maintenance',
            entityId:   $maintenanceId,
            oldValues:  null,
            newValues:  [
                'equipment_id' => $equipmentId,
                'type'         => $payload['type'],
                'status'       => $payload['status'],
            ],
            severity:   'info'
        );

        return $maintenanceId;
    }

    // ============================================
    // MODIFICATION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $maintenance = $this->maintenances->findById($id);
        if ($maintenance === null) {
            throw new ValidationException('Maintenance introuvable.');
        }

        if ($maintenance->isClosed()) {
            throw new ValidationException(
                'Cette maintenance est déjà clôturée et ne peut plus être modifiée.'
            );
        }

        $this->validate($data, false);

        $payload = [
            'type'                => $data['type'],
            'status'              => $data['status'],
            'started_at'          => !empty($data['started_at']) ? $data['started_at'] : null,
            'completed_at'        => !empty($data['completed_at']) ? $data['completed_at'] : null,
            'technician'          => !empty($data['technician']) ? trim((string) $data['technician']) : null,
            'supplier_id'         => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'problem_description' => trim((string) $data['problem_description']),
            'diagnosis'           => !empty($data['diagnosis']) ? trim((string) $data['diagnosis']) : null,
            'work_done'           => !empty($data['work_done']) ? trim((string) $data['work_done']) : null,
            'result'              => !empty($data['result']) ? $data['result'] : null,
            'cost'                => (float) ($data['cost'] ?? 0),
            'downtime_hours'      => (int) ($data['downtime_hours'] ?? 0),
            'invoice_number'      => !empty($data['invoice_number']) ? trim((string) $data['invoice_number']) : null,
            'notes'               => !empty($data['notes']) ? trim((string) $data['notes']) : null,
        ];

        $ok = $this->maintenances->update($id, $payload);

        if ($ok) {
            $this->audit->log(
                action:     'update',
                entityType: 'maintenance',
                entityId:   $id,
                oldValues:  [
                    'status' => $maintenance->status,
                    'type'   => $maintenance->type,
                ],
                newValues:  [
                    'status' => $payload['status'],
                    'type'   => $payload['type'],
                ],
                severity:   'info'
            );

            // Si la maintenance est clôturée, remettre l'équipement en stock
            // (ou le laisser en maintenance si non réparé)
            if (in_array($payload['status'], ['completed', 'cancelled'], true)) {
                $this->syncEquipmentStatus($maintenance, $payload, $userId);
            }
        }

        return $ok;
    }

    /**
     * Synchronise le statut de l'équipement après clôture.
     */
    private function syncEquipmentStatus(Maintenance $maintenance, array $payload, int $userId): void
    {
        // Si résultat = fixed ou replaced → en stock
        // Sinon (unfixed, pending, cancelled) → en maintenance
        $newStatus = in_array($payload['result'] ?? null, ['fixed', 'replaced'], true)
            ? self::STATUS_IN_STOCK
            : self::STATUS_IN_MAINTENANCE;

        $this->equipment->updateStatus($maintenance->equipmentId, $newStatus, $userId);
    }

    // ============================================
    // SUPPRESSION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function delete(int $id, int $userId): bool
    {
        $maintenance = $this->maintenances->findById($id);
        if ($maintenance === null) {
            throw new ValidationException('Maintenance introuvable.');
        }

        if ($maintenance->isActive()) {
            throw new ValidationException(
                'Impossible de supprimer une maintenance en cours. Clôturez-la d\'abord.'
            );
        }

        $ok = $this->maintenances->delete($id);

        if ($ok) {
            $this->audit->log(
                action:     'delete',
                entityType: 'maintenance',
                entityId:   $id,
                oldValues:  [
                    'equipment_id' => $maintenance->equipmentId,
                    'type'         => $maintenance->type,
                    'status'       => $maintenance->status,
                ],
                newValues:  null,
                severity:   'warning'
            );
        }

        return $ok;
    }

    // ============================================
    // STATISTIQUES
    // ============================================

    public function stats(): array
    {
        return [
            'active'     => $this->maintenances->countActive(),
            'total'      => $this->maintenances->countAll(),
            'by_status'  => $this->maintenances->countByStatus(),
            'total_cost' => $this->maintenances->getTotalCost(),
        ];
    }

    // ============================================
    // VALIDATION
    // ============================================

    /**
     * @throws ValidationException
     */
    private function validate(array $data, bool $requireEquipment = true): void
    {
        $errors = [];

        if ($requireEquipment && empty($data['equipment_id'])) {
            $errors['equipment_id'] = 'L\'équipement est obligatoire.';
        }

        if (empty($data['type'])) {
            $errors['type'] = 'Le type de maintenance est obligatoire.';
        } elseif (!in_array($data['type'], ['preventive', 'corrective', 'curative', 'upgrade'], true)) {
            $errors['type'] = 'Type de maintenance invalide.';
        }

        if (!empty($data['status'])) {
            $validStatuses = ['open', 'in_progress', 'waiting_parts', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Statut de maintenance invalide.';
            }
        }

        if (empty($data['problem_description'])) {
            $errors['problem_description'] = 'La description du problème est obligatoire.';
        }

        if (!empty($data['result'])) {
            $validResults = ['fixed', 'unfixed', 'replaced', 'pending'];
            if (!in_array($data['result'], $validResults, true)) {
                $errors['result'] = 'Résultat invalide.';
            }
        }

        if (isset($data['cost']) && $data['cost'] !== '' && !is_numeric($data['cost'])) {
            $errors['cost'] = 'Le coût doit être un nombre.';
        }

        if (!empty($errors)) {
            throw new ValidationException('Erreurs de validation.', $errors);
        }
    }
}