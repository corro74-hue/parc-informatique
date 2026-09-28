<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('dashboard') ?>" class="text-decoration-none">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li class="breadcrumb-item active">Administration</li>
        <li class="breadcrumb-item active">Sauvegardes BDD</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-database-fill-gear text-primary"></i>
            Sauvegardes de la base de données
        </h4>
        <p class="text-muted mb-0 small">
            <?= count($backups) ?> sauvegarde<?= count($backups) > 1 ? 's' : '' ?> disponible<?= count($backups) > 1 ? 's' : '' ?>
            — Total : <?= e($info['size_formatted']) ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/database/health') ?>" class="btn btn-outline-info btn-sm">
            <i class="bi bi-heart-pulse"></i> Santé BDD
        </a>
        <form method="POST" action="<?= url('admin/database/clean') ?>" class="d-inline"
              onsubmit="return confirm('Nettoyer les anciennes sauvegardes selon la politique de rétention ?\n\n(7 jours + 4 semaines + 12 mois)');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eraser"></i> Nettoyer
            </button>
        </form>
        <form method="POST" action="<?= url('admin/database/backup') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nouvelle sauvegarde
            </button>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- 📖 DOCUMENTATION INTÉGRÉE — Comment ça marche ? -->
<!-- ============================================ -->
<div class="alert alert-info mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-info-circle-fill"></i> Comment ça marche ?
        </h6>
        <button class="btn btn-sm btn-link text-decoration-none p-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#docBackups"
                aria-expanded="true">
            <i class="bi bi-chevron-up"></i>
        </button>
    </div>
    <div class="collapse show mt-2" id="docBackups">
        <div class="row g-3 small">
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-bullseye text-primary fs-5"></i>
                    </div>
                    <div>
                        <strong>🎯 À quoi ça sert ?</strong>
                        <p class="mb-2 text-muted">
                            Les sauvegardes protègent vos données contre les pannes, erreurs humaines,
                            corruptions ou attaques. En cas de problème, vous pouvez restaurer la base
                            à un état antérieur.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-tools text-primary fs-5"></i>
                    </div>
                    <div>
                        <strong>🛠️ Comment l'utiliser ?</strong>
                        <ol class="mb-2 text-muted ps-3">
                            <li>Cliquez sur <strong>"Nouvelle sauvegarde"</strong>.</li>
                            <li>Attendez la confirmation (5-10 sec).</li>
                            <li>La sauvegarde apparaît dans la liste ci-dessous.</li>
                            <li>Téléchargez-la pour la conserver hors ligne.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                    </div>
                    <div>
                        <strong>⚠️ Précautions</strong>
                        <ul class="mb-2 text-muted ps-3">
                            <li>Ne supprimez pas la dernière sauvegarde.</li>
                            <li>Conservez une copie sur un disque externe ou cloud.</li>
                            <li>Testez régulièrement la restauration.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-lightbulb text-warning fs-5"></i>
                    </div>
                    <div>
                        <strong>💡 Bonnes pratiques</strong>
                        <ul class="mb-0 text-muted ps-3">
                            <li>Sauvegardez <strong>avant</strong> chaque mise à jour importante.</li>
                            <li>La rétention automatique garde 7 jours + 4 semaines + 12 mois.</li>
                            <li>Le nettoyage supprime les backups obsolètes pour libérer l'espace.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RECOMMANDATIONS AUTOMATIQUES                  -->
<!-- ============================================ -->
<?php if (!empty($recommendations)): ?>
    <?php foreach ($recommendations as $reco): ?>
        <div class="alert alert-<?= e($reco['level']) ?> d-flex align-items-start mb-2 small">
            <i class="bi bi-<?= $reco['level'] === 'danger' ? 'exclamation-octagon-fill' : ($reco['level'] === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill') ?> me-2 fs-5"></i>
            <div>
                <strong><?= e($reco['title']) ?></strong>
                <p class="mb-0"><?= e($reco['message']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ============================================ -->
<!-- STATISTIQUES                                  -->
<!-- ============================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Sauvegardes</div>
                        <div class="fs-4 fw-bold"><?= (int) $stats['total'] ?></div>
                    </div>
                    <div class="text-primary" style="font-size: 2rem;">
                        <i class="bi bi-file-earmark-zip"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Espace utilisé</div>
                        <div class="fs-4 fw-bold"><?= e($this->formatSize ?? number_format($stats['size_total'] / 1024, 0, ',', ' ') . ' Ko') ?></div>
                    </div>
                    <div class="text-success" style="font-size: 2rem;">
                        <i class="bi bi-hdd"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Tables</div>
                        <div class="fs-4 fw-bold"><?= (int) $info['table_count'] ?></div>
                    </div>
                    <div class="text-info" style="font-size: 2rem;">
                        <i class="bi bi-table"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Taille BDD</div>
                        <div class="fs-4 fw-bold"><?= e($info['size_formatted']) ?></div>
                    </div>
                    <div class="text-warning" style="font-size: 2rem;">
                        <i class="bi bi-database-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- LISTE DES SAUVEGARDES                         -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-list-ul text-primary"></i> Historique des sauvegardes
        </h6>
        <span class="badge bg-secondary"><?= count($backups) ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($backups)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-2">Aucune sauvegarde pour l'instant.</p>
                <form method="POST" action="<?= url('admin/database/backup') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> Créer la première sauvegarde
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60" class="text-center">#</th>
                            <th>Nom du fichier</th>
                            <th width="100" class="text-center">Type</th>
                            <th width="120" class="text-end">Taille</th>
                            <th width="120" class="text-center">Statut</th>
                            <th width="150">Créé le</th>
                            <th width="150">Origine</th>
                            <th width="130" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $backup): ?>
                            <?php
                            $statusBadge = match ($backup['status']) {
                                'success' => '<span class="badge bg-success"><i class="bi bi-check"></i> Succès</span>',
                                'failed'  => '<span class="badge bg-danger"><i class="bi bi-x"></i> Échec</span>',
                                default   => '<span class="badge bg-warning text-dark">Partiel</span>',
                            };

                            $triggeredBy = match ($backup['triggered_by']) {
                                'cron'   => '<span class="badge bg-info text-dark"><i class="bi bi-clock"></i> Cron</span>',
                                'system' => '<span class="badge bg-secondary">Système</span>',
                                default  => '<span class="badge bg-primary"><i class="bi bi-person"></i> Manuel</span>',
                            };
                            ?>
                            <tr>
                                <td class="text-center">
                                    <code>#<?= (int) $backup['id'] ?></code>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <i class="bi bi-file-earmark-zip text-primary"></i>
                                        <?= e($backup['filename']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        <?= e($backup['type']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong><?= number_format($backup['size_bytes'] / 1024, 0, ',', ' ') ?> Ko</strong>
                                </td>
                                <td class="text-center"><?= $statusBadge ?></td>
                                <td>
                                    <div><?= e(date('d/m/Y', strtotime($backup['created_at']))) ?></div>
                                    <small class="text-muted"><?= e(date('H:i:s', strtotime($backup['created_at']))) ?></small>
                                </td>
                                <td><?= $triggeredBy ?></td>
                                <td class="text-center">
                                    <a href="<?= url('admin/database/backup/' . (int) $backup['id'] . '/download') ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Télécharger">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST"
                                          action="<?= url('admin/database/backup/' . (int) $backup['id'] . '/delete') ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('⚠️ Supprimer cette sauvegarde ?\n\n<?= e($backup['filename']) ?>\n\nCette action est irréversible.');">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Supprimer"
                                                <?= count($backups) <= 1 ? 'disabled' : '' ?>>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Note de bas de page -->
<div class="text-muted small mt-3">
    <i class="bi bi-info-circle"></i>
    Les sauvegardes sont stockées dans <code>storage/backups/database/</code>.
    La rétention automatique est de <strong>7 jours + 4 semaines + 12 mois</strong>.
</div>