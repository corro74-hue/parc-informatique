<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item active">
            <i class="bi bi-upload"></i> Importer
        </li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-upload text-primary"></i>
            Importer des équipements
        </h4>
        <p class="text-muted mb-0 small">
            Importez un fichier CSV pour ajouter plusieurs équipements en une seule fois.
        </p>
    </div>
    <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<?php if (empty($preview)): ?>
    <!-- ============================================ -->
    <!-- ÉTAPE 1 : Formulaire d'upload                -->
    <!-- ============================================ -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-earmark-arrow-up text-primary"></i>
                        Sélectionner un fichier CSV
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= url('equipment/import/preview') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fichier CSV <span class="text-danger">*</span></label>
                            <input type="file"
                                   name="csv_file"
                                   class="form-control"
                                   accept=".csv,.txt"
                                   required>
                            <small class="text-muted d-block mt-1">
                                Format accepté : <strong>.csv</strong> ou <strong>.txt</strong> — Taille max : 5 Mo
                            </small>
                        </div>

                        <div class="alert alert-info d-flex align-items-start">
                            <i class="bi bi-info-circle-fill fs-5 me-2 mt-1"></i>
                            <div>
                                <strong>Conseils :</strong>
                                <ul class="mb-0 small mt-1">
                                    <li>Utilisez le <strong>séparateur point-virgule (;)</strong></li>
                                    <li>Encodage recommandé : <strong>UTF-8</strong></li>
                                    <li>Les lignes en erreur seront <strong>ignorées</strong></li>
                                    <li>Les N° d'inventaire vides seront <strong>auto-générés</strong></li>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Analyser le fichier
                            </button>
                            <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-download text-success"></i>
                        Modèle CSV
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Téléchargez un modèle pré-rempli avec les bonnes colonnes et 2 exemples.
                    </p>
                    <a href="<?= url('equipment/import/template') ?>" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-arrow-down"></i> Télécharger le modèle
                    </a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-list-check text-primary"></i>
                        Colonnes attendues
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        <li><code>inventory_number</code></li>
                        <li><code>designation</code> <span class="text-danger">*</span></li>
                        <li><code>category_code</code> <span class="text-danger">*</span></li>
                        <li><code>brand_name</code></li>
                        <li><code>model_text</code></li>
                        <li><code>serial_number</code></li>
                        <li><code>service_name</code></li>
                        <li><code>site_name</code></li>
                        <li><code>status_code</code> <span class="text-danger">*</span></li>
                        <li><code>acquisition_date</code></li>
                        <li><code>acquisition_value</code></li>
                        <li><code>warranty_end_date</code></li>
                    </ul>
                    <hr>
                    <p class="small text-muted mb-0">
                        <span class="text-danger">*</span> Champs obligatoires
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ============================================ -->
    <!-- ÉTAPE 2 : Aperçu + validation                -->
    <!-- ============================================ -->
    <div class="alert alert-info d-flex align-items-start mb-3">
        <i class="bi bi-file-earmark-text fs-4 me-2"></i>
        <div>
            <strong>Fichier analysé :</strong> <?= e($filename ?? 'fichier.csv') ?><br>
            <strong><?= (int) ($report['total'] ?? 0) ?></strong> ligne(s) détectée(s) —
            <strong class="text-success"><?= (int) ($report['valid'] ?? 0) ?> valide(s)</strong> —
            <strong class="text-danger"><?= count($report['errors'] ?? []) ?> en erreur</strong>
        </div>
    </div>

    <?php if (!empty($report['errors'])): ?>
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="mb-0 text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Erreurs détectées (<?= count($report['errors']) ?>)
                </h6>
            </div>
            <div class="card-body">
                <div style="max-height: 200px; overflow-y: auto;">
                    <ul class="mb-0 small">
                        <?php foreach ($report['errors'] as $err): ?>
                            <li>
                                <strong>Ligne <?= (int) $err['line'] ?> :</strong>
                                <?= e($err['message']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-table text-primary"></i>
                Aperçu des lignes (<?= count($preview) ?>)
            </h6>
            <div class="d-flex gap-2">
                <a href="<?= url('equipment/import') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Annuler
                </a>
                <form method="POST" action="<?= url('equipment/import/store') ?>" class="m-0 d-inline">
                    <?= csrf_field() ?>
                    <button type="submit"
                            class="btn btn-success btn-sm"
                            <?= empty($report['valid']) ? 'disabled' : '' ?>
                            onclick="return confirm('Importer <?= (int) ($report['valid'] ?? 0) ?> équipement(s) ?');">
                        <i class="bi bi-check-circle"></i>
                        Importer <?= (int) ($report['valid'] ?? 0) ?> équipement(s)
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 550px;">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="60" class="text-center">Ligne</th>
                            <th width="60" class="text-center">État</th>
                            <th>N° inv.</th>
                            <th>Désignation</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Erreurs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview as $item): ?>
                            <tr class="<?= $item['is_valid'] ? '' : 'table-danger' ?>">
                                <td class="text-center">
                                    <small class="text-muted"><?= (int) $item['line'] ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['is_valid']): ?>
                                        <i class="bi bi-check-circle-fill text-success" title="Valide"></i>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle-fill text-danger" title="Erreur"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code><?= e($item['data']['inventory_number'] ?? '—') ?></code>
                                </td>
                                <td>
                                    <div class="fw-semibold small"><?= e($item['data']['designation'] ?? '') ?></div>
                                    <?php if (!empty($item['data']['serial_number'])): ?>
                                        <small class="text-muted">S/N : <?= e($item['data']['serial_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= e($item['data']['category_id'] ? '✓' : '—') ?></small>
                                </td>
                                <td>
                                    <small><?= e($item['data']['status_id'] ? '✓' : '—') ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($item['errors'])): ?>
                                        <small class="text-danger"><?= e(implode(' | ', $item['errors'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-success">OK</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>