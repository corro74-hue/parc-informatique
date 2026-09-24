<?php
/**
 * Partial réutilisable pour la pagination.
 *
 * Variables attendues :
 * @var array $pagination [
 *     'currentPage' => int,
 *     'lastPage'    => int,
 *     'total'       => int,
 *     'perPage'     => int,
 *     'baseUrl'     => string,  // ex: url('users')
 * ]
 */
if (($pagination['lastPage'] ?? 1) <= 1) {
    return;
}

$currentPage = (int) $pagination['currentPage'];
$lastPage    = (int) $pagination['lastPage'];
$baseUrl     = $pagination['baseUrl'] ?? '';

// Conserve les paramètres GET actuels (filtres, tri, etc.)
$queryParams = $_GET;

// Petit helper pour construire l'URL d'une page
$pageUrl = function (int $page) use ($baseUrl, $queryParams): string {
    $queryParams['page'] = $page;
    return $baseUrl . '?' . http_build_query($queryParams);
};

$start = max(1, $currentPage - 2);
$end   = min($lastPage, $currentPage + 2);
?>

<nav class="mt-3" aria-label="Pagination">
    <ul class="pagination justify-content-center mb-0">

        <!-- Première page -->
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $currentPage <= 1 ? '#' : e($pageUrl(1)) ?>" title="Première page">
                <i class="bi bi-chevron-double-left"></i>
            </a>
        </li>

        <!-- Page précédente -->
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $currentPage <= 1 ? '#' : e($pageUrl($currentPage - 1)) ?>" title="Précédent">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <!-- Début (si nécessaire) -->
        <?php if ($start > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= e($pageUrl(1)) ?>">1</a>
            </li>
            <?php if ($start > 2): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Pages du milieu -->
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= e($pageUrl($i)) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <!-- Fin (si nécessaire) -->
        <?php if ($end < $lastPage): ?>
            <?php if ($end < $lastPage - 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="<?= e($pageUrl($lastPage)) ?>"><?= $lastPage ?></a>
            </li>
        <?php endif; ?>

        <!-- Page suivante -->
        <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $currentPage >= $lastPage ? '#' : e($pageUrl($currentPage + 1)) ?>" title="Suivant">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>

        <!-- Dernière page -->
        <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $currentPage >= $lastPage ? '#' : e($pageUrl($lastPage)) ?>" title="Dernière page">
                <i class="bi bi-chevron-double-right"></i>
            </a>
        </li>

    </ul>

    <!-- Info texte -->
    <p class="text-center text-muted small mt-2 mb-0">
        Page <?= $currentPage ?> sur <?= $lastPage ?>
        — <?= number_format((int) $pagination['total'], 0, ',', ' ') ?> résultat<?= $pagination['total'] > 1 ? 's' : '' ?>
    </p>
</nav>