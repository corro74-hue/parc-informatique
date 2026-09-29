<?php
/**
 * Vue : Formulaire de création d'un employé
 *
 * @var string $title
 * @var array $services
 * @var array $old
 * @var array $errors
 */
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('employees') ?>" class="text-decoration-none">
                <i class="bi bi-person-badge"></i> Employés
            </a>
        </li>
        <li class="breadcrumb-item active">Nouvel employé</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-person-plus-fill text-primary"></i>
            Nouvel employé
        </h4>
        <p class="text-muted mb-0 small">Remplissez les informations ci-dessous</p>
    </div>
    <a href="<?= url('employees') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $field => $message): ?>
                <li><?= e(is_array($message) ? implode(' ', $message) : $message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('employees') ?>">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label for="first_name" class="form-label">
                        Prénom <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="first_name" id="first_name" class="form-control"
                           value="<?= e((string) old('first_name')) ?>" required maxlength="100">
                </div>

                <div class="col-md-6">
                    <label for="last_name" class="form-label">
                        Nom <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="last_name" id="last_name" class="form-control"
                           value="<?= e((string) old('last_name')) ?>" required maxlength="100">
                </div>

                <div class="col-md-6">
                    <label for="matricule" class="form-label">Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="form-control"
                           value="<?= e((string) old('matricule')) ?>" maxlength="50"
                           placeholder="Ex : EMP-001">
                    <small class="text-muted">Doit être unique dans la base.</small>
                </div>

                <div class="col-md-6">
                    <label for="service_id" class="form-label">Service</label>
                    <select name="service_id" id="service_id" class="form-select">
                        <option value="">— Sélectionner un service —</option>
                        <?php foreach ($services as $srv): ?>
                            <option value="<?= (int) $srv['id'] ?>"
                                    <?= (string) old('service_id') === (string) $srv['id'] ? 'selected' : '' ?>>
                                <?= e($srv['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= e((string) old('email')) ?>" maxlength="180"
                           placeholder="prenom.nom@example.com">
                    <small class="text-muted">Doit être unique dans la base.</small>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input type="text" name="phone" id="phone" class="form-control"
                           value="<?= e((string) old('phone')) ?>" maxlength="30"
                           placeholder="Ex : 0555 12 34 56">
                </div>

                <div class="col-md-6">
                    <label for="function_title" class="form-label">Fonction / Poste</label>
                    <input type="text" name="function_title" id="function_title" class="form-control"
                           value="<?= e((string) old('function_title')) ?>" maxlength="150"
                           placeholder="Ex : Technicien informatique">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input"
                               value="1" <?= old('is_active') === null || old('is_active') ? 'checked' : '' ?>>
                        <label for="is_active" class="form-check-label">
                            Employé actif (visible dans les listes)
                        </label>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-end gap-2">
            <a href="<?= url('employees') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Créer l'employé
            </button>
        </div>
    </div>
</form>