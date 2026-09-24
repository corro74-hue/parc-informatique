<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item active"><?= e($equipment->inventoryNumber) ?></li>
    </ol>
</nav>

<!-- En-tête avec actions -->
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-box-seam text-primary"></i>
            <?= e($equipment->inventoryNumber) ?>
            <?= $equipment->getStatusBadge() ?>
        </h4>
        <p class="text-muted mb-0"><?= e($equipment->designation) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>

        <!-- Bouton Fiche PDF -->
        <a href="<?= url('equipment/' . $equipment->id . '/show-pdf') ?>"
           class="btn btn-danger btn-sm"
           target="_blank"
           title="Aperçu avant impression de la fiche">
            <i class="bi bi-file-earmark-pdf"></i> Fiche PDF
        </a>

        <!-- Bouton Historique -->
        <a href="<?= url('equipment/' . $equipment->id . '/history') ?>"
           class="btn btn-outline-info btn-sm"
           title="Voir l'historique des modifications">
            <i class="bi bi-clock-history"></i> Historique
        </a>

        <a href="<?= url('equipment/' . $equipment->id . '/edit') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <form method="POST" action="<?= url('equipment/' . $equipment->id . '/delete') ?>"
              class="d-inline"
              onsubmit="return confirm('Supprimer définitivement cet équipement ?\n\nCette action est réversible par un administrateur.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<!-- Alerte si sous garantie -->
<?php if ($equipment->isUnderWarranty()): ?>
    <div class="alert alert-success d-flex align-items-center">
        <i class="bi bi-shield-check fs-4 me-2"></i>
        <div>
            <strong>Équipement sous garantie</strong>
            jusqu'au <?= e(date('d/m/Y', strtotime($equipment->warrantyEndDate))) ?>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">

    <!-- Colonne principale (2/3) -->
    <div class="col-lg-8">

        <!-- Informations générales -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary"></i> Informations générales
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">N° d'inventaire</label>
                        <strong><?= e($equipment->inventoryNumber) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Désignation</label>
                        <strong><?= e($equipment->designation) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Catégorie</label>
                        <span class="badge bg-light text-dark border">
                            <?= e($equipment->categoryName ?? '—') ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Statut</label>
                        <?= $equipment->getStatusBadge() ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Marque</label>
                        <?= e($equipment->brandName ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Modèle</label>
                        <?= e($equipment->modelText ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">N° de série</label>
                        <code><?= e($equipment->serialNumber ?? '—') ?></code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affectation -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-geo-alt text-primary"></i> Affectation actuelle
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Site</label>
                        <?= e($equipment->siteName ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Service</label>
                        <?= e($equipment->serviceName ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Localisation</label>
                        <?= e($equipment->locationName ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Responsable</label>
                        <?= e($equipment->responsibleName ?? '—') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spécifications et notes -->
        <?php if ($equipment->technicalSpecs || $equipment->notes): ?>
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-file-text text-primary"></i> Spécifications et notes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($equipment->technicalSpecs): ?>
                        <label class="text-muted small d-block">Spécifications techniques</label>
                        <p class="mb-3"><?= nl2br(e($equipment->technicalSpecs)) ?></p>
                    <?php endif; ?>
                    <?php if ($equipment->notes): ?>
                        <label class="text-muted small d-block">Notes</label>
                        <p class="mb-0"><?= nl2br(e($equipment->notes)) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historique (lien vers la page dédiée) -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history text-primary"></i> Historique
                </h6>
                <a href="<?= url('equipment/' . $equipment->id . '/history') ?>"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right"></i> Voir tout
                </a>
            </div>
            <div class="card-body text-center py-4">
                <i class="bi bi-clock-history text-primary" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-2">Consultez l'historique complet des modifications</p>
                <a href="<?= url('equipment/' . $equipment->id . '/history') ?>"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-clock-history"></i> Voir l'historique
                </a>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- NOUVEAU : Pièces jointes                     -->
        <!-- ============================================ -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-paperclip text-primary"></i> Pièces jointes
                    <span class="badge bg-secondary"><?= count($attachments ?? []) ?></span>
                </h6>
            </div>
            <div class="card-body">

                <!-- Liste des pièces jointes existantes -->
                <?php if (!empty($attachments)): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($attachments as $att): ?>
                            <?php
                            // Choisir une icône selon le type MIME
                            $icon = 'file-earmark';
                            $iconColor = '#6c757d';
                            if (str_starts_with((string) $att['mime_type'], 'image/')) {
                                $icon = 'file-earmark-image';
                                $iconColor = '#0d6efd';
                            } elseif ($att['mime_type'] === 'application/pdf') {
                                $icon = 'file-earmark-pdf';
                                $iconColor = '#dc3545';
                            } elseif (str_contains((string) $att['mime_type'], 'word') || str_contains((string) $att['mime_type'], 'document')) {
                                $icon = 'file-earmark-word';
                                $iconColor = '#2b579a';
                            } elseif (str_contains((string) $att['mime_type'], 'sheet') || str_contains((string) $att['mime_type'], 'excel')) {
                                $icon = 'file-earmark-excel';
                                $iconColor = '#217346';
                            }

                            // Taille lisible
                            $size = (int) $att['size_bytes'];
                            if ($size >= 1048576) {
                                $sizeText = round($size / 1048576, 1) . ' Mo';
                            } elseif ($size >= 1024) {
                                $sizeText = round($size / 1024, 0) . ' Ko';
                            } else {
                                $sizeText = $size . ' o';
                            }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                                    <i class="bi bi-<?= $icon ?> flex-shrink-0"
                                       style="font-size: 1.8rem; color: <?= $iconColor ?>;"></i>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-semibold small text-truncate"
                                             title="<?= e($att['original_name']) ?>">
                                            <?= e($att['original_name']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            <?= e($sizeText) ?>
                                            — <?= e(date('d/m/Y', strtotime($att['created_at']))) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-1 flex-shrink-0">
                                        <a href="<?= url('equipment/' . $equipment->id . '/attachments/' . (int) $att['id'] . '/download') ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           target="_blank"
                                           title="Voir / Télécharger">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST"
                                              action="<?= url('equipment/' . $equipment->id . '/attachments/' . (int) $att['id'] . '/delete') ?>"
                                              class="d-inline"
                                              onsubmit="return confirm('Supprimer la pièce jointe « <?= e($att['original_name']) ?> » ?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small text-center mb-3">
                        <i class="bi bi-inbox"></i>
                        Aucune pièce jointe pour l'instant.
                    </p>
                <?php endif; ?>

                <!-- Formulaire d'upload (drag & drop) -->
                <form method="POST"
                      action="<?= url('equipment/' . $equipment->id . '/attachments') ?>"
                      enctype="multipart/form-data"
                      id="attachment-form">
                    <?= csrf_field() ?>

                    <div id="attachment-dropzone"
                         class="border border-2 border-dashed rounded p-4 text-center"
                         style="border-color: #cbd5e1; cursor: pointer; transition: all 0.2s; background: #f8fafc;">
                        <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 2.2rem;"></i>
                        <p class="mb-1 mt-2">
                            <strong>Glissez-déposez un fichier ici</strong>
                        </p>
                        <p class="text-muted small mb-2">
                            ou cliquez pour parcourir
                        </p>
                        <p class="text-muted mb-0" style="font-size: 0.72rem;">
                            Types acceptés : JPG, PNG, GIF, WEBP, PDF, DOC, DOCX, XLS, XLSX, CSV, TXT — Max 10 Mo
                        </p>

                        <!-- Input file caché -->
                        <input type="file"
                               name="file"
                               id="attachment-file-input"
                               class="d-none"
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
                               required>
                    </div>

                    <!-- Nom du fichier sélectionné -->
                    <div id="attachment-filename" class="alert alert-info py-2 mt-2 d-none small"></div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted mb-0">Catégorie :</label>
                            <select name="category" class="form-select form-select-sm" style="width: auto;">
                                <option value="autre">Autre</option>
                                <option value="photo">Photo</option>
                                <option value="facture">Facture</option>
                                <option value="garantie">Garantie</option>
                                <option value="document">Document</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload"></i> Ajouter la pièce jointe
                        </button>
                    </div>
                </form>

            </div>
        </div>
        <!-- ============================================ -->
        <!-- FIN Pièces jointes                           -->
        <!-- ============================================ -->

    </div>

    <!-- Colonne latérale (1/3) -->
    <div class="col-lg-4">

        <!-- Informations financières -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-cash-coin text-primary"></i> Informations financières
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small d-block">Valeur d'acquisition</label>
                    <div class="fs-5 fw-bold text-primary">
                        <?= e($equipment->getFormattedValue()) ?>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="text-muted small d-block">Valeur nette comptable</label>
                    <div class="fs-5 fw-bold">
                        <?= e($equipment->getFormattedBookValue()) ?>
                    </div>
                    <small class="text-muted">
                        après amortissement de
                        <?= (int) ($equipment->amortizationYears ?? 0) ?> ans
                    </small>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="text-muted small d-block">Date d'acquisition</label>
                    <?= $equipment->acquisitionDate
                        ? e(date('d/m/Y', strtotime($equipment->acquisitionDate)))
                        : '—' ?>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Fin de garantie</label>
                    <?= $equipment->warrantyEndDate
                        ? e(date('d/m/Y', strtotime($equipment->warrantyEndDate)))
                        : '—' ?>
                </div>
                <?php if ($equipment->invoiceNumber): ?>
                    <div class="mb-0">
                        <label class="text-muted small d-block">N° de facture</label>
                        <code><?= e($equipment->invoiceNumber) ?></code>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Métadonnées -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-square text-primary"></i> Métadonnées
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Créé le</span>
                    <span><?= e(date('d/m/Y H:i', strtotime($equipment->createdAt))) ?></span>
                </div>
                <?php if ($equipment->updatedAt): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Modifié le</span>
                        <span><?= e(date('d/m/Y H:i', strtotime($equipment->updatedAt))) ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">ID interne</span>
                    <code>#<?= $equipment->id ?></code>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-qr-code text-primary"></i> QR Code
                </h6>
                <a href="<?= url('equipment/' . $equipment->id . '/qrcode') ?>" class="btn btn-sm btn-primary" title="Imprimer l'étiquette">
                    <i class="bi bi-printer"></i> Étiquette
                </a>
            </div>
            <div class="card-body text-center">
                <?php
                // Générer le QR code à la volée
                $baseUrl = rtrim((string) ($_ENV['APP_URL'] ?? 'http://localhost'), '/');
                $equipmentUrl = $baseUrl . '/equipment/' . $equipment->id;
                $qrCodeService = new \App\Services\QrCode\QrCodeService();
                $qrCodeDataUri = $qrCodeService->generateBase64($equipmentUrl, 200);
                ?>
                <img src="<?= e($qrCodeDataUri) ?>" alt="QR Code" width="200" height="200" class="img-fluid">
                <p class="mt-2 mb-0 small text-muted">
                    Scannez pour accéder à la fiche
                </p>
            </div>
        </div>

    </div>
</div>

<!-- Styles pour la dropzone -->
<style>
#attachment-dropzone.dragover {
    border-color: #4f46e5 !important;
    background: #eef2ff !important;
    transform: scale(1.01);
}
.border-dashed {
    border-style: dashed !important;
}
</style>

<!-- Script JS pour le drag & drop -->
<script src="<?= url('assets/js/equipment-attachments.js') ?>"></script>