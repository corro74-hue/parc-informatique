<?php
/**
 * Vue : Formulaire de création d'une maintenance
 *
 * @var string $title
 * @var \App\Models\Equipment|null $equipment
 * @var array $old
 * @var array $errors
 */
$currentEqId    = (int) old('equipment_id', $equipment?->id ?? 0);
$currentEqLabel = $equipment
    ? $equipment->inventoryNumber . ' — ' . $equipment->designation
    : '';
$now = date('Y-m-d\TH:i');
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('maintenance') ?>" class="text-decoration-none">
                <i class="bi bi-tools"></i> Maintenance
            </a>
        </li>
        <li class="breadcrumb-item active">Nouvelle intervention</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-wrench-adjustable-circle text-primary"></i>
            Nouvelle maintenance
        </h4>
        <p class="text-muted mb-0 small">Déclarer une intervention sur un équipement</p>
    </div>
    <a href="<?= url('maintenance') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $field => $message): ?>
                <li><?= e(is_array($message) ? implode(' ', $message) : $message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('maintenance') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">

        <!-- Équipement -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-box-seam"></i> Équipement concerné
                </div>
                <div class="card-body">
                    <label for="equipment_search" class="form-label">
                        Rechercher un équipement <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <input type="text" id="equipment_search" class="form-control"
                               placeholder="Tapez un N° d'inventaire, une désignation..."
                               value="<?= e($currentEqLabel) ?>"
                               autocomplete="off" spellcheck="false">
                        <input type="hidden" name="equipment_id" id="equipment_id" value="<?= $currentEqId ?>">
                    </div>
                    <div id="equipment_suggestions" class="list-group position-absolute w-100 shadow-sm"
                         style="z-index: 1050; display: none; max-height: 280px; overflow-y: auto;"></div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i>
                        Saisissez au moins 2 caractères.
                    </small>
                </div>
            </div>
        </div>

        <!-- Type et statut -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-tag"></i> Type d'intervention
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">
                            Type <span class="text-danger">*</span>
                        </label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            <option value="preventive" <?= old('type') === 'preventive' ? 'selected' : '' ?>>Préventive</option>
                            <option value="corrective" <?= old('type') === 'corrective' ? 'selected' : '' ?>>Corrective</option>
                            <option value="curative"   <?= old('type') === 'curative'   ? 'selected' : '' ?>>Curative</option>
                            <option value="upgrade"    <?= old('type') === 'upgrade'    ? 'selected' : '' ?>>Mise à niveau</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Statut initial</label>
                        <select name="status" id="status" class="form-select">
                            <option value="open"          <?= old('status', 'open') === 'open'          ? 'selected' : '' ?>>Ouvert</option>
                            <option value="in_progress"   <?= old('status') === 'in_progress'   ? 'selected' : '' ?>>En cours</option>
                            <option value="waiting_parts" <?= old('status') === 'waiting_parts' ? 'selected' : '' ?>>En attente de pièces</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label for="technician" class="form-label">Technicien assigné</label>
                        <input type="text" name="technician" id="technician" class="form-control"
                               value="<?= e((string) old('technician')) ?>"
                               placeholder="Ex : Karim Benali" maxlength="150">
                    </div>
                </div>
            </div>
        </div>

        <!-- Description du problème -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-chat-left-text"></i> Description du problème
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="problem_description" class="form-label">
                            Problème constaté <span class="text-danger">*</span>
                        </label>
                        <textarea name="problem_description" id="problem_description" class="form-control" rows="4" required
                                  placeholder="Décrivez le problème rencontré..."><?= e((string) old('problem_description')) ?></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="reported_at" class="form-label">Date de déclaration</label>
                            <input type="datetime-local" name="reported_at" id="reported_at" class="form-control"
                                   value="<?= e((string) old('reported_at', $now)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="started_at" class="form-label">Début intervention</label>
                            <input type="datetime-local" name="started_at" id="started_at" class="form-control"
                                   value="<?= e((string) old('started_at')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="downtime_hours" class="form-label">Durée d'indisponibilité (h)</label>
                            <input type="number" name="downtime_hours" id="downtime_hours" class="form-control" min="0"
                                   value="<?= e((string) old('downtime_hours', '0')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-text"></i> Notes internes (optionnel)
                </div>
                <div class="card-body">
                    <textarea name="notes" id="notes" class="form-control" rows="2"
                              placeholder="Remarques, observations..."><?= e((string) old('notes')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('maintenance') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Créer l'intervention
            </button>
        </div>
    </div>
</form>

<script src="<?= url('assets/js/assignment-autocomplete.js') ?>"></script>