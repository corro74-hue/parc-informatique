<?php
/**
 * @var \App\Models\Reformation $reformation
 * @var array $items
 * @var array $decisions
 * @var array $logs
 * @var array $availableEquipment
 * @var array $documents
 */
$transitions = $reformation->getAvailableTransitions();
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
            <li class="breadcrumb-item active"><?= e($reformation->reference) ?></li>
        </ol>
    </nav>
    <a href="<?= url('reformations') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- PANNEAU D'AIDE — Actions disponibles                     -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center"
         style="cursor:pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#reformShowHelp"
         aria-expanded="false"
         aria-controls="reformShowHelp">
        <strong><i class="bi bi-info-circle text-primary"></i> Actions disponibles sur cette réforme</strong>
        <i class="bi bi-chevron-down" id="reformShowHelpIcon"></i>
    </div>

    <div class="collapse" id="reformShowHelp">
        <div class="card-body pt-0">
            <div class="row g-3 small">

                <div class="col-md-4">
                    <strong class="text-primary"><i class="bi bi-send"></i> Soumettre</strong>
                    <p class="text-muted mb-0">Envoie la réforme à la commission. Nécessite au moins 1 équipement.</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-info"><i class="bi bi-search"></i> Prendre en examen</strong>
                    <p class="text-muted mb-0">La commission examine le dossier. Statut : <em>En examen</em>.</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-success"><i class="bi bi-check-circle"></i> Approuver</strong>
                    <p class="text-muted mb-0">La commission valide. Statut : <em>Approuvée</em>.</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-danger"><i class="bi bi-x-circle"></i> Rejeter</strong>
                    <p class="text-muted mb-0">Refus motivé. Statut : <em>Rejetée</em> (workflow terminé).</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-warning"><i class="bi bi-pause-circle"></i> Reporter</strong>
                    <p class="text-muted mb-0">Renvoi à une prochaine réunion. Reste <em>En examen</em>.</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-secondary"><i class="bi bi-slash-circle"></i> Annuler</strong>
                    <p class="text-muted mb-0">Stoppe définitivement la réforme. Statut : <em>Annulée</em>.</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-primary"><i class="bi bi-file-earmark-pdf"></i> Générer le PV</strong>
                    <p class="text-muted mb-0">Produit le procès-verbal officiel (PDF).</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-primary"><i class="bi bi-file-earmark-arrow-down"></i> Générer le bon de sortie</strong>
                    <p class="text-muted mb-0">Produit l'autorisation de sortie (PDF).</p>
                </div>

                <div class="col-md-4">
                    <strong class="text-dark"><i class="bi bi-check2-all"></i> Terminer</strong>
                    <p class="text-muted mb-0">Clôture la réforme. <strong>Action irréversible</strong> : les équipements passent en « Réformé ».</p>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-1"><code><?= e($reformation->reference) ?></code></h4>
        <h5 class="text-muted mb-2"><?= e($reformation->title) ?></h5>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <?= $reformation->getStatusBadge() ?>
            <?= $reformation->getReasonBadge() ?>
            <span class="text-muted small">
                Valeur totale : <strong><?= e($reformation->getFormattedTotalValue()) ?></strong>
            </span>
        </div>
    </div>

    <div class="d-flex gap-2">
        <?php if ($reformation->isEditable() && can('equipment.edit')): ?>
            <a href="<?= url('reformations/' . $reformation->id . '/edit') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
        <?php endif; ?>
        <?php if (!$reformation->isClosed() && can('equipment.delete')): ?>
            <form method="POST" action="<?= url('reformations/' . $reformation->id . '/delete') ?>"
                  onsubmit="return confirm('Supprimer définitivement cette réforme ?');" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($transitions) && can('equipment.edit')): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <strong><i class="bi bi-lightning-charge-fill"></i> Actions disponibles</strong>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <?php
                $simpleActions = ['propose', 'review', 'generate_pv', 'generate_exit_voucher', 'complete'];
                $simpleIcons = [
                    'propose'               => 'send',
                    'review'                => 'search',
                    'generate_pv'           => 'file-earmark-pdf',
                    'generate_exit_voucher' => 'file-earmark-arrow-down',
                    'complete'              => 'check2-all',
                ];
                foreach ($transitions as $action => $label):
                    if (!in_array($action, $simpleActions, true)) continue;
                    $url = url('reformations/' . $reformation->id . '/' . str_replace('_', '-', $action));
                    $icon = $simpleIcons[$action] ?? 'arrow-right';
                ?>
                    <form method="POST" action="<?= $url ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $icon ?>"></i> <?= e($label) ?>
                        </button>
                    </form>
                <?php endforeach; ?>

                <?php if (isset($transitions['approve'])): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalApprove">
                        <i class="bi bi-check-circle"></i> Approuver
                    </button>
                <?php endif; ?>
                <?php if (isset($transitions['reject'])): ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">
                        <i class="bi bi-x-circle"></i> Rejeter
                    </button>
                <?php endif; ?>
                <?php if (isset($transitions['postpone'])): ?>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalPostpone">
                        <i class="bi bi-pause-circle"></i> Reporter
                    </button>
                <?php endif; ?>
                <?php if (isset($transitions['cancel'])): ?>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCancel">
                        <i class="bi bi-slash-circle"></i> Annuler
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($reformation->pvPath || $reformation->exitVoucherPath): ?>
    <div class="card mb-4">
        <div class="card-header"><strong><i class="bi bi-file-earmark-pdf"></i> Documents générés</strong></div>
        <div class="card-body d-flex gap-2 flex-wrap">
            <?php if ($reformation->pvPath): ?>
                <a href="<?= url('reformations/' . $reformation->id . '/pv') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Télécharger le PV
                </a>
            <?php endif; ?>
            <?php if ($reformation->exitVoucherPath): ?>
                <a href="<?= url('reformations/' . $reformation->id . '/exit-voucher') ?>" class="btn btn-outline-primary">
                    <i class="bi bi-download"></i> Télécharger le bon de sortie
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">

        <div class="card mb-4">
            <div class="card-header"><strong><i class="bi bi-info-circle"></i> Informations</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Motif</dt>
                    <dd class="col-sm-8"><?= e($reformation->getReasonLabel()) ?></dd>

                    <?php if ($reformation->reasonDetails): ?>
                        <dt class="col-sm-4">Détails</dt>
                        <dd class="col-sm-8"><?= nl2br(e($reformation->reasonDetails)) ?></dd>
                    <?php endif; ?>

                    <?php if ($reformation->commissionReference): ?>
                        <dt class="col-sm-4">Réf. commission</dt>
                        <dd class="col-sm-8"><?= e($reformation->commissionReference) ?></dd>
                    <?php endif; ?>

                    <?php if ($reformation->meetingDate): ?>
                        <dt class="col-sm-4">Date de réunion</dt>
                        <dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($reformation->meetingDate))) ?></dd>
                    <?php endif; ?>

                    <dt class="col-sm-4">Créée par</dt>
                    <dd class="col-sm-8">
                        <?= e($reformation->createdByName ?? '—') ?>
                        <small class="text-muted">le <?= e($reformation->createdAt ?? '—') ?></small>
                    </dd>

                    <?php if ($reformation->proposedByName): ?>
                        <dt class="col-sm-4">Soumise par</dt>
                        <dd class="col-sm-8">
                            <?= e($reformation->proposedByName) ?>
                            <small class="text-muted">le <?= e($reformation->proposedAt ?? '—') ?></small>
                        </dd>
                    <?php endif; ?>

                    <?php if ($reformation->decidedByName): ?>
                        <dt class="col-sm-4">Décidée par</dt>
                        <dd class="col-sm-8">
                            <?= e($reformation->decidedByName) ?>
                            <small class="text-muted">le <?= e($reformation->decidedAt ?? '—') ?></small>
                        </dd>
                    <?php endif; ?>

                    <?php if ($reformation->decisionNotes): ?>
                        <dt class="col-sm-4">Notes décision</dt>
                        <dd class="col-sm-8"><?= nl2br(e($reformation->decisionNotes)) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="bi bi-box-seam"></i> Équipements concernés (<?= count($items) ?>)</strong>
            </div>
            <div class="card-body p-0">
                <?php if (empty($items)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Aucun équipement ajouté pour l'instant.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>N° inventaire</th>
                                    <th>Désignation</th>
                                    <th>Catégorie</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-end">Valeur</th>
                                    <?php if ($reformation->isEditable() && can('equipment.edit')): ?>
                                        <th class="text-center">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><code><?= e($item->equipmentInventoryNumber ?? '—') ?></code></td>
                                        <td><?= e($item->equipmentDesignation ?? '—') ?></td>
                                        <td><?= e($item->equipmentCategoryName ?? '—') ?></td>
                                        <td class="text-center"><?= $item->getStatusBadge() ?></td>
                                        <td class="text-end"><?= e($item->getFormattedEstimatedValue()) ?></td>
                                        <?php if ($reformation->isEditable() && can('equipment.edit')): ?>
                                            <td class="text-center">
                                                <form method="POST"
                                                      action="<?= url('reformations/' . $reformation->id . '/items/' . $item->id . '/delete') ?>"
                                                      onsubmit="return confirm('Retirer cet équipement ?');"
                                                      class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total :</th>
                                    <th class="text-end"><?= e($reformation->getFormattedTotalValue()) ?></th>
                                    <?php if ($reformation->isEditable() && can('equipment.edit')): ?>
                                        <th></th>
                                    <?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($reformation->isEditable() && can('equipment.edit')): ?>
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
            <?php endif; ?>
        </div>

        <?php if (!empty($decisions)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong><i class="bi bi-shield-check"></i> Décisions de la commission (<?= count($decisions) ?>)</strong>
                </div>
                <div class="card-body">
                    <?php foreach ($decisions as $d): ?>
                        <div class="border rounded p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <?= $d->getDecisionBadge() ?>
                                <small class="text-muted"><?= e($d->decisionDate) ?></small>
                            </div>
                            <?php if ($d->notes): ?>
                                <div><?= nl2br(e($d->notes)) ?></div>
                            <?php endif; ?>
                            <?php $members = $d->getCommissionMembersList(); ?>
                            <?php if (!empty($members)): ?>
                                <div class="mt-2 small text-muted">
                                    <strong>Membres :</strong> <?= e(implode(', ', $members)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- ✅ NOUVEAU : Documents liés (GED)             -->
        <!-- ============================================ -->
        <?php
        $entityType  = 'reformation';
        $entityId    = $reformation->id;
        $entityLabel = 'cette réforme';
        require dirname(__DIR__) . '/partials/_documents_linked.php';
        ?>

    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong><i class="bi bi-clock-history"></i> Historique</strong></div>
            <div class="card-body" style="max-height:600px; overflow-y:auto;">
                <?php if (empty($logs)): ?>
                    <p class="text-muted text-center mb-0">Aucun évènement.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($logs as $log): ?>
                            <li class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="small"><?= e($log['action'] ?? '') ?></strong>
                                    <small class="text-muted">
                                        <?= e(date('d/m H:i', strtotime($log['created_at'] ?? 'now'))) ?>
                                    </small>
                                </div>
                                <?php if (!empty($log['from_status']) || !empty($log['to_status'])): ?>
                                    <div class="small text-muted">
                                        <?= e($log['from_status'] ?? '—') ?> → <?= e($log['to_status'] ?? '—') ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['user_name'])): ?>
                                    <div class="small text-muted">par <?= e($log['user_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($log['comment'])): ?>
                                    <div class="small mt-1 fst-italic">« <?= e($log['comment']) ?> »</div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modales workflow -->

<div class="modal fade" id="modalApprove" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('reformations/' . $reformation->id . '/approve') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Approuver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Date de la décision</label>
                    <input type="date" name="decision_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Membres de la commission</label>
                    <input type="text" name="commission_members" class="form-control" placeholder="Ex : M. Dupont, Mme Martin">
                </div>
                <div class="mb-0">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success">Confirmer l'approbation</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalReject" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('reformations/' . $reformation->id . '/reject') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Rejeter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="decision_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Membres</label>
                    <input type="text" name="commission_members" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label">Motif du rejet</label>
                    <textarea name="notes" rows="3" class="form-control" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalPostpone" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('reformations/' . $reformation->id . '/postpone') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pause-circle"></i> Reporter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Motif du report</label>
                <textarea name="notes" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning">Reporter</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalCancel" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('reformations/' . $reformation->id . '/cancel') ?>" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-slash-circle"></i> Annuler</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Raison de l'annulation</label>
                <textarea name="reason" rows="3" class="form-control" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Retour</button>
                <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        const el = document.getElementById('reformShowHelp');
        const icon = document.getElementById('reformShowHelpIcon');
        if (el && icon) {
            el.addEventListener('show.bs.collapse', () => {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            });
            el.addEventListener('hide.bs.collapse', () => {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            });
        }
    })();
</script>