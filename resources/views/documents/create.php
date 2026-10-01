<?php
/**
 * @var array $errors
 * @var array $old
 * @var string $entityType
 * @var int $entityId
 */
$typeLabels   = \App\Models\Document::typeLabels();
$entityLabels = \App\Models\Document::entityTypeLabels();
?>

<!-- Fil d'Ariane -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= url('documents') ?>">
                    <i class="bi bi-file-earmark-text"></i> Documents
                </a>
            </li>
            <li class="breadcrumb-item active">Nouveau document</li>
        </ol>
    </nav>
    <a href="<?= url('documents') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<!-- En-tête -->
<div class="mb-3">
    <h4 class="mb-0">
        <i class="bi bi-cloud-upload text-primary"></i>
        Nouveau document
    </h4>
    <p class="text-muted mb-0 small">
        Uploadez un fichier (50 Mo max). Les types acceptés : PDF, DOCX, XLSX, images, ZIP…
    </p>
</div>

<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($errors['global']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('documents') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-info-circle"></i> Informations générales</strong>
        </div>
        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="title" class="form-label">
                        Titre du document <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e($old['title'] ?? '') ?>"
                           placeholder="Ex : Facture Dell mars 2026"
                           class="form-control <?= !empty($errors['title']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="type" class="form-label">
                        Type de document <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type" required class="form-select">
                        <?php foreach ($typeLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($old['type'] ?? 'other') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="entity_type" class="form-label">Rattacher à</label>
                    <select id="entity_type" name="entity_type" class="form-select">
                        <option value="">— Aucun rattachement —</option>
                        <?php foreach ($entityLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($entityType ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="entity_id" class="form-label">ID de l'entité liée</label>
                    <input type="number" id="entity_id" name="entity_id" min="0"
                           value="<?= (int) ($entityId ?? 0) ?: '' ?>"
                           placeholder="Ex : 42"
                           class="form-control">
                    <div class="form-text small">
                        L'ID numérique de l'entité (équipement, maintenance…). Laissez vide si aucun rattachement.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Upload du fichier -->
    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-cloud-upload"></i> Fichier à uploader</strong>
        </div>
        <div class="card-body">
            <div class="mb-0">
                <label for="file" class="form-label">
                    Sélectionner un fichier <span class="text-danger">*</span>
                </label>
                <input type="file" id="file" name="file" required
                       class="form-control <?= !empty($errors['file']) ? 'is-invalid' : '' ?>"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar,.7z,.gz">
                <?php if (!empty($errors['file'])): ?>
                    <div class="invalid-feedback"><?= e($errors['file']) ?></div>
                <?php endif; ?>
                <div class="form-text small">
                    <i class="bi bi-info-circle"></i>
                    Taille maximale : <strong>50 Mo</strong>.
                    Types autorisés : PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, TXT, CSV, JPG, PNG, GIF, WEBP, ZIP, RAR, 7Z, GZ.
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons -->
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= url('documents') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Enregistrer le document
        </button>
    </div>
</form>