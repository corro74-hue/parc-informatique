<?php
/** @var array $sites */
/** @var array $services */
?>

<div class="mb-4">
    <a href="<?= url('campaigns') ?>" class="text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Retour aux campagnes
    </a>
    <h3 class="mt-2 mb-1"><i class="bi bi-plus-circle text-primary"></i> Nouvelle campagne d'inventaire</h3>
    <p class="text-muted small">Créez une campagne pour vérifier physiquement votre parc</p>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="<?= url('campaigns') ?>">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nom de la campagne <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" 
                       placeholder="Ex: Inventaire annuel 2026" required autofocus>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date de fin (optionnel)</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Site (optionnel)</label>
                    <select name="site_id" class="form-select">
                        <option value="">-- Tous les sites --</option>
                        <?php foreach ($sites as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service (optionnel)</label>
                    <select name="service_id" class="form-select">
                        <option value="">-- Tous les services --</option>
                        <?php foreach ($services as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description (optionnel)</label>
                <textarea name="description" class="form-control" rows="3" 
                          placeholder="Notes internes, consignes pour les agents..."></textarea>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Note :</strong> Tous les équipements correspondant au périmètre seront 
                automatiquement ajoutés à la campagne avec le statut <code>pending</code>.
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= url('campaigns') ?>" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Créer la campagne
                </button>
            </div>
        </form>
    </div>
</div>