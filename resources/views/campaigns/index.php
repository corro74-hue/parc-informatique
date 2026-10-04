<?php
/** @var array $campaigns */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-clipboard-check text-primary"></i> Campagnes d'inventaire</h3>
        <p class="text-muted mb-0 small"><?= count($campaigns) ?> campagne(s) enregistrée(s)</p>
    </div>
    <a href="<?= url('campaigns/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle campagne
    </a>
</div>

<?php if (empty($campaigns)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Aucune campagne pour le moment. <a href="<?= url('campaigns/create') ?>" class="alert-link">Créez la première</a>.
    </div>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($campaigns as $c): 
            $statusColors = [
                'planned'     => 'secondary',
                'in_progress' => 'primary',
                'completed'   => 'success',
                'cancelled'   => 'danger',
            ];
            $statusLabels = [
                'planned'     => '📋 Planifiée',
                'in_progress' => '🚀 En cours',
                'completed'   => '✅ Terminée',
                'cancelled'   => '❌ Annulée',
            ];
            $progress = $c['total_items'] > 0 
                ? round(($c['done_items'] / $c['total_items']) * 100) 
                : 0;
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-<?= $statusColors[$c['status']] ?? 'secondary' ?>">
                                <?= $statusLabels[$c['status']] ?? $c['status'] ?>
                            </span>
                            <small class="text-muted"><?= e($c['reference']) ?></small>
                        </div>

                        <h5 class="card-title mb-2"><?= e($c['name']) ?></h5>

                        <p class="card-text text-muted small mb-3">
                            <i class="bi bi-calendar"></i> 
                            <?= date('d/m/Y', strtotime($c['start_date'])) ?>
                            <?php if ($c['end_date']): ?>
                                → <?= date('d/m/Y', strtotime($c['end_date'])) ?>
                            <?php endif; ?>
                            <?php if ($c['site_name']): ?>
                                <br><i class="bi bi-geo-alt"></i> <?= e($c['site_name']) ?>
                            <?php endif; ?>
                            <?php if ($c['service_name']): ?>
                                <br><i class="bi bi-building"></i> <?= e($c['service_name']) ?>
                            <?php endif; ?>
                        </p>

                        <!-- Statistiques -->
                        <div class="d-flex justify-content-between small mb-2">
                            <span><i class="bi bi-box"></i> <?= $c['total_items'] ?> équipements</span>
                            <span class="text-success"><i class="bi bi-check-circle"></i> <?= $c['found_items'] ?></span>
                            <span class="text-danger"><i class="bi bi-x-circle"></i> <?= $c['missing_items'] ?></span>
                        </div>

                        <!-- Barre de progression -->
                        <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar bg-<?= $statusColors[$c['status']] ?? 'secondary' ?>" 
                                 style="width: <?= $progress ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $progress ?>% pointé</small>
                    </div>

                    <div class="card-footer bg-transparent">
                        <a href="<?= url('campaigns/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-eye"></i> Voir détails
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>