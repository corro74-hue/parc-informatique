<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('roles') ?>" class="text-decoration-none">
                <i class="bi bi-shield-lock"></i> Rôles
            </a>
        </li>
        <li class="breadcrumb-item active"><?= e($role['name']) ?></li>
    </ol>
</nav>

<!-- En-tête avec actions -->
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-shield-check text-primary"></i>
            <?= e($role['name']) ?>
            <?php if ((int) $role['is_system'] === 1): ?>
                <span class="badge bg-secondary" title="Rôle protégé">
                    <i class="bi bi-lock-fill"></i> Système
                </span>
            <?php endif; ?>
        </h4>
        <?php if (!empty($role['description'])): ?>
            <p class="text-muted mb-0 small"><?= e($role['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('roles') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <a href="<?= url('roles/' . (int) $role['id'] . '/edit') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <?php if ($canDelete['allowed']): ?>
            <form method="POST"
                  action="<?= url('roles/' . (int) $role['id'] . '/delete') ?>"
                  class="d-inline"
                  onsubmit="return confirm('⚠️ Supprimer le rôle « <?= e($role['name']) ?> » ?\n\nCette action est irréversible.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        <?php else: ?>
            <button type="button"
                    class="btn btn-outline-danger btn-sm"
                    disabled
                    title="<?= e($canDelete['reason']) ?>">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Permissions assignées -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-key text-primary"></i> Permissions assignées
                </h6>
                <span class="badge bg-primary">
                    <?php
                    $totalPerms = 0;
                    foreach ($permissionsByModule as $perms) {
                        $totalPerms += count($perms);
                    }
                    echo $totalPerms;
                    ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($permissionsByModule)): ?>
                    <p class="text-muted text-center mb-0 py-3">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <br>Aucune permission assignée à ce rôle.
                    </p>
                <?php else: ?>
                    <?php foreach ($permissionsByModule as $module => $permissions): ?>
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2 pb-1 border-bottom">
                                <h6 class="mb-0">
                                    <i class="bi bi-folder text-primary"></i>
                                    <?= e(ucfirst($module)) ?>
                                </h6>
                                <span class="badge bg-secondary ms-2"><?= count($permissions) ?></span>
                            </div>
                            <div class="row g-2">
                                <?php foreach ($permissions as $perm): ?>
                                    <div class="col-md-6">
                                        <div class="border rounded p-2 d-flex align-items-start gap-2">
                                            <i class="bi bi-check-circle-fill text-success mt-1"></i>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <code class="small"><?= e($perm['name']) ?></code>
                                                <?php if (!empty($perm['description'])): ?>
                                                    <div class="text-muted small">
                                                        <?= e($perm['description']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        <!-- Informations du rôle -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary"></i> Informations
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small d-block">Nom</label>
                    <strong><?= e($role['name']) ?></strong>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Slug</label>
                    <code><?= e($role['slug']) ?></code>
                </div>
                <?php if (!empty($role['description'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small d-block">Description</label>
                        <?= e($role['description']) ?>
                    </div>
                <?php endif; ?>
                <div class="mb-0">
                    <label class="text-muted small d-block">Type</label>
                    <?php if ((int) $role['is_system'] === 1): ?>
                        <span class="badge bg-secondary">
                            <i class="bi bi-lock-fill"></i> Rôle système
                        </span>
                        <div class="text-muted small mt-1">Ne peut pas être supprimé</div>
                    <?php else: ?>
                        <span class="badge bg-info">
                            <i class="bi bi-person-check"></i> Rôle personnalisé
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart text-primary"></i> Statistiques
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 45px; height: 45px;">
                        <i class="bi bi-key fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold"><?= $totalPerms ?></div>
                        <div class="text-muted small">Permission<?= $totalPerms > 1 ? 's' : '' ?></div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width: 45px; height: 45px;">
                        <i class="bi bi-people fs-5"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold">—</div>
                        <div class="text-muted small">Utilisateur(s) associé(s)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métadonnées -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-square text-primary"></i> Métadonnées
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Créé le</span>
                    <span><?= e(date('d/m/Y H:i', strtotime($role['created_at']))) ?></span>
                </div>
                <?php if (!empty($role['updated_at'])): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Modifié le</span>
                        <span><?= e(date('d/m/Y H:i', strtotime($role['updated_at']))) ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">ID interne</span>
                    <code>#<?= (int) $role['id'] ?></code>
                </div>
            </div>
        </div>

    </div>
</div>