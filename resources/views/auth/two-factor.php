<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Vérification 2FA') ?> — <?= e(config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .twofactor-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 440px;
            width: 100%;
            padding: 40px 35px;
        }
        .twofactor-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        .twofactor-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 8px;
        }
        .twofactor-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        .code-input {
            font-size: 1.8rem;
            letter-spacing: 12px;
            text-align: center;
            font-family: ui-monospace, Consolas, monospace;
            font-weight: 600;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .code-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        }
        .btn-verify {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: transform 0.15s;
        }
        .btn-verify:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .user-info {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        .user-avatar-small {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .toggle-backup {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .toggle-backup a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .toggle-backup a:hover {
            text-decoration: underline;
        }
        .backup-help {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.82rem;
            color: #78350f;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="twofactor-card">

        <!-- Icône -->
        <div class="twofactor-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <!-- Titre -->
        <h1 class="twofactor-title">Vérification en 2 étapes</h1>
        <p class="twofactor-subtitle">
            Saisissez le code à 6 chiffres généré par votre application<br>
            (Google Authenticator, Authy, Microsoft Authenticator…)
        </p>

        <!-- Info utilisateur -->
        <div class="user-info">
            <div class="user-avatar-small">
                <?= e(strtoupper(mb_substr($user->firstName, 0, 1) . mb_substr($user->lastName, 0, 1))) ?>
            </div>
            <div>
                <div class="fw-semibold" style="font-size: 0.9rem;">
                    <?= e($user->getFullName()) ?>
                </div>
                <div class="text-muted" style="font-size: 0.78rem;">
                    @<?= e($user->username) ?>
                </div>
            </div>
        </div>

        <!-- Messages flash -->
        <?php if ($msg = flash('error')): ?>
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= e($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('info')): ?>
            <div class="alert alert-info py-2 mb-3">
                <i class="bi bi-info-circle-fill"></i> <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de vérification -->
        <form method="POST" action="<?= url('two-factor/verify') ?>" id="twofactor-form">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-muted">
                    Code d'authentification
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       class="form-control code-input"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       autofocus
                       required
                       maxlength="12"
                       placeholder="000000">
            </div>

            <button type="submit" class="btn btn-verify w-100">
                <i class="bi bi-check-circle-fill"></i> Vérifier
            </button>
        </form>

        <!-- Lien vers code de secours -->
        <div class="toggle-backup">
            <a href="#" id="toggle-backup-link">
                <i class="bi bi-key"></i> Utiliser un code de secours
            </a>
        </div>

        <!-- Aide backup code -->
        <div class="backup-help d-none" id="backup-help">
            <i class="bi bi-info-circle"></i>
            Un code de secours est au format <strong>XXXX-XXXX</strong>.
            Chaque code ne peut être utilisé <strong>qu'une seule fois</strong>.
        </div>

        <!-- Annulation -->
        <hr class="my-4">
        <form method="POST" action="<?= url('two-factor/cancel') ?>" class="text-center">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-link text-muted text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Annuler et retourner à la connexion
            </button>
        </form>
    </div>

    <!-- Script : basculer vers le mode "code de secours" -->
    <script>
        (function() {
            const codeInput   = document.getElementById('code');
            const toggleLink  = document.getElementById('toggle-backup-link');
            const backupHelp  = document.getElementById('backup-help');

            if (toggleLink) {
                toggleLink.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Change le format de l'input
                    codeInput.style.letterSpacing = '4px';
                    codeInput.style.fontSize = '1.4rem';
                    codeInput.setAttribute('maxlength', '9');
                    codeInput.setAttribute('placeholder', 'XXXX-XXXX');
                    codeInput.removeAttribute('inputmode');
                    codeInput.value = '';
                    codeInput.focus();

                    // Affiche l'aide
                    backupHelp.classList.remove('d-none');

                    // Cache le lien
                    this.classList.add('d-none');
                });
            }

            // Filtre automatique des caractères selon le mode
            codeInput.addEventListener('input', function() {
                const isBackupMode = codeInput.getAttribute('placeholder') === 'XXXX-XXXX';

                if (!isBackupMode) {
                    // Mode TOTP : uniquement les chiffres
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                } else {
                    // Mode backup : majuscules, chiffres et un tiret au milieu
                    this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '').slice(0, 9);
                }
            });
        })();
    </script>
</body>
</html>