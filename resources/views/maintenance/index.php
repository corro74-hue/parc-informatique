<?php
/**
 * Vue : Liste des maintenances
 *
 * @var string $title
 * @var \App\Models\Maintenance[] $maintenances
 * @var int $total
 * @var int $page
 * @var int $lastPage
 * @var int $perPage
 * @var array $filters
 * @var array $stats
 */
?>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-tools text-primary"></i>
            Maintenance
            <span class="badge bg-secondary"><?= (int) $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= (int) $total ?> intervention<?= $total > 1 ? 's' : '' ?>
            — page <?= (int) $page ?> sur <?= (int) $lastPage ?>
        </p>
    </div>
    <a href="<?= url('maintenance/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle intervention
    </a>
</div>

<!-- Stats par statut -->
<?php if (!empty($stats['by_status'])): ?>
    <div class="row g-2 mb-3">
        <?php
        $statusCards = [
            'open'          => ['label' => 'Ouverts',        'color' => '#f59e0b', 'icon' => 'exclamation-circle'],
            'in_progress'   => ['label' => 'En cours',       'color' => '#3b82f6', 'icon' => 'gear'],
            'waiting_parts' => ['label' => 'Attente pièces', 'color' => '#8b5cf6', 'icon' => 'hourglass-split'],
            'completed'     => ['label' => 'Terminés',       'color' => '#22c55e', 'icon' => 'check-circle'],
            'cancelled'     => ['label' => 'Annulés',        'color' => '#6b7280', 'icon' => 'x-circle'],
        ];
        foreach ($statusCards as $code => $meta):
            $count = (int) ($stats['by_status'][$code] ?? 0);
        ?>
            <div class="col-md">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3" style="font-size: 1.8rem; color: <?= $meta['color'] ?>;">
                            <i class="bi bi-<?= $meta['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="h5 mb-0"><?= $count ?></div>
                            <div class="text-muted small"><?= $meta['label'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Total coût -->
<?php if (!empty($stats['total_cost'])): ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
        <div>
            <i class="bi bi-cash-coin"></i>
            <strong>Coût total des maintenances terminées :</strong>
            <?= number_format((float) $stats['total_cost'], 2, ',', ' ') ?> DA
        </div>
    </div>
<?php endif; ?>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('maintenance') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Recherche</label>
                <input type="text" name="search" class="form-control"
                       placeholder="N° inventaire, désignation, problème..."
                       value="<?= e((string) ($filters['search'] ?? '')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="open"          <?= ($filters['status'] ?? '') === 'open'          ? 'selected' : '' ?>>Ouvert</option>
                    <option value="in_progress"   <?= ($filters['status'] ?? '') === 'in_progress'   ? 'selected' : '' ?>>En cours</option>
                    <option value="waiting_parts" <?= ($filters['status'] ?? '') === 'waiting_parts' ? 'selected' : '' ?>>Attente pièces</option>
                    <option value="completed"     <?= ($filters['status'] ?? '') === 'completed'     ? 'selected' : '' ?>>Terminé</option>
                    <option value="cancelled"     <?= ($filters['status'] ?? '') === 'cancelled'     ? 'selected' : '' ?>>Annulé</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="preventive" <?= ($filters['type'] ?? '') === 'preventive' ? 'selected' : '' ?>>Préventive</option>
                    <option value="corrective" <?= ($filters['type'] ?? '') === 'corrective' ? 'selected' : '' ?>>Corrective</option>
                    <option value="curative"   <?= ($filters['type'] ?? '') === 'curative'   ? 'selected' : '' ?>>Curative</option>
                    <option value="upgrade"    <?= ($filters['type'] ?? '') === 'upgrade'    ? 'selected' : '' ?>>Mise à niveau</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['type'])): ?>
                    <a href="<?= url('maintenance') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> Réinitialiser
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th width="130">Équipement</th>
                    <th>Problème</th>
                    <th width="110" class="text-center">Type</th>
                    <th width="120" class="text-center">Statut</th>
                    <th width="100">Technicien</th>
                    <th width="120">Déclaré le</th>
                    <th width="100" class="text-end">Coût</th>
                    <th width="140" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($maintenances)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune intervention trouvée.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['type'])): ?>
                                <a href="<?= url('maintenance') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('maintenance/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Créer la première intervention
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($maintenances as $m): ?>
                        <tr>
                            <td><code>#<?= (int) $m->id ?></code></td>
                            <td>
                                <a href="<?= url('equipment/' . $m->equipmentId) ?>" class="text-decoration-none fw-semibold">
                                    <?= e((string) $m->equipmentInventoryNumber) ?>
                                </a>
                                <div class="small text-muted"><?= e((string) $m->equipmentDesignation) ?></div>
                            </td>
                            <td>
                                <div class="small">
                                    <?= e(mb_strimwidth($m->problemDescription, 0, 80, '...')) ?>
                                </div>
                            </td>
                            <td class="text-center"><?= $m->getTypeBadge() ?></td>
                            <td class="text-center"><?= $m->getStatusBadge() ?></td>
                            <td><?= e((string) ($m->technician ?? '—')) ?></td>
                            <td>
                                <div><?= e(date('d/m/Y', strtotime($m->reportedAt))) ?></div>
                                <small class="text-muted"><?= e(date('H:i', strtotime($m->reportedAt))) ?></small>
                            </td>
                            <td class="text-end">
                                <strong><?= e($m->getFormattedCost()) ?></strong>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('maintenance/' . $m->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($m->isActive()): ?>
                                    <a href="<?= url('maintenance/' . $m->id . '/edit') ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($lastPage > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <?php $queryParams = $_GET; ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $page + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>