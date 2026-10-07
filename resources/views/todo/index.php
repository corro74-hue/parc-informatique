<?php
/** @var array $todo */
/** @var array $stats */
/** @var array $filters */
/** @var array $users */
/** @var array $alerts */
/** @var array $history */
?>

<!-- 🆕 Chart.js pour le graphique d'historique -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    .todo-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 16px; margin-bottom: 24px; }
    .todo-header h1 { color: white; margin: 0 0 10px; font-size: 2rem; font-weight: 800; }
    .todo-header p { margin: 0; opacity: 0.9; }

    .progress-main { margin-top: 20px; }
    .progress-main .progress { height: 32px; border-radius: 16px; background: rgba(255,255,255,0.2); }
    .progress-main .progress-bar { font-size: 1rem; font-weight: 700; line-height: 32px; }

    /* 🆕 ALERTES */
    .alert-banner {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 16px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        border: 2px solid transparent;
    }
    .alert-banner:hover { transform: translateY(-2px); text-decoration: none; }
    .alert-banner.alert-late {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-color: #ef4444;
        color: #7f1d1d;
    }
    .alert-banner.alert-today {
        background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
        border-color: #f97316;
        color: #7c2d12;
    }
    .alert-banner.alert-soon {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-color: #f59e0b;
        color: #78350f;
    }
    [data-bs-theme="dark"] .alert-banner.alert-late { background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); color: #fee2e2; }
    [data-bs-theme="dark"] .alert-banner.alert-today { background: linear-gradient(135deg, #7c2d12 0%, #9a3412 100%); color: #fed7aa; }
    [data-bs-theme="dark"] .alert-banner.alert-soon { background: linear-gradient(135deg, #78350f 0%, #92400e 100%); color: #fde68a; }

    .alert-banner .alert-icon { font-size: 2rem; }
    .alert-banner .alert-content { flex: 1; }
    .alert-banner .alert-title { font-weight: 800; font-size: 1.05rem; margin-bottom: 2px; }
    .alert-banner .alert-subtitle { font-size: 0.85rem; opacity: 0.85; }
    .alert-banner .alert-action {
        font-size: 0.85rem;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 8px;
        background: rgba(255,255,255,0.5);
    }
    .alert-banner:hover .alert-action { background: rgba(255,255,255,0.85); }

    /* Alerte : liste détaillée (collapsible) */
    .alert-details {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 0;
        margin-bottom: 20px;
        overflow: hidden;
        display: none;
    }
    .alert-details.show { display: block; }
    .alert-details-header {
        padding: 12px 18px;
        background: rgba(102,126,234,0.08);
        font-weight: 700;
        font-size: 0.9rem;
        border-bottom: 1px solid var(--bs-border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .alert-details-list { max-height: 300px; overflow-y: auto; }
    .alert-detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 18px;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 0.9rem;
    }
    .alert-detail-item:last-child { border-bottom: none; }
    .alert-detail-item:hover { background: rgba(102,126,234,0.05); }
    .alert-detail-days {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }
    .days-late { background: #fee2e2; color: #991b1b; }
    .days-today { background: #ffedd5; color: #7c2d12; }
    .days-soon { background: #fef3c7; color: #78350f; }

    /* 🆕 Graphique d'historique */
    .history-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .history-card h5 {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
    }
    .history-chart-wrapper {
        position: relative;
        height: 260px;
    }
    .history-empty {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }
    .history-empty i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    .history-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--bs-border-color);
    }
    .history-stat { text-align: center; }
    .history-stat .value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #667eea;
    }
    .history-stat .label {
        font-size: 0.7rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 24px; }
    .stat-mini { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 10px; padding: 16px; text-align: center; }
    .stat-mini .value { font-size: 1.8rem; font-weight: 800; color: #667eea; line-height: 1; }
    .stat-mini .label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-top: 6px; }

    .filters-bar { background: var(--bs-body-bg); border: 2px solid #667eea; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; }
    .filters-bar .form-label { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px; }
    .filters-bar .form-control, .filters-bar .form-select { border-radius: 8px; border: 1px solid var(--bs-border-color); font-size: 0.85rem; }
    .filters-bar .form-control:focus, .filters-bar .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }

    .filter-info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; color: #1e40af; font-size: 0.9rem; }
    [data-bs-theme="dark"] .filter-info { background: #1e3a8a; color: #dbeafe; }

    .module-card { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
    .module-header { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: background 0.2s; }
    .module-header:hover { background: rgba(102,126,234,0.05); }
    .module-title { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.1rem; }
    .module-title .icon-box { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; }
    .module-progress { display: flex; align-items: center; gap: 12px; }
    .module-progress .mini-bar { width: 120px; height: 6px; background: rgba(0,0,0,0.08); border-radius: 3px; overflow: hidden; }
    .module-progress .mini-bar-fill { height: 100%; border-radius: 3px; transition: width 0.4s; }
    .module-body { padding: 8px 20px 20px; }

    .task-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; transition: all 0.15s; margin-bottom: 4px; }
    .task-item:hover { background: rgba(102,126,234,0.05); }
    .task-item .form-check-input { width: 20px; height: 20px; cursor: pointer; }
    .task-item .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
    .task-item.done .task-label { text-decoration: line-through; color: #94a3b8; }
    .task-label { cursor: pointer; flex: 1; }
    .task-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }

    .task-type-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .type-feature { background: #dbeafe; color: #1e40af; }
    .type-test    { background: #fef3c7; color: #92400e; }
    .type-fix     { background: #fee2e2; color: #991b1b; }
    .type-bug     { background: #fce7f3; color: #9f1239; }
    .type-deploy  { background: #e0e7ff; color: #3730a3; }

    [data-bs-theme="dark"] .type-feature { background: #1e3a8a; color: #dbeafe; }
    [data-bs-theme="dark"] .type-test    { background: #78350f; color: #fef3c7; }
    [data-bs-theme="dark"] .type-fix     { background: #7f1d1d; color: #fee2e2; }
    [data-bs-theme="dark"] .type-bug     { background: #831843; color: #fce7f3; }
    [data-bs-theme="dark"] .type-deploy  { background: #312e81; color: #e0e7ff; }

    .deadline-badge { font-size: 0.7rem; padding: 3px 8px; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .deadline-ok      { background: #d1fae5; color: #065f46; }
    .deadline-soon    { background: #fef3c7; color: #92400e; }
    .deadline-late    { background: #fee2e2; color: #991b1b; }
    .deadline-today   { background: #ffedd5; color: #7c2d12; }

    [data-bs-theme="dark"] .deadline-ok    { background: #064e3b; color: #d1fae5; }
    [data-bs-theme="dark"] .deadline-soon  { background: #78350f; color: #fef3c7; }
    [data-bs-theme="dark"] .deadline-late  { background: #7f1d1d; color: #fee2e2; }
    [data-bs-theme="dark"] .deadline-today { background: #7c2d12; color: #ffedd5; }

    .avatar-mini { width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.7rem; flex-shrink: 0; }

    .edit-task-btn { background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 4px 6px; border-radius: 6px; opacity: 0; transition: all 0.15s; }
    .task-item:hover .edit-task-btn { opacity: 1; }
    .edit-task-btn:hover { background: rgba(102,126,234,0.15); color: #667eea; }

    .no-results { text-align: center; padding: 60px 20px; color: #94a3b8; }
    .no-results i { font-size: 3rem; margin-bottom: 15px; }
</style>

<!-- ============ BANDEAUX D'ALERTES ============ -->
<?php if (!empty($alerts['counts']['total'])): ?>

    <!-- Alerte RETARD -->
    <?php if ($alerts['counts']['late'] > 0): ?>
        <a href="?alert=late" class="alert-banner alert-late" data-alert="late">
            <i class="bi bi-exclamation-octagon-fill alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">
                    🚨 <?= $alerts['counts']['late'] ?> tâche(s) en retard
                </div>
                <div class="alert-subtitle">
                    Ces tâches ont dépassé leur date limite et doivent être traitées en priorité.
                </div>
            </div>
            <span class="alert-action">Voir →</span>
        </a>
    <?php endif; ?>

    <!-- Alerte AUJOURD'HUI -->
    <?php if ($alerts['counts']['today'] > 0): ?>
        <a href="?alert=today" class="alert-banner alert-today" data-alert="today">
            <i class="bi bi-clock-fill alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">
                    ⏰ <?= $alerts['counts']['today'] ?> tâche(s) à faire aujourd'hui
                </div>
                <div class="alert-subtitle">
                    Échéance fixée à aujourd'hui — dernière ligne droite !
                </div>
            </div>
            <span class="alert-action">Voir →</span>
        </a>
    <?php endif; ?>

    <!-- Alerte BIENTÔT -->
    <?php if ($alerts['counts']['soon'] > 0): ?>
        <a href="?alert=soon" class="alert-banner alert-soon" data-alert="soon">
            <i class="bi bi-hourglass-split alert-icon"></i>
            <div class="alert-content">
                <div class="alert-title">
                    ⚠️ <?= $alerts['counts']['soon'] ?> tâche(s) à échéance dans moins de 3 jours
                </div>
                <div class="alert-subtitle">
                    Anticipe pour éviter le retard.
                </div>
            </div>
            <span class="alert-action">Voir →</span>
        </a>
    <?php endif; ?>

    <!-- Liste détaillée (collapsible) -->
    <div class="alert-details" id="alertDetails">
        <div class="alert-details-header">
            <span><i class="bi bi-list-ul"></i> Détail des tâches concernées</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeAlertDetails()">
                <i class="bi bi-x-lg"></i> Fermer
            </button>
        </div>
        <div class="alert-details-list" id="alertDetailsList"></div>
    </div>

<?php else: ?>
    <!-- Message positif si aucune alerte -->
    <div class="alert-banner" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-color: #10b981; color: #065f46; cursor: default;">
        <i class="bi bi-check-circle-fill alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">✨ Aucune alerte</div>
            <div class="alert-subtitle">Toutes les deadlines sont respectées. Continue comme ça !</div>
        </div>
    </div>
<?php endif; ?>

<!-- ============ HEADER ============ -->
<div class="todo-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-clipboard-check"></i> Tableau de bord du projet</h1>
            <p>
                <i class="bi bi-clock-history"></i>
                Dernière mise à jour : <strong><?= e($todo['last_update']) ?></strong>
                &nbsp;·&nbsp;
                <strong><?= $todo['done'] ?> / <?= $todo['total'] ?></strong> tâches validées
                <?php if (!empty($todo['is_filtered'])): ?>
                    &nbsp;·&nbsp;
                    <span class="badge bg-light text-dark">
                        <?= $todo['filtered_done'] ?? 0 ?> / <?= $todo['filtered_total'] ?? 0 ?> affichées
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($todo['is_filtered'])): ?>
                <a href="<?= url('todo') ?>" class="btn btn-sm btn-light">
                    <i class="bi bi-x-circle"></i> Effacer filtres
                </a>
            <?php endif; ?>
            
            <!-- 🆕 BOUTON EXPORT PDF -->
            <a href="<?= url('todo/export-pdf') ?>" class="btn btn-sm btn-light" title="Exporter en PDF">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Exporter PDF
            </a>
            
            <form method="POST" action="<?= url('todo/reset') ?>" 
                  onsubmit="return confirm('Remettre toutes les tâches à zéro ?');" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                </button>
            </form>
        </div>
    </div>
    <div class="progress-main">
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar"
                 style="width: <?= $todo['percentage'] ?>%">
                <?= $todo['percentage'] ?>%
            </div>
        </div>
    </div>
</div>

<!-- ============ FILTRES ============ -->
<div class="filters-bar">
    <form method="GET" action="<?= url('todo') ?>">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label"><i class="bi bi-search"></i> Recherche</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Mot-clé..." value="<?= e($filters['search']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-check2-circle"></i> Statut</label>
                <select name="status" class="form-select">
                    <option value="all" <?= $filters['status'] === 'all' ? 'selected' : '' ?>>Toutes</option>
                    <option value="done" <?= $filters['status'] === 'done' ? 'selected' : '' ?>>✅ Terminées</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>⏳ À faire</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-tag"></i> Type</label>
                <select name="type" class="form-select">
                    <option value="all" <?= $filters['type'] === 'all' ? 'selected' : '' ?>>Tous</option>
                    <option value="feature" <?= $filters['type'] === 'feature' ? 'selected' : '' ?>>🚀 Feature</option>
                    <option value="test" <?= $filters['type'] === 'test' ? 'selected' : '' ?>>🧪 Test</option>
                    <option value="fix" <?= $filters['type'] === 'fix' ? 'selected' : '' ?>>🔧 Fix</option>
                    <option value="bug" <?= $filters['type'] === 'bug' ? 'selected' : '' ?>>🐛 Bug</option>
                    <option value="deploy" <?= $filters['type'] === 'deploy' ? 'selected' : '' ?>>🚀 Deploy</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-folder"></i> Module</label>
                <select name="module" class="form-select">
                    <option value="all">Tous</option>
                    <?php foreach ($todo['modules'] as $m): ?>
                        <option value="<?= e($m['id']) ?>" <?= $filters['module'] === $m['id'] ? 'selected' : '' ?>>
                            <?= e($m['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><i class="bi bi-person"></i> Assigné à</label>
                <select name="assigned" class="form-select">
                    <option value="all">Tous</option>
                    <?php foreach ($users as $u): 
                        $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                        if ($fullName === '') $fullName = $u['username'];
                    ?>
                        <option value="<?= (int)$u['id'] ?>" <?= $filters['assigned'] === (string)$u['id'] ? 'selected' : '' ?>>
                            <?= e($fullName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary flex-fill" title="Filtrer">
                    <i class="bi bi-funnel"></i>
                </button>
                <?php if ($filters['search'] || $filters['status'] !== 'all' || $filters['type'] !== 'all' || $filters['module'] !== 'all' || $filters['assigned'] !== 'all' || $filters['alert'] !== 'all'): ?>
                    <a href="<?= url('todo') ?>" class="btn btn-outline-secondary" title="Reset">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- ============ 🆕 GRAPHIQUE D'HISTORIQUE ============ -->
<?php if (isset($history) && !empty($history['snapshots'])): ?>
<div class="history-card">
    <h5>
        <i class="bi bi-graph-up-arrow text-primary"></i>
        Évolution sur les 30 derniers jours
    </h5>

    <?php if (count($history['snapshots']) < 2): ?>
        <div class="history-empty">
            <i class="bi bi-graph-up"></i>
            <h6>Pas encore assez de données</h6>
            <p class="mb-0 small">
                Le graphique se remplira automatiquement au fil des jours.
                <br>Reviens demain pour voir la progression !
            </p>
        </div>
    <?php else: ?>
        <div class="history-chart-wrapper">
            <canvas id="historyChart"></canvas>
        </div>

        <?php
        $snapshots = $history['snapshots'];
        $first = $snapshots[0];
        $last  = end($snapshots);
        $gain  = $last['done'] - $first['done'];
        $days  = count($snapshots);
        $avgPerDay = $days > 1 ? round($gain / ($days - 1), 1) : 0;
        ?>
        <div class="history-stats">
            <div class="history-stat">
                <div class="value"><?= $gain >= 0 ? '+' : '' ?><?= $gain ?></div>
                <div class="label">Tâches terminées</div>
            </div>
            <div class="history-stat">
                <div class="value"><?= $avgPerDay ?></div>
                <div class="label">Moy. / jour</div>
            </div>
            <div class="history-stat">
                <div class="value"><?= $days ?></div>
                <div class="label">Jours suivis</div>
            </div>
            <div class="history-stat">
                <div class="value"><?= $last['percentage'] ?>%</div>
                <div class="label">Progression actuelle</div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ STATS ============ -->
<h5 class="mb-3"><i class="bi bi-graph-up text-primary"></i> Statistiques du projet</h5>
<div class="stats-grid">
    <div class="stat-mini"><div class="value"><?= $stats['controllers'] ?></div><div class="label">Contrôleurs</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['models'] ?></div><div class="label">Modèles</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['services'] ?></div><div class="label">Services</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['views'] ?></div><div class="label">Vues</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['routes'] ?></div><div class="label">Routes</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['project_size'] ?></div><div class="label">Taille projet</div></div>
</div>

<h5 class="mb-3 mt-4"><i class="bi bi-database text-primary"></i> Données actuelles</h5>
<div class="stats-grid">
    <div class="stat-mini"><div class="value"><?= $stats['db_equipment'] ?></div><div class="label">Équipements</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['db_users'] ?></div><div class="label">Utilisateurs</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['db_documents'] ?></div><div class="label">Documents</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['db_campaigns'] ?></div><div class="label">Campagnes</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['db_maintenance'] ?></div><div class="label">Maintenances</div></div>
    <div class="stat-mini"><div class="value"><?= $stats['db_employees'] ?></div><div class="label">Employés</div></div>
</div>

<!-- ============ MODULES ============ -->
<h5 class="mb-3 mt-4"><i class="bi bi-list-task text-primary"></i> Tâches par module</h5>

<?php if (!empty($todo['is_filtered'])): ?>
    <div class="filter-info">
        <i class="bi bi-funnel-fill"></i>
        <strong><?= $todo['filtered_total'] ?? 0 ?> tâche(s)</strong> correspondant à tes filtres.
    </div>
<?php endif; ?>

<?php if (empty($todo['modules'])): ?>
    <div class="no-results">
        <i class="bi bi-search"></i>
        <h4>Aucune tâche trouvée</h4>
        <p>Essaie de modifier tes filtres ou <a href="<?= url('todo') ?>">réinitialise-les</a>.</p>
    </div>
<?php else: ?>
    <?php foreach ($todo['modules'] as $index => $module): 
        $pct = $module['percentage'];
        $barColor = $pct === 100 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
    ?>
        <div class="module-card">
            <div class="module-header" data-bs-toggle="collapse" data-bs-target="#module-<?= $index ?>">
                <div class="module-title">
                    <div class="icon-box" style="background: <?= e($module['color']) ?>;">
                        <i class="bi <?= e($module['icon']) ?>"></i>
                    </div>
                    <?= e($module['name']) ?>
                    <span class="badge bg-secondary"><?= $module['done'] ?>/<?= $module['total'] ?></span>
                </div>
                <div class="module-progress">
                    <div class="mini-bar">
                        <div class="mini-bar-fill" style="width: <?= $pct ?>%; background: <?= $barColor ?>;"></div>
                    </div>
                    <strong style="color: <?= $barColor ?>; min-width: 45px; text-align: right;"><?= $pct ?>%</strong>
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>

            <div class="collapse show" id="module-<?= $index ?>">
                <div class="module-body">
                    <?php foreach ($module['tasks'] as $task): 
                        $done = !empty($task['done']);
                        $type = $task['type'] ?? 'feature';
                        
                        $deadline = $task['deadline'] ?? null;
                        $deadlineLabel = '';
                        $deadlineClass = '';
                        if ($deadline) {
                            $daysLeft = (int) floor((strtotime($deadline) - time()) / 86400);
                            if ($daysLeft < 0) {
                                $deadlineClass = 'deadline-late';
                                $deadlineLabel = abs($daysLeft) . 'j de retard';
                            } elseif ($daysLeft === 0) {
                                $deadlineClass = 'deadline-today';
                                $deadlineLabel = "Aujourd'hui";
                            } elseif ($daysLeft <= 3) {
                                $deadlineClass = 'deadline-soon';
                                $deadlineLabel = $daysLeft . 'j restants';
                            } else {
                                $deadlineClass = 'deadline-ok';
                                $deadlineLabel = date('d/m', strtotime($deadline));
                            }
                        }
                        
                        $assignedTo = $task['assigned_to'] ?? null;
                        $assignedUser = null;
                        if ($assignedTo) {
                            foreach ($users as $u) {
                                if ((int)$u['id'] === (int)$assignedTo) {
                                    $assignedUser = $u;
                                    break;
                                }
                            }
                        }
                    ?>
                        <div class="task-item <?= $done ? 'done' : '' ?>"
                             data-task-id="<?= e($task['id']) ?>"
                             data-module-id="<?= e($module['id']) ?>"
                             data-deadline="<?= e($deadline ?? '') ?>"
                             data-assigned="<?= e((string)($assignedTo ?? '')) ?>">
                            <input type="checkbox" class="form-check-input todo-checkbox"
                                   <?= $done ? 'checked' : '' ?>>
                            <span class="task-label"><?= e($task['label']) ?></span>
                            
                            <div class="task-actions">
                                <?php if ($deadline): ?>
                                    <span class="deadline-badge <?= $deadlineClass ?>">
                                        <i class="bi bi-calendar-event"></i> <?= e($deadlineLabel) ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($assignedUser): 
                                    $initial = strtoupper(mb_substr($assignedUser['first_name'] ?: $assignedUser['username'], 0, 1));
                                    $fullName = trim(($assignedUser['first_name'] ?? '') . ' ' . ($assignedUser['last_name'] ?? ''));
                                    if ($fullName === '') $fullName = $assignedUser['username'];
                                ?>
                                    <span class="avatar-mini" title="<?= e($fullName) ?>"><?= e($initial) ?></span>
                                <?php endif; ?>
                                
                                <span class="task-type-badge type-<?= e($type) ?>"><?= e($type) ?></span>
                                
                                <button type="button" class="edit-task-btn" 
                                        onclick="openEditModal('<?= e($task['id']) ?>', '<?= e($module['id']) ?>', '<?= e($task['label']) ?>', '<?= e($deadline ?? '') ?>', '<?= e((string)($assignedTo ?? '')) ?>')"
                                        title="Modifier">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ============ MODAL ÉDITION ============ -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Modifier la tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Tâche</label>
                    <p class="fw-bold mb-0" id="modal-task-label">—</p>
                </div>
                <div class="mb-3">
                    <label for="modal-deadline" class="form-label"><i class="bi bi-calendar-event"></i> Date limite</label>
                    <input type="date" id="modal-deadline" class="form-control">
                    <small class="text-muted">Laisser vide pour supprimer la deadline.</small>
                </div>
                <div class="mb-0">
                    <label for="modal-assigned" class="form-label"><i class="bi bi-person"></i> Assigné à</label>
                    <select id="modal-assigned" class="form-select">
                        <option value="">— Non assigné —</option>
                        <?php foreach ($users as $u): 
                            $fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                            if ($fullName === '') $fullName = $u['username'];
                        ?>
                            <option value="<?= (int)$u['id'] ?>"><?= e($fullName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveTask()">
                    <i class="bi bi-check-lg"></i> Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============ SCRIPTS ============ -->
<script>
// ============ DONNÉES D'ALERTES ============
const ALERT_DATA = {
    late:  <?= json_encode($alerts['late'] ?? [],  JSON_UNESCAPED_UNICODE) ?>,
    today: <?= json_encode($alerts['today'] ?? [], JSON_UNESCAPED_UNICODE) ?>,
    soon:  <?= json_encode($alerts['soon'] ?? [],  JSON_UNESCAPED_UNICODE) ?>
};

// ============ BANDEAUX D'ALERTES CLIQUABLES ============
document.querySelectorAll('.alert-banner[data-alert]').forEach(banner => {
    banner.addEventListener('click', function (e) {
        e.preventDefault();
        showAlertDetails(this.dataset.alert);
    });
});

function showAlertDetails(type) {
    const list = document.getElementById('alertDetailsList');
    const box  = document.getElementById('alertDetails');
    const tasks = ALERT_DATA[type] || [];

    if (tasks.length === 0) {
        list.innerHTML = '<div class="alert-detail-item">Aucune tâche.</div>';
        box.classList.add('show');
        return;
    }

    let html = '';
    tasks.forEach(t => {
        let daysClass = '', daysLabel = '';
        if (t.days_left < 0) {
            daysClass = 'days-late';
            daysLabel = Math.abs(t.days_left) + 'j de retard';
        } else if (t.days_left === 0) {
            daysClass = 'days-today';
            daysLabel = "Aujourd'hui";
        } else {
            daysClass = 'days-soon';
            daysLabel = t.days_left + 'j';
        }

        html += `
            <div class="alert-detail-item">
                <span class="alert-detail-days ${daysClass}">${daysLabel}</span>
                <div style="flex: 1;">
                    <strong>${escapeHtml(t.label)}</strong>
                    <div style="font-size: 0.75rem; color: #94a3b8;">
                        <i class="bi bi-folder"></i> ${escapeHtml(t.module_name)}
                        · <i class="bi bi-calendar-event"></i> ${t.deadline}
                    </div>
                </div>
            </div>
        `;
    });

    list.innerHTML = html;
    box.classList.add('show');
    box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeAlertDetails() {
    document.getElementById('alertDetails').classList.remove('show');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============ MODAL D'ÉDITION ============
let editModalInstance = null;
let currentTaskId = null;
let currentModuleId = null;

document.querySelectorAll('.todo-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', async function () {
        const item = this.closest('.task-item');
        const taskId = item.dataset.taskId;
        const moduleId = item.dataset.moduleId;

        try {
            const response = await fetch('<?= url('todo/toggle') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?= csrf_token() ?>',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ task_id: taskId, module_id: moduleId }),
            });
            const data = await response.json();
            if (data.success) {
                item.classList.toggle('done', data.done);
                setTimeout(() => window.location.reload(), 300);
            } else {
                alert('Erreur : ' + data.message);
                this.checked = !this.checked;
            }
        } catch (err) {
            alert('Erreur réseau : ' + err.message);
            this.checked = !this.checked;
        }
    });
});

function openEditModal(taskId, moduleId, label, deadline, assigned) {
    currentTaskId = taskId;
    currentModuleId = moduleId;
    document.getElementById('modal-task-label').textContent = label;
    document.getElementById('modal-deadline').value = deadline || '';
    document.getElementById('modal-assigned').value = assigned || '';

    if (!editModalInstance) {
        editModalInstance = new bootstrap.Modal(document.getElementById('editTaskModal'));
    }
    editModalInstance.show();
}

async function saveTask() {
    const deadline = document.getElementById('modal-deadline').value;
    const assigned = document.getElementById('modal-assigned').value;

    try {
        const response = await fetch('<?= url('todo/update-task') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                task_id: currentTaskId,
                module_id: currentModuleId,
                deadline: deadline,
                assigned_to: assigned,
            }),
        });
        const data = await response.json();
        if (data.success) {
            if (editModalInstance) editModalInstance.hide();
            setTimeout(() => window.location.reload(), 300);
        } else {
            alert('Erreur : ' + data.message);
        }
    } catch (err) {
        alert('Erreur réseau : ' + err.message);
    }
}

// ============ 🆕 GRAPHIQUE D'HISTORIQUE ============
<?php if (isset($history) && count($history['snapshots'] ?? []) >= 2): ?>
(function() {
    const canvas = document.getElementById('historyChart');
    if (!canvas) return;

    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#e2e8f0' : '#1e293b';
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: <?= json_encode($history['labels']) ?>,
            datasets: [
                {
                    label: 'Tâches terminées',
                    data: <?= json_encode($history['done']) ?>,
                    borderColor: '#10b981',
                    backgroundColor: gradient,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: isDark ? '#1e293b' : '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    yAxisID: 'y',
                },
                {
                    label: 'Progression (%)',
                    data: <?= json_encode($history['percentage']) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#667eea',
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        padding: 15,
                        font: { size: 12, weight: '600' },
                        usePointStyle: true,
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#0f172a' : '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#e2e8f0',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Tâches terminées',
                        font: { size: 11, weight: '600' },
                    },
                    grid: { color: gridColor },
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Progression (%)',
                        font: { size: 11, weight: '600' },
                    },
                    grid: { drawOnChartArea: false },
                    ticks: {
                        callback: function(value) { return value + '%'; }
                    }
                },
                x: {
                    grid: { display: false },
                }
            }
        }
    });
})();
<?php endif; ?>
</script>