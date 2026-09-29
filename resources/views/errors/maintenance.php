<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance en cours — <?= e(config('app.name', 'Parc Info')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
        }
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .maintenance-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }
        .maintenance-icon {
            font-size: 5rem;
            color: var(--primary);
            margin-bottom: 20px;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .maintenance-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        .maintenance-message {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        .maintenance-reason {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 15px 20px;
            text-align: left;
            margin-bottom: 25px;
        }
        .maintenance-reason strong {
            color: #92400e;
        }
        .maintenance-reason p {
            color: #78350f;
            margin: 0;
            font-size: 0.9rem;
        }
        .maintenance-end {
            background: #dbeafe;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 25px;
            color: #1e40af;
            font-size: 0.9rem;
        }
        .maintenance-footer {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .admin-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .admin-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="maintenance-card">

        <!-- Icône animée -->
        <div class="maintenance-icon">
            <i class="bi bi-tools"></i>
        </div>

        <!-- Titre -->
        <h1 class="maintenance-title">
            Maintenance en cours
        </h1>

        <!-- Message principal -->
        <p class="maintenance-message">
            L'application <strong><?= e(config('app.name', 'Parc Info')) ?></strong>
            est temporairement indisponible pour cause de maintenance.
            <br>
            Merci de votre patience, nous revenons très vite.
        </p>

        <!-- Raison (si fournie) -->
        <?php if (!empty($reason)): ?>
            <div class="maintenance-reason">
                <strong><i class="bi bi-info-circle-fill"></i> Raison :</strong>
                <p><?= e($reason) ?></p>
            </div>
        <?php endif; ?>

        <!-- Date de fin prévue (si fournie) -->
        <?php if (!empty($endAt)): ?>
            <div class="maintenance-end">
                <i class="bi bi-clock-history"></i>
                Fin prévue : <strong><?= e($endAt) ?></strong>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise"></i> Réessayer
            </button>
            <a href="<?= url('login') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-box-arrow-in-right"></i> Connexion admin
            </a>
        </div>

        <!-- Footer -->
        <div class="maintenance-footer">
            <p class="mb-1">
                <i class="bi bi-shield-lock"></i>
                Vos données sont en sécurité. Aucune intervention n'est nécessaire de votre part.
            </p>
            <p class="mb-0">
                © <?= date('Y') ?> <?= e(config('app.name', 'Parc Info')) ?>
                — Tous droits réservés
            </p>
        </div>

    </div>
</body>
</html>