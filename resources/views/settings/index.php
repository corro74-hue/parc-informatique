<?php
/**
 * Vue : Paramètres de l'application
 * @var array $settings  Tableau groupé par catégorie
 */

// Icônes et libellés par groupe
$groupMeta = [
    'general'   => ['label' => 'Général',      'icon' => 'bi-gear'],
    'app'       => ['label' => 'Application',  'icon' => 'bi-app-indicator'],
    'equipment' => ['label' => 'Équipements',  'icon' => 'bi-box-seam'],
    'alert'     => ['label' => 'Alertes',      'icon' => 'bi-bell'],
    'alerts'    => ['label' => 'Alertes',      'icon' => 'bi-bell'],
    'reform'    => ['label' => 'Réformes',     'icon' => 'bi-recycle'],
    'mail'      => ['label' => 'Email / SMTP', 'icon' => 'bi-envelope'],
    'security'  => ['label' => 'Sécurité',     'icon' => 'bi-shield-lock'],
    'system'    => ['label' => 'Système',      'icon' => 'bi-cpu'],
];

$readonlyKeys = ['maintenance_mode', 'maintenance_reason', 'maintenance_end_at', 'maintenance_activated_by', 'maintenance_activated_at'];
$passwordKeys = ['smtp_pass'];

$pdo = \App\Core\Database::getInstance();
?>

<style>
    /* ============ VARIABLES ADAPTATIVES ============ */
    :root {
        --settings-card-bg:   #ffffff;
        --settings-border:    #e2e8f0;
        --settings-text:      #1e293b;
        --settings-muted:     #64748b;
        --settings-input-bg:  #ffffff;
        --settings-input-brd: #e2e8f0;
    }
    [data-bs-theme="dark"] {
        --settings-card-bg:   #1e293b;
        --settings-border:    #334155;
        --settings-text:      #e2e8f0;
        --settings-muted:     #94a3b8;
        --settings-input-bg:  #0f172a;
        --settings-input-brd: #334155;
    }

    .settings-tabs { display: flex; gap: 6px; border-bottom: 2px solid var(--settings-border); margin-bottom: 24px; flex-wrap: wrap; }
    .settings-tab { padding: 12px 20px; border: none; background: transparent; font-weight: 600; color: var(--settings-muted); cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s; font-size: 0.9rem; }
    .settings-tab:hover { color: #667eea; }
    .settings-tab.active { color: #667eea; border-bottom-color: #667eea; }
    .settings-panel { display: none; }
    .settings-panel.active { display: block; }

    .settings-section {
        background: var(--settings-card-bg);
        border-radius: 10px;
        padding: 28px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid var(--settings-border);
    }
    .settings-section h4 {
        font-size: 1.1rem;
        margin-bottom: 20px;
        color: var(--settings-text);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group { margin-bottom: 18px; }
    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--settings-text);
        margin-bottom: 6px;
    }
    .form-group label code {
        background: rgba(102, 126, 234, 0.15);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #667eea;
    }
    .form-group small {
        display: block;
        color: var(--settings-muted);
        font-size: 0.75rem;
        margin-top: 4px;
    }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--settings-input-brd);
        border-radius: 8px;
        font-size: 0.9rem;
        transition: all 0.15s;
        background: var(--settings-input-bg);
        color: var(--settings-text);
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }

    .settings-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid var(--settings-border);
    }

    /* Section maintenance adaptative */
    .maintenance-box {
        border-radius: 8px;
        padding: 16px;
    }
    [data-bs-theme="dark"] .maintenance-box { background: rgba(255,255,255,0.03) !important; }

    /* Logo preview */
    .logo-preview {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: rgba(102, 126, 234, 0.05);
        border-radius: 10px;
        border: 1px dashed var(--settings-border);
        margin-bottom: 16px;
    }
    .logo-preview img {
        max-height: 70px;
        max-width: 70px;
        object-fit: contain;
        border-radius: 8px;
        background: #fff;
        padding: 4px;
    }
    [data-bs-theme="dark"] .logo-preview img { background: #334155; }
    .logo-preview-info { flex: 1; }
    .logo-preview-info strong {
        display: block;
        font-size: 0.9rem;
        color: var(--settings-text);
    }
    .logo-preview-info code {
        font-size: 0.75rem;
        color: var(--settings-muted);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-gear-fill text-primary"></i> Paramètres</h3>
        <p class="text-muted mb-0 small">Configuration générale de l'application</p>
    </div>
</div>

<?php if (empty($settings)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        Aucun paramètre n'est défini. Ajoutez-en dans la table <code>settings</code>.
    </div>
<?php else: ?>

<!-- ============ ONGLETS ============ -->
<div class="settings-tabs" id="settings-tabs">
    <?php $first = true; foreach (array_keys($settings) as $group):
        $meta = $groupMeta[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-folder'];
    ?>
        <button type="button" class="settings-tab <?= $first ? 'active' : '' ?>" data-target="panel-<?= e($group) ?>">
            <i class="bi <?= e($meta['icon']) ?>"></i> <?= e($meta['label']) ?>
        </button>
    <?php $first = false; endforeach; ?>
</div>

<!-- ============ FORMULAIRE PRINCIPAL ============ -->
<form method="POST" action="<?= url('settings/save') ?>">
    <?= csrf_field() ?>

    <?php $first = true; foreach ($settings as $group => $items):
        $meta = $groupMeta[$group] ?? ['label' => ucfirst($group), 'icon' => 'bi-folder'];
    ?>
        <div class="settings-panel <?= $first ? 'active' : '' ?>" id="panel-<?= e($group) ?>">
            <div class="settings-section">
                <h4><i class="bi <?= e($meta['icon']) ?>"></i> <?= e($meta['label']) ?></h4>

                <?php foreach ($items as $s):
                    $key   = $s['key'] ?? '';
                    $value = $s['value'] ?? '';
                    $desc  = $s['description'] ?? '';
                    $type  = $s['type'] ?? 'string';

                    if (in_array($key, $readonlyKeys, true)) continue;

                    $isPassword = in_array($key, $passwordKeys, true);
                ?>

                    <div class="form-group">
                        <label for="set-<?= e($key) ?>">
                            <?= e($desc ?: $key) ?>
                            <code><?= e($key) ?></code>
                        </label>

                        <?php if ($type === 'bool'): ?>
                            <select id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]">
                                <option value="1" <?= $value == '1' ? 'selected' : '' ?>>✅ Activé</option>
                                <option value="0" <?= $value == '0' ? 'selected' : '' ?>>❌ Désactivé</option>
                            </select>

                        <?php elseif ($type === 'text'): ?>
                            <textarea id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" rows="3"><?= e($value) ?></textarea>

                        <?php elseif ($type === 'int'): ?>
                            <input type="number" id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" value="<?= e($value) ?>">

                        <?php elseif ($type === 'json'): ?>
                            <textarea id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" rows="4" style="font-family: monospace; font-size: 0.8rem;"><?= e($value) ?></textarea>

                        <?php elseif ($isPassword): ?>
                            <input type="password"
                                   id="set-<?= e($key) ?>"
                                   name="settings[<?= e($key) ?>]"
                                   value="<?= e($value) ?>"
                                   autocomplete="new-password"
                                   placeholder="••••••••••••••••">

                        <?php else: ?>
                            <input type="text" id="set-<?= e($key) ?>" name="settings[<?= e($key) ?>]" value="<?= e($value) ?>">
                        <?php endif; ?>

                        <?php if ($desc): ?>
                            <small>Type : <?= e($type) ?><?= $isPassword ? ' • 🔒 champ protégé' : '' ?></small>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

                <!-- ============ SECTION SPÉCIALE : LOGO (dans l'onglet Général) ============ -->
                <?php if ($group === 'general'):
                    $currentLogo = $pdo->query("SELECT `value` FROM `settings` WHERE `key` = 'app.logo'")->fetchColumn();
                    $logoFullPath = $currentLogo ? dirname(__DIR__, 3) . '/public/' . $currentLogo : null;
                    $logoExists = $currentLogo && file_exists($logoFullPath);
                ?>
                    <hr style="border-color: var(--settings-border); margin: 24px 0;">
                    <h5 style="color: var(--settings-text); font-size: 1rem; margin-bottom: 16px;">
                        <i class="bi bi-image"></i> Logo de l'application
                    </h5>

                    <div class="logo-preview">
                        <?php if ($logoExists): ?>
                            <img src="<?= url($currentLogo) ?>?v=<?= time() ?>" alt="Logo actuel">
                            <div class="logo-preview-info">
                                <strong>Logo actuel</strong>
                                <code><?= e($currentLogo) ?></code>
                            </div>
                        <?php else: ?>
                            <div style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; background: rgba(102,126,234,0.1); border-radius: 8px;">
                                <i class="bi bi-image" style="font-size: 2rem; color: #667eea;"></i>
                            </div>
                            <div class="logo-preview-info">
                                <strong>Aucun logo défini</strong>
                                <code>Uploadez un fichier PNG, JPG ou SVG</code>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= url('settings/upload-logo') ?>" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/svg+xml" required>
                                <small>Formats acceptés : PNG, JPG, SVG — Taille max : 2 Mo</small>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i> Uploader le logo
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    <?php $first = false; endforeach; ?>

    <div class="settings-section">
        <div class="settings-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</form>

<!-- ============ SECTION : Test SMTP ============ -->
<div class="settings-section mt-4">
    <h4><i class="bi bi-envelope-check"></i> Tester l'envoi d'email (SMTP)</h4>
    <p class="text-muted small mb-3">Envoyez un email de test pour vérifier que les paramètres SMTP fonctionnent.</p>
    <form method="POST" action="<?= url('settings/test-mail') ?>" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <div class="col-md-8">
            <label class="form-label small fw-semibold">Email destinataire</label>
            <input type="email" name="test_email" class="form-control" placeholder="vous@exemple.com" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-send"></i> Envoyer un email test
            </button>
        </div>
    </form>
</div>

<!-- ============ SECTION : Mode Maintenance ============ -->
<?php
$maintenanceMode = $pdo->query("SELECT `value` FROM `settings` WHERE `key` = 'maintenance_mode'")->fetchColumn();
$isActive = $maintenanceMode === '1';
?>
<div class="settings-section mt-4" style="border: 2px solid <?= $isActive ? '#fbbf24' : '#22c55e' ?>;">
    <h4><i class="bi bi-tools"></i> Mode Maintenance</h4>
    <p class="text-muted small mb-3">Activez le mode maintenance pour rendre l'application inaccessible aux utilisateurs non-admin.</p>

    <div class="maintenance-box d-flex align-items-center justify-content-between"
         style="background: <?= $isActive ? 'rgba(251,191,36,0.1)' : 'rgba(34,197,94,0.1)' ?>;">
        <div>
            <strong class="<?= $isActive ? 'text-warning' : 'text-success' ?>">
                <i class="bi bi-<?= $isActive ? 'exclamation-triangle-fill' : 'check-circle-fill' ?>"></i>
                Mode maintenance : <?= $isActive ? 'ACTIVÉ' : 'Désactivé' ?>
            </strong>
            <p class="mb-0 small text-muted">
                <?= $isActive ? 'L\'application est actuellement en maintenance.' : 'L\'application est accessible normalement.' ?>
            </p>
        </div>
        <form method="POST" action="<?= url('settings/toggle-maintenance') ?>" class="m-0">
            <?= csrf_field() ?>
            <input type="hidden" name="active" value="<?= $isActive ? '0' : '1' ?>">
            <button type="submit" class="btn <?= $isActive ? 'btn-success' : 'btn-warning' ?>">
                <i class="bi bi-power"></i> <?= $isActive ? 'Désactiver' : 'Activer' ?>
            </button>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
document.querySelectorAll('.settings-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.getAttribute('data-target'))?.classList.add('active');
    });
});
</script>