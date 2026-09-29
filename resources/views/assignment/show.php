<?php
/**
 * Vue : Détail d'une affectation
 *
 * @var string $title
 * @var \App\Models\Assignment $assignment
 */
?>

<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('assignments') ?>" class="text-decoration-none">
                <i class="bi bi-people-fill"></i> Affectations
            </a>
        </li>
        <li class="breadcrumb-item active">Affectation #<?= (int) $assignment->id ?></li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-person-badge text-primary"></i>
            Affectation #<?= (int) $assignment->id ?>
            <?= $assignment->getStatusBadge() ?>
        </h4>
        <p class="text-muted mb-0 small">
            Affectation du <?= e(date('d/m/Y', strtotime($assignment->startDate))) ?>
            <?php if ($assignment->endDate): ?>
                au <?= e(date('d/m/Y', strtotime($assignment->endDate))) ?>
            <?php endif; ?>
            — Durée : <?= (int) $assignment->getDurationInDays() ?> jour(s)
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('assignments') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour à la liste
        </a>
        <?php if ($assignment->isActive()): ?>
            <a href="<?= url('assignments/' . $assignment->id . '/edit') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        <?php endif; ?>
        <a href="<?= url('equipment/' . $assignment->equipmentId) ?>" class="btn btn-outline-info btn-sm">
            <i class="bi bi-box-seam"></i> Voir l'équipement
        </a>
    </div>
</div>

<!-- Alertes -->
<?php if ($assignment->isActive()): ?>
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle-fill"></i>
        Cette affectation est <strong>active</strong>. L'équipement est actuellement affecté à cet employé.
    </div>
<?php else: ?>
    <div class="alert alert-secondary mb-3">
        <i class="bi bi-check-circle-fill"></i>
        Cette affectation est <strong>clôturée</strong>. L'équipement a été retourné le
        <?= e(date('d/m/Y', strtotime($assignment->endDate))) ?>.
    </div>
<?php endif; ?>

<div class="row g-3">
    <!-- Colonne gauche : Équipement -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-box-seam"></i> Équipement affecté
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">N° inventaire</dt>
                    <dd class="col-sm-8">
                        <a href="<?= url('equipment/' . $assignment->equipmentId) ?>" class="text-decoration-none fw-semibold">
                            <?= e((string) $assignment->equipmentInventoryNumber) ?>
                        </a>
                    </dd>

                    <dt class="col-sm-4">Désignation</dt>
                    <dd class="col-sm-8"><?= e((string) $assignment->equipmentDesignation) ?></dd>

                    <?php if ($assignment->equipmentCategoryName): ?>
                        <dt class="col-sm-4">Catégorie</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <?= e($assignment->equipmentCategoryName) ?>
                            </span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <!-- Colonne droite : Employé -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-person-fill"></i> Employé destinataire
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nom complet</dt>
                    <dd class="col-sm-8 fw-semibold"><?= e($assignment->getEmployeeFullName()) ?></dd>

                    <?php if ($assignment->employeeMatricule): ?>
                        <dt class="col-sm-4">Matricule</dt>
                        <dd class="col-sm-8">
                            <code><?= e($assignment->employeeMatricule) ?></code>
                        </dd>
                    <?php endif; ?>

                    <?php if ($assignment->employeeFunctionTitle): ?>
                        <dt class="col-sm-4">Fonction</dt>
                        <dd class="col-sm-8"><?= e($assignment->employeeFunctionTitle) ?></dd>
                    <?php endif; ?>

                    <?php if ($assignment->serviceName): ?>
                        <dt class="col-sm-4">Service</dt>
                        <dd class="col-sm-8"><?= e($assignment->serviceName) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <!-- Lieu d'affectation -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-geo-alt-fill"></i> Lieu d'affectation
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Site</small>
                        <strong><?= e((string) ($assignment->siteName ?? '—')) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Service</small>
                        <strong><?= e((string) ($assignment->serviceName ?? '—')) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Localisation</small>
                        <strong><?= e((string) ($assignment->locationName ?? '—')) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-event"></i> Détails de l'affectation
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Date de début</small>
                        <strong><?= e(date('d/m/Y', strtotime($assignment->startDate))) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Date de fin</small>
                        <strong>
                            <?= $assignment->endDate
                                ? e(date('d/m/Y', strtotime($assignment->endDate)))
                                : '<span class="text-muted">— En cours —</span>' ?>
                        </strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Durée</small>
                        <strong><?= (int) $assignment->getDurationInDays() ?> jour(s)</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Créée par</small>
                        <strong><?= e((string) ($assignment->createdByName ?? '—')) ?></strong>
                    </div>
                    <?php if ($assignment->reason): ?>
                        <div class="col-12">
                            <small class="text-muted d-block">Motif</small>
                            <p class="mb-0"><?= nl2br(e($assignment->reason)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-info-circle text-muted"></i>
                    <small class="text-muted">
                        Créée le <?= e(date('d/m/Y à H:i', strtotime($assignment->createdAt))) ?>
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($assignment->isActive()): ?>
                        <form method="POST"
                              action="<?= url('assignments/' . $assignment->id . '/return') ?>"
                              class="d-inline"
                              onsubmit="return confirm('⚠️ Confirmer le retour de cet équipement ?\n\nL\'équipement sera remis en stock.');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-return-left"></i> Retourner l'équipement
                            </button>
                        </form>
                    <?php endif; ?>

                    <form method="POST"
                          action="<?= url('assignments/' . $assignment->id . '/delete') ?>"
                          class="d-inline"
                          onsubmit="return confirm('⚠️ Supprimer définitivement cette affectation ?\n\nCette action est irréversible.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                <?= $assignment->isActive() ? 'disabled title="Impossible : affectation active"' : '' ?>>
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>