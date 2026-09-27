<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-key-fill text-warning"></i>
            Vos codes de secours
        </h4>
        <p class="text-muted mb-0 small">
            Conservez ces codes en lieu sûr — ils ne seront plus jamais affichés
        </p>
    </div>
    <a href="<?= url('profile/security') ?>" class="btn btn-primary">
        <i class="bi bi-check-circle"></i> J'ai sauvegardé mes codes
    </a>
</div>

<!-- Alerte importante -->
<div class="alert alert-warning d-flex align-items-start mb-3">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
    <div>
        <strong>Important :</strong> ces codes ne seront affichés <strong>qu'une seule fois</strong>.
        Notez-les maintenant dans un endroit sûr (gestionnaire de mots de passe, coffre-fort, papier…).
    </div>
</div>

<!-- Codes de secours -->
<div class="card mb-3">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bi bi-list-ol text-primary"></i>
            Vos 8 codes de secours
        </h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Chaque code ne peut être utilisé <strong>qu'une seule fois</strong>.
            Si vous perdez votre téléphone, vous pourrez vous connecter avec l'un de ces codes.
        </p>

        <div class="row g-2" id="backup-codes-grid">
            <?php foreach ($backupCodes as $index => $code): ?>
                <div class="col-md-6">
                    <div class="border rounded p-3 text-center bg-light">
                        <code style="font-size: 1.2rem; letter-spacing: 3px; font-weight: 700;">
                            <?= e($code) ?>
                        </code>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-footer bg-light">
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimer
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="copy-codes">
                <i class="bi bi-clipboard"></i> Copier tous les codes
            </button>
        </div>
    </div>
</div>

<!-- Info -->
<div class="card">
    <div class="card-body">
        <h6 class="mb-2">
            <i class="bi bi-info-circle text-primary"></i> Quand utiliser ces codes ?
        </h6>
        <ul class="small text-muted mb-0">
            <li>Si vous perdez votre téléphone ou votre application d'authentification.</li>
            <li>Si votre application ne génère plus de codes valides.</li>
            <li>En cas d'urgence pour accéder à votre compte.</li>
        </ul>
    </div>
</div>

<!-- Script pour copier les codes -->
<script>
document.getElementById('copy-codes')?.addEventListener('click', function() {
    const codes = <?= json_encode($backupCodes) ?>;
    const text = codes.join('\n');

    navigator.clipboard.writeText(text).then(() => {
        const btn = this;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Copiés !';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');

        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
});
</script>