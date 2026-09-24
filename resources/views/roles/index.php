<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-shield-lock text-primary"></i>
            Rôles et permissions
            <span class="badge bg-secondary"><?= count($roles) ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= count($roles) ?> rôle<?= count($roles) > 1 ? 's' : '' ?> configuré<?= count($roles) > 1 ? 's' : '' ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('roles/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau rôle
        </a>
    </div>
</div>

<!-- Info -->
<div class="alert alert-info d-flex align-items-start mb-3">
    <i class="bi bi-info-circle fs-5 me-2"></i>
    <div>
        <strong>Comment ça marche ?</strong>
        <p class="mb-0 small">
            Les rôles définissent ce que les utilisateurs peuvent faire dans l'application.
            Chaque rôle est associé à un ensemble de <strong>permissions</strong>.
            Les rôles marqués <span class="badge bg-secondary">Système</span> ne peuvent pas être supprimés.
        </p>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="60" class="text-center">ID</th>
                    <th>Nom du rôle</th>
                    <th width="150">Slug</th>
                    <th width="120" class="text-center">Permissions</th>
                    <th width="120" class="text-center">Utilisateurs</th>
                    <th width="100" class="text-center">Système</th>
                    <th width="200" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun rôle configuré.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <?php
                        $isSystem = (int) $role['is_system'] === 1;
                        $canDelete = !$isSystem && (int) $role['user_count'] === 0;
                        ?>
                        <tr>
                            <td class="text-center">
                                <code>#<?= (int) $role['id'] ?></code>
                            </td>
                            <td>
                                <a href="<?= url('roles/' . (int) $role['id']) ?>" class="text-decoration-none fw-semibold">
                                    <?= e($role['name']) ?>
                                </a>
                                <?php if (!empty($role['description'])): ?>
                                    <div class="text-muted small"><?= e($role['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="small"><?= e($role['slug']) ?></code>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="bi bi-key"></i> <?= (int) $role['permission_count'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <i class="bi bi-people"></i> <?= (int) $role['user_count'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($isSystem): ?>
                                    <span class="badge bg-secondary" title="Rôle protégé">
                                        <i class="bi bi-lock-fill"></i> Système
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('roles/' . (int) $role['id']) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('roles/' . (int) $role['id'] . '/edit') ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($canDelete): ?>
                                    <form method="POST"
                                          action="<?= url('roles/' . (int) $role['id'] . '/delete') ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('⚠️ Supprimer le rôle « <?= e($role['name']) ?> » ?\n\nCette action est irréversible.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            disabled
                                            title="<?= $isSystem
                                                ? 'Impossible de supprimer un rôle système.'
                                                : 'Ce rôle est utilisé par ' . (int) $role['user_count'] . ' utilisateur(s).' ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>