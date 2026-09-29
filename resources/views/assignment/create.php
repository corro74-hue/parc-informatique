<?php
/**
 * Vue : Formulaire de création d'affectation
 *
 * @var string $title
 * @var \App\Models\Equipment|null $equipment
 * @var \App\Models\Employee[]    $employees
 * @var array  $old
 * @var array  $errors
 */
$currentEqId    = (int) old('equipment_id', $equipment?->id ?? 0);
$currentEqLabel = $equipment
    ? $equipment->inventoryNumber . ' — ' . $equipment->designation
    : '';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-person-plus-fill text-primary"></i>
            Nouvelle affectation
        </h4>
        <p class="text-muted mb-0 small">Affecter un équipement à un employé</p>
    </div>
    <a href="<?= url('assignments') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste
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

<form method="POST" action="<?= url('assignments') ?>">
    <?= csrf_field() ?>

    <div class="row g-3">

        <!-- Équipement -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-box-seam"></i> Équipement à affecter
                </div>
                <div class="card-body">
                    <label for="equipment_search" class="form-label">
                        Rechercher un équipement <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <input
                            type="text"
                            id="equipment_search"
                            class="form-control"
                            placeholder="Tapez un N° d'inventaire, une désignation..."
                            value="<?= e($currentEqLabel) ?>"
                            autocomplete="off"
                            spellcheck="false"
                        >
                        <input type="hidden" name="equipment_id" id="equipment_id" value="<?= $currentEqId ?>">
                    </div>
                    <div id="equipment_suggestions" class="list-group position-absolute w-100 shadow-sm"
                         style="z-index: 1050; display: none; max-height: 280px; overflow-y: auto;"></div>

                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i>
                        Saisissez au moins 2 caractères. Utilisez ↑ ↓ pour naviguer, Entrée pour sélectionner.
                    </small>
                </div>
            </div>
        </div>

        <!-- Employé -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-person-fill"></i> Employé destinataire
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">
                            Employé <span class="text-danger">*</span>
                        </label>
                        <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">— Sélectionner un employé —</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= (int) $emp->id ?>"
                                        <?= (int) old('employee_id') === $emp->id ? 'selected' : '' ?>>
                                    <?= e($emp->getDisplayName()) ?>
                                    <?php if ($emp->serviceName): ?>
                                        — <?= e($emp->serviceName) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="service_id" class="form-label">Service (optionnel)</label>
                        <input type="number" name="service_id" id="service_id"
                               class="form-control"
                               value="<?= e((string) old('service_id')) ?>"
                               placeholder="ID du service">
                    </div>

                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site (optionnel)</label>
                        <input type="number" name="site_id" id="site_id"
                               class="form-control"
                               value="<?= e((string) old('site_id')) ?>"
                               placeholder="ID du site">
                    </div>

                    <div class="mb-0">
                        <label for="location_id" class="form-label">Localisation (optionnel)</label>
                        <input type="number" name="location_id" id="location_id"
                               class="form-control"
                               value="<?= e((string) old('location_id')) ?>"
                               placeholder="ID de la localisation">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates & Motif -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-event"></i> Informations de l'affectation
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">
                                Date de début <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date"
                                   class="form-control"
                                   value="<?= e((string) old('start_date', date('Y-m-d'))) ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">Date de fin prévue (optionnel)</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="form-control"
                                   value="<?= e((string) old('end_date')) ?>">
                        </div>
                        <div class="col-12">
                            <label for="reason" class="form-label">Motif de l'affectation</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3"
                                      placeholder="Ex : Mise à disposition pour télétravail..."><?= e((string) old('reason')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('assignments') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Créer l'affectation
            </button>
        </div>
    </div>
</form>

<!-- Script autocomplete -->
<script src="<?= url('assets/js/assignment-autocomplete.js') ?>"></script>