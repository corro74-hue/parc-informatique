<?php
/**
 * @var array $errors
 * @var array $old
 */
$reasonLabels = \App\Models\Reformation::reasonLabels();
?>

<!-- Fil d'Ariane + Bouton retour -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= url('reformations') ?>">
                    <i class="bi bi-recycle"></i> Réformes
                </a>
            </li>
            <li class="breadcrumb-item active">Nouvelle réforme</li>
        </ol>
    </nav>
    <a href="<?= url('reformations') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<!-- En-tête -->
<div class="mb-3">
    <h4 class="mb-0">
        <i class="bi bi-plus-circle text-primary"></i>
        Nouvelle réforme
    </h4>
    <p class="text-muted mb-0 small">
        Créez le brouillon. Vous pourrez ajouter les équipements concernés après.
    </p>
</div>

<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($errors['global']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('reformations') ?>">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-info-circle"></i> Informations générales</strong>
        </div>
        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="title" class="form-label">
                        Titre de la réforme <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e($old['title'] ?? '') ?>"
                           placeholder="Ex : Réforme des PC obsolètes 2026"
                           class="form-control <?= !empty($errors['title']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="reason" class="form-label">
                        Motif principal <span class="text-danger">*</span>
                    </label>
                    <select id="reason" name="reason" required
                            class="form-select <?= !empty($errors['reason']) ? 'is-invalid' : '' ?>">
                        <?php foreach ($reasonLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($old['reason'] ?? 'obsolescence') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['reason'])): ?>
                        <div class="invalid-feedback"><?= e($errors['reason']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="meeting_date" class="form-label">Date de réunion</label>
                    <input type="date" id="meeting_date" name="meeting_date"
                           value="<?= e($old['meeting_date'] ?? '') ?>"
                           class="form-control">
                </div>

                <div class="col-md-12">
                    <label for="reason_details" class="form-label">Détails du motif</label>
                    <textarea id="reason_details" name="reason_details" rows="4" maxlength="2000"
                              placeholder="Précisez les raisons (état des équipements, contexte, etc.)"
                              class="form-control"><?= e($old['reason_details'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="commission_reference" class="form-label">Référence commission</label>
                    <input type="text" id="commission_reference" name="commission_reference" maxlength="100"
                           value="<?= e($old['commission_reference'] ?? '') ?>"
                           placeholder="Ex : COM-2026-001"
                           class="form-control">
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= url('reformations') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Créer la réforme
        </button>
    </div>
</form>