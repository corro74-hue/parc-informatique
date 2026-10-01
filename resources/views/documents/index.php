<?php
/**
 * Vue : Liste des documents
 *
 * @var array $paginated
 * @var array $filters
 * @var array $stats
 * @var int $totalAll
 * @var int $totalSize
 */
$documents  = $paginated['data'];
$total      = (int) $paginated['total'];
$page       = (int) $paginated['page'];
$lastPage   = (int) $paginated['last_page'];
$typeLabels = \App\Models\Document::typeLabels();
$entityLabels = \App\Models\Document::entityTypeLabels();
?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- PANNEAU D'AIDE — Comment ça marche ?                     -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center"
         style="cursor:pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#docHelp"
         aria-expanded="true"
         aria-controls="docHelp">
        <strong><i class="bi bi-info-circle text-primary"></i> Comment ça marche ?</strong>
        <i class="bi bi-chevron-up" id="docHelpIcon"></i>
    </div>

    <div class="collapse show" id="docHelp">
        <div class="card-body pt-0">
            <div class="row g-4">

                <!-- Colonne gauche -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-bullseye"></i> À quoi ça sert ?
                        </h6>
                        <p class="small text-muted mb-0">
                            La <strong>GED</strong> (Gestion Électronique de Documents) centralise tous les
                            fichiers liés à votre parc : <strong>factures</strong>, <strong>garanties</strong>,
                            <strong>PV de réforme</strong>, <strong>bons de sortie</strong>, <strong>rapports</strong>…
                            Chaque document peut être rattaché à un équipement, une maintenance, une réforme,
                            un employé ou autre, et dispose d'un <strong>versioning</strong> intégré.
                        </p>
                    </div>

                    <div>
                        <h6 class="text-warning mb-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> Précautions
                        </h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Taille maximale par fichier : <strong>50 Mo</strong>.</li>
                            <li>Types autorisés : PDF, DOCX, XLSX, images, archives ZIP…</li>
                            <li>Les fichiers sont stockés <strong>hors du dossier public</strong> (sécurité).</li>
                            <li>La suppression d'un document supprime <strong>toutes ses versions</strong>.</li>
                            <li>Seul un utilisateur avec la permission <code>document.delete</code> peut supprimer.</li>
                        </ul>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <h6 class="text-info mb-2">
                            <i class="bi bi-tools"></i> Comment l'utiliser ?
                        </h6>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Cliquez sur <strong>« Nouveau document »</strong>.</li>
                            <li>Choisissez un fichier (ou glissez-déposez).</li>
                            <li>Donnez un <strong>titre</strong> et un <strong>type</strong>.</li>
                            <li>Rattachez-le à une entité (optionnel).</li>
                            <li>Cliquez sur <strong>« Enregistrer »</strong>.</li>
                            <li>Uploadez une <strong>nouvelle version</strong> depuis la fiche.</li>
                            <li>Marquez comme <strong>signé</strong> si nécessaire.</li>
                            <li>Téléchargez ou prévisualisez à tout moment.</li>
                        </ol>
                    </div>

                    <div>
                        <h6 class="text-success mb-2">
                            <i class="bi bi-lightbulb-fill"></i> Bonnes pratiques
                        </h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Utilisez des <strong>titres clairs</strong> (ex: « Facture Dell mars 2026 »).</li>
                            <li>Rattachez chaque document à son <strong>équipement</strong> pour traçabilité.</li>
                            <li>Marquez les documents contractuels comme <strong>signés</strong>.</li>
                            <li>Consultez régulièrement le <strong>type de doc</strong> pour filtrer.</li>
                            <li>Pour les <strong>gros PDFs scannés</strong>, utilisez le format PDF compressé.</li>
                            <li>Utilisez les <strong>actions groupées</strong> pour télécharger/supprimer en masse.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-file-earmark-text text-primary"></i>
            Documents
            <span class="badge bg-secondary"><?= $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= $total ?> document<?= $total > 1 ? 's' : '' ?>
            — page <?= $page ?> sur <?= $lastPage ?>
            — taille totale : <strong><?= number_format($totalSize / 1024 / 1024, 2, ',', ' ') ?> Mo</strong>
        </p>
    </div>
    <?php if (can('document.create')): ?>
        <a href="<?= url('documents/create') ?>" class="btn btn-primary">
            <i class="bi bi-cloud-upload"></i> Nouveau document
        </a>
    <?php endif; ?>
</div>

<!-- Stats par type -->
<?php if (!empty($stats)): ?>
    <div class="row g-2 mb-3">
        <?php
        $typeCards = [
            'prv_reform'   => ['label' => 'PV de réforme', 'color' => '#8b5cf6', 'icon' => 'file-earmark-pdf'],
            'exit_voucher' => ['label' => 'Bons de sortie', 'color' => '#06b6d4', 'icon' => 'file-earmark-arrow-down'],
            'invoice'      => ['label' => 'Factures',      'color' => '#f59e0b', 'icon' => 'receipt'],
            'report'       => ['label' => 'Rapports',      'color' => '#3b82f6', 'icon' => 'bar-chart'],
            'other'        => ['label' => 'Autres',        'color' => '#6b7280', 'icon' => 'file-earmark'],
        ];
        foreach ($typeCards as $code => $meta):
            $count = (int) ($stats[$code] ?? 0);
        ?>
            <div class="col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3" style="font-size: 1.5rem; color: <?= $meta['color'] ?>;">
                            <i class="bi bi-<?= $meta['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="h5 mb-0"><?= $count ?></div>
                            <div class="text-muted small"><?= $meta['label'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('documents') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Recherche</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Titre, référence..."
                       value="<?= e((string) ($filters['search'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <?php foreach ($typeLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($filters['type'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Rattaché à</label>
                <select name="entity_type" class="form-select">
                    <option value="">Toutes les entités</option>
                    <?php foreach ($entityLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($filters['entity_type'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['type']) || !empty($filters['entity_type'])): ?>
                    <a href="<?= url('documents') ?>" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" id="documents-table">
            <thead class="table-light">
                <tr>
                    <th width="40">
                        <input type="checkbox" class="form-check-input" id="select-all-docs"
                               title="Tout sélectionner">
                    </th>
                    <th width="50"></th>
                    <th width="150">Référence</th>
                    <th>Titre</th>
                    <th width="140" class="text-center">Type</th>
                    <th width="140">Rattaché à</th>
                    <th width="110" class="text-end">Taille</th>
                    <th width="80" class="text-center">Signé</th>
                    <th width="140" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun document trouvé.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['type']) || !empty($filters['entity_type'])): ?>
                                <a href="<?= url('documents') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('documents/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-cloud-upload"></i> Ajouter le premier document
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input doc-checkbox"
                                       value="<?= (int) $doc->id ?>">
                            </td>
                            <td class="text-center">
                                <i class="bi <?= $doc->getIcon() ?>" style="font-size:1.5rem; color:#6b7280;"></i>
                            </td>
                            <td>
                                <a href="<?= url('documents/' . $doc->id) ?>" class="text-decoration-none fw-semibold">
                                    <code><?= e($doc->reference ?? '#'.$doc->id) ?></code>
                                </a>
                            </td>
                            <td>
                                <div class="fw-medium"><?= e($doc->title) ?></div>
                                <?php if ($doc->createdByName): ?>
                                    <div class="small text-muted">
                                        par <?= e($doc->createdByName) ?>
                                        le <?= e(date('d/m/Y', strtotime($doc->createdAt ?? 'now'))) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $doc->getTypeBadge() ?></td>
                            <td>
                                <?php if ($doc->isAttached()): ?>
                                    <?php $entityUrl = $doc->getEntityUrl(); ?>
                                    <?php if ($entityUrl): ?>
                                        <a href="<?= url($entityUrl) ?>" class="text-decoration-none small">
                                            <i class="bi bi-link-45deg"></i>
                                            <?= e($doc->getEntityTypeLabel()) ?> #<?= (int) $doc->entityId ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="small text-muted"><?= e($doc->getEntityTypeLabel()) ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <small><?= e($doc->getFormattedSize()) ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($doc->isSigned): ?>
                                    <i class="bi bi-check-circle-fill text-success" title="Signé"></i>
                                <?php else: ?>
                                    <i class="bi bi-dash-circle text-muted" title="Non signé"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('documents/' . $doc->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (can('document.download')): ?>
                                    <a href="<?= url('documents/' . $doc->id . '/download') ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Télécharger">
                                        <i class="bi bi-download"></i>
                                    </a>
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
<?php if ($lastPage > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <?php $queryParams = array_filter($filters, fn($v) => $v !== ''); ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $page + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- BARRE D'ACTIONS GROUPÉES FLOTTANTE                      -->
<!-- ═══════════════════════════════════════════════════════ -->
<div id="doc-bulk-bar" class="bulk-actions-bar">
    <div class="bulk-bar-content">
        <div class="bulk-bar-info">
            <i class="bi bi-check2-square"></i>
            <span id="doc-selected-count">0</span> document(s) sélectionné(s)
        </div>
        <div class="bulk-bar-actions">
            <?php if (can('document.download')): ?>
                <form method="POST" action="<?= url('documents/bulk/download') ?>" class="d-inline" id="form-bulk-download">
                    <?= csrf_field() ?>
                    <div id="bulk-download-ids"></div>
                    <button type="submit" class="btn btn-sm btn-light">
                        <i class="bi bi-file-earmark-zip"></i> Télécharger ZIP
                    </button>
                </form>
            <?php endif; ?>
            <?php if (can('document.delete')): ?>
                <form method="POST" action="<?= url('documents/bulk/delete') ?>" class="d-inline" id="form-bulk-delete"
                      onsubmit="return confirm('Supprimer tous les documents sélectionnés ? Cette action est irréversible.');">
                    <?= csrf_field() ?>
                    <div id="bulk-delete-ids"></div>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </form>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-light" id="doc-bulk-clear">
                <i class="bi bi-x"></i> Désélectionner
            </button>
        </div>
    </div>
</div>

<script>
    (function() {
        const el = document.getElementById('docHelp');
        const icon = document.getElementById('docHelpIcon');
        if (el && icon) {
            el.addEventListener('show.bs.collapse', () => {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            });
            el.addEventListener('hide.bs.collapse', () => {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            });
        }
    })();
</script>

<!-- Script actions groupées -->
<script src="<?= url('assets/js/documents-bulk.js') ?>"></script>