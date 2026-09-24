<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('roles') ?>" class="text-decoration-none">
                <i class="bi bi-shield-lock"></i> Rôles
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('roles/' . (int) $role['id']) ?>" class="text-decoration-none">
                <?= e($role['name']) ?>
            </a>
        </li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-pencil text-primary"></i>
            Modifier : <?= e($role['name']) ?>
            <?php if ((int) $role['is_system'] === 1): ?>
                <span class="badge bg-secondary" title="Rôle protégé">
                    <i class="bi bi-lock-fill"></i> Système
                </span>
            <?php endif; ?>
        </h4>
        <p class="text-muted mb-0 small">Slug : <code><?= e($role['slug']) ?></code></p>
    </div>
    <a href="<?= url('roles/' . (int) $role['id']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<!-- Avertissement pour rôle système -->
<?php if ((int) $role['is_system'] === 1): ?>
    <div class="alert alert-warning d-flex align-items-start mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div>
            <strong>Rôle système</strong>
            <p class="mb-0 small">
                Le slug de ce rôle ne peut pas être modifié.
                <?php if ($role['slug'] === 'admin'): ?>
                    Les permissions du rôle <strong>admin</strong> ne peuvent pas être modifiées (il a toujours tous les droits).
                <?php endif; ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Erreurs -->
<?php if (!empty($_SESSION['_errors'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['_errors'] as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>

<form method="POST" action="<?= url('roles/' . (int) $role['id']) ?>" autocomplete="off" id="role-form">
    <?= csrf_field() ?>

    <div class="row g-3">
        <!-- Colonne principale -->
        <div class="col-lg-5">

            <!-- Informations du rôle -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle text-primary"></i> Informations du rôle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">
                            Nom du rôle <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="role-name"
                               class="form-control"
                               value="<?= e($_SESSION['_old']['name'] ?? $role['name']) ?>"
                               required
                               minlength="3"
                               maxlength="80">
                        <small class="text-muted">3 à 80 caractères</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Slug
                            <?php if ((int) $role['is_system'] === 1): ?>
                                <i class="bi bi-lock-fill text-warning" title="Non modifiable pour les rôles système"></i>
                            <?php endif; ?>
                        </label>
                        <input type="text"
                               name="slug"
                               id="role-slug"
                               class="form-control"
                               value="<?= e($_SESSION['_old']['slug'] ?? $role['slug']) ?>"
                               maxlength="80"
                               pattern="[a-z0-9\-]+"
                               <?= (int) $role['is_system'] === 1 ? 'readonly' : '' ?>>
                        <small class="text-muted">
                            <?php if ((int) $role['is_system'] === 1): ?>
                                Le slug d'un rôle système ne peut pas être modifié.
                            <?php else: ?>
                                Identifiant technique (lettres minuscules, chiffres, tirets).
                            <?php endif; ?>
                        </small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  maxlength="255"><?= e($_SESSION['_old']['description'] ?? $role['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Enregistrer les modifications
                    </button>
                    <a href="<?= url('roles/' . (int) $role['id']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Annuler
                    </a>
                </div>
            </div>

        </div>

        <!-- Colonne permissions -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-key text-primary"></i> Permissions
                        <?php if ($role['slug'] === 'admin'): ?>
                            <i class="bi bi-lock-fill text-warning" title="Le rôle admin a toujours toutes les permissions"></i>
                        <?php endif; ?>
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-perms">
                            <i class="bi bi-check-all"></i> Tout cocher
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-perms">
                            <i class="bi bi-x-lg"></i> Tout décocher
                        </button>
                    </div>
                </div>
                <div class="card-body" style="max-height: 70vh; overflow-y: auto;">

                    <?php if (empty($permissionsByModule)): ?>
                        <p class="text-muted text-center mb-0">Aucune permission disponible.</p>
                    <?php else: ?>
                        <?php foreach ($permissionsByModule as $module => $permissions): ?>
                            <?php
                            // Compte les permissions cochées pour ce module
                            $moduleChecked = 0;
                            foreach ($permissions as $p) {
                                if (in_array((int) $p['id'], $assignedPermissionIds, true)) {
                                    $moduleChecked++;
                                }
                            }
                            ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <h6 class="mb-0">
                                        <i class="bi bi-folder text-primary"></i>
                                        <?= e(ucfirst($module)) ?>
                                        <span class="badge bg-secondary ms-1"><?= count($permissions) ?></span>
                                        <?php if ($moduleChecked > 0): ?>
                                            <span class="badge bg-success ms-1">
                                                <?= $moduleChecked ?> / <?= count($permissions) ?>
                                            </span>
                                        <?php endif; ?>
                                    </h6>
                                    <button type="button"
                                            class="btn btn-sm btn-link text-decoration-none module-toggle"
                                            data-module="<?= e($module) ?>">
                                        <i class="bi bi-check2-square"></i> Inverser
                                    </button>
                                </div>

                                <div class="row g-2">
                                    <?php foreach ($permissions as $perm): ?>
                                        <?php
                                        $isChecked = in_array((int) $perm['id'], $assignedPermissionIds, true);
                                        $isAdminRole = ($role['slug'] === 'admin');
                                        ?>
                                        <div class="col-md-6">
                                            <div class="form-check p-2 border rounded hover-bg-light <?= $isChecked ? 'bg-primary-subtle' : '' ?>">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       id="perm_<?= (int) $perm['id'] ?>"
                                                       class="form-check-input permission-checkbox"
                                                       data-module="<?= e($module) ?>"
                                                       value="<?= (int) $perm['id'] ?>"
                                                       <?= $isChecked ? 'checked' : '' ?>
                                                       <?= $isAdminRole ? 'disabled' : '' ?>>
                                                <label class="form-check-label w-100" for="perm_<?= (int) $perm['id'] ?>">
                                                    <code class="small"><?= e($perm['name']) ?></code>
                                                    <?php if (!empty($perm['description'])): ?>
                                                        <div class="text-muted small"><?= e($perm['description']) ?></div>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            <span id="perm-count">
                                <?= count($assignedPermissionIds) ?>
                            </span> permission(s) sélectionnée(s)
                        </span>
                        <?php if ($role['slug'] === 'admin'): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-lock-fill"></i> Rôle admin : permissions verrouillées
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Styles -->
<style>
.hover-bg-light {
    transition: background-color 0.15s;
    cursor: pointer;
}
.hover-bg-light:hover {
    background-color: var(--bs-tertiary-bg);
}
.permission-checkbox {
    cursor: pointer;
}
.permission-checkbox:disabled {
    cursor: not-allowed;
}
</style>

<!-- Script -->
<script>
(function() {
    'use strict';

    // ============================================
    // 1. Compteur de permissions sélectionnées
    // ============================================
    const checkboxes = document.querySelectorAll('.permission-checkbox:not(:disabled)');
    const counter = document.getElementById('perm-count');

    function updateCounter() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        if (counter) counter.textContent = count;
    }

    checkboxes.forEach(cb => cb.addEventListener('change', function() {
        // Met en surbrillance la case cochée
        const wrapper = this.closest('.form-check');
        if (wrapper) {
            wrapper.classList.toggle('bg-primary-subtle', this.checked);
        }
        updateCounter();
    }));

    // ============================================
    // 2. Boutons "Tout cocher" / "Tout décocher"
    // ============================================
    const selectAllBtn = document.getElementById('select-all-perms');
    const deselectAllBtn = document.getElementById('deselect-all-perms');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => {
                cb.checked = true;
                cb.closest('.form-check')?.classList.add('bg-primary-subtle');
            });
            updateCounter();
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => {
                cb.checked = false;
                cb.closest('.form-check')?.classList.remove('bg-primary-subtle');
            });
            updateCounter();
        });
    }

    // ============================================
    // 3. Boutons "Inverser" par module
    // ============================================
    document.querySelectorAll('.module-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const module = btn.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(
                '.permission-checkbox[data-module="' + module + '"]:not(:disabled)'
            );

            moduleCheckboxes.forEach(cb => {
                cb.checked = !cb.checked;
                const wrapper = cb.closest('.form-check');
                if (wrapper) {
                    wrapper.classList.toggle('bg-primary-subtle', cb.checked);
                }
            });
            updateCounter();
        });
    });
})();
</script>

<?php unset($_SESSION['_old']); ?>