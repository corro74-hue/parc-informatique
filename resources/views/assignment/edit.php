<?php
/**
 * Vue : Modification d'une affectation
 *
 * @var string $title
 * @var \App\Models\Assignment $assignment
 * @var \App\Models\Employee[] $employees
 * @var array $old
 * @var array $errors
 */
$currentEqId    = $assignment->equipmentId;
$currentEqLabel = $assignment->equipmentInventoryNumber . ' — ' . $assignment->equipmentDesignation;
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('assignments') ?>" class="text-decoration-none">
                <i class="bi bi-people-fill"></i> Affectations
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('assignments/' . $assignment->id) ?>" class="text-decoration-none">
                Affectation #<?= (int) $assignment->id ?>
            </a>
        </li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-pencil-square text-primary"></i>
            Modifier l'affectation #<?= (int) $assignment->id ?>
        </h4>
        <p class="text-muted mb-0 small">Corrigez les informations puis enregistrez</p>
    </div>
    <a href="<?= url('assignments/' . $assignment->id) ?>" class="btn btn-outline-secondary btn-sm">
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

<form method="POST" action="<?= url('assignments/' . $assignment->id) ?>">
    <?= csrf_field() ?>

    <div class="row g-3">

        <!-- Équipement (verrouillé) -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-box-seam"></i> Équipement affecté
                    <span class="badge bg-secondary ms-2">Verrouillé</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-secondary mb-0 py-2 small">
                        <i class="bi bi-lock-fill"></i>
                        L'équipement ne peut pas être modifié. Pour changer d'équipement,
                        clôturez cette affectation et créez-en une nouvelle.
                    </div>
                    <div class="mt-3">
                        <strong><?= e($currentEqLabel) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employé (modifiable) -->
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
                                        <?= (int) old('employee_id', $assignment->employeeId) === $emp->id ? 'selected' : '' ?>>
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
                               value="<?= e((string) old('service_id', $assignment->serviceId)) ?>"
                               placeholder="ID du service">
                    </div>

                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site (optionnel)</label>
                        <input type="number" name="site_id" id="site_id"
                               class="form-control"
                               value="<?= e((string) old('site_id', $assignment->siteId)) ?>"
                               placeholder="ID du site">
                    </div>

                    <div class="mb-0">
                        <label for="location_id" class="form-label">Localisation (optionnel)</label>
                        <input type="number" name="location_id" id="location_id"
                               class="form-control"
                               value="<?= e((string) old('location_id', $assignment->locationId)) ?>"
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
                                   value="<?= e((string) old('start_date', $assignment->startDate)) ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="reason" class="form-label">Motif de l'affectation</label>
                            <textarea name="reason" id="reason" class="form-control" rows="2"
                                      placeholder="Ex : Mise à disposition pour télétravail..."><?= e((string) old('reason', $assignment->reason)) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('assignments/' . $assignment->id) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</form>