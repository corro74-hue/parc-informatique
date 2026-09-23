<div class="auth-logo">
    <i class="bi bi-pc-display-horizontal"></i>
    <h1>Parc Informatique</h1>
    <p>Connectez-vous à votre espace</p>
</div>

<form method="POST" action="<?= url('login') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="username" class="form-label">Nom d'utilisateur</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                value="<?= e(old('username')) ?>"
                placeholder="admin"
                required
                autofocus
                autocomplete="username">
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Mot de passe</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password">
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-box-arrow-in-right"></i> Se connecter
    </button>
</form>