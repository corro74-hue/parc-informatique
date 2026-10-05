<?php
/** @var array $equipment */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('reports') ?>" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour aux rapports
        </a>
        <h3 class="mt-2 mb-1"><i class="bi bi-box-seam text-primary"></i> Rapport Équipements</h3>
        <p class="text-muted small mb-0"><?= count($equipment) ?> équipement(s) au total</p>
    </div>
    <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-printer"></i> Imprimer
    </button>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° Inventaire</th>
                    <th>Désignation</th>
                    <th>Catégorie</th>
                    <th>Marque</th>
                    <th>Statut</th>
                    <th>Service</th>
                    <th>Valeur</th>
                    <th>Garantie</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipment as $e): 
                    $warrantyStatus = '';
                    if ($e['warranty_end_date']) {
                        $days = (int)((strtotime($e['warranty_end_date']) - time()) / 86400);
                        if ($days < 0) {
                            $warrantyStatus = '<span class="badge bg-danger">Expirée</span>';
                        } elseif ($days < 30) {
                            $warrantyStatus = '<span class="badge bg-warning text-dark">' . $days . 'j</span>';
                        } else {
                            $warrantyStatus = '<span class="badge bg-success">' . $days . 'j</span>';
                        }
                    }
                ?>
                    <tr>
                        <td><code><?= e($e['inventory_number']) ?></code></td>
                        <td><?= e($e['designation']) ?></td>
                        <td><?= e($e['category_name'] ?? '—') ?></td>
                        <td><?= e($e['brand_name'] ?? '—') ?></td>
                        <td>
                            <span class="badge" style="background: <?= e($e['status_color'] ?: '#6b7280') ?>;">
                                <?= e($e['status_name'] ?? '—') ?>
                            </span>
                        </td>
                        <td><?= e($e['service_name'] ?? '—') ?></td>
                        <td><?= $e['acquisition_value'] ? number_format((float)$e['acquisition_value'], 0, ',', ' ') . ' DA' : '—' ?></td>
                        <td><?= $warrantyStatus ?: '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>