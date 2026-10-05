<?php
/** @var array $maintenances */
/** @var float $totalCost */
/** @var array $byType */
/** @var array $byStatus */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('reports') ?>" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour aux rapports
        </a>
        <h3 class="mt-2 mb-1"><i class="bi bi-tools text-warning"></i> Rapport Maintenance</h3>
        <p class="text-muted small mb-0">
            <?= count($maintenances) ?> intervention(s)
            — Coût total : <strong><?= number_format($totalCost, 0, ',', ' ') ?> DA</strong>
        </p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-printer"></i> Imprimer
    </button>
</div>

<!-- Stats par type -->
<?php if (!empty($byType)): ?>
<div class="row g-3 mb-4">
    <?php foreach ($byType as $t): ?>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase fw-bold"><?= e($t['label']) ?></div>
                            <div class="fs-3 fw-bold"><?= $t['count'] ?></div>
                            <small class="text-muted"><?= number_format((float)$t['total_cost'], 0, ',', ' ') ?> DA</small>
                        </div>
                        <i class="bi bi-tools fs-2 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($maintenances)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Aucune intervention de maintenance pour le moment.
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Équipement</th>
                        <th>Type</th>
                        <th>Problème</th>
                        <th>Technicien</th>
                        <th>Statut</th>
                        <th>Signalé le</th>
                        <th>Résultat</th>
                        <th>Coût</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenances as $m): 
                        $statusBadges = [
                            'open'          => '<span class="badge bg-secondary">Ouvert</span>',
                            'in_progress'   => '<span class="badge bg-primary">En cours</span>',
                            'waiting_parts' => '<span class="badge bg-warning text-dark">Attente pièces</span>',
                            'completed'     => '<span class="badge bg-success">Terminé</span>',
                            'cancelled'     => '<span class="badge bg-danger">Annulé</span>',
                        ];
                        $resultBadges = [
                            'fixed'     => '<span class="badge bg-success">Réparé</span>',
                            'unfixed'   => '<span class="badge bg-danger">Non réparé</span>',
                            'replaced'  => '<span class="badge bg-info">Remplacé</span>',
                            'pending'   => '<span class="badge bg-warning text-dark">En attente</span>',
                        ];
                    ?>
                        <tr>
                            <td><code>#<?= e($m['id']) ?></code></td>
                            <td>
                                <?= e($m['inventory_number'] ?? '—') ?>
                                <br><small class="text-muted"><?= e($m['designation'] ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= e($m['type'] ?? '—') ?></span></td>
                            <td><?= e(mb_substr($m['problem_description'] ?? '', 0, 50)) ?><?= mb_strlen($m['problem_description'] ?? '') > 50 ? '…' : '' ?></td>
                            <td><?= e($m['technician'] ?? '—') ?></td>
                            <td><?= $statusBadges[$m['status']] ?? e($m['status']) ?></td>
                            <td><?= $m['reported_at'] ? date('d/m/Y', strtotime($m['reported_at'])) : '—' ?></td>
                            <td><?= $resultBadges[$m['result']] ?? '—' ?></td>
                            <td><?= $m['cost'] ? number_format((float)$m['cost'], 0, ',', ' ') . ' DA' : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>