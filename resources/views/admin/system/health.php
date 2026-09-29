<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('dashboard') ?>" class="text-decoration-none">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li class="breadcrumb-item active">Administration</li>
        <li class="breadcrumb-item active">Santé système</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-cpu-fill text-primary"></i>
            Santé du système
        </h4>
        <p class="text-muted mb-0 small">
            Monitoring complet de l'application et du serveur
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/database') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-database"></i> Sauvegardes BDD
        </a>
        <form method="POST" action="<?= url('admin/system/clear/all') ?>" class="d-inline"
              onsubmit="return confirm('⚠️ Nettoyage COMPLET (cache + logs + sessions) ?\n\nCette action supprime les fichiers temporaires et les vieilles sessions.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-broom"></i> Nettoyage complet
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
                            La santé système surveille l'état de l'application : version PHP,
                            extensions, espace disque, permissions des dossiers et performances
                            du serveur. Elle détecte les problèmes avant qu'ils n'impactent les utilisateurs.
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
                            <li><strong>Consulter</strong> les statistiques pour détecter les anomalies.</li>
                            <li><strong>Nettoyer</strong> le cache, les logs et les sessions régulièrement.</li>
                            <li><strong>Activer</strong> le mode maintenance avant une intervention majeure.</li>
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
                            <li>Ne nettoyez pas le cache en pleine activité.</li>
                            <li>Vérifiez que les permissions des dossiers sont correctes.</li>
                            <li>Désactivez le mode maintenance dès que possible.</li>
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
                            <li><strong>Santé système</strong> : 1 fois par mois.</li>
                            <li><strong>Nettoyage cache</strong> : tous les 15 jours.</li>
                            <li><strong>Purge logs</strong> : tous les 3 mois.</li>
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
<?php else: ?>
    <div class="alert alert-success d-flex align-items-center mb-3 small">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>
            <strong>Aucune recommandation</strong> — Votre système est en bonne santé ! 🎉
        </div>
    </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- STATUT MODE MAINTENANCE                       -->
<!-- ============================================ -->
<?php if ($maintenance['active']): ?>
    <div class="alert alert-danger d-flex align-items-start mb-3">
        <i class="bi bi-cone-striped me-2 fs-4"></i>
        <div class="flex-grow-1">
            <strong>🔧 Mode maintenance ACTIF</strong>
            <p class="mb-1 small">
                <?php if ($maintenance['reason']): ?>
                    Raison : <?= e($maintenance['reason']) ?><br>
                <?php endif; ?>
                Activé depuis <?= (int) $maintenance['duration_minutes'] ?> minute(s).
                <?php if ($maintenance['end_at']): ?>
                    <br>Fin prévue : <?= e(date('d/m/Y H:i', strtotime($maintenance['end_at']))) ?>
                <?php endif; ?>
            </p>
            <form method="POST" action="<?= url('admin/system/maintenance/disable') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-light">
                    <i class="bi bi-x-circle"></i> Désactiver
                </button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-cone-striped text-warning"></i> Mode maintenance
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Activer le mode maintenance bloque temporairement l'accès aux visiteurs.
                Vous (administrateurs) pourrez continuer à travailler.
            </p>
            <form method="POST" action="<?= url('admin/system/maintenance/enable') ?>">
                <?= csrf_field() ?>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Raison (visible par les visiteurs)</label>
                        <input type="text" name="reason" class="form-control form-control-sm"
                               placeholder="Ex : Mise à jour de la base de données" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Fin prévue (optionnel)</label>
                        <input type="datetime-local" name="end_at" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning btn-sm w-100">
                            <i class="bi bi-cone-striped"></i> Activer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- INFORMATIONS SYSTÈME                          -->
<!-- ============================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Version PHP</div>
                <div class="fs-5 fw-bold <?= $info['php_version_ok'] ? 'text-success' : 'text-danger' ?>">
                    <?= e($info['php_version']) ?>
                </div>
                <small class="text-muted">Min : <?= e($info['php_version_min']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Espace disque libre</div>
                <div class="fs-5 fw-bold <?= $disk['alert'] ? 'text-danger' : 'text-success' ?>">
                    <?= e($disk['free_formatted']) ?>
                </div>
                <small class="text-muted">Sur <?= e($disk['total_formatted']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Mémoire utilisée</div>
                <div class="fs-5 fw-bold text-primary"><?= e($info['memory_usage']) ?></div>
                <small class="text-muted">Limite : <?= e($info['memory_limit']) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Environnement</div>
                <div class="fs-5 fw-bold">
                    <span class="badge bg-<?= $info['app_env'] === 'production' ? 'danger' : 'info' ?>">
                        <?= e(strtoupper($info['app_env'])) ?>
                    </span>
                </div>
                <small class="text-muted">
                    Debug : <?= $info['app_debug'] ? '✅ On' : '❌ Off' ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- INFORMATIONS DÉTAILLÉES                       -->
<!-- ============================================ -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle-fill text-primary"></i> Informations serveur
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Système d'exploitation</span>
                    <strong><?= e($info['os']) ?></strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Serveur web</span>
                    <strong><?= e($info['server_software']) ?></strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Timezone</span>
                    <strong><?= e($info['timezone']) ?></strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Max execution time</span>
                    <strong><?= (int) $info['max_execution_time'] ?> s</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span class="text-muted">Upload max</span>
                    <strong><?= e($info['upload_max_filesize']) ?></strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Post max</span>
                    <strong><?= e($info['post_max_size']) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-hdd-fill text-primary"></i> Espace disque
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Utilisé</span>
                        <span class="small"><strong><?= e($disk['used_percent']) ?>%</strong></span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar <?= $disk['used_percent'] > 80 ? 'bg-danger' : ($disk['used_percent'] > 60 ? 'bg-warning' : 'bg-success') ?>"
                             style="width: <?= e($disk['used_percent']) ?>%">
                            <?= e($disk['used_formatted']) ?>
                        </div>
                    </div>
                </div>
                <div class="row g-2 small">
                    <div class="col-6">
                        <div class="text-muted">Total</div>
                        <strong><?= e($disk['total_formatted']) ?></strong>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Libre</div>
                        <strong><?= e($disk['free_formatted']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- EXTENSIONS PHP                                -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-puzzle-fill text-primary"></i> Extensions PHP
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="small text-muted mb-2">REQUISES</h6>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($extensions['required'] as $ext): ?>
                        <span class="badge bg-<?= $ext['loaded'] ? 'success' : 'danger' ?>">
                            <i class="bi bi-<?= $ext['loaded'] ? 'check-circle' : 'x-circle' ?>"></i>
                            <?= e($ext['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="small text-muted mb-2">RECOMMANDÉES</h6>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($extensions['recommended'] as $ext): ?>
                        <span class="badge bg-<?= $ext['loaded'] ? 'info' : 'secondary' ?>">
                            <i class="bi bi-<?= $ext['loaded'] ? 'check-circle' : 'dash-circle' ?>"></i>
                            <?= e($ext['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PERMISSIONS DOSSIERS                          -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-folder-fill text-primary"></i> Permissions des dossiers
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Dossier</th>
                        <th width="120" class="text-center">Existe</th>
                        <th width="120" class="text-center">Accessible</th>
                        <th width="120" class="text-center">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $perm): ?>
                        <tr>
                            <td><code class="small"><?= e($perm['path']) ?></code></td>
                            <td class="text-center">
                                <?= $perm['exists'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
                            </td>
                            <td class="text-center">
                                <?= $perm['writable'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
                            </td>
                            <td class="text-center">
                                <?php if ($perm['status'] === 'ok'): ?>
                                    <span class="badge bg-success">OK</span>
                                <?php elseif ($perm['status'] === 'missing'): ?>
                                    <span class="badge bg-warning text-dark">Manquant</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Non inscriptible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TAILLE DES DOSSIERS                           -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-archive-fill text-primary"></i> Taille des dossiers
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($folderSizes as $label => $folder): ?>
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small"><?= e($label) ?></div>
                        <div class="fs-5 fw-bold"><?= e($folder['formatted']) ?></div>
                        <small class="text-muted"><?= (int) $folder['file_count'] ?> fichier(s)</small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- NETTOYAGE                                     -->
<!-- ============================================ -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-broom-fill text-primary"></i> Nettoyage
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <form method="POST" action="<?= url('admin/system/clear/cache') ?>">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <div class="fw-bold small">Cache</div>
                            <div class="text-muted small"><?= e($cacheStats['Cache']['formatted']) ?> (<?= (int) $cacheStats['Cache']['file_count'] ?> fichiers)</div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eraser"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="POST" action="<?= url('admin/system/clear/logs') ?>">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <div class="fw-bold small">Logs > 90 jours</div>
                            <div class="text-muted small"><?= e($cacheStats['Logs']['formatted']) ?> (<?= (int) $cacheStats['Logs']['file_count'] ?> fichiers)</div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eraser"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-4">
                <form method="POST" action="<?= url('admin/system/clear/sessions') ?>">
                    <?= csrf_field() ?>
                    <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <div class="fw-bold small">Sessions > 7 jours</div>
                            <div class="text-muted small"><?= e($cacheStats['Sessions']['formatted']) ?> (<?= (int) $cacheStats['Sessions']['file_count'] ?> fichiers)</div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eraser"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>