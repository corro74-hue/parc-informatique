<?php
declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\MySql\UserRepository;
use App\Exceptions\ValidationException;

final class UserService
{
    private UserRepositoryInterface $users;

    public function __construct(?UserRepositoryInterface $users = null)
    {
        $this->users = $users ?? new UserRepository();
    }

    // ============================================
    // CRÉATION D'UTILISATEUR
    // ============================================

    /**
     * Crée un nouvel utilisateur.
     *
     * @param array $data Données du formulaire
     * @param string $plainPassword Mot de passe en clair
     * @param array<int> $roleIds IDs des rôles à assigner
     * @return int L'ID du nouvel utilisateur
     * @throws ValidationException
     */
    public function create(array $data, string $plainPassword, array $roleIds = []): int
    {
        // 1. Validation
        $this->validate($data, $plainPassword, null);

        // 2. Préparation des données
        $userData = [
            'username'             => trim($data['username']),
            'email'                => trim(strtolower($data['email'])),
            'password_hash'        => $this->hashPassword($plainPassword),
            'first_name'           => trim($data['first_name']),
            'last_name'            => trim($data['last_name']),
            'phone'                => !empty($data['phone']) ? trim($data['phone']) : null,
            'is_active'            => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'must_change_password' => !empty($data['must_change_password']) ? 1 : 0,
        ];

        // 3. Création
        $userId = $this->users->create($userData);

        // 4. Assignation des rôles
        if (!empty($roleIds)) {
            $this->users->syncRoles($userId, $roleIds);
        }

        return $userId;
    }

    // ============================================
    // MISE À JOUR D'UTILISATEUR
    // ============================================

    /**
     * Met à jour un utilisateur existant.
     *
     * @throws ValidationException
     */
    public function update(int $id, array $data, array $roleIds = []): bool
    {
        // 1. Vérifier que l'utilisateur existe
        $user = $this->users->findById($id);
        if (!$user) {
            throw new ValidationException('Utilisateur introuvable.');
        }

        // 2. Validation
        $this->validate($data, null, $id);

        // 3. Préparation des données
        $updateData = [
            'username'             => trim($data['username']),
            'email'                => trim(strtolower($data['email'])),
            'first_name'           => trim($data['first_name']),
            'last_name'            => trim($data['last_name']),
            'phone'                => !empty($data['phone']) ? trim($data['phone']) : null,
            'is_active'            => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'must_change_password' => !empty($data['must_change_password']) ? 1 : 0,
        ];

        // 4. Mise à jour
        $updated = $this->users->update($id, $updateData);

        // 5. Synchronisation des rôles
        $this->users->syncRoles($id, $roleIds);

        return $updated;
    }

    // ============================================
    // SUPPRESSION / RESTAURATION
    // ============================================

    /**
     * Vérifie si un utilisateur peut être supprimé.
     *
     * @param int $targetId ID de l'utilisateur à supprimer
     * @param int $currentUserId ID de l'utilisateur qui effectue l'action
     * @return array{allowed: bool, reason: ?string}
     */
    public function canDelete(int $targetId, int $currentUserId): array
    {
        // Protection : on ne peut pas se supprimer soi-même
        if ($targetId === $currentUserId) {
            return [
                'allowed' => false,
                'reason'  => 'Vous ne pouvez pas supprimer votre propre compte.',
            ];
        }

        // Protection : on ne peut pas supprimer le dernier admin
        if ($this->isLastAdmin($targetId)) {
            return [
                'allowed' => false,
                'reason'  => 'Impossible de supprimer le dernier administrateur.',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Supprime un utilisateur (soft delete).
     *
     * @param int $id ID de l'utilisateur à supprimer
     * @param int $currentUserId ID de l'utilisateur qui effectue l'action (pour éviter l'auto-suppression)
     * @throws ValidationException
     */
    public function delete(int $id, int $currentUserId): bool
    {
        // Vérification centralisée
        $canDelete = $this->canDelete($id, $currentUserId);
        if (!$canDelete['allowed']) {
            throw new ValidationException($canDelete['reason']);
        }

        return $this->users->softDelete($id);
    }

    /**
     * Restaure un utilisateur supprimé.
     */
    public function restore(int $id): bool
    {
        return $this->users->restore($id);
    }

    /**
     * Vérifie si un utilisateur est le dernier admin actif.
     */
    private function isLastAdmin(int $userId): bool
    {
        $user = $this->users->findById($userId);
        if (!$user || !$user->isAdmin()) {
            return false;
        }

        // Compter les autres admins actifs
        $allUsers = $this->users->findAll(['role' => 'admin', 'status' => 'active'], 1, 1000);
        $adminCount = 0;

        foreach ($allUsers as $u) {
            if ($u->id !== $userId) {
                $adminCount++;
            }
        }

        return $adminCount === 0;
    }

    // ============================================
    // MOTS DE PASSE
    // ============================================

    /**
     * Change le mot de passe d'un utilisateur.
     */
    public function changePassword(int $id, string $plainPassword, bool $mustChange = false): bool
    {
        // Validation de la force du mot de passe
        $this->validatePasswordStrength($plainPassword);

        return $this->users->updatePassword($id, $this->hashPassword($plainPassword), $mustChange);
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur (par un admin).
     * Génère un mot de passe temporaire aléatoire.
     *
     * @return string Le mot de passe temporaire en clair (à communiquer à l'utilisateur)
     */
    public function resetPassword(int $id): string
    {
        $temporaryPassword = $this->generateTemporaryPassword();

        $this->users->updatePassword($id, $this->hashPassword($temporaryPassword), true);

        return $temporaryPassword;
    }

    /**
     * Génère un mot de passe temporaire aléatoire (12 caractères).
     */
    private function generateTemporaryPassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';

        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    /**
     * Hache un mot de passe avec Argon2id.
     */
    private function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost'   => 4,
            'threads'     => 2,
        ]);
    }

    // ============================================
    // VALIDATION
    // ============================================

    /**
     * Valide les données d'un utilisateur.
     *
     * @param int|null $excludeId ID à exclure de la vérification d'unicité (pour l'édition)
     * @throws ValidationException
     */
    private function validate(array $data, ?string $plainPassword, ?int $excludeId): void
    {
        $errors = [];

        // Username
        if (empty($data['username'])) {
            $errors[] = 'Le nom d\'utilisateur est requis.';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
        } elseif (strlen($data['username']) > 80) {
            $errors[] = 'Le nom d\'utilisateur ne peut pas dépasser 80 caractères.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['username'])) {
            $errors[] = 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, points, tirets et underscores.';
        } else {
            $existing = $this->users->findByUsername($data['username']);
            if ($existing && $existing->id !== $excludeId) {
                $errors[] = 'Ce nom d\'utilisateur est déjà pris.';
            }
        }

        // Email
        if (empty($data['email'])) {
            $errors[] = 'L\'email est requis.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email n\'est pas valide.';
        } else {
            $existing = $this->users->findByEmail($data['email']);
            if ($existing && $existing->id !== $excludeId) {
                $errors[] = 'Cet email est déjà utilisé.';
            }
        }

        // First name
        if (empty($data['first_name'])) {
            $errors[] = 'Le prénom est requis.';
        }

        // Last name
        if (empty($data['last_name'])) {
            $errors[] = 'Le nom est requis.';
        }

        // Mot de passe (uniquement à la création)
        if ($plainPassword !== null) {
            $this->validatePasswordStrength($plainPassword, $errors);
        }

        if (!empty($errors)) {
            throw new ValidationException('Données invalides', $errors);
        }
    }

    /**
     * Valide la force d'un mot de passe.
     *
     * @param array $errors Tableau d'erreurs à compléter (par référence)
     */
    private function validatePasswordStrength(string $password, array &$errors = []): void
    {
        if (strlen($password) < 10) {
            $errors[] = 'Le mot de passe doit contenir au moins 10 caractères.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
        }

        if (!empty($errors)) {
            throw new ValidationException('Mot de passe trop faible', $errors);
        }
    }

    // ============================================
    // LECTURE
    // ============================================

    /**
     * Récupère un utilisateur par son ID.
     */
    public function find(int $id): ?User
    {
        return $this->users->findById($id);
    }

    /**
     * Récupère la liste paginée des utilisateurs.
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $users = $this->users->findAll($filters, $page, $perPage);
        $total = $this->users->countAll($filters);

        return [
            'data'      => $users,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'lastPage'  => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Récupère tous les rôles disponibles.
     */
    public function getAllRoles(): array
    {
        return $this->users->findAllRoles();
    }

    /**
     * Récupère toutes les permissions disponibles.
     */
    public function getAllPermissions(): array
    {
        return $this->users->findAllPermissions();
    }

    /**
     * Récupère l'historique des connexions d'un utilisateur.
     */
    public function getLoginHistory(int $userId, int $limit = 20): array
    {
        return $this->users->getLoginHistory($userId, $limit);
    }
}