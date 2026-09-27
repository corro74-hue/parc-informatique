<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('profile') ?>" class="text-decoration-none">
                <i class="bi bi-person"></i> Mon profil
            </a>
        </li>
        <li class="breadcrumb-item active">Sécurité</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-shield-lock text-primary"></i>
            Sécurité du compte
        </h4>
        <p class="text-muted mb-0 small">Protégez votre compte avec l'authentification à deux facteurs</p>
    </div>
    <a href="<?= url('profile') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour au profil
    </a>
</div>

<div class="row g-3">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Authentification à deux facteurs (2FA) -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-shield-check text-primary"></i> Authentification à deux facteurs (2FA)
                </h6>
                <?php if ($user->twoFactorEnabled): ?>
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle-fill"></i> Activée
                    </span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle-fill"></i> Désactivée
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <?php if ($user->twoFactorEnabled): ?>
                    <!-- 2FA activée -->
                    <div class="alert alert-success d-flex align-items-start mb-3">
                        <i class="bi bi-shield-fill-check fs-4 me-2"></i>
                        <div>
                            <strong>Votre compte est protégé par la 2FA</strong>
                            <p class="mb-0 small">
                                Un code de vérification vous sera demandé à chaque connexion.
                                Vous pouvez désactiver la 2FA à tout moment ci-dessous.
                            </p>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle"></i>
                        Pour désactiver la 2FA, vous devez confirmer votre mot de passe.
                    </p>

                    <button type="button"
                            class="btn btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#disableTwoFactorModal">
                        <i class="bi bi-shield-slash"></i> Désactiver la 2FA
                    </button>

                <?php else: ?>
                    <!-- 2FA désactivée -->
                    <div class="alert alert-warning d-flex align-items-start mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                        <div>
                            <strong>La 2FA n'est pas activée</strong>
                            <p class="mb-0 small">
                                Ajoutez une couche de sécurité supplémentaire à votre compte.
                                Même si votre mot de passe est volé, personne ne pourra se connecter sans le code 2FA.
                            </p>
                        </div>
                    </div>

                    <h6 class="mb-3">Comment ça marche ?</h6>
                    <ol class="small text-muted mb-4">
                        <li class="mb-1">
                            <strong>Installez une application d'authentification</strong>
                            (Google Authenticator, Authy, Microsoft Authenticator, 1Password…)
                        </li>
                        <li class="mb-1">
                            <strong>Scannez le QR code</strong> que nous vous montrerons à l'étape suivante.
                        </li>
                        <li class="mb-1">
                            <strong>Saisissez le code à 6 chiffres</strong> généré par l'application pour confirmer.
                        </li>
                        <li>
                            <strong>Conservez vos codes de secours</strong> en lieu sûr (en cas de perte du téléphone).
                        </li>
                    </ol>

                    <a href="<?= url('profile/security/enable') ?>" class="btn btn-primary">
                        <i class="bi bi-shield-plus"></i> Activer la 2FA
                    </a>
                <?php endif; ?>

            </div>
        </div>

        <!-- Autres informations de sécurité -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary"></i> Autres informations
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Dernière connexion</label>
                        <strong>
                            <?= $user->lastLoginAt
                                ? e(date('d/m/Y à H:i', strtotime($user->lastLoginAt)))
                                : 'Jamais' ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Compte créé le</label>
                        <strong><?= e(date('d/m/Y', strtotime($user->createdAt))) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Mot de passe</label>
                        <a href="<?= url('profile') ?>" class="text-decoration-none">
                            <i class="bi bi-key"></i> Changer le mot de passe
                        </a>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Statut du compte</label>
                        <?php if ($user->isActive): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        <!-- Carte de conseils -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb text-warning"></i> Conseils de sécurité
                </h6>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Utilisez un mot de passe <strong>unique et complexe</strong>.</span>
                    </li>
                    <li class="mb-2 d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Activez la <strong>2FA</strong> pour une protection maximale.</span>
                    </li>
                    <li class="mb-2 d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Ne partagez <strong>jamais</strong> vos codes de secours.</span>
                    </li>
                    <li class="d-flex">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <span>Déconnectez-vous sur les <strong>appareils publics</strong>.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Statut 2FA -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-2">
                    <?php if ($user->twoFactorEnabled): ?>
                        <i class="bi bi-shield-fill-check text-success" style="font-size: 3rem;"></i>
                    <?php else: ?>
                        <i class="bi bi-shield-slash text-warning" style="font-size: 3rem;"></i>
                    <?php endif; ?>
                </div>
                <h6 class="mb-1">
                    <?= $user->twoFactorEnabled ? 'Compte protégé' : 'Compte non protégé' ?>
                </h6>
                <p class="text-muted small mb-0">
                    <?= $user->twoFactorEnabled
                        ? 'La 2FA est active sur votre compte.'
                        : 'Activez la 2FA pour plus de sécurité.' ?>
                </p>
            </div>
        </div>

    </div>
</div>

<!-- ============================================ -->
<!-- Modal : Désactivation de la 2FA              -->
<!-- ============================================ -->
<?php if ($user->twoFactorEnabled): ?>
<div class="modal fade" id="disableTwoFactorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('profile/security/disable') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-shield-slash text-danger"></i> Désactiver la 2FA
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Attention :</strong> Désactiver la 2FA réduit la sécurité de votre compte.
                    </div>

                    <p class="small">Pour confirmer, saisissez votre mot de passe actuel :</p>

                    <div class="mb-0">
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Votre mot de passe"
                               required
                               autocomplete="current-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-shield-slash"></i> Désactiver la 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>