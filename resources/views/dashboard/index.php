<!-- Bandeau de bienvenue -->
<div class="card mb-4">
    <div class="card-body">
        <h3 class="mb-1">👋 Bienvenue, <?= e($user->getFullName() ?? 'Utilisateur') ?></h3>
        <p class="text-muted mb-0">
            Dernière connexion :
            <?= $user && $user->lastLoginAt ? e(date('d/m/Y à H:i', strtotime($user->lastLoginAt))) : 'Première connexion' ?>
        </p>
    </div>
</div>

<!-- ============================================ -->
<!-- NOUVEAU : ALERTES GARANTIES                  -->
<!-- ============================================ -->

<!-- Alerte : garanties qui expirent bientôt (dans ≤ 30 jours) -->
<?php if (!empty($expiringWarranties)): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-warning-emphasis">
                <i class="bi bi-shield-exclamation"></i>
                Garanties qui expirent bientôt
                <span class="badge bg-warning text-dark ms-2"><?= count($expiringWarranties) ?></span>
            </h5>
            <a href="<?= url('equipment?under_warranty=1') ?>" class="btn btn-sm btn-outline-warning">
                Voir tous <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Ces équipements ont une garantie qui expire dans les <strong>30 prochains jours</strong>.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Inventaire</th>
                            <th>Désignation</th>
                            <th>Service</th>
                            <th>Fin de garantie</th>
                            <th class="text-end">Jours restants</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiringWarranties as $eq): ?>
                            <?php
                            $daysLeft = (int) ceil((strtotime($eq->warrantyEndDate) - time()) / 86400);
                            $badgeClass = $daysLeft <= 7 ? 'bg-danger' : ($daysLeft <= 15 ? 'bg-warning text-dark' : 'bg-info text-dark');
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= url('equipment/' . $eq->id) ?>" class="text-decoration-none fw-semibold">
                                        <?= e($eq->inventoryNumber) ?>
                                    </a>
                                </td>
                                <td><?= e($eq->designation) ?></td>
                                <td><?= e($eq->serviceName ?? '—') ?></td>
                                <td><?= e(date('d/m/Y', strtotime($eq->warrantyEndDate))) ?></td>
                                <td class="text-end">
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $daysLeft ?> jour<?= $daysLeft > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Alerte : garanties récemment expirées (dans les 30 derniers jours) -->
<?php if (!empty($recentlyExpiredWarranties)): ?>
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-danger-emphasis">
                <i class="bi bi-shield-x"></i>
                Garanties récemment expirées
                <span class="badge bg-danger ms-2"><?= count($recentlyExpiredWarranties) ?></span>
            </h5>
            <a href="<?= url('equipment') ?>" class="btn btn-sm btn-outline-danger">
                Voir tous <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Ces équipements ont vu leur garantie expirer dans les <strong>30 derniers jours</strong>.
                Pensez à vérifier s'ils sont toujours couverts.
            </p>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Inventaire</th>
                            <th>Désignation</th>
                            <th>Service</th>
                            <th>Fin de garantie</th>
                            <th class="text-end">Expirée depuis</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentlyExpiredWarranties as $eq): ?>
                            <?php
                            $daysAgo = (int) ceil((time() - strtotime($eq->warrantyEndDate)) / 86400);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= url('equipment/' . $eq->id) ?>" class="text-decoration-none fw-semibold">
                                        <?= e($eq->inventoryNumber) ?>
                                    </a>
                                </td>
                                <td><?= e($eq->designation) ?></td>
                                <td><?= e($eq->serviceName ?? '—') ?></td>
                                <td><?= e(date('d/m/Y', strtotime($eq->warrantyEndDate))) ?></td>
                                <td class="text-end">
                                    <span class="badge bg-secondary">
                                        <?= $daysAgo ?> jour<?= $daysAgo > 1 ? 's' : '' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- FIN ALERTES GARANTIES                        -->
<!-- ============================================ -->

<!-- Cartes statistiques (placeholder) -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">ÉQUIPEMENTS</div>
                        <h2 class="mb-0">0</h2>
                    </div>
                    <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">EN MAINTENANCE</div>
                        <h2 class="mb-0">0</h2>
                    </div>
                    <i class="bi bi-tools text-warning" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">À RÉFORMER</div>
                        <h2 class="mb-0">0</h2>
                    </div>
                    <i class="bi bi-recycle text-danger" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small mb-1">UTILISATEURS</div>
                        <h2 class="mb-0">1</h2>
                    </div>
                    <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Message informatif -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-rocket-takeoff text-primary" style="font-size: 4rem;"></i>
        <h4 class="mt-3">L'application est en cours de construction</h4>
        <p class="text-muted">
            Vous êtes connecté avec succès. Les modules Inventaire, Maintenance, Réforme et Documents
            seront ajoutés progressivement.
        </p>
        <div class="mt-3">
            <span class="badge bg-primary">Étape 1 : Authentification ✅</span>
            <span class="badge bg-secondary">Étape 2 : Inventaire (à venir)</span>
            <span class="badge bg-secondary">Étape 3 : Maintenance (à venir)</span>
            <span class="badge bg-secondary">Étape 4 : Réforme (à venir)</span>
        </div>
    </div>
</div>