<?php
/** @var array $todo */
/** @var array $stats */
/** @var array $alerts */
/** @var array $history */
/** @var array $usersById */

// Calcul date et heure
$dateStr = date('d/m/Y à H:i');
$year = date('Y');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 30px 35px; }
        
        * { box-sizing: border-box; }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.5;
        }
        
        /* ============================================ */
        /* HEADER                                       */
        /* ============================================ */
        .header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .logo-cell { width: 60px; }
        .logo-cell img { max-width: 55px; height: auto; }
        .title-cell { padding-left: 12px; }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
            margin: 0 0 3px;
        }
        .report-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }
        .date-cell {
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
        
        /* ============================================ */
        /* PROGRESS GLOBAL                              */
        /* ============================================ */
        .progress-box {
            background: #f8fafc;
            border-left: 5px solid #10b981;
            padding: 12px 16px;
            margin-bottom: 18px;
        }
        .progress-box-table { width: 100%; border-collapse: collapse; }
        .progress-box-table td { vertical-align: middle; }
        .progress-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .progress-value {
            font-size: 26px;
            font-weight: bold;
            color: #10b981;
            line-height: 1;
        }
        .progress-total {
            font-size: 11px;
            color: #64748b;
        }
        
        /* Barre de progression */
        .bar-container {
            background: #e2e8f0;
            height: 18px;
            border-radius: 9px;
            overflow: hidden;
            margin-top: 6px;
        }
        .bar-fill {
            background: linear-gradient(90deg, #10b981, #059669);
            height: 100%;
            color: white;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            line-height: 18px;
        }
        
        /* ============================================ */
        /* ALERTES                                      */
        /* ============================================ */
        .alert-box {
            padding: 10px 14px;
            margin-bottom: 10px;
            border-left: 4px solid;
            font-size: 10px;
        }
        .alert-late { background: #fee2e2; border-color: #ef4444; color: #7f1d1d; }
        .alert-today { background: #ffedd5; border-color: #f97316; color: #7c2d12; }
        .alert-soon { background: #fef3c7; border-color: #f59e0b; color: #78350f; }
        .alert-ok { background: #d1fae5; border-color: #10b981; color: #065f46; }
        
        /* ============================================ */
        /* SECTIONS                                     */
        /* ============================================ */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #667eea;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        
        /* ============================================ */
        /* STATS GRID                                   */
        /* ============================================ */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .stat-cell {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 6px;
            text-align: center;
            width: 16.66%;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            line-height: 1;
        }
        .stat-label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        
        /* ============================================ */
        /* TABLEAU TÂCHES                               */
        /* ============================================ */
        .module-block {
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .module-header {
            background: #667eea;
            color: white;
            padding: 6px 10px;
            border-radius: 4px 4px 0 0;
        }
        .module-header-table { width: 100%; border-collapse: collapse; }
        .module-header-table td { padding: 0; vertical-align: middle; }
        .module-name {
            font-size: 11px;
            font-weight: bold;
        }
        .module-count {
            font-size: 9px;
            opacity: 0.9;
        }
        .module-pct {
            text-align: right;
            font-size: 12px;
            font-weight: bold;
        }
        
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-top: none;
        }
        .tasks-table th {
            background: #f1f5f9;
            padding: 5px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            border-bottom: 1px solid #e2e8f0;
        }
        .tasks-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
            vertical-align: middle;
        }
        .tasks-table tr:last-child td { border-bottom: none; }
        
        /* Statuts */
        .status-done {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .status-pending {
            display: inline-block;
            background: #f1f5f9;
            color: #64748b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        /* Deadline */
        .deadline {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .deadline-ok    { background: #d1fae5; color: #065f46; }
        .deadline-soon  { background: #fef3c7; color: #92400e; }
        .deadline-late  { background: #fee2e2; color: #991b1b; }
        .deadline-today { background: #ffedd5; color: #7c2d12; }
        .deadline-none  { color: #94a3b8; font-size: 8px; }
        
        /* Type badge */
        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .type-feature { background: #dbeafe; color: #1e40af; }
        .type-test    { background: #fef3c7; color: #92400e; }
        .type-fix     { background: #fee2e2; color: #991b1b; }
        .type-bug     { background: #fce7f3; color: #9f1239; }
        .type-deploy  { background: #e0e7ff; color: #3730a3; }
        
        /* ============================================ */
        /* FOOTER                                       */
        /* ============================================ */
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
        .footer strong { color: #667eea; }
        
        /* Utilitaires */
        .text-muted { color: #94a3b8; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mt-3 { margin-top: 12px; }
        .small { font-size: 8px; }
        
        /* Badge page */
        .page-badge {
            background: #667eea;
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- ============================================ -->
    <!-- HEADER                                       -->
    <!-- ============================================ -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php
                    // Logo de l'appli
                    $logoPath = dirname(__DIR__, 3) . '/public/assets/images/logo-app.png';
                    if (!is_file($logoPath)) {
                        $logoPath = dirname(__DIR__, 3) . '/public/assets/images/logo.png';
                    }
                    if (is_file($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                        echo '<img src="data:' . $logoMime . ';base64,' . $logoData . '" alt="Logo">';
                    }
                    ?>
                </td>
                <td class="title-cell">
                    <h1 class="report-title">Rapport de Projet</h1>
                    <p class="report-subtitle">
                        <strong><?= e(config('app.name', 'Parc Info')) ?></strong>
                        — <?= e(config('app.organization', 'SADID-DIAGNOPHARME')) ?>
                    </p>
                </td>
                <td class="date-cell">
                    <span class="page-badge">Tableau de bord</span><br>
                    <span style="display: block; margin-top: 5px;">
                        Généré le<br>
                        <strong><?= $dateStr ?></strong>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================ -->
    <!-- PROGRESSION GLOBALE                          -->
    <!-- ============================================ -->
    <div class="progress-box">
        <table class="progress-box-table">
            <tr>
                <td style="width: 70%;">
                    <div class="progress-label">Progression globale du projet</div>
                    <div>
                        <span class="progress-value"><?= $todo['percentage'] ?>%</span>
                        <span class="progress-total">
                            &nbsp;·&nbsp; <?= $todo['done'] ?> / <?= $todo['total'] ?> tâches validées
                        </span>
                    </div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: <?= $todo['percentage'] ?>%;">
                            <?= $todo['percentage'] ?>%
                        </div>
                    </div>
                </td>
                <td style="width: 30%; text-align: right; padding-left: 15px;">
                    <div class="progress-label">Dernière mise à jour</div>
                    <div style="font-size: 13px; font-weight: bold; color: #667eea; margin-top: 4px;">
                        <?= date('d/m/Y', strtotime($todo['last_update'])) ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ============================================ -->
    <!-- ALERTES                                      -->
    <!-- ============================================ -->
    <?php if (!empty($alerts['counts']['total'])): ?>
        <?php if ($alerts['counts']['late'] > 0): ?>
            <div class="alert-box alert-late">
                🚨 <strong><?= $alerts['counts']['late'] ?> tâche(s) en retard</strong>
                — Ces tâches ont dépassé leur date limite et doivent être traitées en priorité.
            </div>
        <?php endif; ?>

        <?php if ($alerts['counts']['today'] > 0): ?>
            <div class="alert-box alert-today">
                ⏰ <strong><?= $alerts['counts']['today'] ?> tâche(s) à faire aujourd'hui</strong>
                — Dernière ligne droite !
            </div>
        <?php endif; ?>

        <?php if ($alerts['counts']['soon'] > 0): ?>
            <div class="alert-box alert-soon">
                ⚠️ <strong><?= $alerts['counts']['soon'] ?> tâche(s) à échéance dans moins de 3 jours</strong>
                — Anticipe pour éviter le retard.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert-box alert-ok">
            ✨ <strong>Aucune alerte</strong> — Toutes les deadlines sont respectées. 
        </div>
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- STATISTIQUES DU PROJET                       -->
    <!-- ============================================ -->
    <h2 class="section-title">📊 Statistiques du projet</h2>
    <table class="stats-table">
        <tr>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['controllers'] ?></div>
                <div class="stat-label">Contrôleurs</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['models'] ?></div>
                <div class="stat-label">Modèles</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['services'] ?></div>
                <div class="stat-label">Services</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['views'] ?></div>
                <div class="stat-label">Vues</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['routes'] ?></div>
                <div class="stat-label">Routes</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['project_size'] ?></div>
                <div class="stat-label">Taille</div>
            </td>
        </tr>
    </table>

    <h2 class="section-title">💾 Données actuelles</h2>
    <table class="stats-table">
        <tr>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_equipment'] ?></div>
                <div class="stat-label">Équipements</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_users'] ?></div>
                <div class="stat-label">Utilisateurs</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_documents'] ?></div>
                <div class="stat-label">Documents</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_campaigns'] ?></div>
                <div class="stat-label">Campagnes</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_maintenance'] ?></div>
                <div class="stat-label">Maintenances</div>
            </td>
            <td class="stat-cell">
                <div class="stat-value"><?= $stats['db_employees'] ?></div>
                <div class="stat-label">Employés</div>
            </td>
        </tr>
    </table>

    <!-- ============================================ -->
    <!-- DÉTAIL PAR MODULE                            -->
    <!-- ============================================ -->
    <h2 class="section-title">📋 Détail des tâches par module</h2>

    <?php foreach ($todo['modules'] as $module): 
        $pct = $module['percentage'];
    ?>
        <div class="module-block">
            <div class="module-header">
                <table class="module-header-table">
                    <tr>
                        <td>
                            <span class="module-name"><?= e($module['name']) ?></span>
                            <span class="module-count">
                                &nbsp;— <?= $module['done'] ?> / <?= $module['total'] ?> tâches
                            </span>
                        </td>
                        <td class="module-pct" style="width: 60px;">
                            <?= $pct ?>%
                        </td>
                    </tr>
                </table>
            </div>
            
            <table class="tasks-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 45%;">Tâche</th>
                        <th style="width: 12%; text-align: center;">Type</th>
                        <th style="width: 13%; text-align: center;">Statut</th>
                        <th style="width: 15%; text-align: center;">Deadline</th>
                        <th style="width: 10%;">Assigné</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($module['tasks'] as $i => $task): 
                        $done = !empty($task['done']);
                        $type = $task['type'] ?? 'feature';
                        $deadline = $task['deadline'] ?? null;
                        
                        // Calcul deadline
                        $deadlineClass = 'deadline-none';
                        $deadlineLabel = '—';
                        if ($deadline) {
                            $daysLeft = (int) floor((strtotime($deadline) - time()) / 86400);
                            if ($daysLeft < 0) {
                                $deadlineClass = 'deadline-late';
                                $deadlineLabel = abs($daysLeft) . 'j retard';
                            } elseif ($daysLeft === 0) {
                                $deadlineClass = 'deadline-today';
                                $deadlineLabel = "Aujourd'hui";
                            } elseif ($daysLeft <= 3) {
                                $deadlineClass = 'deadline-soon';
                                $deadlineLabel = $daysLeft . 'j';
                            } else {
                                $deadlineClass = 'deadline-ok';
                                $deadlineLabel = date('d/m/Y', strtotime($deadline));
                            }
                        }
                        
                        // Assigned name
                        $assignedTo = $task['assigned_to'] ?? null;
                        $assignedName = $assignedTo && isset($usersById[(int)$assignedTo]) 
                            ? $usersById[(int)$assignedTo] 
                            : '—';
                    ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td><?= e($task['label']) ?></td>
                            <td class="text-center">
                                <span class="type-badge type-<?= e($type) ?>"><?= e($type) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($done): ?>
                                    <span class="status-done">✓ Terminée</span>
                                <?php else: ?>
                                    <span class="status-pending">○ À faire</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($deadline): ?>
                                    <span class="deadline <?= $deadlineClass ?>"><?= e($deadlineLabel) ?></span>
                                <?php else: ?>
                                    <span class="deadline-none">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= e(mb_substr($assignedName, 0, 15)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <!-- ============================================ -->
    <!-- FOOTER                                       -->
    <!-- ============================================ -->
    <div class="footer">
        <p class="mb-1">
            <strong><?= e(config('app.name', 'Parc Info')) ?></strong>
            — Rapport généré automatiquement par le tableau de bord du projet
        </p>
        <p class="mb-0 small">
            © <?= $year ?> <?= e(config('app.organization', 'SADID-DIAGNOPHARME')) ?>
            — Tous droits réservés
            · Page 1/1
        </p>
    </div>

</body>
</html>