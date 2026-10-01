<?php
/**
 * @var \App\Models\Reformation $reformation
 * @var array $items
 * @var array $errors
 * @var array $old
 * @var array $availableEquipment
 */
$reasonLabels = \App\Models\Reformation::reasonLabels();
$data = !empty($old) ? $old : [
    'title'                => $reformation->title,
    'reason'               => $reformation->reason,
    'reason_details'       => $reformation->reasonDetails,
    'commission_reference' => $reformation->commissionReference,
    'meeting_date'         => $reformation->meetingDate,
];
?>

<!-- Fil d'Ariane + Bouton retour -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="<?= url('reformations') ?>">
                    <i class="bi bi-recycle"></i> Réformes
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= url('reformations/' . $reformation->id) ?>"><?= e($reformation->reference) ?></a>
            </li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>
    <a href="<?= url('reformations/' . $reformation->id) ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour à la réforme
    </a>
</div>

<div class="mb-3">
    <h4 class="mb-0">
        <i class="bi bi-pencil text-primary"></i>
        Modifier la réforme
        <span class="badge bg-secondary"><?= e($reformation->reference) ?></span>
    </h4>
    <p class="text-muted mb-0 small">Mettez à jour les informations et les équipements concernés.</p>
</div>

<form method="POST" action="<?= url('reformations/' . $reformation->id) ?>">
    <?= csrf_field() ?>

    <div class="card mb-3">
        <div class="card-header">
            <strong><i class="bi bi-info-circle"></i> Informations générales</strong>
        </div>
        <div class="card-body">

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="title" class="form-label">
                        Titre <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="title" name="title" required maxlength="255"
                           value="<?= e($data['title'] ?? '') ?>"
                           class="form-control <?= !empty($errors['title']) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="reason" class="form-label">
                        Motif <span class="text-danger">*</span>
                    </label>
                    <select id="reason" name="reason" required
                            class="form-select <?= !empty($errors['reason']) ? 'is-invalid' : '' ?>">
                        <?php foreach ($reasonLabels as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= ($data['reason'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['reason'])): ?>
                        <div class="invalid-feedback"><?= e($errors['reason']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="meeting_date" class="form-label">Date de réunion</label>
                    <input type="date" id="meeting_date" name="meeting_date"
                           value="<?= e($data['meeting_date'] ?? '') ?>"
                           class="form-control">
                </div>

                <div class="col-md-12">
                    <label for="reason_details" class="form-label">Détails du motif</label>
                    <textarea id="reason_details" name="reason_details" rows="4" maxlength="2000"
                              class="form-control"><?= e($data['reason_details'] ?? '') ?></textarea>
                </div>

                <div class="col-md-6">
                    <label for="commission_reference" class="form-label">Référence commission</label>
                    <input type="text" id="commission_reference" name="commission_reference" maxlength="100"
                           value="<?= e($data['commission_reference'] ?? '') ?>"
                           class="form-control">
                </div>
            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="<?= url('reformations/' . $reformation->id) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-x-lg"></i> Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg"></i> Enregistrer
        </button>
    </div>
</form>

<!-- Équipements -->
<?php if ($reformation->canModifyItems() || $reformation->isEditable()): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-box-seam"></i> Équipements concernés (<?= count($items) ?>)</strong>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0">Aucun équipement pour l'instant.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>N° inventaire</th>
                                <th>Désignation</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end">Valeur</th>
                                <th class="text-center" width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><code><?= e($item->equipmentInventoryNumber ?? '—') ?></code></td>
                                    <td><?= e($item->equipmentDesignation ?? '—') ?></td>
                                    <td class="text-center"><?= $item->getStatusBadge() ?></td>
                                    <td class="text-end"><strong><?= e($item->getFormattedEstimatedValue()) ?></strong></td>
                                    <td class="text-center">
                                        <form method="POST"
                                              action="<?= url('reformations/' . $reformation->id . '/items/' . $item->id . '/delete') ?>"
                                              onsubmit="return confirm('Retirer cet équipement ?');"
                                              class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <form method="POST" action="<?= url('reformations/' . $reformation->id . '/items') ?>"
                  class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Équipement</label>
                    <select name="equipment_id" class="form-select" required>
                        <option value="">— Sélectionner un équipement —</option>
                        <?php foreach ($availableEquipment as $eq): ?>
                            <option value="<?= (int) $eq['id'] ?>">
                                <?= e(($eq['inventory_number'] ?? '—') . ' — ' . ($eq['designation'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Quantité</label>
                    <input type="number" name="quantity" value="1" min="1" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Valeur estimée</label>
                    <input type="number" name="estimated_value" value="0" min="0" step="0.01" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>