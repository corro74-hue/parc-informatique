<?php
/**
 * Partial : Liste des documents liés à une entité
 *
 * Usage :
 *   $entityType  = 'equipment';
 *   $entityId    = $equipment->id;
 *   $entityLabel = 'cet équipement';
 *   require dirname(__DIR__) . '/partials/_documents_linked.php';
 *
 * @var array  $documents     Liste des documents (Document[])
 * @var string $entityType    'equipment' | 'maintenance' | 'reformation'
 * @var int    $entityId      ID de l'entité
 * @var string $entityLabel   Libellé pour les messages (ex: "cet équipement")
 */

$entityType  = $entityType  ?? 'equipment';
$entityId    = $entityId    ?? 0;
$entityLabel = $entityLabel ?? 'cette entité';
$documents   = $documents   ?? [];
?>

<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-file-earmark-text text-info"></i>
            Documents liés
            <span class="badge bg-secondary ms-1"><?= count($documents) ?></span>
        </h6>
        <div class="d-flex gap-2">
            <?php if (can('document.create')): ?>
                <a href="<?= url('documents/create?entity_type=' . urlencode($entityType) . '&entity_id=' . (int) $entityId) ?>"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-circle"></i> Ajouter
                </a>
            <?php endif; ?>
            <a href="<?= url('documents?entity_type=' . urlencode($entityType)) ?>"
               class="btn btn-sm btn-outline-secondary">
                Voir tous <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if (empty($documents)): ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">Aucun document lié à <?= e($entityLabel) ?>.</p>
                <?php if (can('document.create')): ?>
                    <a href="<?= url('documents/create?entity_type=' . urlencode($entityType) . '&entity_id=' . (int) $entityId) ?>"
                       class="btn btn-sm btn-primary mt-2">
                        <i class="bi bi-cloud-upload"></i> Ajouter le premier document
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50"></th>
                            <th width="150">Référence</th>
                            <th>Titre</th>
                            <th width="130" class="text-center">Type</th>
                            <th width="100" class="text-end">Taille</th>
                            <th width="80" class="text-center">Signé</th>
                            <th width="100" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td class="text-center">
                                    <i class="bi <?= $doc->getIcon() ?>" style="font-size: 1.5rem; color: #6b7280;"></i>
                                </td>
                                <td>
                                    <a href="<?= url('documents/' . $doc->id) ?>" class="text-decoration-none fw-semibold">
                                        <code><?= e($doc->reference ?? '#'.$doc->id) ?></code>
                                    </a>
                                </td>
                                <td><?= e($doc->title) ?></td>
                                <td class="text-center"><?= $doc->getTypeBadge() ?></td>
                                <td class="text-end">
                                    <small><?= e($doc->getFormattedSize()) ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($doc->isSigned): ?>
                                        <i class="bi bi-check-circle-fill text-success" title="Signé"></i>
                                    <?php else: ?>
                                        <i class="bi bi-dash-circle text-muted" title="Non signé"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('documents/' . $doc->id) ?>"
                                       class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (can('document.download')): ?>
                                        <a href="<?= url('documents/' . $doc->id . '/download') ?>"
                                           class="btn btn-sm btn-outline-secondary" title="Télécharger">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>