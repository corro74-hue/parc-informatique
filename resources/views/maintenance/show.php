<?php
/**
 * Vue : Détail d'une maintenance
 *
 * @var string $title
 * @var \App\Models\Maintenance $maintenance
 */
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('maintenance') ?>" class="text-decoration-none">
                <i class="bi bi-tools"></i> Maintenance
            </a>
        </li>
        <li class="breadcrumb-item active">Ticket #<?= (int) $maintenance->id ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-wrench-adjustable text-primary"></i>
            Ticket #<?= (int) $maintenance->id ?>
            <?= $maintenance->getStatusBadge() ?>
            <?= $maintenance->getTypeBadge() ?>
        </h4>
        <p class="text-muted mb-0 small">
            Déclaré le <?= e(date('d/m/Y à H:i', strtotime($maintenance->reportedAt))) ?>
            — Durée : <?= (int) $maintenance->getDurationInDays() ?> jour(s)
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('maintenance') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <?php if ($maintenance->isActive()): ?>
            <a href="<?= url('maintenance/' . $maintenance->id . '/edit') ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        <?php endif; ?>
        <a href="<?= url('equipment/' . $maintenance->equipmentId) ?>" class="btn btn-outline-info btn-sm">
            <i class="bi bi-box-seam"></i> Voir l'équipement
        </a>
    </div>
</div>

<?php if ($maintenance->isActive()): ?>
    <div class="alert alert-warning mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Cette intervention est <strong>en cours</strong>. L'équipement est actuellement marqué "En maintenance".
    </div>
<?php else: ?>
    <div class="alert alert-secondary mb-3">
        <i class="bi bi-check-circle-fill"></i>
        Cette intervention est <strong>clôturée</strong>.
        <?php if ($maintenance->completedAt): ?>
            Terminée le <?= e(date('d/m/Y', strtotime($maintenance->completedAt))) ?>.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-3">

    <!-- Équipement -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-box-seam"></i> Équipement concerné
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">N° inventaire</dt>
                    <dd class="col-sm-8">
                        <a href="<?= url('equipment/' . $maintenance->equipmentId) ?>" class="text-decoration-none fw-semibold">
                            <?= e((string) $maintenance->equipmentInventoryNumber) ?>
                        </a>
                    </dd>
                    <dt class="col-sm-4">Désignation</dt>
                    <dd class="col-sm-8"><?= e((string) $maintenance->equipmentDesignation) ?></dd>
                    <?php if ($maintenance->equipmentCategoryName): ?>
                        <dt class="col-sm-4">Catégorie</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <?= e($maintenance->equipmentCategoryName) ?>
                            </span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <!-- Intervenant -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-person-gear"></i> Intervenant & suivi
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Technicien</dt>
                    <dd class="col-sm-7"><?= e((string) ($maintenance->technician ?? '— Non assigné —')) ?></dd>
                    <dt class="col-sm-5">Fournisseur</dt>
                    <dd class="col-sm-7"><?= e((string) ($maintenance->supplierName ?? '—')) ?></dd>
                    <dt class="col-sm-5">Créé par</dt>
                    <dd class="col-sm-7"><?= e((string) ($maintenance->createdByName ?? '—')) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Description du problème -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-chat-left-text"></i> Description du problème
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-wrap;"><?= e($maintenance->problemDescription) ?></p>
            </div>
        </div>
    </div>

    <!-- Diagnostic et travaux -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-search"></i> Diagnostic
            </div>
            <div class="card-body">
                <?php if ($maintenance->diagnosis): ?>
                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($maintenance->diagnosis) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0">Aucun diagnostic renseigné.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-tools"></i> Travaux effectués
            </div>
            <div class="card-body">
                <?php if ($maintenance->workDone): ?>
                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($maintenance->workDone) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0">Aucun travail effectué renseigné.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Suivi financier -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cash-coin"></i> Suivi financier & dates
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Déclaré le</small>
                        <strong><?= e(date('d/m/Y H:i', strtotime($maintenance->reportedAt))) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Début intervention</small>
                        <strong>
                            <?= $maintenance->startedAt
                                ? e(date('d/m/Y H:i', strtotime($maintenance->startedAt)))
                                : '<span class="text-muted">—</span>' ?>
                        </strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Fin intervention</small>
                        <strong>
                            <?= $maintenance->completedAt
                                ? e(date('d/m/Y H:i', strtotime($maintenance->completedAt)))
                                : '<span class="text-muted">—</span>' ?>
                        </strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Durée d'indisponibilité</small>
                        <strong><?= (int) $maintenance->downtimeHours ?> heure(s)</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Résultat</small>
                        <strong><?= $maintenance->getResultBadge() ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Coût</small>
                        <strong><?= e($maintenance->getFormattedCost()) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">N° facture</small>
                        <strong><?= e((string) ($maintenance->invoiceNumber ?? '—')) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <?php if ($maintenance->notes): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-text"></i> Notes internes
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($maintenance->notes) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    <i class="bi bi-info-circle"></i>
                    Créé le <?= e(date('d/m/Y à H:i', strtotime($maintenance->createdAt))) ?>
                </div>
                <form method="POST"
                      action="<?= url('maintenance/' . $maintenance->id . '/delete') ?>"
                      class="d-inline"
                      onsubmit="return confirm('⚠️ Supprimer définitivement ce ticket ?\n\nCette action est irréversible.');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm"
                            <?= $maintenance->isActive() ? 'disabled title="Impossible : intervention en cours"' : '' ?>>
                        <i class="bi bi-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>