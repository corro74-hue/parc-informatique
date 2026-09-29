<?php
declare(strict_types=1);

namespace App\Services\Employee;

use App\Exceptions\ValidationException;
use App\Models\Employee;
use App\Repositories\MySql\EmployeeRepository;
use App\Services\Audit\AuditService;

final class EmployeeService
{
    private EmployeeRepository $employees;
    private AuditService $audit;

    public function __construct()
    {
        $this->employees = new EmployeeRepository();
        $this->audit     = new AuditService();
    }

    // ============================================
    // LECTURE
    // ============================================

    public function find(int $id): ?Employee
    {
        return $this->employees->findById($id);
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        return $this->employees->paginate($filters, $page, $perPage);
    }

    /** @return Employee[] */
    public function findAllActive(): array
    {
        return $this->employees->findAllActive();
    }

    /** @return Employee[] */
    public function search(string $term, int $limit = 20): array
    {
        return $this->employees->search($term, $limit);
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

        // Vérifier unicité du matricule
        if (!empty($data['matricule'])) {
            $existing = $this->employees->findByMatricule($data['matricule']);
            if ($existing !== null) {
                throw new ValidationException(
                    'Le matricule "' . $data['matricule'] . '" est déjà utilisé.'
                );
            }
        }

        // Vérifier unicité de l'email
        if (!empty($data['email'])) {
            $existing = $this->employees->findByEmail($data['email']);
            if ($existing !== null) {
                throw new ValidationException(
                    'L\'email "' . $data['email'] . '" est déjà utilisé.'
                );
            }
        }

        $payload = [
            'service_id'     => !empty($data['service_id']) ? (int) $data['service_id'] : null,
            'first_name'     => trim((string) $data['first_name']),
            'last_name'      => trim((string) $data['last_name']),
            'matricule'      => !empty($data['matricule']) ? trim((string) $data['matricule']) : null,
            'email'          => !empty($data['email']) ? trim((string) $data['email']) : null,
            'phone'          => !empty($data['phone']) ? trim((string) $data['phone']) : null,
            'function_title' => !empty($data['function_title']) ? trim((string) $data['function_title']) : null,
            'is_active'      => isset($data['is_active']) ? 1 : 1, // Actif par défaut
        ];

        $employeeId = $this->employees->create($payload);

        $this->audit->log(
            action:     'create',
            entityType: 'employee',
            entityId:   $employeeId,
            oldValues:  null,
            newValues:  [
                'first_name' => $payload['first_name'],
                'last_name'  => $payload['last_name'],
                'matricule'  => $payload['matricule'],
            ],
            severity:   'info'
        );

        return $employeeId;
    }

    // ============================================
    // MODIFICATION
    // ============================================

    /**
     * @throws ValidationException
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $employee = $this->employees->findById($id);
        if ($employee === null) {
            throw new ValidationException('Employé introuvable.');
        }

        $this->validate($data, $id);

        // Vérifier unicité du matricule (sauf soi-même)
        if (!empty($data['matricule']) && $data['matricule'] !== $employee->matricule) {
            $existing = $this->employees->findByMatricule($data['matricule']);
            if ($existing !== null && $existing->id !== $id) {
                throw new ValidationException(
                    'Le matricule "' . $data['matricule'] . '" est déjà utilisé.'
                );
            }
        }

        // Vérifier unicité de l'email (sauf soi-même)
        if (!empty($data['email']) && $data['email'] !== $employee->email) {
            $existing = $this->employees->findByEmail($data['email']);
            if ($existing !== null && $existing->id !== $id) {
                throw new ValidationException(
                    'L\'email "' . $data['email'] . '" est déjà utilisé.'
                );
            }
        }

        $payload = [
            'service_id'     => !empty($data['service_id']) ? (int) $data['service_id'] : null,
            'first_name'     => trim((string) $data['first_name']),
            'last_name'      => trim((string) $data['last_name']),
            'matricule'      => !empty($data['matricule']) ? trim((string) $data['matricule']) : null,
            'email'          => !empty($data['email']) ? trim((string) $data['email']) : null,
            'phone'          => !empty($data['phone']) ? trim((string) $data['phone']) : null,
            'function_title' => !empty($data['function_title']) ? trim((string) $data['function_title']) : null,
            'is_active'      => !empty($data['is_active']) ? 1 : 0,
        ];

        $ok = $this->employees->update($id, $payload);

        if ($ok) {
            $this->audit->log(
                action:     'update',
                entityType: 'employee',
                entityId:   $id,
                oldValues:  [
                    'first_name' => $employee->firstName,
                    'last_name'  => $employee->lastName,
                    'matricule'  => $employee->matricule,
                    'is_active'  => $employee->isActive,
                ],
                newValues:  $payload,
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
        $employee = $this->employees->findById($id);
        if ($employee === null) {
            throw new ValidationException('Employé introuvable.');
        }

        // Vérifier s'il a des affectations actives (à faire plus tard)
        // Pour l'instant on autorise la suppression.

        $ok = $this->employees->delete($id);

        if ($ok) {
            $this->audit->log(
                action:     'delete',
                entityType: 'employee',
                entityId:   $id,
                oldValues:  [
                    'first_name' => $employee->firstName,
                    'last_name'  => $employee->lastName,
                    'matricule'  => $employee->matricule,
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
            'active' => $this->employees->countActive(),
            'total'  => $this->employees->countAll(),
        ];
    }

    // ============================================
    // VALIDATION
    // ============================================

    /**
     * @throws ValidationException
     */
    private function validate(array $data, ?int $ignoreId = null): void
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Le prénom est obligatoire.';
        } elseif (mb_strlen(trim($data['first_name'])) > 100) {
            $errors['first_name'] = 'Le prénom ne peut pas dépasser 100 caractères.';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen(trim($data['last_name'])) > 100) {
            $errors['last_name'] = 'Le nom ne peut pas dépasser 100 caractères.';
        }

        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'adresse email est invalide.';
            } elseif (mb_strlen($data['email']) > 180) {
                $errors['email'] = 'L\'email ne peut pas dépasser 180 caractères.';
            }
        }

        if (!empty($data['phone'])) {
            // Accepte les chiffres, espaces, +, -, (), .
            if (!preg_match('/^[0-9+\s\-().]{4,30}$/', $data['phone'])) {
                $errors['phone'] = 'Le numéro de téléphone est invalide.';
            }
        }

        if (!empty($data['matricule'])) {
            if (mb_strlen(trim($data['matricule'])) > 50) {
                $errors['matricule'] = 'Le matricule ne peut pas dépasser 50 caractères.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Erreurs de validation.', $errors);
        }
    }
}