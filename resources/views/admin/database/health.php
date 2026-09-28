<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('dashboard') ?>" class="text-decoration-none">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('admin/database') ?>" class="text-decoration-none">
                <i class="bi bi-database-fill-gear"></i> Sauvegardes BDD
            </a>
        </li>
        <li class="breadcrumb-item active">Santé BDD</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-heart-pulse text-danger"></i>
            Santé de la base de données
        </h4>
        <p class="text-muted mb-0 small">
            Monitoring, intégrité et optimisation
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/database') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour aux sauvegardes
        </a>
        <form method="POST" action="<?= url('admin/database/check') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-info btn-sm">
                <i class="bi bi-shield-check"></i> Vérifier l'intégrité
            </button>
        </form>
        <form method="POST" action="<?= url('admin/database/optimize') ?>" class="d-inline"
              onsubmit="return confirm('Lancer l\'optimisation de toutes les tables ?\n\nCette opération peut prendre plusieurs secondes.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-lightning-charge"></i> Optimiser les tables
            </button>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- 📖 DOCUMENTATION INTÉGRÉE                     -->
<!-- ============================================ -->
<div class="alert alert-info mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-info-circle-fill"></i> Comment ça marche ?
        </h6>
        <button class="btn btn-sm btn-link text-decoration-none p-0"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#docHealth"
                aria-expanded="true">
            <i class="bi bi-chevron-up"></i>
        </button>
    </div>
    <div class="collapse show mt-2" id="docHealth">
        <div class="row g-3 small">
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="me-2">
                        <i class="bi bi-bullseye text-primary fs-5"></i>
                    </div>
                    <div>
                        <strong>🎯 À quoi ça sert ?</strong>
                        <p class="mb-2 text-muted">
                            La santé de la BDD surveille la taille, l'intégrité et les performances
                            de votre base de données pour détecter les problèmes avant qu'ils n'impactent l'application.
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
                        <ul class="mb-2 text-muted ps-3">
                            <li><strong>Vérifier l'intégrité</strong> : lance <code>CHECK TABLE</code> sur toutes les tables.</li>
                            <li><strong>Optimiser</strong> : lance <code>OPTIMIZE TABLE</code> pour libérer de l'espace.</li>
                            <li><strong>Consulter</strong> : analyse les tailles pour anticiper les problèmes de disque.</li>
                        </ul>
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
                            <li>L'optimisation peut prendre plusieurs minutes sur grosse BDD.</li>
                            <li>Préférez une période de faible activité.</li>
                            <li>Faites une sauvegarde avant de grosse modification.</li>
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
                        <strong>💡 Fréquences recommandées</strong>
                        <ul class="mb-0 text-muted ps-3">
                            <li><strong>Intégrité</strong> : 1 fois par mois.</li>
                            <li><strong>Optimisation</strong> : 1 fois par trimestre.</li>
                            <li><strong>Surveillance taille</strong> : hebdomadaire.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- RECOMMANDATIONS                               -->
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
<!-- INFORMATIONS GÉNÉRALES                        -->
<!-- ============================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Taille totale</div>
                <div class="fs-4 fw-bold text-primary"><?= e($info['size_formatted']) ?></div>
                <small class="text-muted">Base : <?= e($info['table_count']) ?> tables</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Version MySQL</div>
                <div class="fs-6 fw-bold"><?= e($info['version']) ?></div>
                <small class="text-muted"><?= e($info['charset']) ?> / <?= e($info['collation']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Uptime serveur</div>
                <div class="fs-5 fw-bold text-success"><?= e($info['uptime_formatted']) ?></div>
                <small class="text-muted">Depuis le dernier démarrage</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total lignes</div>
                <div class="fs-5 fw-bold text-info"><?= number_format($stats['total_rows'], 0, ',', ' ') ?></div>
                <small class="text-muted">Toutes tables confondues</small>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- STATISTIQUES DÉTAILLÉES                       -->
<!-- ============================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart-fill text-primary"></i> Statistiques
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Nombre de tables</span>
                    <strong><?= (int) $stats['tables'] ?></strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Total lignes</span>
                    <strong><?= number_format($stats['total_rows'], 0, ',', ' ') ?></strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Taille totale</span>
                    <strong><?= number_format($stats['total_size'] / 1024, 0, ',', ' ') ?> Ko</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Taille moyenne/ligne</span>
                    <strong><?= number_format($stats['avg_row_size'], 0, ',', ' ') ?> o</strong>
                </div>
                <div class="pt-2">
                    <div class="text-muted small mb-2">Moteurs de stockage</div>
                    <?php foreach ($stats['engine_breakdown'] as $engine => $count): ?>
                        <span class="badge bg-light text-dark border me-1">
                            <?= e($engine) ?> : <?= (int) $count ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-trophy-fill text-warning"></i> Plus grosse table
                </h6>
            </div>
            <div class="card-body">
                <?php if ($stats['biggest_table']): ?>
                    <div class="mb-3">
                        <div class="text-muted small">Nom de la table</div>
                        <code class="fs-6"><?= e($stats['biggest_table']['table_name']) ?></code>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">Taille</div>
                            <strong><?= number_format((float) $stats['biggest_table']['size_kb'], 2, ',', ' ') ?> Ko</strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Lignes</div>
                            <strong><?= number_format((int) $stats['biggest_table']['row_count'], 0, ',', ' ') ?></strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Données</div>
                            <strong><?= number_format((float) $stats['biggest_table']['data_kb'], 2, ',', ' ') ?> Ko</strong>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">Index</div>
                            <strong><?= number_format((float) $stats['biggest_table']['index_kb'], 2, ',', ' ') ?> Ko</strong>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Aucune donnée</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- DÉTAIL DES TABLES                             -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-table text-primary"></i> Détail des tables
        </h6>
        <span class="badge bg-secondary"><?= count($tables) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Table</th>
                        <th width="100" class="text-center">Moteur</th>
                        <th width="100" class="text-end">Lignes</th>
                        <th width="100" class="text-end">Données</th>
                        <th width="100" class="text-end">Index</th>
                        <th width="100" class="text-end">Total</th>
                        <th width="150">Dernière modif</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $table): ?>
                        <tr>
                            <td>
                                <i class="bi bi-table text-primary me-1"></i>
                                <code><?= e($table['table_name']) ?></code>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?= $table['engine'] === 'InnoDB' ? 'success' : 'warning text-dark' ?>">
                                    <?= e($table['engine']) ?>
                                </span>
                            </td>
                            <td class="text-end"><?= number_format((int) $table['row_count'], 0, ',', ' ') ?></td>
                            <td class="text-end"><?= number_format((float) $table['data_kb'], 0, ',', ' ') ?> Ko</td>
                            <td class="text-end"><?= number_format((float) $table['index_kb'], 0, ',', ' ') ?> Ko</td>
                            <td class="text-end">
                                <strong><?= number_format((float) $table['size_kb'], 0, ',', ' ') ?> Ko</strong>
                            </td>
                            <td>
                                <?php if ($table['updated_at']): ?>
                                    <small><?= e(date('d/m/Y H:i', strtotime($table['updated_at']))) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Note de bas de page -->
<div class="text-muted small mt-3">
    <i class="bi bi-info-circle"></i>
    Les vérifications et optimisations utilisent les commandes natives MySQL :
    <code>CHECK TABLE</code>, <code>OPTIMIZE TABLE</code>.
</div>