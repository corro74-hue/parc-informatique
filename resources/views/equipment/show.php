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
    <div class="d-flex gap-2">
        <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
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

        <!-- Historique (placeholder) -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history text-primary"></i> Historique
                </h6>
                <span class="badge bg-secondary">Bientôt</span>
            </div>
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">L'historique des mouvements sera affiché ici.</p>
                <small>Acquisition, affectations, maintenances, réformes...</small>
            </div>
        </div>

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