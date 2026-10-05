<?php
/** @var array $stats */
/** @var array $byCategory */
/** @var array $byStatus */
/** @var array $byService */
/** @var array $byBrand */
/** @var array $evolution */
/** @var int $warrantyExpiring */
/** @var int $warrantyExpired */
?>

<style>
    .stat-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.2s;
        height: 100%;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
        margin-bottom: 12px;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--bs-secondary-color);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .chart-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 24px;
        height: 100%;
    }
    .chart-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--bs-body-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chart-container {
        position: relative;
        height: 260px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-bar-chart-fill text-primary"></i> Rapports et Statistiques</h3>
        <p class="text-muted mb-0 small">Vue d'ensemble du parc informatique</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('reports/equipment') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-seam"></i> Rapport Équipements
        </a>
        <a href="<?= url('reports/maintenance') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-tools"></i> Rapport Maintenance
        </a>
    </div>
</div>

<!-- ============ STATS GLOBALES ============ -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-value"><?= number_format($stats['total_equipment']) ?></div>
            <div class="stat-label">Équipements totaux</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-value text-success"><?= number_format($stats['in_service']) ?></div>
            <div class="stat-label">En service</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="bi bi-tools"></i>
            </div>
            <div class="stat-value text-warning"><?= number_format($stats['in_maintenance']) ?></div>
            <div class="stat-label">En maintenance</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <i class="bi bi-recycle"></i>
            </div>
            <div class="stat-value text-danger"><?= number_format($stats['reformed']) ?></div>
            <div class="stat-label">Réformés</div>
        </div>
    </div>
</div>

<!-- ============ STATS SECONDAIRES ============ -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-people fs-2 text-primary"></i>
            <div class="stat-value"><?= number_format($stats['total_employees']) ?></div>
            <div class="stat-label">Employés actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-arrow-left-right fs-2 text-info"></i>
            <div class="stat-value"><?= number_format($stats['total_assignments']) ?></div>
            <div class="stat-label">Affectations en cours</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-tools fs-2 text-warning"></i>
            <div class="stat-value"><?= number_format($stats['total_maintenance']) ?></div>
            <div class="stat-label">Interventions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-cash-stack fs-2 text-success"></i>
            <div class="stat-value"><?= number_format($stats['total_value'], 0, ',', ' ') ?> DA</div>
            <div class="stat-label">Valeur du parc</div>
        </div>
    </div>
</div>

<!-- ============ ALERTES ============ -->
<?php if ($warrantyExpiring > 0 || $warrantyExpired > 0): ?>
<div class="row g-3 mb-4">
    <?php if ($warrantyExpiring > 0): ?>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-center mb-0">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                <div>
                    <strong><?= $warrantyExpiring ?> garantie(s) expire(nt) bientôt</strong>
                    <p class="mb-0 small">Dans les 30 prochains jours</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($warrantyExpired > 0): ?>
        <div class="col-md-6">
            <div class="alert alert-danger d-flex align-items-center mb-0">
                <i class="bi bi-x-circle-fill fs-3 me-3"></i>
                <div>
                    <strong><?= $warrantyExpired ?> garantie(s) expirée(s)</strong>
                    <p class="mb-0 small">À vérifier</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ GRAPHIQUES ============ -->
<div class="row g-3 mb-4">
    <!-- Camembert : Par catégorie -->
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart-fill text-primary"></i> Par catégorie
            </div>
            <div class="chart-container">
                <canvas id="chartCategory"></canvas>
            </div>
        </div>
    </div>

    <!-- Camembert : Par statut -->
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-pie-chart-fill text-info"></i> Par statut
            </div>
            <div class="chart-container">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>

    <!-- Camembert : Par marque -->
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-tag-fill text-success"></i> Par marque
            </div>
            <div class="chart-container">
                <canvas id="chartBrand"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Barres : Par service -->
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-bar-chart-fill text-warning"></i> Répartition par service
            </div>
            <div class="chart-container">
                <canvas id="chartService"></canvas>
            </div>
        </div>
    </div>

    <!-- Ligne : Évolution mensuelle -->
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="chart-title">
                <i class="bi bi-graph-up-arrow text-danger"></i> Évolution du parc (12 mois)
            </div>
            <div class="chart-container">
                <canvas id="chartEvolution"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ============ CHART.JS ============ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#e2e8f0' : '#1e293b';
    const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;
    Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";

    // ============ CAMEMBERT : Catégorie ============
    new Chart(document.getElementById('chartCategory'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($byCategory, 'label')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($byCategory, 'count')) ?>,
                backgroundColor: ['#667eea', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1'],
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
            }
        }
    });

    // ============ CAMEMBERT : Statut ============
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($byStatus, 'label')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($byStatus, 'count')) ?>,
                backgroundColor: <?= json_encode(array_map(function($s) { return $s['color'] ?: '#667eea'; }, $byStatus)) ?>,
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
            }
        }
    });

    // ============ CAMEMBERT : Marque ============
    new Chart(document.getElementById('chartBrand'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($byBrand, 'label')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($byBrand, 'count')) ?>,
                backgroundColor: ['#06b6d4', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#ef4444', '#6366f1', '#84cc16', '#f97316', '#14b8a6'],
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
            }
        }
    });

    // ============ BARRES : Service ============
    new Chart(document.getElementById('chartService'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($byService, 'label')) ?>,
            datasets: [{
                label: 'Équipements',
                data: <?= json_encode(array_column($byService, 'count')) ?>,
                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                borderColor: '#f59e0b',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // ============ LIGNE : Évolution ============
    new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(function($e) { 
                return date('M Y', strtotime($e['mois'] . '-01')); 
            }, $evolution)) ?>,
            datasets: [{
                label: 'Nouveaux équipements',
                data: <?= json_encode(array_column($evolution, 'count')) ?>,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ef4444',
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>