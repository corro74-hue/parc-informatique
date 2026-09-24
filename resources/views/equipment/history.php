<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('equipment') ?>" class="text-decoration-none">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('equipment/' . $equipment->id) ?>" class="text-decoration-none">
                <?= e($equipment->inventoryNumber) ?>
            </a>
        </li>
        <li class="breadcrumb-item active">
            <i class="bi bi-clock-history"></i> Historique
        </li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-clock-history text-primary"></i>
            Historique des modifications
        </h4>
        <p class="text-muted mb-0 small">
            <?= e($equipment->inventoryNumber) ?> — <?= e($equipment->designation) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('equipment/' . $equipment->id) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour à la fiche
        </a>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row g-2 mb-3">
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <div class="fs-4 fw-bold text-primary"><?= count($logs) ?></div>
                <div class="small text-muted">Entrées au total</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <?php
                $actionsCount = [];
                foreach ($logs as $log) {
                    $actionsCount[$log['action']] = ($actionsCount[$log['action']] ?? 0) + 1;
                }
                $uniqueActions = count($actionsCount);
                ?>
                <div class="fs-4 fw-bold text-info"><?= $uniqueActions ?></div>
                <div class="small text-muted">Types d'actions</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <?php
                $usersCount = [];
                foreach ($logs as $log) {
                    $uname = $log['user_name'] ?? 'Système';
                    $usersCount[$uname] = true;
                }
                ?>
                <div class="fs-4 fw-bold text-success"><?= count($usersCount) ?></div>
                <div class="small text-muted">Utilisateurs</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center">
            <div class="card-body py-3">
                <?php
                $lastDate = !empty($logs) && !empty($logs[0]['created_at'])
                    ? date('d/m/Y', strtotime($logs[0]['created_at']))
                    : '—';
                ?>
                <div class="fs-6 fw-bold text-warning"><?= $lastDate ?></div>
                <div class="small text-muted">Dernière action</div>
            </div>
        </div>
    </div>
</div>

<!-- Liste chronologique -->
<?php if (empty($logs)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
            <p class="mt-3 mb-0">Aucun historique pour cet équipement.</p>
            <small>Les modifications apparaîtront ici automatiquement.</small>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-list-ul text-primary"></i>
                Chronologie (<?= count($logs) ?> entrée<?= count($logs) > 1 ? 's' : '' ?>)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="timeline-container">
                <?php foreach ($logs as $index => $log): ?>
                    <?php
                    // ----- Décoder les JSON -----
                    $oldValues = !empty($log['old_values']) ? json_decode($log['old_values'], true) : [];
                    $newValues = !empty($log['new_values']) ? json_decode($log['new_values'], true) : [];

                    // ----- Couleur et icône selon l'action -----
                    $actionConfig = [
                        'create'             => ['color' => '#10b981', 'icon' => 'plus-circle',       'label' => 'Création'],
                        'update'             => ['color' => '#3b82f6', 'icon' => 'pencil',            'label' => 'Modification'],
                        'status_change'      => ['color' => '#f59e0b', 'icon' => 'arrow-repeat',      'label' => 'Changement de statut'],
                        'delete'             => ['color' => '#dc3545', 'icon' => 'trash',             'label' => 'Mise à la corbeille'],
                        'restore'            => ['color' => '#10b981', 'icon' => 'arrow-counterclockwise', 'label' => 'Restauration'],
                        'force_delete'       => ['color' => '#7f1d1d', 'icon' => 'x-octagon',         'label' => 'Suppression définitive'],
                        'bulk_status_change' => ['color' => '#f59e0b', 'icon' => 'arrow-repeat',      'label' => 'Changement de statut (masse)'],
                        'bulk_delete'        => ['color' => '#dc3545', 'icon' => 'trash',             'label' => 'Mise à la corbeille (masse)'],
                        'import'             => ['color' => '#8b5cf6', 'icon' => 'upload',            'label' => 'Import CSV'],
                    ];
                    $cfg = $actionConfig[$log['action']] ?? ['color' => '#6c757d', 'icon' => 'circle', 'label' => $log['action']];

                    // ----- Severity badge -----
                    $severityBadge = [
                        'info'     => 'bg-info',
                        'warning'  => 'bg-warning text-dark',
                        'error'    => 'bg-danger',
                        'critical' => 'bg-dark',
                    ][$log['severity'] ?? 'info'] ?? 'bg-secondary';

                    // ----- Date -----
                    $date = $log['created_at'] ? date('d/m/Y à H:i:s', strtotime($log['created_at'])) : '—';
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-marker" style="background-color: <?= $cfg['color'] ?>;">
                            <i class="bi bi-<?= $cfg['icon'] ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                <div>
                                    <span class="badge" style="background-color: <?= $cfg['color'] ?>;">
                                        <?= e($cfg['label']) ?>
                                    </span>
                                    <span class="badge <?= $severityBadge ?> ms-1">
                                        <?= e($log['severity'] ?? 'info') ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?= e($date) ?>
                                </small>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-person-circle text-muted"></i>
                                <strong><?= e($log['user_name'] ?? 'Système') ?></strong>
                                <small class="text-muted">
                                    (IP : <?= e($log['ip_address'] ?? '—') ?>)
                                </small>
                            </div>

                            <!-- Détails des changements -->
                            <?php if ($log['action'] === 'create' && !empty($newValues)): ?>
                                <div class="changes-block">
                                    <div class="small text-muted mb-1">Données initiales :</div>
                                    <div class="change-values">
                                        <?php foreach ($newValues as $field => $value): ?>
                                            <?php if ($value !== null && $value !== ''): ?>
                                                <span class="change-chip">
                                                    <strong><?= e($field) ?></strong> : <?= e((string) $value) ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                            <?php elseif ($log['action'] === 'update' && (!empty($oldValues) || !empty($newValues))): ?>
                                <?php
                                // Calculer les changements
                                $changedFields = [];
                                foreach ($newValues as $field => $newVal) {
                                    $oldVal = $oldValues[$field] ?? null;
                                    if ((string) $oldVal !== (string) $newVal) {
                                        $changedFields[$field] = ['old' => $oldVal, 'new' => $newVal];
                                    }
                                }
                                ?>
                                <div class="changes-block">
                                    <div class="small text-muted mb-1">
                                        <?= count($changedFields) ?> champ(s) modifié(s) :
                                    </div>
                                    <?php foreach ($changedFields as $field => $vals): ?>
                                        <div class="change-line">
                                            <strong><?= e($field) ?></strong> :
                                            <span class="old-value"><?= e((string) ($vals['old'] ?? '—')) ?></span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span class="new-value"><?= e((string) ($vals['new'] ?? '—')) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                            <?php elseif ($log['action'] === 'status_change' && !empty($oldValues) && !empty($newValues)): ?>
                                <div class="changes-block">
                                    <div class="change-line">
                                        <strong>Statut</strong> :
                                        <span class="old-value"><?= e($oldValues['status_name'] ?? '—') ?></span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="new-value"><?= e($newValues['status_name'] ?? '—') ?></span>
                                    </div>
                                </div>

                            <?php elseif ($log['action'] === 'delete' && !empty($oldValues)): ?>
                                <div class="changes-block">
                                    <div class="small text-muted mb-1">Équipement placé dans la corbeille.</div>
                                    <?php if (!empty($oldValues['inventory_number'])): ?>
                                        <div class="change-chip">
                                            <strong>N° inventaire</strong> : <?= e($oldValues['inventory_number']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($log['action'] === 'restore'): ?>
                                <div class="changes-block">
                                    <div class="small text-success">
                                        <i class="bi bi-check-circle"></i> Restauré depuis la corbeille
                                    </div>
                                </div>

                            <?php elseif ($log['action'] === 'force_delete'): ?>
                                <div class="changes-block">
                                    <div class="small text-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Suppression définitive — IRRÉVERSIBLE
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Contexte technique (URL, méthode) -->
                            <?php if (!empty($log['url'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <code><?= e($log['method'] ?? 'GET') ?></code>
                                        <code><?= e($log['url']) ?></code>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Styles timeline -->
<style>
.timeline-container {
    padding: 20px;
    position: relative;
}
.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 25px;
}
.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}
.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}
.timeline-content {
    background: #f8fafc;
    border-left: 3px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.15s;
}
.timeline-content:hover {
    background: #f1f5f9;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
.changes-block {
    background: #fff;
    border-radius: 6px;
    padding: 10px 12px;
    margin-top: 8px;
    border: 1px solid #e2e8f0;
}
.change-line {
    padding: 4px 0;
    font-size: 0.88rem;
}
.change-line:not(:last-child) {
    border-bottom: 1px dashed #e2e8f0;
}
.old-value {
    color: #dc3545;
    text-decoration: line-through;
    font-size: 0.85rem;
}
.new-value {
    color: #10b981;
    font-weight: 600;
    font-size: 0.88rem;
}
.change-values {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.change-chip {
    display: inline-block;
    background: #eef2ff;
    color: #4338ca;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.78rem;
    border: 1px solid #c7d2fe;
}
.change-chip strong {
    color: #1e293b;
}
</style>