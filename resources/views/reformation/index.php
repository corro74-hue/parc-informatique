<?php
/**
 * Vue : Liste des réformes
 *
 * @var array $paginated
 * @var array $filters
 * @var array $stats
 * @var int $totalAll
 * @var int $totalActive
 */
$reformations = $paginated['data'];
$total        = (int) $paginated['total'];
$page         = (int) $paginated['page'];
$lastPage     = (int) $paginated['last_page'];
$statusLabels = \App\Models\Reformation::statusLabels();
$reasonLabels = \App\Models\Reformation::reasonLabels();
?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- PANNEAU D'AIDE — Comment ça marche ?                     -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center"
         style="cursor:pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#reformHelp"
         aria-expanded="true"
         aria-controls="reformHelp">
        <strong><i class="bi bi-info-circle text-primary"></i> Comment ça marche ?</strong>
        <i class="bi bi-chevron-up" id="reformHelpIcon"></i>
    </div>

    <div class="collapse show" id="reformHelp">
        <div class="card-body pt-0">
            <div class="row g-4">

                <!-- Colonne gauche -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <h6 class="text-primary mb-2">
                            <i class="bi bi-bullseye"></i> À quoi ça sert ?
                        </h6>
                        <p class="small text-muted mb-0">
                            Le module <strong>Réformes</strong> gère le cycle de vie complet des équipements
                            destinés à être déclassés (obsolescence, panne irréparable, usure, fin de vie).
                            Il produit les documents officiels (<strong>PV</strong> et <strong>Bon de sortie</strong>)
                            et passe automatiquement les équipements concernés en statut <em>Réformé</em>
                            à la clôture.
                        </p>
                    </div>

                    <div>
                        <h6 class="text-warning mb-2">
                            <i class="bi bi-exclamation-triangle-fill"></i> Précautions
                        </h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Une réforme <strong>terminée ne peut plus être modifiée</strong>.</li>
                            <li>L'action <em>Terminer</em> est <strong>irréversible</strong> : les équipements passent en statut « Réformé ».</li>
                            <li>Impossible d'ajouter un équipement déjà en statut final (Réformé, Perdu, Sorti d'inventaire).</li>
                            <li>Au moins <strong>1 équipement</strong> est requis avant de soumettre.</li>
                            <li>La suppression est <em>douce</em> (soft delete) — récupérable en BDD.</li>
                        </ul>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <h6 class="text-info mb-2">
                            <i class="bi bi-tools"></i> Comment l'utiliser ?
                        </h6>
                        <ol class="small text-muted mb-0 ps-3">
                            <li>Créer une réforme (titre + motif).</li>
                            <li>Ajouter les équipements concernés (valeur estimée).</li>
                            <li><strong>Soumettre</strong> pour examen.</li>
                            <li><strong>Prendre en examen</strong> par la commission.</li>
                            <li><strong>Approuver</strong> ou <strong>Rejeter</strong> (avec notes et membres).</li>
                            <li><strong>Générer le PV</strong> (PDF officiel).</li>
                            <li><strong>Générer le bon de sortie</strong> (PDF autorisation).</li>
                            <li><strong>Terminer</strong> — les équipements passent en « Réformé ».</li>
                        </ol>
                    </div>

                    <div>
                        <h6 class="text-success mb-2">
                            <i class="bi bi-lightbulb-fill"></i> Bonnes pratiques
                        </h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li><strong>Renseignez la référence commission</strong> (ex: COM-2026-001) pour traçabilité.</li>
                            <li>Notez les <strong>membres de la commission</strong> lors de la décision.</li>
                            <li>Documentez <strong>le motif du rejet</strong> pour éviter les recours.</li>
                            <li><strong>Téléchargez les PDFs</strong> et archivez-les hors ligne.</li>
                            <li>Générez les <strong>sauvegardes BDD</strong> avant chaque clôture importante.</li>
                            <li>Vérifiez le <strong>statut des équipements</strong> après chaque clôture.</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- En-tête -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">
            <i class="bi bi-recycle text-primary"></i>
            Réformes
            <span class="badge bg-secondary"><?= $total ?></span>
        </h4>
        <p class="text-muted mb-0 small">
            <?= $total ?> réforme<?= $total > 1 ? 's' : '' ?>
            — page <?= $page ?> sur <?= $lastPage ?>
        </p>
    </div>
    <a href="<?= url('reformations/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouvelle réforme
    </a>
</div>

<!-- Stats par statut -->
<div class="row g-2 mb-3">
    <?php
    $statusCards = [
        'draft'        => ['label' => 'Brouillons',  'color' => '#6b7280', 'icon' => 'file-earmark-text'],
        'proposed'     => ['label' => 'Soumises',    'color' => '#f59e0b', 'icon' => 'send'],
        'under_review' => ['label' => 'En examen',   'color' => '#3b82f6', 'icon' => 'search'],
        'approved'     => ['label' => 'Approuvées',  'color' => '#22c55e', 'icon' => 'check-circle'],
        'completed'    => ['label' => 'Terminées',   'color' => '#10b981', 'icon' => 'check-all'],
    ];
    foreach ($statusCards as $code => $meta):
        $count = (int) ($stats[$code] ?? 0);
    ?>
        <div class="col-md">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3" style="font-size: 1.8rem; color: <?= $meta['color'] ?>;">
                        <i class="bi bi-<?= $meta['icon'] ?>"></i>
                    </div>
                    <div>
                        <div class="h5 mb-0"><?= $count ?></div>
                        <div class="text-muted small"><?= $meta['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Info : total actif -->
<?php if ($totalActive > 0): ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
        <div>
            <i class="bi bi-info-circle"></i>
            <strong>Réformes en cours :</strong>
            <?= (int) $totalActive ?> dossier<?= $totalActive > 1 ? 's' : '' ?> non clôturé<?= $totalActive > 1 ? 's' : '' ?>
        </div>
    </div>
<?php endif; ?>

<!-- Filtres -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('reformations') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Recherche</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Référence, titre, commission..."
                       value="<?= e((string) ($filters['search'] ?? '')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Statut</label>
                <select name="status" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Motif</label>
                <select name="reason" class="form-select">
                    <option value="">Tous les motifs</option>
                    <?php foreach ($reasonLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($filters['reason'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filtrer
                </button>
                <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['reason'])): ?>
                    <a href="<?= url('reformations') ?>" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th width="160">Référence</th>
                    <th>Titre</th>
                    <th width="130" class="text-center">Motif</th>
                    <th width="140" class="text-center">Statut</th>
                    <th width="130" class="text-end">Valeur</th>
                    <th width="130" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reformations)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Aucune réforme trouvée.</p>
                            <?php if (!empty($filters['search']) || !empty($filters['status']) || !empty($filters['reason'])): ?>
                                <a href="<?= url('reformations') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
                                </a>
                            <?php else: ?>
                                <a href="<?= url('reformations/create') ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-circle"></i> Créer la première réforme
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reformations as $r): ?>
                        <tr>
                            <td><code>#<?= (int) $r->id ?></code></td>
                            <td>
                                <a href="<?= url('reformations/' . $r->id) ?>" class="text-decoration-none fw-semibold">
                                    <?= e($r->reference) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-medium"><?= e($r->title) ?></div>
                                <?php if ($r->createdByName): ?>
                                    <div class="small text-muted">par <?= e($r->createdByName) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $r->getReasonBadge() ?></td>
                            <td class="text-center"><?= $r->getStatusBadge() ?></td>
                            <td class="text-end">
                                <strong><?= e($r->getFormattedTotalValue()) ?></strong>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('reformations/' . $r->id) ?>"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($r->isEditable()): ?>
                                    <a href="<?= url('reformations/' . $r->id . '/edit') ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($lastPage > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <?php $queryParams = array_filter($filters); ?>
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($lastPage, $page + 1)])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<script>
    (function() {
        const el = document.getElementById('reformHelp');
        const icon = document.getElementById('reformHelpIcon');
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