<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('users') ?>" class="text-decoration-none">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
        </li>
        <li class="breadcrumb-item active">Nouvel utilisateur</li>
    </ol>
</nav>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-person-plus text-primary"></i>
            Nouvel utilisateur
        </h4>
        <p class="text-muted mb-0 small">Créer un nouveau compte utilisateur</p>
    </div>
    <a href="<?= url('users') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<!-- Affichage des erreurs -->
<?php if (!empty($_SESSION['_errors'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($_SESSION['_errors'] as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['_errors']); ?>
<?php endif; ?>

<form method="POST" action="<?= url('users') ?>" autocomplete="off">
    <?= csrf_field() ?>

    <div class="row g-3">
        <!-- Colonne principale -->
        <div class="col-lg-8">

            <!-- Informations de connexion -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-key text-primary"></i> Informations de connexion
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                Nom d'utilisateur <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="username"
                                   class="form-control"
                                   value="<?= e($_SESSION['_old']['username'] ?? '') ?>"
                                   required
                                   minlength="3"
                                   maxlength="80"
                                   pattern="[a-zA-Z0-9_.\-]+"
                                   title="Lettres, chiffres, points, tirets et underscores uniquement">
                            <small class="text-muted">3 à 80 caractères (lettres, chiffres, . _ -)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= e($_SESSION['_old']['email'] ?? '') ?>"
                                   required
                                   maxlength="180">
                        </div>

                        <!-- NOUVEAU : Mot de passe avec générateur -->
                        <div class="col-12">
                            <label class="form-label">
                                Mot de passe <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="password-input"
                                       class="form-control"
                                       required
                                       minlength="12"
                                       autocomplete="new-password"
                                       placeholder="Saisissez ou générez un mot de passe">
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        id="generate-password-btn"
                                        title="Générer un mot de passe fort">
                                    <i class="bi bi-magic"></i> Générer
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary d-none"
                                        id="copy-password-btn"
                                        title="Copier le mot de passe">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary d-none"
                                        id="regenerate-password-btn"
                                        title="Régénérer un autre mot de passe">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                Min. 12 caractères, avec majuscule, minuscule, chiffre et caractère spécial.
                                <strong>Cliquez sur "Générer" pour créer un mot de passe fort automatiquement.</strong>
                            </small>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox"
                                       name="must_change_password"
                                       id="must_change_password"
                                       class="form-check-input"
                                       value="1"
                                       checked>
                                <label class="form-check-label" for="must_change_password">
                                    L'utilisateur devra changer son mot de passe à la 1ère connexion
                                    <small class="text-muted d-block">
                                        (Recommandé : le mot de passe sera envoyé par email et devra être changé)
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-person text-primary"></i> Informations personnelles
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="first_name"
                                   class="form-control"
                                   value="<?= e($_SESSION['_old']['first_name'] ?? '') ?>"
                                   required
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="last_name"
                                   class="form-control"
                                   value="<?= e($_SESSION['_old']['last_name'] ?? '') ?>"
                                   required
                                   maxlength="100">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="<?= e($_SESSION['_old']['phone'] ?? '') ?>"
                                   maxlength="30">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">

            <!-- Rôles -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-shield-check text-primary"></i> Rôles
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (empty($roles)): ?>
                        <p class="text-muted small mb-0">Aucun rôle disponible.</p>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <div class="form-check mb-2">
                                <input type="checkbox"
                                       name="roles[]"
                                       id="role_<?= (int) $role['id'] ?>"
                                       class="form-check-input"
                                       value="<?= (int) $role['id'] ?>">
                                <label class="form-check-label" for="role_<?= (int) $role['id'] ?>">
                                    <?= e($role['name']) ?>
                                    <small class="text-muted d-block"><?= e($role['slug']) ?></small>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statut -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-toggle-on text-primary"></i> Statut du compte
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               class="form-check-input"
                               value="1"
                               checked>
                        <label class="form-check-label" for="is_active">
                            Compte actif
                        </label>
                    </div>
                    <small class="text-muted">
                        Un compte inactif ne peut pas se connecter.
                    </small>
                </div>
            </div>

            <!-- Boutons -->
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Créer l'utilisateur
                    </button>
                    <a href="<?= url('users') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Annuler
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

<!-- NOUVEAU : Script du générateur de mot de passe -->
<script src="<?= url('assets/js/password-generator.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.initPasswordGenerator(
            'password-input',
            'generate-password-btn',
            'copy-password-btn',
            'regenerate-password-btn'
        );

        // Afficher les boutons Copier/Régénérer après la 1ère génération
        document.getElementById('generate-password-btn')?.addEventListener('click', function() {
            document.getElementById('copy-password-btn')?.classList.remove('d-none');
            document.getElementById('regenerate-password-btn')?.classList.remove('d-none');
        });
    });
</script>

<?php unset($_SESSION['_old']); ?>