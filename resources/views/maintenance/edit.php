<?php
/**
 * Vue : Modification d'une maintenance
 *
 * @var string $title
 * @var \App\Models\Maintenance $maintenance
 * @var array $old
 * @var array $errors
 */
$currentEqId    = $maintenance->equipmentId;
$currentEqLabel = $maintenance->equipmentInventoryNumber . ' — ' . $maintenance->equipmentDesignation;
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('maintenance') ?>" class="text-decoration-none">
                <i class="bi bi-tools"></i> Maintenance
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= url('maintenance/' . $maintenance->id) ?>" class="text-decoration-none">
                Ticket #<?= (int) $maintenance->id ?>
            </a>
        </li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-pencil-square text-primary"></i>
            Modifier le ticket #<?= (int) $maintenance->id ?>
        </h4>
        <p class="text-muted mb-0 small">Mettez à jour les informations de l'intervention</p>
    </div>
    <a href="<?= url('maintenance/' . $maintenance->id) ?>" class="btn btn-outline-secondary btn-sm">
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

<form method="POST" action="<?= url('maintenance/' . $maintenance->id) ?>">
    <?= csrf_field() ?>

    <div class="row g-3">

        <!-- Équipement (verrouillé) -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-box-seam"></i> Équipement concerné
                    <span class="badge bg-secondary ms-2">Verrouillé</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-secondary mb-0 py-2 small">
                        <i class="bi bi-lock-fill"></i>
                        L'équipement ne peut pas être modifié sur ce ticket.
                    </div>
                    <div class="mt-3">
                        <strong><?= e($currentEqLabel) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Type & statut -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-tag"></i> Type et statut
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="preventive" <?= old('type', $maintenance->type) === 'preventive' ? 'selected' : '' ?>>Préventive</option>
                            <option value="corrective" <?= old('type', $maintenance->type) === 'corrective' ? 'selected' : '' ?>>Corrective</option>
                            <option value="curative"   <?= old('type', $maintenance->type) === 'curative'   ? 'selected' : '' ?>>Curative</option>
                            <option value="upgrade"    <?= old('type', $maintenance->type) === 'upgrade'    ? 'selected' : '' ?>>Mise à niveau</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="open"          <?= old('status', $maintenance->status) === 'open'          ? 'selected' : '' ?>>Ouvert</option>
                            <option value="in_progress"   <?= old('status', $maintenance->status) === 'in_progress'   ? 'selected' : '' ?>>En cours</option>
                            <option value="waiting_parts" <?= old('status', $maintenance->status) === 'waiting_parts' ? 'selected' : '' ?>>En attente de pièces</option>
                            <option value="completed"     <?= old('status', $maintenance->status) === 'completed'     ? 'selected' : '' ?>>Terminé</option>
                            <option value="cancelled"     <?= old('status', $maintenance->status) === 'cancelled'     ? 'selected' : '' ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label for="technician" class="form-label">Technicien assigné</label>
                        <input type="text" name="technician" id="technician" class="form-control"
                               value="<?= e((string) old('technician', $maintenance->technician)) ?>" maxlength="150">
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-chat-left-text"></i> Description & diagnostic
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="problem_description" class="form-label">
                            Problème constaté <span class="text-danger">*</span>
                        </label>
                        <textarea name="problem_description" id="problem_description" class="form-control" rows="3" required><?= e((string) old('problem_description', $maintenance->problemDescription)) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Diagnostic</label>
                        <textarea name="diagnosis" id="diagnosis" class="form-control" rows="3"
                                  placeholder="Analyse technique du problème..."><?= e((string) old('diagnosis', $maintenance->diagnosis)) ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label for="work_done" class="form-label">Travaux effectués</label>
                        <textarea name="work_done" id="work_done" class="form-control" rows="3"
                                  placeholder="Description des réparations / actions..."><?= e((string) old('work_done', $maintenance->workDone)) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dates -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-event"></i> Dates & durée
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="started_at" class="form-label">Début intervention</label>
                            <input type="datetime-local" name="started_at" id="started_at" class="form-control"
                                   value="<?= e((string) old('started_at', $maintenance->startedAt ? date('Y-m-d\TH:i', strtotime($maintenance->startedAt)) : '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="completed_at" class="form-label">Fin intervention</label>
                            <input type="datetime-local" name="completed_at" id="completed_at" class="form-control"
                                   value="<?= e((string) old('completed_at', $maintenance->completedAt ? date('Y-m-d\TH:i', strtotime($maintenance->completedAt)) : '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="downtime_hours" class="form-label">Durée d'indisponibilité (h)</label>
                            <input type="number" name="downtime_hours" id="downtime_hours" class="form-control" min="0"
                                   value="<?= e((string) old('downtime_hours', (string) $maintenance->downtimeHours)) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultat & coût -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-cash-coin"></i> Résultat & coût
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="result" class="form-label">Résultat</label>
                            <select name="result" id="result" class="form-select">
                                <option value="">— Non défini —</option>
                                <option value="fixed"    <?= old('result', $maintenance->result) === 'fixed'    ? 'selected' : '' ?>>Réparé</option>
                                <option value="replaced" <?= old('result', $maintenance->result) === 'replaced' ? 'selected' : '' ?>>Remplacé</option>
                                <option value="unfixed"  <?= old('result', $maintenance->result) === 'unfixed'  ? 'selected' : '' ?>>Non réparé</option>
                                <option value="pending"  <?= old('result', $maintenance->result) === 'pending'  ? 'selected' : '' ?>>En attente</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i>
                                Si "Réparé" ou "Remplacé" → équipement remis en stock à la clôture.
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label for="cost" class="form-label">Coût (DA)</label>
                            <input type="number" name="cost" id="cost" class="form-control" step="0.01" min="0"
                                   value="<?= e((string) old('cost', (string) $maintenance->cost)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="invoice_number" class="form-label">N° facture</label>
                            <input type="text" name="invoice_number" id="invoice_number" class="form-control" maxlength="80"
                                   value="<?= e((string) old('invoice_number', $maintenance->invoiceNumber)) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <label for="notes" class="form-label">Notes internes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"><?= e((string) old('notes', $maintenance->notes)) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Boutons -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="<?= url('maintenance/' . $maintenance->id) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</form>