<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Connexion') ?> — <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            padding: 20px;
        }
        .auth-card {
            max-width: 420px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-logo i {
            font-size: 3rem;
            color: #667eea;
        }
        .auth-logo h1 {
            font-size: 1.6rem;
            color: #1a1a1a;
            margin: 10px 0 5px 0;
        }
        .auth-logo p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0;
        }
        .btn-primary {
            background: #667eea;
            border-color: #667eea;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #5568d3;
            border-color: #5568d3;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        .auth-footer {
            text-align: center;
            margin-top: 25px;
            color: #adb5bd;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <?= $content ?>

        <div class="auth-footer">
            &copy; <?= date('Y') ?> <?= e(config('app.name')) ?>
        </div>
    </div>
</body>
</html>