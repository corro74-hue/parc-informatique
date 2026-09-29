<?php
/**
 * Vue : Liste des employés
 *
 * @var string $title
 * @var \App\Models\Employee[] $employees
 * @var int $total
 * @var int $page
 * @var int $lastPage
 * @var int $perPage
 * @var array $filters
 * @var array $stats
 * @var array $services
 */
?>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-person-badge text-primary"></i>
            Employés
            <span class="badge bg-secondary"><?= (int) $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= (int) $total ?> employé<?= $total > 1 ? 's' : '' ?>
            — page <?= (int) $page ?> sur <?= (int) $lastPage ?>
        </p>
    </div>
    <a href="<?= url('employees/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvel employé
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3" style="font-size: 2rem; color: #22c55e;">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div>
                    <div class="h4 mb-0"><?= (int) $stats['active'] ?></div>
                    <div class="text-muted small">Employés actifs</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3" style="font-size: 2rem; color: #6c757d;">
                    <i class="bi bi-people-fill"></i>
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
        <form method="GET" action="<?= url('employees') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Recherche</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Nom, prénom, matricule, email..."
                       value="<?= e((string) ($filters['search'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Service</label>
                <select name="service_id" class="form-select">
                    <option value="">Tous les services</option>
                    <?php foreach ($services as $srv): ?>
                        <option value="<?= (int) $srv['id'] ?>"
                                <?= (string) ($filters['service_id'] ?? '') === (string) $srv['id'] ? 'selected' : '' ?>>
                            <?= e($srv['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Statut</label>
                <select name="is_active" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Actifs</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactifs</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['service_id']) || $filters['is_active'] !== ''): ?>
                    <a href="<?= url('employees') ?>" class="btn btn-outline-secondary">
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
                    <th width="80">Matricule</th>
                    <th>Nom complet</th>
                    <th>Fonction</th>
                    <th>Service</th>
                    <th>Contact</th>
                    <th width="100" class="text-center">Statut</th>
                    <th width="140" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employees)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucun employé trouvé.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['service_id'])): ?>
                                <a href="<?= url('employees') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('employees/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Ajouter le premier employé
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><code><?= e((string) ($emp->matricule ?? '—')) ?></code></td>
                            <td>
                                <a href="<?= url('employees/' . $emp->id) ?>" class="text-decoration-none fw-semibold">
                                    <?= e($emp->getFullName()) ?>
                                </a>
                            </td>
                            <td><?= e((string) ($emp->functionTitle ?? '—')) ?></td>
                            <td>
                                <?php if ($emp->serviceName): ?>
                                    <span class="badge bg-light text-dark border">
                                        <?= e($emp->serviceName) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp->email): ?>
                                    <div class="small"><i class="bi bi-envelope"></i> <?= e($emp->email) ?></div>
                                <?php endif; ?>
                                <?php if ($emp->phone): ?>
                                    <div class="small"><i class="bi bi-telephone"></i> <?= e($emp->phone) ?></div>
                                <?php endif; ?>
                                <?php if (!$emp->email && !$emp->phone): ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($emp->isActive): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('employees/' . $emp->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= url('employees/' . $emp->id . '/edit') ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
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