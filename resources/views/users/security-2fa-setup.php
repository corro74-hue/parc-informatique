<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('profile') ?>" class="text-decoration-none">
                <i class="bi bi-person"></i> Mon profil
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('profile/security') ?>" class="text-decoration-none">Sécurité</a>
        </li>
        <li class="breadcrumb-item active">Configurer la 2FA</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-shield-plus text-primary"></i>
            Configurer la 2FA
        </h4>
        <p class="text-muted mb-0 small">Scannez le QR code avec votre application d'authentification</p>
    </div>
    <a href="<?= url('profile/security') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Annuler
    </a>
</div>

<!-- Messages flash -->
<?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= e($msg) ?>
    </div>
<?php endif; ?>

<div class="row g-3">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Étape 1 : Scanner le QR code -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <span class="badge bg-primary me-2">1</span>
                    Scannez ce QR code
                </h6>
            </div>
            <div class="card-body text-center">
                <p class="text-muted small mb-3">
                    Ouvrez votre application d'authentification
                    (Google Authenticator, Authy, Microsoft Authenticator…)
                    et scannez ce code :
                </p>

                <img src="<?= e($qrCodeImageUrl) ?>"
                     alt="QR Code 2FA"
                     class="img-fluid border rounded p-2 bg-white"
                     style="max-width: 250px;">

                <div class="mt-3">
                    <p class="text-muted small mb-1">
                        <i class="bi bi-info-circle"></i>
                        Vous ne pouvez pas scanner le QR code ?
                        Saisissez manuellement cette clé dans votre application :
                    </p>
                    <div class="d-inline-flex align-items-center gap-2 mt-2">
                        <code class="bg-light px-3 py-2 rounded" style="font-size: 1.1rem; letter-spacing: 2px;">
                            <?= e($secret) ?>
                        </code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 2 : Saisir le code -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <span class="badge bg-primary me-2">2</span>
                    Saisissez le code à 6 chiffres
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Votre application affiche un code à 6 chiffres qui change toutes les 30 secondes.
                    Saisissez-le ci-dessous pour confirmer l'activation :
                </p>

                <form method="POST" action="<?= url('profile/security/confirm') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <input type="text"
                               name="code"
                               class="form-control text-center fw-bold"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               autofocus
                               required
                               maxlength="6"
                               pattern="\d{6}"
                               placeholder="000000"
                               style="font-size: 1.8rem; letter-spacing: 12px; padding: 15px;">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-check-circle-fill"></i> Confirmer et activer la 2FA
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb text-warning"></i> Conseils
                </h6>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Utilisez une application <strong>fiable</strong> (Google Authenticator, Authy…)</span>
                    </li>
                    <li class="mb-2 d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Ne partagez <strong>jamais</strong> votre clé secrète.</span>
                    </li>
                    <li class="d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Conservez les <strong>codes de secours</strong> en lieu sûr.</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-phone text-primary" style="font-size: 3rem;"></i>
                <h6 class="mt-2 mb-1">Application requise</h6>
                <p class="text-muted small mb-0">
                    Google Authenticator, Authy, Microsoft Authenticator, 1Password…
                </p>
            </div>
        </div>

    </div>
</div>