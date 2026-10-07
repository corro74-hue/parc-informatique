<?php
/** @var array $todo */
/** @var array $stats */
?>

<style>
    .todo-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 16px; margin-bottom: 24px; }
    .todo-header h1 { color: white; margin: 0 0 10px; font-size: 2rem; font-weight: 800; }
    .todo-header p { margin: 0; opacity: 0.9; }

    .progress-main { margin-top: 20px; }
    .progress-main .progress { height: 32px; border-radius: 16px; background: rgba(255,255,255,0.2); }
    .progress-main .progress-bar { font-size: 1rem; font-weight: 700; line-height: 32px; }

    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 24px; }
    .stat-mini { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 10px; padding: 16px; text-align: center; }
    .stat-mini .value { font-size: 1.8rem; font-weight: 800; color: #667eea; line-height: 1; }
    .stat-mini .label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-top: 6px; }

    .module-card { background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
    .module-header { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: background 0.2s; }
    .module-header:hover { background: rgba(102,126,234,0.05); }
    .module-title { display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 1.1rem; }
    .module-title .icon-box { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.1rem; }
    .module-progress { display: flex; align-items: center; gap: 12px; }
    .module-progress .mini-bar { width: 120px; height: 6px; background: rgba(0,0,0,0.08); border-radius: 3px; overflow: hidden; }
    .module-progress .mini-bar-fill { height: 100%; border-radius: 3px; transition: width 0.4s; }

    .module-body { padding: 8px 20px 20px; }

    .task-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 8px; transition: all 0.15s; cursor: pointer; margin-bottom: 4px; }
    .task-item:hover { background: rgba(102,126,234,0.05); }
    .task-item .form-check-input { width: 20px; height: 20px; cursor: pointer; }
    .task-item .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
    .task-item.done .task-label { text-decoration: line-through; color: #94a3b8; }

    .task-type-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; margin-left: auto; }
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
</style>

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
            </p>
        </div>
        <form method="POST" action="<?= url('todo/reset') ?>" 
              onsubmit="return confirm('Remettre toutes les tâches à zéro ?');" class="m-0">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
            </button>
        </form>
    </div>

    <div class="progress-main">
        <div class="progress">
            <div class="progress-bar bg-success" role="progressbar"
                 style="width: <?= $todo['percentage'] ?>%"
                 aria-valuenow="<?= $todo['percentage'] ?>" aria-valuemin="0" aria-valuemax="100">
                <?= $todo['percentage'] ?>%
            </div>
        </div>
    </div>
</div>

<!-- ============ STATS DU PROJET ============ -->
<h5 class="mb-3"><i class="bi bi-graph-up text-primary"></i> Statistiques du projet</h5>
<div class="stats-grid">
    <div class="stat-mini">
        <div class="value"><?= $stats['controllers'] ?></div>
        <div class="label">Contrôleurs</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['models'] ?></div>
        <div class="label">Modèles</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['services'] ?></div>
        <div class="label">Services</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['views'] ?></div>
        <div class="label">Vues</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['routes'] ?></div>
        <div class="label">Routes</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['project_size'] ?></div>
        <div class="label">Taille projet</div>
    </div>
</div>

<h5 class="mb-3 mt-4"><i class="bi bi-database text-primary"></i> Données actuelles</h5>
<div class="stats-grid">
    <div class="stat-mini">
        <div class="value"><?= $stats['db_equipment'] ?></div>
        <div class="label">Équipements</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['db_users'] ?></div>
        <div class="label">Utilisateurs</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['db_documents'] ?></div>
        <div class="label">Documents</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['db_campaigns'] ?></div>
        <div class="label">Campagnes</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['db_maintenance'] ?></div>
        <div class="label">Maintenances</div>
    </div>
    <div class="stat-mini">
        <div class="value"><?= $stats['db_employees'] ?></div>
        <div class="label">Employés</div>
    </div>
</div>

<!-- ============ MODULES ============ -->
<h5 class="mb-3 mt-4"><i class="bi bi-list-task text-primary"></i> Tâches par module</h5>

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
                ?>
                    <label class="task-item <?= $done ? 'done' : '' ?>"
                           data-task-id="<?= e($task['id']) ?>"
                           data-module-id="<?= e($module['id']) ?>">
                        <input type="checkbox" class="form-check-input todo-checkbox"
                               <?= $done ? 'checked' : '' ?>>
                        <span class="task-label"><?= e($task['label']) ?></span>
                        <span class="task-type-badge type-<?= e($type) ?>"><?= e($type) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- ============ SCRIPT AJAX ============ -->
<script>
document.querySelectorAll('.todo-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', async function (e) {
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
                if (data.done) {
                    item.classList.add('done');
                } else {
                    item.classList.remove('done');
                }
                setTimeout(() => window.location.reload(), 400);
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
</script>