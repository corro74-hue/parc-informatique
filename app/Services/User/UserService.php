<?php
declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\MySql\UserRepository;
use App\Exceptions\ValidationException;
use App\Services\Security\PasswordPolicyService;

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

        // 5. Enregistrer le mot de passe initial dans l'historique + tracker la date
        $this->users->addPasswordToHistory($userId, $userData['password_hash']);
        $this->users->updatePasswordChangedAt($userId);

        // ============================================
        // 6. NOUVEAU : Envoyer l'email de bienvenue
        // ============================================
        try {
            $mailService = new \App\Services\Mail\MailService();
            $mailService->sendTemplate(
                $userData['email'],
                'Bienvenue sur ' . config('app.name', 'Parc Info') . ' — Vos identifiants',
                'emails.welcome',
                [
                    'appName'   => config('app.name', 'Parc Info'),
                    'firstName' => $userData['first_name'],
                    'username'  => $userData['username'],
                    'password'  => $plainPassword,
                    'appUrl'    => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
                    'loginUrl'  => url('login'),
                ]
            );
        } catch (\Throwable $e) {
            error_log('Erreur envoi email bienvenue : ' . $e->getMessage());
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
        $user = $this->users->findById($id);
        if (!$user) {
            throw new ValidationException('Utilisateur introuvable.');
        }

        $this->validate($data, null, $id);

        $updateData = [
            'username'             => trim($data['username']),
            'email'                => trim(strtolower($data['email'])),
            'first_name'           => trim($data['first_name']),
            'last_name'            => trim($data['last_name']),
            'phone'                => !empty($data['phone']) ? trim($data['phone']) : null,
            'is_active'            => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'must_change_password' => !empty($data['must_change_password']) ? 1 : 0,
        ];

        $updated = $this->users->update($id, $updateData);

        $this->users->syncRoles($id, $roleIds);

        return $updated;
    }

    // ============================================
    // SUPPRESSION / RESTAURATION
    // ============================================

    public function canDelete(int $targetId, int $currentUserId): array
    {
        if ($targetId === $currentUserId) {
            return [
                'allowed' => false,
                'reason'  => 'Vous ne pouvez pas supprimer votre propre compte.',
            ];
        }

        if ($this->isLastAdmin($targetId)) {
            return [
                'allowed' => false,
                'reason'  => 'Impossible de supprimer le dernier administrateur.',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function delete(int $id, int $currentUserId): bool
    {
        $canDelete = $this->canDelete($id, $currentUserId);
        if (!$canDelete['allowed']) {
            throw new ValidationException($canDelete['reason']);
        }

        return $this->users->softDelete($id);
    }

    public function restore(int $id): bool
    {
        return $this->users->restore($id);
    }

    private function isLastAdmin(int $userId): bool
    {
        $user = $this->users->findById($userId);
        if (!$user || !$user->isAdmin()) {
            return false;
        }

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

    public function changePassword(int $id, string $plainPassword, bool $mustChange = false): bool
    {
        $policy = new PasswordPolicyService();
        $validation = $policy->validate($plainPassword, $id);

        if (!$validation['valid']) {
            throw new ValidationException('Mot de passe non conforme', $validation['errors']);
        }

        $user = $this->users->findById($id);
        if ($user) {
            $this->users->addPasswordToHistory($id, $user->passwordHash);
        }

        $newHash = $this->hashPassword($plainPassword);
        $result = $this->users->updatePassword($id, $newHash, $mustChange);

        if ($result) {
            $this->users->addPasswordToHistory($id, $newHash);
            $this->users->updatePasswordChangedAt($id);
        }

        return $result;
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur (par un admin).
     * Génère un mot de passe temporaire ET l'envoie par email.
     *
     * @return array{password: string, email_sent: bool}
     * @throws ValidationException
     */
    public function resetPassword(int $id): array
    {
        // 1. Vérifier que l'utilisateur existe
        $user = $this->users->findById($id);
        if (!$user) {
            throw new ValidationException('Utilisateur introuvable.');
        }

        // 2. Générer un mot de passe temporaire
        $temporaryPassword = $this->generateTemporaryPassword();
        $newHash = $this->hashPassword($temporaryPassword);

        // 3. Mettre à jour en BDD (avec must_change_password = true)
        $this->users->updatePassword($id, $newHash, true);

        // 4. Ajouter le nouveau hash à l'historique + tracker la date
        $this->users->addPasswordToHistory($id, $newHash);
        $this->users->updatePasswordChangedAt($id);

        // ============================================
        // 5. NOUVEAU : Envoyer l'email de réinitialisation
        // ============================================
        $emailSent = false;
        try {
            $mailService = new \App\Services\Mail\MailService();
            $emailSent = $mailService->sendTemplate(
                $user->email,
                'Votre mot de passe a été réinitialisé — ' . config('app.name', 'Parc Info'),
                'emails.password-reset',
                [
                    'appName'    => config('app.name', 'Parc Info'),
                    'firstName'  => $user->firstName,
                    'username'   => $user->username,
                    'password'   => $temporaryPassword,
                    'appUrl'     => rtrim((string) env('APP_URL', 'http://localhost'), '/'),
                    'loginUrl'   => url('login'),
                ]
            );
        } catch (\Throwable $e) {
            error_log('Erreur envoi email reset password : ' . $e->getMessage());
        }

        return [
            'password'   => $temporaryPassword,
            'email_sent' => $emailSent,
        ];
    }

    private function generateTemporaryPassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';

        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

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

    private function validate(array $data, ?string $plainPassword, ?int $excludeId): void
    {
        $errors = [];

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

        if (empty($data['first_name'])) {
            $errors[] = 'Le prénom est requis.';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'Le nom est requis.';
        }

        if ($plainPassword !== null) {
            $this->validatePasswordStrength($plainPassword, $errors);
        }

        if (!empty($errors)) {
            throw new ValidationException('Données invalides', $errors);
        }
    }

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

    public function find(int $id): ?User
    {
        return $this->users->findById($id);
    }

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

    public function getAllRoles(): array
    {
        return $this->users->findAllRoles();
    }

    public function getAllPermissions(): array
    {
        return $this->users->findAllPermissions();
    }

    public function getLoginHistory(int $userId, int $limit = 20): array
    {
        return $this->users->getLoginHistory($userId, $limit);
    }
}