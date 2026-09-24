<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('roles') ?>" class="text-decoration-none">
                <i class="bi bi-shield-lock"></i> Rôles
            </a>
        </li>
        <li class="breadcrumb-item active">Nouveau rôle</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-shield-plus text-primary"></i>
            Nouveau rôle
        </h4>
        <p class="text-muted mb-0 small">Créer un nouveau rôle et lui assigner des permissions</p>
    </div>
    <a href="<?= url('roles') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

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

<form method="POST" action="<?= url('roles') ?>" autocomplete="off" id="role-form">
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
                               value="<?= e($_SESSION['_old']['name'] ?? '') ?>"
                               required
                               minlength="3"
                               maxlength="80"
                               placeholder="Ex: Superviseur">
                        <small class="text-muted">3 à 80 caractères</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug (optionnel)</label>
                        <input type="text"
                               name="slug"
                               id="role-slug"
                               class="form-control"
                               value="<?= e($_SESSION['_old']['slug'] ?? '') ?>"
                               maxlength="80"
                               pattern="[a-z0-9\-]+"
                               placeholder="superviseur">
                        <small class="text-muted">
                            Identifiant technique (lettres minuscules, chiffres, tirets).
                            Laissez vide pour générer automatiquement.
                        </small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"
                                  maxlength="255"
                                  placeholder="Description courte du rôle..."><?= e($_SESSION['_old']['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Créer le rôle
                    </button>
                    <a href="<?= url('roles') ?>" class="btn btn-outline-secondary">
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
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <h6 class="mb-0">
                                        <i class="bi bi-folder text-primary"></i>
                                        <?= e(ucfirst($module)) ?>
                                        <span class="badge bg-secondary ms-1"><?= count($permissions) ?></span>
                                    </h6>
                                    <button type="button"
                                            class="btn btn-sm btn-link text-decoration-none module-toggle"
                                            data-module="<?= e($module) ?>">
                                        <i class="bi bi-check2-square"></i> Tout
                                    </button>
                                </div>

                                <div class="row g-2">
                                    <?php foreach ($permissions as $perm): ?>
                                        <div class="col-md-6">
                                            <div class="form-check p-2 border rounded hover-bg-light">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       id="perm_<?= (int) $perm['id'] ?>"
                                                       class="form-check-input permission-checkbox"
                                                       data-module="<?= e($module) ?>"
                                                       value="<?= (int) $perm['id'] ?>">
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
                            <span id="perm-count">0</span> permission(s) sélectionnée(s)
                        </span>
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
</style>

<!-- Script pour l'auto-slug + compteur + tout cocher -->
<script>
(function() {
    'use strict';

    // ============================================
    // 1. Auto-génération du slug depuis le nom
    // ============================================
    const nameInput = document.getElementById('role-name');
    const slugInput = document.getElementById('role-slug');
    let slugManuallyEdited = false;

    if (slugInput) {
        slugInput.addEventListener('input', () => {
            slugManuallyEdited = slugInput.value.trim() !== '';
        });
    }

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', () => {
            if (slugManuallyEdited) return;

            const slug = nameInput.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        });
    }

    // ============================================
    // 2. Compteur de permissions sélectionnées
    // ============================================
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const counter = document.getElementById('perm-count');

    function updateCounter() {
        const count = document.querySelectorAll('.permission-checkbox:checked').length;
        if (counter) counter.textContent = count;
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateCounter));
    updateCounter(); // Init

    // ============================================
    // 3. Boutons "Tout cocher" / "Tout décocher"
    // ============================================
    const selectAllBtn = document.getElementById('select-all-perms');
    const deselectAllBtn = document.getElementById('deselect-all-perms');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = true);
            updateCounter();
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = false);
            updateCounter();
        });
    }

    // ============================================
    // 4. Boutons "Tout" par module
    // ============================================
    document.querySelectorAll('.module-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const module = btn.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(
                '.permission-checkbox[data-module="' + module + '"]'
            );

            // Si tous sont cochés → décocher, sinon → cocher
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);

            moduleCheckboxes.forEach(cb => cb.checked = !allChecked);
            updateCounter();
        });
    });
})();
</script>

<?php unset($_SESSION['_old']); ?>