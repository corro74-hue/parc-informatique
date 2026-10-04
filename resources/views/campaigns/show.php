<?php
/** @var array $campaign */
/** @var array $items */
/** @var array $stats */
/** @var array $locations */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('campaigns') ?>" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Retour aux campagnes
        </a>
        <h3 class="mt-2 mb-1">
            <i class="bi bi-clipboard-check text-primary"></i> <?= e($campaign['name']) ?>
        </h3>
        <p class="text-muted small mb-0">
            Réf : <code><?= e($campaign['reference']) ?></code> • 
            <?= date('d/m/Y', strtotime($campaign['start_date'])) ?>
            <?php if ($campaign['end_date']): ?>
                → <?= date('d/m/Y', strtotime($campaign['end_date'])) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($campaign['status'] === 'planned'): ?>
            <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/start') ?>" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-play-fill"></i> Démarrer
                </button>
            </form>
        <?php elseif ($campaign['status'] === 'in_progress'): ?>
            <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/complete') ?>" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success" 
                        onclick="return confirm('Clôturer cette campagne ?')">
                    <i class="bi bi-check-circle"></i> Clôturer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center border-secondary">
            <div class="card-body py-3">
                <h3 class="mb-0"><?= $stats['total'] ?></h3>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-info">
            <div class="card-body py-3">
                <h3 class="mb-0 text-info"><?= $stats['pending'] ?></h3>
                <small class="text-muted">En attente</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-success">
            <div class="card-body py-3">
                <h3 class="mb-0 text-success"><?= $stats['found'] ?></h3>
                <small class="text-muted">Trouvés</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-danger">
            <div class="card-body py-3">
                <h3 class="mb-0 text-danger"><?= $stats['missing'] ?></h3>
                <small class="text-muted">Manquants</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning"><?= $stats['moved'] ?></h3>
                <small class="text-muted">Déplacés</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center border-warning">
            <div class="card-body py-3">
                <h3 class="mb-0 text-warning"><?= $stats['damaged'] ?></h3>
                <small class="text-muted">Endommagés</small>
            </div>
        </div>
    </div>
</div>

<!-- Liste des items -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> Équipements à vérifier</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° Inventaire</th>
                    <th>Désignation</th>
                    <th>Statut</th>
                    <th>Vérifié le</th>
                    <th>Par</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): 
                    $badges = [
                        'pending' => '<span class="badge bg-secondary">⏳ En attente</span>',
                        'found'   => '<span class="badge bg-success">✅ Trouvé</span>',
                        'missing' => '<span class="badge bg-danger">❌ Manquant</span>',
                        'moved'   => '<span class="badge bg-warning text-dark">📦 Déplacé</span>',
                        'damaged' => '<span class="badge bg-warning text-dark">⚠️ Endommagé</span>',
                    ];
                ?>
                    <tr>
                        <td><code><?= e($item['inventory_number'] ?? '?') ?></code></td>
                        <td><?= e($item['designation'] ?? '?') ?></td>
                        <td><?= $badges[$item['status']] ?? $item['status'] ?></td>
                        <td>
                            <?= $item['verified_at'] 
                                ? date('d/m/Y H:i', strtotime($item['verified_at'])) 
                                : '—' ?>
                        </td>
                        <td><?= e($item['verified_by_name'] ?? '—') ?></td>
                        <td class="text-end">
                            <?php if ($campaign['status'] === 'in_progress'): ?>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-success" onclick="verifyItem(<?= $item['id'] ?>, 'found')" title="Trouvé">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-danger" onclick="verifyItem(<?= $item['id'] ?>, 'missing')" title="Manquant">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <button class="btn btn-warning" onclick="verifyItem(<?= $item['id'] ?>, 'moved')" title="Déplacé">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function verifyItem(itemId, status) {
    const campaignId = <?= (int)$campaign['id'] ?>;
    const url = `<?= url('campaigns/' . $campaign['id'] . '/items') ?>/${itemId}/verify`;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status }),
        });
        
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur : ' + data.message);
        }
    } catch (e) {
        alert('Erreur réseau : ' + e.message);
    }
}
</script>