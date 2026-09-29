<?php
declare(strict_types=1);

namespace App\Services\Assignment;

use App\Exceptions\ValidationException;
use App\Models\Assignment;
use App\Repositories\MySql\AssignmentRepository;
use App\Repositories\MySql\EquipmentRepository;
use App\Services\Audit\AuditService;

final class AssignmentService
{
    private AssignmentRepository $assignments;
    private EquipmentRepository $equipment;
    private AuditService $audit;

    /**
     * IDs des statuts utilisés par le module (alignés sur equipment_statuses).
     */
    private const STATUS_ASSIGNED = 10; // "Affecté"   — créé précédemment
    private const STATUS_IN_STOCK = 2;  // "En stock"  — statut au retour

    public function __construct()
    {
        $this->assignments = new AssignmentRepository();
        $this->equipment   = new EquipmentRepository();
        $this->audit       = new AuditService();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function find(int $id): ?Assignment
    {
        return $this->assignments->findById($id);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->assignments->paginate($filters, $page, $perPage);
    }

    public function historyForEquipment(int $equipmentId): array
    {
        return $this->assignments->findByEquipment($equipmentId);
    }

    public function historyForEmployee(int $employeeId): array
    {
        return $this->assignments->findByEmployee($employeeId);
    }

    public function findActiveForEquipment(int $equipmentId): ?Assignment
    {
        return $this->assignments->findActiveByEquipment($equipmentId);
    }

    // ============================================
    // CRÉATION
    // ============================================

    /**
     * Crée une nouvelle affectation.
     *
     * @throws ValidationException
     */
    public function create(array $data, int $userId): int
    {
        $this->validate($data);

        $equipmentId = (int) $data['equipment_id'];

        // ----- Vérifications métier -----
        $equipment = $this->equipment->findById($equipmentId);
        if ($equipment === null) {
            throw new ValidationException('Équipement introuvable.');
        }

        // 1) L'équipement ne doit pas être déjà affecté
        $existing = $this->assignments->findActiveByEquipment($equipmentId);
        if ($existing !== null) {
            throw new ValidationException(
                'Cet équipement est déjà affecté. Retournez-le avant de le réaffecter.'
            );
        }

        // 2) L'équipement ne doit pas être dans un statut final
        if (in_array($equipment->statusCode, ['reformed', 'lost', 'disposed'], true)) {
            throw new ValidationException(
                'Impossible d\'affecter un équipement ' . $equipment->statusName . '.'
            );
        }

        // ----- Préparer les données -----
        $payload = [
            'equipment_id'  => $equipmentId,
            'site_id'       => !empty($data['site_id'])     ? (int) $data['site_id']     : null,
            'service_id'    => !empty($data['service_id'])  ? (int) $data['service_id']  : null,
            'location_id'   => !empty($data['location_id']) ? (int) $data['location_id'] : null,
            'employee_id'   => !empty($data['employee_id']) ? (int) $data['employee_id'] : null,
            'start_date'    => $data['start_date'],
            'end_date'      => !empty($data['end_date']) ? $data['end_date'] : null,
            'reason'        => $data['reason'] ?? null,
            'document_path' => null,
            'created_by'    => $userId,
        ];

        // ----- Créer l'affectation -----
        $assignmentId = $this->assignments->create($payload);

        // ----- Mettre à jour le statut de l'équipement -----
        $this->equipment->updateStatus($equipmentId, self::STATUS_ASSIGNED, $userId);

        // ----- Audit (nouvelle signature) -----
        $this->audit->log(
            action:     'create',
            entityType: 'assignment',
            entityId:   $assignmentId,
            oldValues:  null,
            newValues:  [
                'equipment_id' => $equipmentId,
                'employee_id'  => $payload['employee_id'],
                'start_date'   => $payload['start_date'],
            ],
            severity:   'info'
        );

        return $assignmentId;
    }

    // ============================================
    // MISE À JOUR
    // ============================================

    /**
     * @throws ValidationException
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $assignment = $this->assignments->findById($id);
        if ($assignment === null) {
            throw new ValidationException('Affectation introuvable.');
        }

        if (!$assignment->isActive()) {
            throw new ValidationException(
                'Cette affectation est déjà clôturée et ne peut plus être modifiée.'
            );
        }

        $this->validate($data, false);

        $payload = [
            'site_id'       => !empty($data['site_id'])     ? (int) $data['site_id']     : null,
            'service_id'    => !empty($data['service_id'])  ? (int) $data['service_id']  : null,
            'location_id'   => !empty($data['location_id']) ? (int) $data['location_id'] : null,
            'employee_id'   => !empty($data['employee_id']) ? (int) $data['employee_id'] : null,
            'start_date'    => $data['start_date'],
            'end_date'      => $assignment->endDate,
            'reason'        => $data['reason'] ?? null,
            'document_path' => $assignment->documentPath,
        ];

        $ok = $this->assignments->update($id, $payload);

        if ($ok) {
            $this->audit->log(
                action:     'update',
                entityType: 'assignment',
                entityId:   $id,
                oldValues:  [
                    'employee_id' => $assignment->employeeId,
                    'start_date'  => $assignment->startDate,
                ],
                newValues:  [
                    'employee_id' => $payload['employee_id'],
                    'start_date'  => $payload['start_date'],
                ],
                severity:   'info'
            );
        }

        return $ok;
    }

    // ============================================
    // RETOUR D'AFFECTATION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function returnEquipment(int $id, ?string $endDate, int $userId): bool
    {
        $assignment = $this->assignments->findById($id);
        if ($assignment === null) {
            throw new ValidationException('Affectation introuvable.');
        }

        if (!$assignment->isActive()) {
            throw new ValidationException('Cette affectation est déjà clôturée.');
        }

        $endDate = $endDate ?: date('Y-m-d');

        // 1) Clôturer l'affectation
        $ok = $this->assignments->markAsReturned($id, $endDate);

        if ($ok) {
            // 2) Remettre l'équipement "En stock"
            $this->equipment->updateStatus($assignment->equipmentId, self::STATUS_IN_STOCK, $userId);

            // 3) Audit
            $this->audit->log(
                action:     'return',
                entityType: 'assignment',
                entityId:   $id,
                oldValues:  [
                    'end_date' => null,
                ],
                newValues:  [
                    'end_date' => $endDate,
                ],
                severity:   'info'
            );
        }

        return $ok;
    }

    // ============================================
    // SUPPRESSION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function delete(int $id, int $userId): bool
    {
        $assignment = $this->assignments->findById($id);
        if ($assignment === null) {
            throw new ValidationException('Affectation introuvable.');
        }

        if ($assignment->isActive()) {
            throw new ValidationException(
                'Impossible de supprimer une affectation active. Effectuez d\'abord le retour.'
            );
        }

        $ok = $this->assignments->delete($id);

        if ($ok) {
            $this->audit->log(
                action:     'delete',
                entityType: 'assignment',
                entityId:   $id,
                oldValues:  [
                    'equipment_id' => $assignment->equipmentId,
                    'employee_id'  => $assignment->employeeId,
                    'start_date'   => $assignment->startDate,
                    'end_date'     => $assignment->endDate,
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
            'active' => $this->assignments->countActive(),
            'total'  => $this->assignments->countAll(),
        ];
    }

    public function recent(int $limit = 5): array
    {
        return $this->assignments->findRecent($limit);
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

        if (empty($data['employee_id'])) {
            $errors['employee_id'] = 'L\'employé destinataire est obligatoire.';
        }

        if (empty($data['start_date'])) {
            $errors['start_date'] = 'La date de début est obligatoire.';
        } elseif (!$this->isValidDate($data['start_date'])) {
            $errors['start_date'] = 'La date de début est invalide.';
        }

        if (!empty($data['end_date'])) {
            if (!$this->isValidDate($data['end_date'])) {
                $errors['end_date'] = 'La date de fin est invalide.';
            } elseif (!empty($data['start_date']) && $data['end_date'] < $data['start_date']) {
                $errors['end_date'] = 'La date de fin doit être après la date de début.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Erreurs de validation.', $errors);
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}