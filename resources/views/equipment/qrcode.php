<!-- Styles d'impression -->
<style>
    .label-preview {
        width: 400px;
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 20px;
        background: white;
        margin: 0 auto;
    }
    .label-content {
        text-align: center;
    }
    .label-qr img {
        max-width: 100%;
        height: auto;
    }
    .label-header {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .label-inventory {
        font-size: 1.3rem;
        font-weight: bold;
        color: #1e293b;
        margin: 10px 0 5px 0;
    }
    .label-designation {
        font-size: 0.9rem;
        color: #475569;
        margin-bottom: 15px;
    }
    .label-footer {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 15px;
    }

    /* Styles d'impression */
    @media print {
        .sidebar, .topbar, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
        }
        .content-area {
            padding: 0 !important;
        }
        .label-preview {
            border: none;
            box-shadow: none;
            width: 100%;
            max-width: 400px;
        }
        body {
            background: white !important;
        }
    }
</style>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3 no-print">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('equipment/' . $equipment->id) ?>" class="text-decoration-none">
                <?= e($equipment->inventoryNumber) ?>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <i class="bi bi-qr-code"></i> Étiquette QR Code
        </li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-qr-code text-primary"></i>
            Étiquette QR Code
        </h4>
        <p class="text-muted mb-0 small">
            <?= e($equipment->inventoryNumber) ?> — <?= e($equipment->designation) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('equipment/' . $equipment->id) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Imprimer
        </button>
    </div>
</div>

<!-- Aperçu de l'étiquette -->
<div class="card no-print">
    <div class="card-body">
        <div class="text-center mb-4">
            <h6 class="text-muted">
                <i class="bi bi-eye"></i> Aperçu de l'étiquette
            </h6>
            <p class="small text-muted mb-0">
                Imprimez cette page pour obtenir une étiquette à coller sur l'équipement.
            </p>
        </div>

        <!-- L'étiquette (visible à l'écran et à l'impression) -->
        <div class="label-preview">
            <div class="label-content">
                <div class="label-header">
                    Parc Informatique
                </div>

                <div class="label-qr">
                    <img src="<?= e($qrCodeDataUri) ?>" alt="QR Code" width="300" height="300">
                </div>

                <div class="label-inventory">
                    <?= e($equipment->inventoryNumber) ?>
                </div>

                <div class="label-designation">
                    <?= e($equipment->designation) ?>
                </div>

                <?php if ($equipment->serviceName): ?>
                    <div class="label-footer">
                        <?= e($equipment->serviceName) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                URL encodée : <code><?= e($equipmentUrl) ?></code>
            </small>
        </div>
    </div>
</div>

<!-- Informations pratiques -->
<div class="card mt-3 no-print">
    <div class="card-body">
        <h6 class="text-primary">
            <i class="bi bi-lightbulb"></i> Comment utiliser cette étiquette ?
        </h6>
        <ul class="mb-0 small">
            <li><strong>Imprimez</strong> cette page (ou imprimez directement l'étiquette)</li>
            <li><strong>Découpez</strong> l'étiquette autour des pointillés</li>
            <li><strong>Collez</strong>-la sur l'équipement (visible, sur une surface propre)</li>
            <li><strong>Scannez</strong>-la avec un smartphone pour accéder à la fiche</li>
            <li>Le QR code contient l'URL : <code><?= e($equipmentUrl) ?></code></li>
        </ul>
    </div>
</div>