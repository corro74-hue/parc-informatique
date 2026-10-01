<?php
/**
 * @var \App\Models\User|null $user
 * @var array $expiringWarranties
 * @var array $recentlyExpiredWarranties
 * @var array $stats
 */
?>

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
<!-- ALERTES GARANTIES                            -->
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
<!-- CARTES STATISTIQUES DYNAMIQUES              -->
<!-- ============================================ -->
<div class="row g-3 mb-4">

    <!-- Équipements -->
    <div class="col-md-3">
        <a href="<?= url('equipment') ?>" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">ÉQUIPEMENTS</div>
                            <h2 class="mb-0 text-dark"><?= (int) $stats['equipment_total'] ?></h2>
                            <small>
                                <?php if ($stats['equipment_active'] > 0): ?>
                                    <span class="text-success"><?= (int) $stats['equipment_active'] ?> actifs</span>
                                <?php endif; ?>
                                <?php if ($stats['equipment_reformed'] > 0): ?>
                                    <span class="text-secondary ms-2"><?= (int) $stats['equipment_reformed'] ?> réformés</span>
                                <?php endif; ?>
                                <?php if ($stats['equipment_to_reform'] > 0): ?>
                                    <span class="text-warning ms-2"><?= (int) $stats['equipment_to_reform'] ?> à réformer</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Maintenance en cours -->
    <div class="col-md-3">
        <a href="<?= url('maintenance') ?>" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">MAINTENANCES OUVERTES</div>
                            <h2 class="mb-0 text-dark"><?= (int) $stats['maintenance_open'] ?></h2>
                            <small class="text-muted">interventions en cours</small>
                        </div>
                        <i class="bi bi-tools text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Réformes en attente -->
    <div class="col-md-3">
        <a href="<?= url('reformations') ?>" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">RÉFORMES EN COURS</div>
                            <h2 class="mb-0 text-dark"><?= (int) $stats['reforms_pending'] ?></h2>
                            <small class="text-muted">dossiers non clôturés</small>
                        </div>
                        <i class="bi bi-recycle text-danger" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Documents -->
    <div class="col-md-3">
        <a href="<?= url('documents') ?>" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">DOCUMENTS</div>
                            <h2 class="mb-0 text-dark"><?= (int) $stats['documents_total'] ?></h2>
                            <small class="text-muted">fichiers en GED</small>
                        </div>
                        <i class="bi bi-file-earmark-text text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

</div>

<!-- ============================================ -->
<!-- RACCOURCIS RAPIDES                           -->
<!-- ============================================ -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <strong><i class="bi bi-lightning-charge-fill text-warning"></i> Accès rapides</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">

                    <div class="col-md-2 col-4">
                        <a href="<?= url('equipment/create') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-plus-circle text-primary" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Nouvel équipement</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-4">
                        <a href="<?= url('maintenance/create') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-wrench-adjustable text-warning" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Nouvelle maintenance</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-4">
                        <a href="<?= url('reformations/create') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-recycle text-danger" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Nouvelle réforme</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-4">
                        <a href="<?= url('documents/create') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-cloud-upload text-info" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Nouveau document</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-4">
                        <a href="<?= url('assignments/create') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Nouvelle affectation</div>
                        </a>
                    </div>

                    <div class="col-md-2 col-4">
                        <a href="<?= url('admin/database') ?>" class="text-decoration-none d-block p-3 rounded hover-bg-light">
                            <i class="bi bi-database-fill-gear text-secondary" style="font-size: 2rem;"></i>
                            <div class="mt-2 small fw-semibold text-dark">Sauvegardes</div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DERNIERS DOCUMENTS AJOUTÉS                   -->
<!-- ============================================ -->
<?php if (!empty($stats['documents_recent'])): ?>
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-clock-history text-info"></i> Derniers documents ajoutés</strong>
                    <a href="<?= url('documents') ?>" class="btn btn-sm btn-outline-secondary">
                        Voir tous <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Référence</th>
                                    <th>Titre</th>
                                    <th class="text-end">Ajouté</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['documents_recent'] as $doc): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('documents/' . $doc['id']) ?>" class="text-decoration-none">
                                                <code><?= e($doc['reference'] ?? '#'.$doc['id']) ?></code>
                                            </a>
                                        </td>
                                        <td><?= e($doc['title']) ?></td>
                                        <td class="text-end">
                                            <small class="text-muted">
                                                <?= e(date('d/m/Y', strtotime($doc['created_at']))) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé rapide -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong><i class="bi bi-bar-chart-fill text-primary"></i> Résumé</strong>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small"><i class="bi bi-people"></i> Utilisateurs</span>
                        <strong><?= (int) $stats['users_total'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small"><i class="bi bi-person-badge"></i> Employés</span>
                        <strong><?= (int) $stats['employees_total'] ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted small"><i class="bi bi-link-45deg"></i> Affectations actives</span>
                        <strong><?= (int) $stats['assignments_active'] ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Style pour les raccourcis -->
<style>
    .hover-bg-light {
        transition: background-color 0.15s;
    }
    .hover-bg-light:hover {
        background-color: rgba(0, 0, 0, 0.04);
    }
    [data-bs-theme="dark"] .hover-bg-light:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    [data-bs-theme="dark"] .text-dark {
        color: #e2e8f0 !important;
    }
</style>