<?php
// Récupérer les paramètres de tri actuels
$currentSort = $_GET['sort'] ?? 'created_at';
$currentDir  = strtoupper($_GET['dir'] ?? 'DESC');

$sortUrl = function (string $column) use ($currentSort, $currentDir) {
    $params = $_GET;
    $params['sort'] = $column;
    $params['dir'] = ($currentSort === $column && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    return '?' . http_build_query($params);
};

$sortIcon = function (string $column) use ($currentSort, $currentDir) {
    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size: 0.7rem;"></i>';
    }
    return $currentDir === 'ASC'
        ? '<i class="bi bi-caret-up-fill text-primary ms-1" style="font-size: 0.8rem;"></i>'
        : '<i class="bi bi-caret-down-fill text-primary ms-1" style="font-size: 0.8rem;"></i>';
};
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-box-seam text-primary"></i>
            Équipements
            <span class="badge bg-secondary"><?= $result['total'] ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= $result['total'] ?> équipement<?= $result['total'] > 1 ? 's' : '' ?>
            — page <?= $result['page'] ?> sur <?= $result['last_page'] ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('equipment/export-csv?' . http_build_query($_GET)) ?>"
           class="btn btn-success"
           title="Exporter la liste filtrée vers Excel">
            <i class="bi bi-file-earmark-excel"></i> Exporter Excel
        </a>

        <a href="<?= url('equipment/export-pdf?' . http_build_query($_GET)) ?>"
           class="btn btn-danger"
           target="_blank"
           title="Aperçu avant impression PDF">
            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
        </a>

        <a href="<?= url('equipment/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvel équipement
        </a>
    </div>
</div>

<!-- NOUVEAU : Barre de compteurs par statut -->
<?php if (!empty($statusCounts)): ?>
    <?php
    // Total de tous les équipements (hors corbeille)
    $totalCount = array_sum(array_column($statusCounts, 'count'));
    // Statut actuellement sélectionné dans les filtres
    $activeStatusId = !empty($filters['status_id']) ? (int) $filters['status_id'] : null;
    ?>
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="text-muted small me-1">
            <i class="bi bi-funnel"></i> Filtrer par statut :
        </span>

        <?php foreach ($statusCounts as $st): ?>
            <?php
            $isActive = ($activeStatusId === (int) $st['id']);
            // URL de filtre : on conserve les filtres existants et on change status_id
            $params = $_GET;
            $params['status_id'] = $st['id'];
            unset($params['page']); // on repart à la page 1
            $url = url('equipment') . '?' . http_build_query($params);
            ?>
            <a href="<?= $url ?>"
               class="badge text-decoration-none d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill"
               style="
                   background-color: <?= $isActive ? e($st['color']) : '#f1f5f9' ?>;
                   color: <?= $isActive ? '#fff' : '#475569' ?>;
                   border: 2px solid <?= e($st['color']) ?>;
                   font-weight: 600;
                   font-size: 0.8rem;
               "
               title="Filtrer sur : <?= e($st['name']) ?>">
                <span style="
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background-color: <?= $isActive ? '#fff' : e($st['color']) ?>;
                "></span>
                <?= e($st['name']) ?>
                <span class="badge <?= $isActive ? 'bg-white text-dark' : 'bg-secondary' ?> ms-1">
                    <?= (int) $st['count'] ?>
                </span>
            </a>
        <?php endforeach; ?>

        <?php
        // Badge "Tous" — pour réinitialiser le filtre statut
        $paramsAll = $_GET;
        unset($paramsAll['status_id'], $paramsAll['page']);
        $urlAll = url('equipment') . (!empty($paramsAll) ? '?' . http_build_query($paramsAll) : '');
        $isAllActive = ($activeStatusId === null);
        ?>
        <a href="<?= $urlAll ?>"
           class="badge text-decoration-none d-inline-flex align-items-center gap-1 px-3 py-2 rounded-pill"
           style="
               background-color: <?= $isAllActive ? '#1e293b' : '#f1f5f9' ?>;
               color: <?= $isAllActive ? '#fff' : '#475569' ?>;
               border: 2px solid #1e293b;
               font-weight: 600;
               font-size: 0.8rem;
           "
           title="Voir tous les équipements">
            <i class="bi bi-collection"></i>
            Tous
            <span class="badge <?= $isAllActive ? 'bg-white text-dark' : 'bg-secondary' ?> ms-1">
                <?= $totalCount ?>
            </span>
        </a>
    </div>
<?php endif; ?>

<!-- Filtres -->
<?php require __DIR__ . '/partials/_filters.php'; ?>

<!-- Indicateur de tri actif -->
<?php if ($currentSort !== 'created_at' || $currentDir !== 'DESC'): ?>
    <?php
    $sortLabels = [
        'created_at'        => 'Date de création',
        'updated_at'        => 'Date de modification',
        'inventory_number'  => 'N° d\'inventaire',
        'designation'       => 'Désignation',
        'acquisition_date'  => 'Date d\'acquisition',
        'acquisition_value' => 'Valeur d\'acquisition',
    ];
    ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center py-2 mb-3">
        <div>
            <i class="bi bi-sort-down"></i>
            Trié par <strong><?= e($sortLabels[$currentSort] ?? $currentSort) ?></strong>
            <span class="badge bg-primary"><?= $currentDir === 'ASC' ? 'Croissant' : 'Décroissant' ?></span>
        </div>
        <a href="<?= url('equipment') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x"></i> Réinitialiser le tri
        </a>
    </div>
<?php endif; ?>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="130">
                        <a href="<?= $sortUrl('inventory_number') ?>" class="text-decoration-none text-dark">
                            N° inventaire <?= $sortIcon('inventory_number') ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?= $sortUrl('designation') ?>" class="text-decoration-none text-dark">
                            Désignation <?= $sortIcon('designation') ?>
                        </a>
                    </th>
                    <th width="130">Catégorie</th>
                    <th width="120">Marque</th>
                    <th width="120">Service</th>
                    <th width="180">Statut</th>
                    <th width="130" class="text-end">
                        <a href="<?= $sortUrl('acquisition_value') ?>" class="text-decoration-none text-dark">
                            Valeur <?= $sortIcon('acquisition_value') ?>
                        </a>
                    </th>
                    <th width="210" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun équipement trouvé.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['category_id']) || !empty($filters['status_id'])): ?>
                                <p class="small">Essayez de modifier vos filtres.</p>
                                <a href="<?= url('equipment') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('equipment/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Ajouter le premier équipement
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['data'] as $eq): ?>
                        <tr>
                            <td>
                                <a href="<?= url('equipment/' . $eq->id) ?>" class="text-decoration-none fw-semibold">
                                    <?= e($eq->inventoryNumber) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($eq->designation) ?></div>
                                <?php if ($eq->serialNumber): ?>
                                    <small class="text-muted">S/N : <?= e($eq->serialNumber) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= e($eq->categoryName ?? '—') ?>
                                </span>
                            </td>
                            <td><?= e($eq->brandName ?? '—') ?></td>
                            <td><?= e($eq->serviceName ?? '—') ?></td>
                            <td>
                                <select class="form-select form-select-sm status-select"
                                        data-id="<?= $eq->id ?>"
                                        data-original="<?= (int) $eq->statusId ?>"
                                        style="border-left: 4px solid <?= e($eq->statusColor ?? '#6c757d') ?>;">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= (int) $s['id'] ?>"
                                                <?= (int) $s['id'] === (int) $eq->statusId ? 'selected' : '' ?>>
                                            <?= e($s['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-end">
                                <?= e($eq->getFormattedValue()) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('equipment/' . $eq->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('equipment/' . $eq->id . '/edit') ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= url('equipment/' . $eq->id . '/duplicate') ?>"
                                   class="btn btn-sm btn-outline-info"
                                   title="Dupliquer">
                                    <i class="bi bi-files"></i>
                                </a>
                                <form method="POST"
                                      action="<?= url('equipment/' . $eq->id . '/delete') ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('⚠️ Supprimer l\'équipement <?= e($eq->inventoryNumber) ?> ?\n\nIl sera placé dans la corbeille et pourra être restauré.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Mettre à la corbeille">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($result['last_page'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <?php
            $currentPage = $result['page'];
            $lastPage = $result['last_page'];
            $queryParams = $_GET;
            ?>

            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => max(1, $currentPage - 1)])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>

            <?php
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
            ?>

            <?php if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => 1])) ?>">1</a>
                </li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($end < $lastPage): ?>
                <?php if ($end < $lastPage - 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $lastPage])) ?>"><?= $lastPage ?></a>
                </li>
            <?php endif; ?>

            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $currentPage + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>