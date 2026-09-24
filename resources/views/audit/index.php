<!-- En-tête de page -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-journal-text text-primary"></i>
            Journal d'audit
            <span class="badge bg-secondary"><?= $result['total'] ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            Historique complet des actions effectuées dans l'application
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('audit') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-clockwise"></i> Réinitialiser
        </a>
    </div>
</div>

<!-- Statistiques par sévérité -->
<div class="row g-2 mb-3">
    <div class="col-md-3 col-6">
        <div class="card text-center border-info">
            <div class="card-body py-2">
                <div class="fs-5 fw-bold text-info"><?= (int) ($counts['info'] ?? 0) ?></div>
                <div class="small text-muted">
                    <i class="bi bi-info-circle"></i> Info
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-warning">
            <div class="card-body py-2">
                <div class="fs-5 fw-bold text-warning"><?= (int) ($counts['warning'] ?? 0) ?></div>
                <div class="small text-muted">
                    <i class="bi bi-exclamation-triangle"></i> Warning
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-danger">
            <div class="card-body py-2">
                <div class="fs-5 fw-bold text-danger"><?= (int) ($counts['error'] ?? 0) ?></div>
                <div class="small text-muted">
                    <i class="bi bi-x-circle"></i> Error
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center border-dark">
            <div class="card-body py-2">
                <div class="fs-5 fw-bold text-dark"><?= (int) ($counts['critical'] ?? 0) ?></div>
                <div class="small text-muted">
                    <i class="bi bi-exclamation-octagon"></i> Critique
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('audit') ?>" class="row g-2 align-items-end">
            <!-- Recherche -->
            <div class="col-md-3">
                <label class="form-label small text-muted">Recherche</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Utilisateur, URL..."
                           value="<?= e($filters['search'] ?? '') ?>">
                </div>
            </div>

            <!-- Action -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>"
                            <?= ($filters['action'] ?? '') === $act ? 'selected' : '' ?>>
                            <?= e($act) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Entité -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Entité</label>
                <select name="entity_type" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($entityTypes as $et): ?>
                        <option value="<?= e($et) ?>"
                            <?= ($filters['entity_type'] ?? '') === $et ? 'selected' : '' ?>>
                            <?= e($et) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sévérité -->
            <div class="col-md-2">
                <label class="form-label small text-muted">Sévérité</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($severities as $sev): ?>
                        <option value="<?= e($sev) ?>"
                            <?= ($filters['severity'] ?? '') === $sev ? 'selected' : '' ?>>
                            <?= e(ucfirst($sev)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Boutons -->
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
                <a href="<?= url('audit') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tableau des logs -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-list-ul text-primary"></i>
            Résultats (<?= $result['total'] ?>)
        </h6>
        <small class="text-muted">
            Page <?= $result['page'] ?> sur <?= $result['last_page'] ?>
        </small>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th width="140">Date / Heure</th>
                    <th width="150">Utilisateur</th>
                    <th width="140">Action</th>
                    <th width="100">Entité</th>
                    <th width="70" class="text-center">ID</th>
                    <th width="80" class="text-center">Sévérité</th>
                    <th>Détails</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune entrée trouvée.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['data'] as $log): ?>
                        <?php
                        // Config visuelle par action
                        $actionConfig = [
                            'create'             => ['color' => 'success', 'icon' => 'plus-circle'],
                            'update'             => ['color' => 'primary', 'icon' => 'pencil'],
                            'status_change'      => ['color' => 'warning', 'icon' => 'arrow-repeat'],
                            'delete'             => ['color' => 'danger',  'icon' => 'trash'],
                            'restore'            => ['color' => 'success', 'icon' => 'arrow-counterclockwise'],
                            'force_delete'       => ['color' => 'dark',    'icon' => 'x-octagon'],
                            'bulk_status_change' => ['color' => 'warning', 'icon' => 'arrow-repeat'],
                            'bulk_delete'        => ['color' => 'danger',  'icon' => 'trash'],
                            'import'             => ['color' => 'info',    'icon' => 'upload'],
                        ];
                        $cfg = $actionConfig[$log['action']] ?? ['color' => 'secondary', 'icon' => 'circle'];

                        // Severity badge
                        $severityBadge = [
                            'info'     => 'bg-info',
                            'warning'  => 'bg-warning text-dark',
                            'error'    => 'bg-danger',
                            'critical' => 'bg-dark',
                        ][$log['severity'] ?? 'info'] ?? 'bg-secondary';

                        // Détails (JSON new_values)
                        $details = '';
                        if (!empty($log['new_values'])) {
                            $decoded = json_decode($log['new_values'], true);
                            if (is_array($decoded)) {
                                $parts = [];
                                foreach (array_slice($decoded, 0, 3) as $k => $v) {
                                    if ($v !== null && $v !== '') {
                                        $parts[] = '<strong>' . e($k) . '</strong>: ' . e((string) $v);
                                    }
                                }
                                $details = implode(' • ', $parts);
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <small class="text-muted">
                                    <?= $log['created_at']
                                        ? e(date('d/m/Y', strtotime($log['created_at'])))
                                        : '—' ?><br>
                                    <span class="text-secondary">
                                        <?= $log['created_at']
                                            ? e(date('H:i:s', strtotime($log['created_at'])))
                                            : '' ?>
                                    </span>
                                </small>
                            </td>
                            <td>
                                <i class="bi bi-person-circle text-muted"></i>
                                <small><?= e($log['user_name'] ?? 'Système') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $cfg['color'] ?>">
                                    <i class="bi bi-<?= $cfg['icon'] ?>"></i>
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-box-seam text-muted"></i>
                                    <?= e($log['entity_type'] ?? '—') ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($log['entity_id']) && $log['entity_type'] === 'equipment'): ?>
                                    <a href="<?= url('equipment/' . (int) $log['entity_id']) ?>"
                                       class="text-decoration-none fw-semibold">
                                        #<?= (int) $log['entity_id'] ?>
                                    </a>
                                <?php else: ?>
                                    <code><?= !empty($log['entity_id']) ? '#' . (int) $log['entity_id'] : '—' ?></code>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $severityBadge ?>">
                                    <?= e($log['severity'] ?? 'info') ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $details ?: '<em>Aucun détail</em>' ?>
                                </small>
                                <?php if (!empty($log['url'])): ?>
                                    <br>
                                    <small class="text-secondary">
                                        <code style="font-size: 0.7rem;"><?= e($log['method'] ?? 'GET') ?></code>
                                        <code style="font-size: 0.7rem;"><?= e($log['url']) ?></code>
                                    </small>
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
<?php if ($result['last_page'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <?php
            $currentPage = $result['page'];
            $lastPage    = $result['last_page'];
            $queryParams = $_GET;
            ?>

            <!-- Précédent -->
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

            <!-- Suivant -->
            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $currentPage + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>