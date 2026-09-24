<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-person-circle text-primary"></i>
            Mon profil
        </h4>
        <p class="text-muted mb-0 small">Gérez vos informations personnelles et votre mot de passe</p>
    </div>
</div>

<div class="row g-3">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Informations personnelles (lecture seule) -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-person text-primary"></i> Mes informations
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Nom d'utilisateur</label>
                        <strong>@<?= e($user->username) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Email</label>
                        <strong><?= e($user->email) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Prénom</label>
                        <?= e($user->firstName) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Nom</label>
                        <?= e($user->lastName) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Téléphone</label>
                        <?= e($user->phone ?? '—') ?>
                    </div>
                </div>
                <hr>
                <p class="text-muted small mb-0">
                    <i class="bi bi-info-circle"></i>
                    Pour modifier vos informations, contactez un administrateur.
                </p>
            </div>
        </div>

        <!-- Changer le mot de passe -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-key text-primary"></i> Changer mon mot de passe
                </h6>
            </div>
            <div class="card-body">

                <?php if (!empty($_SESSION['_errors'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($_SESSION['_errors'] as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['_errors']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= url('profile/change-password') ?>" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">
                            Mot de passe actuel <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="current_password"
                               class="form-control"
                               required
                               autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Nouveau mot de passe <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="new_password"
                               class="form-control"
                               required
                               minlength="10"
                               autocomplete="new-password">
                        <small class="text-muted">
                            Min. 10 caractères, avec majuscule, minuscule, chiffre et caractère spécial.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Confirmer le nouveau mot de passe <span class="text-danger">*</span>
                        </label>
                        <input type="password"
                               name="confirm_password"
                               class="form-control"
                               required
                               minlength="10"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Changer mon mot de passe
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        <!-- Carte profil -->
        <div class="card mb-3">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= e($user->getInitials()) ?>
                </div>
                <h5 class="mb-1"><?= e($user->getFullName()) ?></h5>
                <p class="text-muted small mb-2">@<?= e($user->username) ?></p>
                <div class="d-flex justify-content-center gap-1 flex-wrap">
                    <?php foreach ($user->roles as $role): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= e($role['name']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-shield-check text-primary"></i> Sécurité
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Dernière connexion</span>
                    <span>
                        <?= $user->lastLoginAt
                            ? e(date('d/m/Y H:i', strtotime($user->lastLoginAt)))
                            : '—' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Compte créé le</span>
                    <span><?= e(date('d/m/Y', strtotime($user->createdAt))) ?></span>
                </div>
                <?php if ($user->mustChangePassword): ?>
                    <div class="alert alert-warning py-2 mb-0 small mt-2">
                        <i class="bi bi-exclamation-triangle"></i>
                        Vous devez changer votre mot de passe.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>