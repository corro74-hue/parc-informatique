<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
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
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-pencil-square text-primary"></i>
            Modifier — <?= e($equipment->inventoryNumber) ?>
        </h4>
        <p class="text-muted mb-0 small">
            <?= e($equipment->designation) ?>
        </p>
    </div>
</div>

<!-- Avertissement -->
<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="bi bi-info-circle fs-4 me-2"></i>
    <div>
        <strong>Numéro d'inventaire non modifiable :</strong>
        <code><?= e($equipment->inventoryNumber) ?></code>
        <br>
        <small>Pour des raisons de traçabilité, le numéro d'inventaire ne peut pas être modifié après création.</small>
    </div>
</div>

<!-- Formulaire -->
<form method="POST" action="<?= url('equipment/' . $equipment->id) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-body">
            <?php
            // IMPORTANT : on passe $equipment explicitement au partial
            $equipment = $equipment;
            require __DIR__ . '/partials/_form.php';
            ?>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between">
            <div>
                <a href="<?= url('equipment/' . $equipment->id) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Annuler
                </a>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</form>