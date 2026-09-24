<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-people text-primary"></i>
            Utilisateurs
            <span class="badge bg-secondary"><?= $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= $total ?> utilisateur<?= $total > 1 ? 's' : '' ?>
            — page <?= $page ?> sur <?= $lastPage ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('users/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvel utilisateur
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('users') ?>" class="row g-2 align-items-end">
            <!-- Recherche -->
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">
                    <i class="bi bi-search"></i> Recherche
                </label>
                <input type="text"
                       name="search"
                       class="form-control form-control-sm"
                       placeholder="Nom, email, username..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>

            <!-- Rôle -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Rôle</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">Tous les rôles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e($role['slug']) ?>"
                                <?= ($filters['role'] ?? '') === $role['slug'] ? 'selected' : '' ?>>
                            <?= e($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Statut -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="active"   <?= ($filters['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Actif</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    <option value="locked"   <?= ($filters['status'] ?? '') === 'locked'   ? 'selected' : '' ?>>Verrouillé</option>
                </select>
            </div>

            <!-- Boutons -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
                <?php if (!empty(array_filter($filters))): ?>
                    <a href="<?= url('users') ?>" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="60">Avatar</th>
                    <th>Nom complet</th>
                    <th width="180">Email</th>
                    <th width="120">Rôle</th>
                    <th width="120">Statut</th>
                    <th width="160">Dernière connexion</th>
                    <th width="200" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun utilisateur trouvé.</p>
                            <?php if (!empty(array_filter($filters))): ?>
                                <a href="<?= url('users') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('users/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Créer le premier utilisateur
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        // Déterminer le statut
                        $statusBadge = '<span class="badge bg-success">Actif</span>';
                        if (!$user->isActive) {
                            $statusBadge = '<span class="badge bg-secondary">Inactif</span>';
                        } elseif ($user->isLocked()) {
                            $statusBadge = '<span class="badge bg-danger">Verrouillé</span>';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="user-avatar" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                    <?= e($user->getInitials()) ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?= url('users/' . $user->id) ?>" class="text-decoration-none fw-semibold">
                                    <?= e($user->getFullName()) ?>
                                </a>
                                <div class="text-muted small">@<?= e($user->username) ?></div>
                            </td>
                            <td>
                                <a href="mailto:<?= e($user->email) ?>" class="text-decoration-none">
                                    <?= e($user->email) ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($user->roles)): ?>
                                    <?php foreach ($user->roles as $role): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <?= e($role['name']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $statusBadge ?></td>
                            <td>
                                <?php if ($user->lastLoginAt): ?>
                                    <span title="<?= e($user->lastLoginAt) ?>">
                                        <?= e(date('d/m/Y H:i', strtotime($user->lastLoginAt))) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Jamais</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('users/' . $user->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('users/' . $user->id . '/edit') ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user->id !== (int) $_SESSION['user_id']): ?>
                                    <form method="POST"
                                          action="<?= url('users/' . $user->id . '/delete') ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('⚠️ Supprimer l\'utilisateur <?= e($user->getFullName()) ?> ?\n\nIl sera désactivé et pourra être restauré.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php require dirname(__DIR__) . '/partials/_pagination.php'; ?>