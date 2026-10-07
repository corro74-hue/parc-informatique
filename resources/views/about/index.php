<?php
/** @var array $appInfo */
?>

<style>
    /* ============================================ */
    /* PAGE À PROPOS                                */
    /* ============================================ */

    /* HERO */
    .about-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 40px;
        border-radius: 20px;
        text-align: center;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
    }
    .about-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulseBg 8s ease-in-out infinite;
    }
    @keyframes pulseBg {
        0%, 100% { transform: scale(1); opacity: 0.15; }
        50% { transform: scale(1.2); opacity: 0.25; }
    }
    .about-hero-content { position: relative; z-index: 1; }
    .about-hero-icon {
        font-size: 5rem;
        margin-bottom: 20px;
        display: inline-block;
        animation: floatIcon 3s ease-in-out infinite;
    }
    @keyframes floatIcon {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .about-hero h1 {
        font-size: 2.5rem;
        font-weight: 900;
        margin: 0 0 10px;
        letter-spacing: -1px;
        color: white;
    }
    .about-hero p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
        max-width: 700px;
        margin: 0 auto;
    }
    .about-version {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-top: 20px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.3);
    }

    /* ACTIONS RAPIDES */
    .about-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 24px;
        flex-wrap: wrap;
    }
    .about-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 12px;
        background: rgba(255,255,255,0.15);
        color: white;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1.5px solid rgba(255,255,255,0.3);
        transition: all 0.25s;
        backdrop-filter: blur(10px);
    }
    .about-action-btn:hover {
        background: rgba(255,255,255,0.25);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }
    .about-action-btn.primary {
        background: white;
        color: #667eea;
        border-color: white;
    }
    .about-action-btn.primary:hover {
        background: #f8fafc;
        color: #5568d3;
    }

    /* CARTES */
    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) {
        .about-grid { grid-template-columns: 1fr; }
    }

    .about-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s;
    }
    .about-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .about-card-title {
        font-size: 1.1rem;
        font-weight: 800;
        margin-bottom: 20px;
        color: var(--bs-body-color);
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--bs-border-color);
    }
    .about-card-title i {
        color: #667eea;
        font-size: 1.3rem;
    }

    /* AUTEUR */
    .author-profile {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px;
        background: linear-gradient(135deg, rgba(102,126,234,0.08) 0%, rgba(118,75,162,0.08) 100%);
        border-radius: 14px;
        margin-bottom: 20px;
        border: 2px solid rgba(102,126,234,0.15);
    }
    .author-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
        font-weight: 900;
        flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(102,126,234,0.4);
        border: 4px solid white;
    }
    .author-info h3 {
        margin: 0 0 6px;
        font-size: 1.4rem;
        font-weight: 900;
        color: var(--bs-body-color);
        letter-spacing: -0.5px;
    }
    .author-info .role {
        color: #667eea;
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 4px;
    }
    .author-info .city {
        color: #64748b;
        font-size: 0.85rem;
    }

    .author-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .btn-contact {
        padding: 10px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.25s;
        border: 2px solid;
    }
    .btn-contact.github {
        background: #24292e;
        color: white;
        border-color: #24292e;
    }
    .btn-contact.github:hover {
        background: #1a1e22;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(36,41,46,0.4);
    }
    .btn-contact.email {
        background: #ea4335;
        color: white;
        border-color: #ea4335;
    }
    .btn-contact.email:hover {
        background: #d33426;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(234,67,53,0.4);
    }
    .btn-contact.linkedin {
        background: #0a66c2;
        color: white;
        border-color: #0a66c2;
    }
    .btn-contact.linkedin:hover {
        background: #084f96;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(10,102,194,0.4);
    }

    /* STATS */
    .stats-mini-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    .stat-mini-box {
        text-align: center;
        padding: 16px 12px;
        background: linear-gradient(135deg, rgba(102,126,234,0.08), rgba(118,75,162,0.08));
        border-radius: 12px;
        border: 1.5px solid rgba(102,126,234,0.15);
    }
    .stat-mini-box .value {
        font-size: 1.6rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
    }
    .stat-mini-box .label {
        font-size: 0.7rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-top: 6px;
    }

    /* TECHNOLOGIES */
    .tech-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }
    .tech-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px;
        transition: all 0.2s;
    }
    .tech-item:hover {
        transform: translateX(4px);
        border-color: #667eea;
    }
    .tech-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: white;
        flex-shrink: 0;
    }
    .tech-info strong {
        display: block;
        font-size: 0.9rem;
        color: var(--bs-body-color);
        font-weight: 800;
    }
    .tech-info small {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* MODULES */
    .modules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 12px;
    }
    .module-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px;
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        transition: all 0.2s;
    }
    .module-item:hover {
        border-color: #667eea;
        transform: translateY(-2px);
    }
    .module-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .module-text strong {
        display: block;
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--bs-body-color);
        margin-bottom: 2px;
    }
    .module-text small {
        color: #64748b;
        font-size: 0.75rem;
    }

    /* INFO LIST */
    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: rgba(102,126,234,0.05);
        border-radius: 10px;
        border-left: 4px solid #667eea;
    }
    .info-row .label {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-row .value {
        font-weight: 800;
        color: var(--bs-body-color);
        font-size: 0.95rem;
    }

    /* ROADMAP */
    .roadmap-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .roadmap-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 10px;
        background: var(--bs-body-bg);
        border-left: 4px solid #f59e0b;
    }
    .roadmap-item.done { border-left-color: #10b981; }
    .roadmap-item.planned { border-left-color: #3b82f6; }
    .roadmap-item .badge-status {
        font-size: 0.7rem;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .roadmap-item.done .badge-status { background: #d1fae5; color: #065f46; }
    .roadmap-item.planned .badge-status { background: #dbeafe; color: #1e40af; }
    .roadmap-item .roadmap-label {
        flex: 1;
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--bs-body-color);
    }

    /* FOOTER SIGNATURE */
    .about-footer {
        text-align: center;
        padding: 40px 20px 20px;
        margin-top: 20px;
        border-top: 1px solid var(--bs-border-color);
    }
    .about-footer p {
        color: #94a3b8;
        font-size: 0.9rem;
        margin: 0 0 8px;
    }
    .about-footer .signature {
        font-size: 1.1rem;
        font-weight: 900;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }
    .about-footer .heart {
        color: #ef4444;
        animation: heartBeat 1.5s ease-in-out infinite;
        display: inline-block;
    }
    @keyframes heartBeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    /* FADE IN */
    .fade-in {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .fade-in.delay-1 { animation-delay: 0.1s; }
    .fade-in.delay-2 { animation-delay: 0.2s; }
    .fade-in.delay-3 { animation-delay: 0.3s; }
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<!-- ============ HERO ============ -->
<div class="about-hero fade-in">
    <div class="about-hero-content">
        <div class="about-hero-icon">🖥️</div>
        <h1><?= e($appInfo['name']) ?></h1>
        <p>Application web de gestion de parc informatique, développée en PHP pur avec une architecture MVC moderne.</p>
        <div class="about-version">
            <i class="bi bi-tag-fill"></i>
            Version <?= e($appInfo['version']) ?>
            · <?= date('d/m/Y', strtotime($appInfo['release'])) ?>
        </div>

        <!-- Actions rapides -->
        <div class="about-actions">
            <a href="<?= e($appInfo['author']['github']) ?>" target="_blank" rel="noopener" class="about-action-btn primary">
                <i class="bi bi-github"></i> Voir sur GitHub
            </a>
            <a href="<?= url('todo') ?>" class="about-action-btn">
                <i class="bi bi-clipboard-check"></i> Tableau du projet
            </a>
            <a href="<?= url('reports') ?>" class="about-action-btn">
                <i class="bi bi-bar-chart"></i> Rapports
            </a>
        </div>
    </div>
</div>

<!-- ============ AUTEUR + STATS ============ -->
<div class="about-grid fade-in delay-1">

    <!-- AUTEUR -->
    <div class="about-card">
        <div class="about-card-title">
            <i class="bi bi-person-badge-fill"></i>
            Développeur
        </div>

        <div class="author-profile">
            <div class="author-avatar">
                <?= e(strtoupper(mb_substr($appInfo['author']['name'], 0, 1))) ?>
            </div>
            <div class="author-info">
                <h3><?= e($appInfo['author']['name']) ?></h3>
                <div class="role"><?= e($appInfo['author']['role']) ?></div>
                <div class="city">
                    <i class="bi bi-geo-alt-fill"></i>
                    <?= e($appInfo['author']['city']) ?>
                </div>
            </div>
        </div>

        <div class="author-actions">
            <a href="<?= e($appInfo['author']['github']) ?>" target="_blank" rel="noopener" class="btn-contact github">
                <i class="bi bi-github"></i> GitHub
            </a>
            <a href="mailto:corro74@gmail.com" class="btn-contact email">
                <i class="bi bi-envelope-fill"></i> Email
            </a>
        </div>
    </div>

    <!-- STATS + INFOS -->
    <div class="about-card">
        <div class="about-card-title">
            <i class="bi bi-info-circle-fill"></i>
            Informations & Statistiques
        </div>

        <!-- Stats mini -->
        <div class="stats-mini-grid">
            <div class="stat-mini-box">
                <div class="value"><?= $appInfo['stats']['modules'] ?></div>
                <div class="label">Modules</div>
            </div>
            <div class="stat-mini-box">
                <div class="value"><?= $appInfo['stats']['controllers'] ?></div>
                <div class="label">Contrôleurs</div>
            </div>
        </div>

        <div class="info-list">
            <div class="info-row">
                <span class="label">Version</span>
                <span class="value"><?= e($appInfo['version']) ?></span>
            </div>
            <div class="info-row">
                <span class="label">Environnement</span>
                <span class="value">
                    <span class="badge bg-warning text-dark"><?= e(strtoupper($appInfo['environment'])) ?></span>
                </span>
            </div>
            <div class="info-row">
                <span class="label">Fuseau horaire</span>
                <span class="value"><?= e($appInfo['timezone']) ?></span>
            </div>
        </div>
    </div>

</div>

<!-- ============ TECHNOLOGIES ============ -->
<div class="about-card fade-in delay-2" style="margin-bottom: 24px;">
    <div class="about-card-title">
        <i class="bi bi-stack"></i>
        Stack technique
    </div>
    <div class="tech-grid">
        <?php foreach ($appInfo['technologies'] as $tech): ?>
            <div class="tech-item">
                <div class="tech-icon" style="background: <?= e($tech['color']) ?>;">
                    <i class="bi <?= e($tech['icon']) ?>"></i>
                </div>
                <div class="tech-info">
                    <strong><?= e($tech['name']) ?></strong>
                    <small>v<?= e($tech['version']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============ MODULES ============ -->
<div class="about-card fade-in delay-3" style="margin-bottom: 24px;">
    <div class="about-card-title">
        <i class="bi bi-grid-3x3-gap-fill"></i>
        Modules de l'application
    </div>
    <div class="modules-grid">
        <?php foreach ($appInfo['modules'] as $module): ?>
            <div class="module-item">
                <div class="module-icon">
                    <i class="bi <?= e($module['icon']) ?>"></i>
                </div>
                <div class="module-text">
                    <strong><?= e($module['name']) ?></strong>
                    <small><?= e($module['desc']) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============ ROADMAP ============ -->
<div class="about-card fade-in delay-3" style="margin-bottom: 24px;">
    <div class="about-card-title">
        <i class="bi bi-rocket-takeoff-fill"></i>
        Roadmap
    </div>
    <div class="roadmap-list">
        <div class="roadmap-item done">
            <span class="badge-status">Terminé</span>
            <span class="roadmap-label">Version 0.6.0 — Tableau de bord</span>
        </div>
        <div class="roadmap-item planned">
            <span class="badge-status">Prévu</span>
            <span class="roadmap-label">Mode sombre complet</span>
        </div>
        <div class="roadmap-item planned">
            <span class="badge-status">Prévu</span>
            <span class="roadmap-label">Notifications email automatiques</span>
        </div>
        <div class="roadmap-item planned">
            <span class="badge-status">Prévu</span>
            <span class="roadmap-label">API REST + Application mobile</span>
        </div>
    </div>
</div>

<!-- ============ FOOTER SIGNATURE ============ -->
<div class="about-footer">
    <p>Développé avec <span class="heart">❤️</span> en PHP</p>
    <p class="signature">par <?= e($appInfo['author']['name']) ?></p>
    <p style="margin-top: 10px;">
        <a href="<?= e($appInfo['author']['github']) ?>" target="_blank" rel="noopener"
           style="color: #667eea; text-decoration: none; font-weight: 700;">
            <i class="bi bi-github"></i> github.com/<?= e($appInfo['author']['github_id']) ?>
        </a>
    </p>
</div>