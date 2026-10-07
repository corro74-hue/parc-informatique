<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Accueil') ?> — <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- 🎨 Google Fonts : Inter + Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --sidebar-width: 260px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        h1, h2, h3, h4 {
            font-family: 'Poppins', system-ui, sans-serif;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        /* ============================================ */
        /* 🎯 SIDEBAR - FIX : pas de débordement        */
        /* ============================================ */
        .sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #1e293b;
            color: #cbd5e1;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }

        /* ============================================ */
        /* 🎨 SIDEBAR HEADER - Logo 80px                */
        /* ============================================ */
        .sidebar-header {
            padding: 20px 15px;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 10px;
            min-height: 120px;
            width: 100%;
        }
        .sidebar-header i {
            font-size: 2.5rem;
            color: var(--primary);
        }
        .sidebar-header h1 {
            font-size: 1.1rem;
            margin: 0;
            color: white;
            font-weight: 600;
            text-align: center;
            font-family: 'Poppins', system-ui, sans-serif;
        }
        .sidebar-header img {
            height: 80px;
            width: auto;
            max-width: 100%;
            object-fit: contain;
            background: #ffffff;
            padding: 8px;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            transition: all 0.25s ease;
            flex-shrink: 0;
        }
        .sidebar-header img:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
        }

        /* ============================================ */
        /* NAVIGATION                                   */
        /* ============================================ */
        .sidebar-nav {
            padding: 15px 0;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.95rem;
            white-space: nowrap;
            font-weight: 500;
        }
        .sidebar-nav a:hover {
            background: #334155;
            color: white;
        }
        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
            border-left: 3px solid white;
            font-weight: 700;
        }
        .sidebar-nav a i {
            font-size: 1.1rem;
            width: 20px;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-section {
            padding: 15px 20px 5px;
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.8px;
            font-weight: 800;
            white-space: nowrap;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* ============================================ */
        /* MAIN CONTENT                                 */
        /* ============================================ */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
        }
        .topbar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .topbar h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #1e293b;
            font-weight: 800;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .user-info strong {
            font-weight: 700;
        }
        .user-info small {
            display: block;
            color: #64748b;
            font-size: 0.75rem;
        }
        .content-area {
            padding: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
        }

        /* ============================================ */
        /* Breadcrumb sans soulignement                 */
        /* ============================================ */
        .breadcrumb a,
        .breadcrumb-item a {
            text-decoration: none;
        }
        .breadcrumb a:hover,
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }

        /* ============================================ */
        /* Barre de recherche globale                   */
        /* ============================================ */
        .global-search-wrapper {
            position: relative;
            flex: 1;
            max-width: 480px;
            margin: 0 20px;
        }
        .global-search-box {
            position: relative;
            display: flex;
            align-items: center;
        }
        .global-search-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
        }
        .global-search-input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            background: #f8fafc;
            transition: all 0.15s;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .global-search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }
        .global-search-results {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-height: 460px;
            overflow-y: auto;
            display: none;
            z-index: 2000;
        }
        .global-search-results.show {
            display: block;
        }
        .gs-header {
            padding: 8px 14px;
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
            font-weight: 700;
        }
        .gs-empty {
            padding: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .gs-list {
            padding: 6px;
        }
        .gs-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 10px;
            border-radius: 6px;
            text-decoration: none;
            color: inherit;
            transition: background 0.1s;
        }
        .gs-item:hover {
            background: #f1f5f9;
            color: inherit;
            text-decoration: none;
        }
        .gs-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .gs-item-body {
            flex: 1;
            min-width: 0;
        }
        .gs-item-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .gs-item-sub {
            font-size: 0.78rem;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .gs-item-badge {
            font-size: 0.65rem;
            padding: 3px 8px;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            flex-shrink: 0;
        }

        /* ============================================ */
        /* Barre d'actions groupées (bulk)              */
        /* ============================================ */
        .bulk-actions-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            z-index: 1050;
            transition: transform 0.25s ease;
            max-width: 1100px;
            width: calc(100% - 40px);
        }
        .bulk-actions-bar.show {
            transform: translateX(-50%) translateY(0);
        }
        .bulk-bar-content {
            background: #1e293b;
            color: #fff;
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .bulk-bar-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .bulk-bar-info i {
            font-size: 1.3rem;
            color: var(--primary);
        }
        .bulk-bar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .bulk-bar-actions .form-select {
            background-color: #fff;
            border-color: #475569;
        }
        @media (max-width: 768px) {
            .bulk-bar-content {
                flex-direction: column;
                align-items: stretch;
            }
            .bulk-bar-actions {
                justify-content: center;
            }
        }

        /* ============================================ */
        /* Ajustements pour le Mode Sombre              */
        /* ============================================ */
        [data-bs-theme="dark"] body {
            background: #0f172a;
        }
        [data-bs-theme="dark"] .topbar {
            background: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        [data-bs-theme="dark"] .topbar h2 {
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .global-search-input {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .global-search-input:focus {
            background: #1e293b;
        }
        [data-bs-theme="dark"] .global-search-results {
            background: #1e293b;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .gs-header {
            background: #0f172a;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .gs-item:hover {
            background: #334155;
        }
        [data-bs-theme="dark"] .gs-item-title {
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .card {
            background: #1e293b;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .card-header {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #e2e8f0;
        }
        [data-bs-theme="dark"] .border-dashed {
            border-color: #475569 !important;
            background: #0f172a !important;
        }
    </style>

    <!-- Configuration JS globale -->
    <script>
        window.APP_CONFIG = {
            csrfToken: '<?= csrf_token() ?>',
            baseUrl:   '<?= rtrim(url(''), '/') ?>'
        };
    </script>

    <!-- Script anti-flash pour le mode sombre -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <?php
            $logoPath = \App\Core\Database::getInstance()
                ->query("SELECT `value` FROM `settings` WHERE `key` = 'app.logo'")
                ->fetchColumn();
            ?>
            <?php if ($logoPath && file_exists(dirname(__DIR__, 3) . '/public/' . $logoPath)): ?>
                <img src="<?= url($logoPath) ?>?v=<?= time() ?>" alt="Logo">
            <?php else: ?>
                <i class="bi bi-pc-display"></i>
                <h1>Parc Info</h1>
            <?php endif; ?>
        </div>
        <nav class="sidebar-nav">
            <?php
            $currentUri = $_SERVER['REQUEST_URI'] ?? '';
            $uriLower = strtolower($currentUri);
            ?>

            <div class="nav-section">Principal</div>
            <a href="<?= url('dashboard') ?>" class="<?= str_contains($uriLower, '/dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>

            <div class="nav-section">Gestion</div>
            <a href="<?= url('equipment') ?>" class="<?= (str_contains($uriLower, '/equipment') && !str_contains($uriLower, '/equipment/trash')) ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Équipements
            </a>
            <a href="<?= url('equipment/trash') ?>" class="<?= str_contains($uriLower, '/equipment/trash') ? 'active' : '' ?>">
                <i class="bi bi-trash"></i> Corbeille
            </a>
            <a href="<?= url('assignments') ?>" class="<?= str_contains($uriLower, '/assignments') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Affectations
            </a>
            <a href="<?= url('employees') ?>" class="<?= str_contains($uriLower, '/employees') ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Employés
            </a>
            <a href="<?= url('maintenance') ?>" class="<?= str_contains($uriLower, '/maintenance') ? 'active' : '' ?>">
                <i class="bi bi-tools"></i> Maintenance
            </a>
            <a href="<?= url('reformations') ?>" class="<?= str_contains($uriLower, '/reformations') ? 'active' : '' ?>">
                <i class="bi bi-recycle"></i> Réformes
            </a>
            <a href="<?= url('campaigns') ?>" class="<?= str_contains($uriLower, '/campaigns') ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i> Campagnes d'inventaire
            </a>

            <div class="nav-section">Documents</div>
            <a href="<?= url('documents') ?>" class="<?= str_contains($uriLower, '/documents') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Documents
            </a>
            <a href="<?= url('reports') ?>" class="<?= str_contains($uriLower, '/reports') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart"></i> Rapports
            </a>

            <?php if (can('users.view') || can('settings.manage') || has_role('admin')): ?>
                <div class="nav-section">Administration</div>
                <a href="<?= url('users') ?>" class="<?= str_contains($uriLower, '/users') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i> Utilisateurs
                </a>
                <a href="<?= url('roles') ?>" class="<?= str_contains($uriLower, '/roles') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i> Rôles et permissions
                </a>
                <a href="<?= url('audit') ?>" class="<?= str_contains($uriLower, '/audit') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i> Journal d'audit
                </a>
                <a href="<?= url('admin/database') ?>" class="<?= str_contains($uriLower, '/admin/database') ? 'active' : '' ?>">
                    <i class="bi bi-database-fill-gear"></i> Sauvegardes BDD
                </a>
                <a href="<?= url('todo') ?>" class="<?= str_contains($uriLower, '/todo') ? 'active' : '' ?>">
                    <i class="bi bi-clipboard-check"></i> Tableau du projet
                </a>
                <a href="<?= url('settings') ?>" class="<?= str_contains($uriLower, '/settings') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
                <a href="<?= url('about') ?>" class="<?= str_contains($uriLower, '/about') ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i> À propos
                </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <div class="topbar">
            <h2><?= e($title ?? 'Accueil') ?></h2>

            <!-- Barre de recherche globale -->
            <div class="global-search-wrapper">
                <div class="global-search-box">
                    <i class="bi bi-search global-search-icon"></i>
                    <input type="text"
                           id="global-search-input"
                           class="global-search-input"
                           placeholder="Rechercher un équipement..."
                           autocomplete="off"
                           spellcheck="false">
                </div>
                <div id="global-search-results" class="global-search-results"></div>
            </div>

            <div class="user-menu">
                <button id="theme-toggle" class="btn btn-sm btn-light" title="Changer de thème">
                    <i class="bi bi-moon-stars"></i>
                </button>

                <div class="user-info text-end">
                    <strong><?= e($_SESSION['full_name'] ?? 'Utilisateur') ?></strong>
                    <small><?= e(implode(', ', $_SESSION['roles'] ?? [])) ?></small>
                </div>
                <div class="user-avatar">
                    <?= e(strtoupper(mb_substr($_SESSION['full_name'] ?? 'U', 0, 1))) ?>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="<?= url('profile') ?>" class="dropdown-item">
                                <i class="bi bi-person"></i> Mon profil
                            </a>
                        </li>
                        <li>
                            <a href="<?= url('profile/security') ?>" class="dropdown-item d-flex align-items-center">
                                <i class="bi bi-shield-lock"></i>
                                <span class="ms-2">Sécurité</span>
                                <?php if (!empty($_SESSION['user_id'])): ?>
                                    <?php
                                    $authForMenu = new \App\Services\Auth\AuthService();
                                    $currentUser = $authForMenu->user();
                                    ?>
                                    <?php if ($currentUser && $currentUser->twoFactorEnabled): ?>
                                        <span class="badge bg-success ms-auto" style="font-size: 0.6rem;">2FA</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?= url('logout') ?>" class="m-0">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-area">
            <?php if ($msg = flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill"></i> <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($msg = flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($msg = flash('warning')): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['_password_expiry_warning'])): ?>
                <?php $daysLeft = (int) $_SESSION['_password_expiry_warning']; ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center">
                    <i class="bi bi-shield-exclamation fs-4 me-2"></i>
                    <div class="flex-grow-1">
                        <strong>Votre mot de passe expire bientôt</strong>
                        <p class="mb-0 small">
                            Il vous reste <strong><?= $daysLeft ?> jour<?= $daysLeft > 1 ? 's' : '' ?></strong>
                            avant que votre mot de passe n'expire.
                            <a href="<?= url('profile') ?>" class="alert-link">
                                Changez-le maintenant <i class="bi bi-arrow-right"></i>
                            </a>
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['_password_expiry_warning']); ?>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('assets/js/equipment-status.js') ?>"></script>
    <script src="<?= url('assets/js/global-search.js') ?>"></script>
    <script src="<?= url('assets/js/equipment-bulk.js') ?>"></script>
    <script src="<?= url('assets/js/theme.js') ?>"></script>
</body>
</html>