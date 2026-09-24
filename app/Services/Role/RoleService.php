<?php
declare(strict_types=1);

namespace App\Services\Role;

use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\MySql\RoleRepository;
use App\Exceptions\ValidationException;

final class RoleService
{
    private RoleRepositoryInterface $roles;

    public function __construct(?RoleRepositoryInterface $roles = null)
    {
        $this->roles = $roles ?? new RoleRepository();
    }

    // ============================================
    // CRÉATION DE RÔLE
    // ============================================

    /**
     * Crée un nouveau rôle.
     *
     * @param array $data Données du formulaire
     * @param array<int> $permissionIds IDs des permissions à assigner
     * @return int L'ID du nouveau rôle
     * @throws ValidationException
     */
    public function create(array $data, array $permissionIds = []): int
    {
        // 1. Validation
        $this->validate($data, null);

        // 2. Préparation des données
        $roleData = [
            'name'        => trim($data['name']),
            'slug'        => $this->generateSlug($data['slug'] ?? $data['name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'is_system'   => 0, // Les nouveaux rôles créés par admin ne sont JAMAIS système
        ];

        // 3. Création
        $roleId = $this->roles->create($roleData);

        // 4. Assignation des permissions
        if (!empty($permissionIds)) {
            $this->roles->syncPermissions($roleId, $permissionIds);
        }

        return $roleId;
    }

    // ============================================
    // MISE À JOUR DE RÔLE
    // ============================================

    /**
     * Met à jour un rôle existant.
     *
     * @throws ValidationException
     */
    public function update(int $id, array $data, array $permissionIds = []): bool
    {
        // 1. Vérifier que le rôle existe
        $role = $this->roles->findById($id);
        if (!$role) {
            throw new ValidationException('Rôle introuvable.');
        }

        // 2. Validation
        $this->validate($data, $id);

        // 3. Protection : on ne peut pas modifier le slug d'un rôle système
        $updateData = [
            'name'        => trim($data['name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
        ];

        if ((int) $role['is_system'] === 0) {
            // Rôle non-système : le slug peut être modifié
            $updateData['slug'] = $this->generateSlug($data['slug'] ?? $data['name']);
        }

        // 4. Mise à jour
        $updated = $this->roles->update($id, $updateData);

        // 5. Synchronisation des permissions
        //    (autorisée même pour les rôles système, sauf pour 'admin' qui a tout par défaut)
        if ($role['slug'] !== 'admin') {
            $this->roles->syncPermissions($id, $permissionIds);
        }

        return $updated;
    }

    // ============================================
    // SUPPRESSION DE RÔLE
    // ============================================

    /**
     * Vérifie si un rôle peut être supprimé.
     *
     * @return array{allowed: bool, reason: ?string}
     */
    public function canDelete(int $id): array
    {
        $role = $this->roles->findById($id);
        if (!$role) {
            return [
                'allowed' => false,
                'reason'  => 'Rôle introuvable.',
            ];
        }

        // Protection : on ne peut pas supprimer un rôle système
        if ((int) $role['is_system'] === 1) {
            return [
                'allowed' => false,
                'reason'  => 'Impossible de supprimer un rôle système.',
            ];
        }

        // Protection : on ne peut pas supprimer un rôle utilisé par des utilisateurs
        $userCount = $this->roles->countUsers($id);
        if ($userCount > 0) {
            return [
                'allowed' => false,
                'reason'  => "Impossible de supprimer ce rôle : {$userCount} utilisateur(s) y sont associés.",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Supprime un rôle.
     *
     * @throws ValidationException
     */
    public function delete(int $id): bool
    {
        $canDelete = $this->canDelete($id);
        if (!$canDelete['allowed']) {
            throw new ValidationException($canDelete['reason']);
        }

        return $this->roles->delete($id);
    }

    // ============================================
    // VALIDATION
    // ============================================

    /**
     * Valide les données d'un rôle.
     *
     * @param int|null $excludeId ID à exclure pour la vérification d'unicité
     * @throws ValidationException
     */
    private function validate(array $data, ?int $excludeId): void
    {
        $errors = [];

        // Nom
        if (empty($data['name'])) {
            $errors[] = 'Le nom du rôle est requis.';
        } elseif (strlen($data['name']) < 3) {
            $errors[] = 'Le nom du rôle doit contenir au moins 3 caractères.';
        } elseif (strlen($data['name']) > 80) {
            $errors[] = 'Le nom du rôle ne peut pas dépasser 80 caractères.';
        }

        // Slug (uniquement si fourni explicitement)
        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors[] = 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.';
            } elseif ($this->roles->slugExists($data['slug'], $excludeId)) {
                $errors[] = 'Ce slug est déjà utilisé par un autre rôle.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Données invalides', $errors);
        }
    }

    /**
     * Génère un slug à partir d'une chaîne.
     */
    private function generateSlug(string $value): string
    {
        // Convertit en minuscules
        $slug = mb_strtolower(trim($value));

        // Remplace les caractères accentués
        $slug = str_replace(
            ['à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç', 'ñ'],
            ['a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c', 'n'],
            $slug
        );

        // Remplace tout ce qui n'est pas alphanumérique par des tirets
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Supprime les tirets en début et fin
        $slug = trim($slug, '-');

        return $slug;
    }

    // ============================================
    // LECTURE
    // ============================================

    /**
     * Récupère tous les rôles avec statistiques.
     */
    public function getAllWithStats(): array
    {
        $roles = $this->roles->findAll();

        foreach ($roles as &$role) {
            $role['user_count'] = $this->roles->countUsers((int) $role['id']);
            $role['permission_count'] = count($this->roles->getPermissionIds((int) $role['id']));
        }

        return $roles;
    }

    /**
     * Récupère un rôle par son ID.
     */
    public function find(int $id): ?array
    {
        return $this->roles->findById($id);
    }

    /**
     * Récupère les permissions d'un rôle.
     */
    public function getPermissions(int $roleId): array
    {
        return $this->roles->getPermissions($roleId);
    }

    /**
     * Récupère les IDs des permissions d'un rôle.
     */
    public function getPermissionIds(int $roleId): array
    {
        return $this->roles->getPermissionIds($roleId);
    }

    /**
     * Récupère toutes les permissions groupées par module.
     */
    public function getPermissionsGroupedByModule(): array
    {
        return $this->roles->getPermissionsGroupedByModule();
    }

    /**
     * Vérifie si un rôle peut être supprimé (pour l'affichage dans les vues).
     */
    public function canDeleteCheck(int $id): array
    {
        return $this->canDelete($id);
    }
}