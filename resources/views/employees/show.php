<?php
/**
 * Vue : Détail d'un employé
 *
 * @var string $title
 * @var \App\Models\Employee $employee
 */
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('employees') ?>" class="text-decoration-none">
                <i class="bi bi-person-badge"></i> Employés
            </a>
        </li>
        <li class="breadcrumb-item active"><?= e($employee->getFullName()) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-person-circle text-primary"></i>
            <?= e($employee->getFullName()) ?>
            <?php if ($employee->isActive): ?>
                <span class="badge bg-success">Actif</span>
            <?php else: ?>
                <span class="badge bg-secondary">Inactif</span>
            <?php endif; ?>
        </h4>
        <p class="text-muted mb-0 small">
            <?= e((string) ($employee->functionTitle ?? 'Aucune fonction renseignée')) ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('employees') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
        <a href="<?= url('employees/' . $employee->id . '/edit') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
        <form method="POST" action="<?= url('employees/' . $employee->id . '/delete') ?>"
              class="d-inline"
              onsubmit="return confirm('⚠️ Supprimer cet employé ?\n\n<?= e($employee->getFullName()) ?>\n\nCette action est irréversible.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<div class="row g-3">

    <!-- Infos personnelles -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-person-fill"></i> Informations personnelles
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Prénom</dt>
                    <dd class="col-sm-8"><?= e($employee->firstName) ?></dd>

                    <dt class="col-sm-4">Nom</dt>
                    <dd class="col-sm-8 fw-semibold"><?= e($employee->lastName) ?></dd>

                    <dt class="col-sm-4">Matricule</dt>
                    <dd class="col-sm-8">
                        <?php if ($employee->matricule): ?>
                            <code><?= e($employee->matricule) ?></code>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Fonction</dt>
                    <dd class="col-sm-8"><?= e((string) ($employee->functionTitle ?? '—')) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Contact & Service -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-telephone-fill"></i> Contact & Affectation
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">
                        <?php if ($employee->email): ?>
                            <a href="mailto:<?= e($employee->email) ?>" class="text-decoration-none">
                                <?= e($employee->email) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Téléphone</dt>
                    <dd class="col-sm-8">
                        <?php if ($employee->phone): ?>
                            <a href="tel:<?= e($employee->phone) ?>" class="text-decoration-none">
                                <?= e($employee->phone) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Service</dt>
                    <dd class="col-sm-8">
                        <?php if ($employee->serviceName): ?>
                            <span class="badge bg-light text-dark border">
                                <?= e($employee->serviceName) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Métadonnées -->
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    <i class="bi bi-info-circle"></i>
                    Créé le <?= e(date('d/m/Y à H:i', strtotime($employee->createdAt))) ?>
                    <?php if ($employee->updatedAt): ?>
                        — Modifié le <?= e(date('d/m/Y à H:i', strtotime($employee->updatedAt))) ?>
                    <?php endif; ?>
                </div>
                <div class="text-muted small">
                    ID interne : <code>#<?= (int) $employee->id ?></code>
                </div>
            </div>
        </div>
    </div>

</div>