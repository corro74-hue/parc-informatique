<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item active">
            <i class="bi bi-trash"></i> Corbeille
        </li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-trash text-danger"></i>
            Corbeille
            <span class="badge bg-secondary"><?= $result['total'] ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            Éléments supprimés récemment.
            Ils peuvent être restaurés à tout moment.
        </p>
    </div>
    <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour aux équipements
    </a>
</div>

<!-- Info -->
<div class="alert alert-warning d-flex align-items-center mb-3">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
    <div>
        Les équipements de la corbeille <strong>ne sont pas définitivement supprimés</strong>.
        Vous pouvez les restaurer à tout moment, ou les supprimer définitivement.
    </div>
</div>

<!-- Recherche -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('equipment/trash') ?>" class="row g-2 align-items-end">
            <div class="col-md-10">
                <label class="form-label small text-muted">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="N° inventaire, désignation, N° série..."
                           value="<?= e($filters['search'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Rechercher
                </button>
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
                    <th width="130">N° inventaire</th>
                    <th>Désignation</th>
                    <th width="130">Catégorie</th>
                    <th width="150">Supprimé le</th>
                    <th width="200" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($result['data'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-trash" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">La corbeille est vide.</p>
                            <p class="small">Les équipements supprimés apparaîtront ici.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($result['data'] as $eq): ?>
                        <tr>
                            <td>
                                <strong class="text-muted"><?= e($eq->inventoryNumber) ?></strong>
                            </td>
                            <td>
                                <div><?= e($eq->designation) ?></div>
                                <?php if ($eq->serialNumber): ?>
                                    <small class="text-muted">S/N : <?= e($eq->serialNumber) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= e($eq->categoryName ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $eq->deletedAt ? e(date('d/m/Y H:i', strtotime($eq->deletedAt))) : '—' ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <!-- Restaurer -->
                                <form method="POST" action="<?= url('equipment/' . $eq->id . '/restore') ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('Restaurer cet équipement ?\n\nIl réapparaîtra dans la liste principale.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restaurer">
                                        <i class="bi bi-arrow-clockwise"></i> Restaurer
                                    </button>
                                </form>

                                <!-- Supprimer définitivement -->
                                <form method="POST" action="<?= url('equipment/' . $eq->id . '/force-delete') ?>"
                                      class="d-inline"
                                      onsubmit="return confirm('⚠️ SUPPRESSION DÉFINITIVE ⚠️\n\nCette action est IRRÉVERSIBLE.\n\nConfirmer la suppression définitive ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer définitivement">
                                        <i class="bi bi-x-circle"></i>
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

            <?php for ($i = max(1, $currentPage - 2); $i <= min($lastPage, $currentPage + 2); $i++): ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $currentPage + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>