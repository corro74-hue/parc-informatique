<?php
/**
 * @var \App\Models\Document $document
 * @var \App\Models\DocumentVersion[] $versions
 */
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
            <li class="breadcrumb-item active"><?= e($document->reference ?? '#'.$document->id) ?></li>
        </ol>
    </nav>
    <a href="<?= url('documents') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-1">
            <code><?= e($document->reference ?? '#'.$document->id) ?></code>
        </h4>
        <h5 class="text-muted mb-2"><?= e($document->title) ?></h5>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <?= $document->getTypeBadge() ?>
            <?php if ($document->isSigned): ?>
                <span class="badge bg-success">
                    <i class="bi bi-check-circle-fill"></i> Signé
                </span>
            <?php endif; ?>
            <?php if ($document->hasMultipleVersions()): ?>
                <span class="badge bg-info text-dark">
                    <i class="bi bi-layers"></i> <?= (int) $document->currentVersion ?> versions
                </span>
            <?php endif; ?>
            <span class="text-muted small">
                <i class="bi <?= $document->getIcon() ?>"></i>
                <?= e($document->getFormattedSize()) ?>
            </span>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <?php if ($document->canPreview()): ?>
            <a href="<?= url('documents/' . $document->id . '/preview') ?>"
               target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-eye"></i> Prévisualiser
            </a>
        <?php endif; ?>
        <?php if (can('document.download')): ?>
            <a href="<?= url('documents/' . $document->id . '/download') ?>"
               class="btn btn-primary">
                <i class="bi bi-download"></i> Télécharger
            </a>
        <?php endif; ?>
        <?php if (can('document.edit')): ?>
            <a href="<?= url('documents/' . $document->id . '/edit') ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        <?php endif; ?>
        <?php if (can('document.delete')): ?>
            <form method="POST" action="<?= url('documents/' . $document->id . '/delete') ?>"
                  onsubmit="return confirm('Supprimer définitivement ce document et toutes ses versions ?');"
                  class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Aperçu rapide (si PDF ou image) -->
<?php if ($document->canPreview()): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-eye"></i> Aperçu</strong>
            <a href="<?= url('documents/' . $document->id . '/preview') ?>"
               target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right"></i> Ouvrir en grand
            </a>
        </div>
        <div class="card-body p-0" style="max-height: 600px; overflow: auto;">
            <?php if ($document->isPdf()): ?>
                <iframe src="<?= url('documents/' . $document->id . '/preview') ?>"
                        style="width: 100%; height: 600px; border: 0;"
                        title="Aperçu PDF"></iframe>
            <?php elseif ($document->isImage()): ?>
                <div class="text-center p-3">
                    <img src="<?= url('documents/' . $document->id . '/preview') ?>"
                         alt="<?= e($document->title) ?>"
                         style="max-width: 100%; max-height: 550px; object-fit: contain;">
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Informations -->
        <div class="card mb-4">
            <div class="card-header"><strong><i class="bi bi-info-circle"></i> Informations</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Titre</dt>
                    <dd class="col-sm-8"><?= e($document->title) ?></dd>

                    <dt class="col-sm-4">Type</dt>
                    <dd class="col-sm-8"><?= e($document->getTypeLabel()) ?></dd>

                    <?php if ($document->isAttached()): ?>
                        <dt class="col-sm-4">Rattaché à</dt>
                        <dd class="col-sm-8">
                            <?php $entityUrl = $document->getEntityUrl(); ?>
                            <?php if ($entityUrl): ?>
                                <a href="<?= url($entityUrl) ?>">
                                    <i class="bi bi-link-45deg"></i>
                                    <?= e($document->getEntityTypeLabel()) ?> #<?= (int) $document->entityId ?>
                                </a>
                            <?php else: ?>
                                <?= e($document->getEntityTypeLabel()) ?> #<?= (int) $document->entityId ?>
                            <?php endif; ?>
                        </dd>
                    <?php endif; ?>

                    <dt class="col-sm-4">Type MIME</dt>
                    <dd class="col-sm-8"><code><?= e($document->mimeType ?? '—') ?></code></dd>

                    <dt class="col-sm-4">Taille</dt>
                    <dd class="col-sm-8"><?= e($document->getFormattedSize()) ?></dd>

                    <dt class="col-sm-4">Version actuelle</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-primary">v<?= (int) $document->currentVersion ?></span>
                    </dd>

                    <dt class="col-sm-4">Signé</dt>
                    <dd class="col-sm-8">
                        <?php if ($document->isSigned): ?>
                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> Oui</span>
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-dash-circle"></i> Non</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Ajouté par</dt>
                    <dd class="col-sm-8">
                        <?= e($document->createdByName ?? '—') ?>
                        <small class="text-muted">le <?= e($document->createdAt ?? '—') ?></small>
                    </dd>

                    <dt class="col-sm-4">Dernière mise à jour</dt>
                    <dd class="col-sm-8">
                        <small class="text-muted"><?= e($document->updatedAt ?? '—') ?></small>
                    </dd>

                    <dt class="col-sm-4">Chemin fichier</dt>
                    <dd class="col-sm-8"><code class="small"><?= e($document->filePath) ?></code></dd>
                </dl>
            </div>
        </div>

        <!-- Historique des versions -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-clock-history"></i> Historique des versions (<?= count($versions) ?>)</strong>
                <?php if (can('document.edit')): ?>
                    <button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="modal" data-bs-target="#modalNewVersion">
                        <i class="bi bi-cloud-upload"></i> Nouvelle version
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($versions)): ?>
                    <div class="text-center text-muted py-4">Aucune version.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Version</th>
                                    <th>Notes</th>
                                    <th width="150">Par</th>
                                    <th width="130">Date</th>
                                    <th width="100" class="text-center">Courante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($versions as $v): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $v->version === $document->currentVersion ? 'primary' : 'secondary' ?>">
                                                v<?= (int) $v->version ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= e($v->changeNotes ?? '—') ?></small>
                                        </td>
                                        <td><small><?= e($v->createdByName ?? '—') ?></small></td>
                                        <td>
                                            <small class="text-muted" title="<?= e($v->createdAt ?? '') ?>">
                                                <?= e($v->getRelativeDate()) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($v->version === $document->currentVersion): ?>
                                                <i class="bi bi-check-circle-fill text-success" title="Version courante"></i>
                                            <?php else: ?>
                                                <?php if (can('document.edit')): ?>
                                                    <form method="POST"
                                                          action="<?= url('documents/' . $document->id . '/versions/' . $v->id . '/restore') ?>"
                                                          onsubmit="return confirm('Restaurer cette version comme version courante ?');"
                                                          class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                title="Restaurer cette version">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><i class="bi bi-tools"></i> Actions rapides</strong></div>
            <div class="card-body">
                <?php if (can('document.edit')): ?>
                    <form method="POST" action="<?= url('documents/' . $document->id . '/toggle-signed') ?>"
                          class="mb-2">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-<?= $document->isSigned ? 'outline-warning' : 'outline-success' ?> w-100">
                            <?php if ($document->isSigned): ?>
                                <i class="bi bi-x-circle"></i> Marquer comme non signé
                            <?php else: ?>
                                <i class="bi bi-check-circle"></i> Marquer comme signé
                            <?php endif; ?>
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (can('document.download')): ?>
                    <a href="<?= url('documents/' . $document->id . '/download') ?>"
                       class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-download"></i> Télécharger le fichier
                    </a>
                <?php endif; ?>

                <?php if ($document->canPreview()): ?>
                    <a href="<?= url('documents/' . $document->id . '/preview') ?>"
                       target="_blank" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-eye"></i> Ouvrir dans un onglet
                    </a>
                <?php endif; ?>

                <?php if ($document->isAttached() && $document->getEntityUrl()): ?>
                    <a href="<?= url($document->getEntityUrl()) ?>"
                       class="btn btn-outline-secondary w-100 mb-0">
                        <i class="bi bi-link-45deg"></i> Voir l'entité liée
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal : Nouvelle version -->
<div class="modal fade" id="modalNewVersion" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('documents/' . $document->id . '/versions') ?>"
              enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cloud-upload"></i> Nouvelle version</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nouveau fichier <span class="text-danger">*</span></label>
                    <input type="file" name="file" required class="form-control"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar,.7z,.gz">
                    <div class="form-text small">
                        Taille max : 50 Mo. Le fichier actuel est conservé dans l'historique.
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes de changement</label>
                    <textarea name="change_notes" rows="3" class="form-control"
                              placeholder="Ex : Correction du montant, mise à jour des signatures..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Uploader la version
                </button>
            </div>
        </form>
    </div>
</div>