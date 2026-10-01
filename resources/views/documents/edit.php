<?php
/**
 * @var \App\Models\Document $document
 * @var array $errors
 * @var array $old
 */
$typeLabels   = \App\Models\Document::typeLabels();
$entityLabels = \App\Models\Document::entityTypeLabels();
$data = !empty($old) ? $old : [
    'title'       => $document->title,
    'type'        => $document->type,
    'entity_type' => $document->entityType,
    'entity_id'   => $document->entityId,
    'is_signed'   => $document->isSigned,
];
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
            <li class="breadcrumb-item">
                <a href="<?= url('documents/' . $document->id) ?>">
                    <?= e($document->reference ?? '#'.$document->id) ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>
    <a href="<?= url('documents/' . $document->id) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour au document
    </a>
</div>

<!-- En-tête -->
<div class="mb-3">
    <h4 class="mb-0">
        <i class="bi bi-pencil text-primary"></i>
        Modifier le document
        <span class="badge bg-secondary"><?= e($document->reference ?? '#'.$document->id) ?></span>
    </h4>
    <p class="text-muted mb-0 small">
        Modifiez les métadonnées. Pour changer le fichier, uploadez une <strong>nouvelle version</strong>.
    </p>
</div>

<?php if (!empty($errors['global'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($errors['global']) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('documents/' . $document->id) ?>">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-info-circle"></i> Informations générales</strong>
        </div>
        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="title" class="form-label">
                        Titre <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e($data['title'] ?? '') ?>"
                           class="form-control <?= !empty($errors['title']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="type" class="form-label">
                        Type <span class="text-danger">*</span>
                    </label>
                    <select id="type" name="type" required class="form-select">
                        <?php foreach ($typeLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($data['type'] ?? '') === $key ? 'selected' : '' ?>>
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
                            <option value="<?= e($key) ?>" <?= ($data['entity_type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="entity_id" class="form-label">ID de l'entité liée</label>
                    <input type="number" id="entity_id" name="entity_id" min="0"
                           value="<?= (int) ($data['entity_id'] ?? 0) ?: '' ?>"
                           placeholder="Ex : 42"
                           class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Signature</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_signed" name="is_signed" value="1"
                               <?= !empty($data['is_signed']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_signed">
                            Document signé
                        </label>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Info : rappel du fichier -->
    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-file-earmark"></i> Fichier actuel</strong>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <i class="bi <?= $document->getIcon() ?>" style="font-size: 2rem; color:#6b7280;"></i>
                <div>
                    <div><code><?= e($document->filePath) ?></code></div>
                    <div class="small text-muted">
                        Taille : <?= e($document->getFormattedSize()) ?> ·
                        Type : <?= e($document->mimeType ?? '—') ?> ·
                        Version courante : <strong>v<?= (int) $document->currentVersion ?></strong>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="bi bi-info-circle"></i>
                Pour remplacer le fichier, uploadez une <strong>nouvelle version</strong> depuis la page du document.
            </div>
        </div>
    </div>

    <!-- Boutons -->
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= url('documents/' . $document->id) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Enregistrer
        </button>
    </div>
</form>