<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item active">Nouvel équipement</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-plus-circle text-primary"></i>
            Nouvel équipement
        </h4>
        <p class="text-muted mb-0 small">
            Remplissez les informations ci-dessous. Les champs marqués d'un
            <span class="text-danger">*</span> sont obligatoires.
        </p>
    </div>
</div>

<!-- Formulaire -->
<form method="POST" action="<?= url('equipment') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-body">
            <?php
            // Données attendues par le partial
            // $categories, $statuses, $services, $sites, $brands, $nextNumber
            require __DIR__ . '/partials/_form.php';
            ?>
        </div>

        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Enregistrer l'équipement
            </button>
        </div>
    </div>
</form>