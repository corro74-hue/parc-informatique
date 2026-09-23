<?php
/**
 * Partial de formulaire réutilisable
 */

// Équipement en cours (peut être null en mode création)
$currentEquipment = $equipment ?? null;

$errors = $_SESSION['_errors'] ?? [];
unset($_SESSION['_errors']);

// Helper : retourne la valeur d'un champ
// On utilise une closure SANS `use` pour éviter les problèmes de portée
$val = function (string $field, $default = '') use ($currentEquipment) {
    // 1. Old input (après erreur de validation)
    $old = $_SESSION['_old'][$field] ?? null;
    if ($old !== null && $old !== '') {
        return $old;
    }

    // 2. Valeur de l'équipement (mode édition)
    if ($currentEquipment !== null) {
        $mapping = [
            'category_id'        => 'categoryId',
            'brand_id'           => 'brandId',
            'model_text'         => 'modelText',
            'serial_number'      => 'serialNumber',
            'site_id'            => 'siteId',
            'service_id'         => 'serviceId',
            'status_id'          => 'statusId',
            'acquisition_date'   => 'acquisitionDate',
            'acquisition_value'  => 'acquisitionValue',
            'amortization_years' => 'amortizationYears',
            'warranty_end_date'  => 'warrantyEndDate',
            'invoice_number'     => 'invoiceNumber',
            'technical_specs'    => 'technicalSpecs',
            'designation'        => 'designation',
            'notes'              => 'notes',
        ];

        $property = $mapping[$field] ?? null;

        if ($property && property_exists($currentEquipment, $property)) {
            $value = $currentEquipment->$property;
            return $value ?? $default;
        }
    }

    return $default;
};

// Helper : classe d'erreur
$err = function (string $field) use ($errors) {
    return isset($errors[$field]) ? ' is-invalid' : '';
};

// Helper : message d'erreur
$errMsg = function (string $field) use ($errors) {
    return $errors[$field] ?? '';
};

$isEdit = $currentEquipment !== null;
?>

<div class="row g-3">

    <!-- ============================================ -->
    <!-- INFORMATIONS GÉNÉRALES -->
    <!-- ============================================ -->
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="bi bi-info-circle"></i> Informations générales
        </h6>
    </div>

    <!-- Numéro d'inventaire -->
    <div class="col-md-4">
        <label class="form-label">
            N° d'inventaire
            <?php if (!$isEdit): ?>
                <span class="badge bg-secondary">auto</span>
            <?php endif; ?>
        </label>
        <input type="text" class="form-control"
               value="<?= e($isEdit ? $currentEquipment->inventoryNumber : ($nextNumber ?? 'INF-XXXX-XXXX')) ?>"
               disabled>
        <?php if (!$isEdit): ?>
            <div class="form-text">Ce numéro sera généré automatiquement.</div>
        <?php endif; ?>
    </div>

    <!-- Catégorie -->
    <div class="col-md-4">
        <label class="form-label">Catégorie <span class="text-danger">*</span></label>
        <select name="category_id" class="form-select<?= $err('category_id') ?>" required>
            <option value="">— Sélectionner —</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= (string) $val('category_id') === (string) $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($errMsg('category_id')): ?>
            <div class="invalid-feedback"><?= e($errMsg('category_id')) ?></div>
        <?php endif; ?>
    </div>

    <!-- Statut -->
    <div class="col-md-4">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select name="status_id" class="form-select<?= $err('status_id') ?>" required>
            <option value="">— Sélectionner —</option>
            <?php foreach ($statuses as $st): ?>
                <option value="<?= $st['id'] ?>"
                    <?= (string) $val('status_id') === (string) $st['id'] ? 'selected' : '' ?>>
                    <?= e($st['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($errMsg('status_id')): ?>
            <div class="invalid-feedback"><?= e($errMsg('status_id')) ?></div>
        <?php endif; ?>
    </div>

    <!-- Désignation -->
    <div class="col-md-8">
        <label class="form-label">Désignation <span class="text-danger">*</span></label>
        <input type="text" name="designation"
               class="form-control<?= $err('designation') ?>"
               value="<?= e($val('designation')) ?>"
               placeholder="Ex : Unité centrale, Écran Dell 24 pouces..."
               maxlength="180" required>
        <?php if ($errMsg('designation')): ?>
            <div class="invalid-feedback"><?= e($errMsg('designation')) ?></div>
        <?php endif; ?>
    </div>

    <!-- Marque -->
    <div class="col-md-4">
        <label class="form-label">Marque</label>
        <select name="brand_id" class="form-select">
            <option value="">— Sélectionner —</option>
            <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>"
                    <?= (string) $val('brand_id') === (string) $b['id'] ? 'selected' : '' ?>>
                    <?= e($b['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Modèle -->
    <div class="col-md-6">
        <label class="form-label">Modèle</label>
        <input type="text" name="model_text" class="form-control"
               value="<?= e($val('model_text')) ?>"
               placeholder="Ex : ProDesk 400 G6"
               maxlength="150">
    </div>

    <!-- Numéro de série -->
    <div class="col-md-6">
        <label class="form-label">Numéro de série</label>
        <input type="text" name="serial_number"
               class="form-control<?= $err('serial_number') ?>"
               value="<?= e($val('serial_number')) ?>"
               placeholder="Ex : CZ123456"
               maxlength="150">
        <?php if ($errMsg('serial_number')): ?>
            <div class="invalid-feedback"><?= e($errMsg('serial_number')) ?></div>
        <?php endif; ?>
        <div class="form-text">Doit être unique dans la base.</div>
    </div>

    <!-- ============================================ -->
    <!-- AFFECTATION -->
    <!-- ============================================ -->
    <div class="col-12 mt-4">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="bi bi-geo-alt"></i> Affectation
        </h6>
    </div>

    <!-- Site -->
    <div class="col-md-6">
        <label class="form-label">Site</label>
        <select name="site_id" class="form-select">
            <option value="">— Sélectionner —</option>
            <?php foreach ($sites as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= (string) $val('site_id') === (string) $s['id'] ? 'selected' : '' ?>>
                    <?= e($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Service -->
    <div class="col-md-6">
        <label class="form-label">Service</label>
        <select name="service_id" class="form-select">
            <option value="">— Sélectionner —</option>
            <?php foreach ($services as $sv): ?>
                <option value="<?= $sv['id'] ?>"
                    <?= (string) $val('service_id') === (string) $sv['id'] ? 'selected' : '' ?>>
                    <?= e($sv['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- ============================================ -->
    <!-- INFORMATIONS FINANCIÈRES -->
    <!-- ============================================ -->
    <div class="col-12 mt-4">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="bi bi-cash-coin"></i> Informations financières
        </h6>
    </div>

    <div class="col-md-3">
        <label class="form-label">Date d'acquisition</label>
        <input type="date" name="acquisition_date"
               class="form-control<?= $err('acquisition_date') ?>"
               value="<?= e($val('acquisition_date')) ?>">
        <?php if ($errMsg('acquisition_date')): ?>
            <div class="invalid-feedback"><?= e($errMsg('acquisition_date')) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Valeur d'acquisition (DA)</label>
        <input type="number" name="acquisition_value"
               class="form-control<?= $err('acquisition_value') ?>"
               value="<?= e($val('acquisition_value', '0')) ?>"
               step="0.01" min="0" placeholder="0.00">
        <?php if ($errMsg('acquisition_value')): ?>
            <div class="invalid-feedback"><?= e($errMsg('acquisition_value')) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Amortissement (années)</label>
        <input type="number" name="amortization_years"
               class="form-control<?= $err('amortization_years') ?>"
               value="<?= e($val('amortization_years')) ?>"
               min="1" max="50" placeholder="5">
        <?php if ($errMsg('amortization_years')): ?>
            <div class="invalid-feedback"><?= e($errMsg('amortization_years')) ?></div>
        <?php endif; ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Fin de garantie</label>
        <input type="date" name="warranty_end_date" class="form-control"
               value="<?= e($val('warranty_end_date')) ?>">
    </div>

    <div class="col-md-6">
        <label class="form-label">N° de facture</label>
        <input type="text" name="invoice_number" class="form-control"
               value="<?= e($val('invoice_number')) ?>"
               placeholder="Ex : FAC-2024-0042"
               maxlength="80">
    </div>

    <!-- ============================================ -->
    <!-- NOTES -->
    <!-- ============================================ -->
    <div class="col-12 mt-4">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="bi bi-file-text"></i> Notes et spécifications
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label">Spécifications techniques</label>
        <textarea name="technical_specs" class="form-control" rows="3"
                  placeholder="Processeur, RAM, disque dur..."><?= e($val('technical_specs')) ?></textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3"
                  placeholder="Remarques, observations..."><?= e($val('notes')) ?></textarea>
    </div>

</div>