<?php
/**
 * Vue : Liste des affectations
 *
 * @var string $title
 * @var \App\Models\Assignment[] $assignments
 * @var int    $total
 * @var int    $page
 * @var int    $lastPage
 * @var int    $perPage
 * @var array  $filters
 * @var array  $stats
 */
?>

<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-people-fill text-primary"></i>
            Affectations
            <span class="badge bg-secondary"><?= (int) $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= (int) $total ?> affectation<?= $total > 1 ? 's' : '' ?>
            — page <?= (int) $page ?> sur <?= (int) $lastPage ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('assignments/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvelle affectation
        </a>
    </div>
</div>

<!-- Cartes stats -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3" style="font-size: 2rem; color: #3b82f6;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="h4 mb-0"><?= (int) $stats['active'] ?></div>
                    <div class="text-muted small">Affectations actives</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3" style="font-size: 2rem; color: #6c757d;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="h4 mb-0"><?= (int) $stats['total'] ?></div>
                    <div class="text-muted small">Total historique</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('assignments') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Recherche</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="N° inventaire, désignation, employé, matricule..."
                    value="<?= e((string) ($filters['search'] ?? '')) ?>"
                >
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <option value="active"   <?= ($filters['status'] ?? '') === 'active'   ? 'selected' : '' ?>>En cours</option>
                    <option value="returned" <?= ($filters['status'] ?? '') === 'returned' ? 'selected' : '' ?>>Retournées</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                    <a href="<?= url('assignments') ?>" class="btn btn-outline-secondary">
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
                    <th width="150">N° inventaire</th>
                    <th>Désignation</th>
                    <th>Employé</th>
                    <th width="140">Service</th>
                    <th width="110">Début</th>
                    <th width="110">Fin</th>
                    <th width="120">Statut</th>
                    <th width="140" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune affectation trouvée.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['status'])): ?>
                                <p class="small">Essayez de modifier vos filtres.</p>
                                <a href="<?= url('assignments') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('assignments/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Créer la première affectation
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td>
                                <a href="<?= url('assignments/' . $a->id) ?>" class="text-decoration-none fw-semibold">
                                    <?= e((string) $a->equipmentInventoryNumber) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e((string) $a->equipmentDesignation) ?></div>
                                <?php if ($a->equipmentCategoryName): ?>
                                    <small class="text-muted">
                                        <span class="badge bg-light text-dark border">
                                            <?= e($a->equipmentCategoryName) ?>
                                        </span>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= e($a->getEmployeeFullName()) ?></div>
                                <?php if ($a->employeeMatricule): ?>
                                    <small class="text-muted">Matricule : <?= e($a->employeeMatricule) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e((string) ($a->serviceName ?? '—')) ?></td>
                            <td><?= e(date('d/m/Y', strtotime($a->startDate))) ?></td>
                            <td>
                                <?= $a->endDate
                                    ? e(date('d/m/Y', strtotime($a->endDate)))
                                    : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td><?= $a->getStatusBadge() ?></td>
                            <td class="text-center">
                                <a href="<?= url('assignments/' . $a->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($a->isActive()): ?>
                                    <a href="<?= url('assignments/' . $a->id . '/edit') ?>"
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
            <?php
            $currentPage = (int) $page;
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