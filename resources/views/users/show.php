<!-- Fil d'Ariane -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('users') ?>" class="text-decoration-none">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
        </li>
        <li class="breadcrumb-item active"><?= e($user->getFullName()) ?></li>
    </ol>
</nav>

<!-- En-tête avec actions -->
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="user-avatar" style="width: 60px; height: 60px; font-size: 1.5rem;">
            <?= e($user->getInitials()) ?>
        </div>
        <div>
            <h4 class="mb-1">
                <?= e($user->getFullName()) ?>
                <?php if (!$user->isActive): ?>
                    <span class="badge bg-secondary">Inactif</span>
                <?php elseif ($user->isLocked()): ?>
                    <span class="badge bg-danger">Verrouillé</span>
                <?php else: ?>
                    <span class="badge bg-success">Actif</span>
                <?php endif; ?>
            </h4>
            <p class="text-muted mb-0 small">@<?= e($user->username) ?></p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('users') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>

        <!-- Réinitialisation mot de passe -->
        <form method="POST"
              action="<?= url('users/' . $user->id . '/reset-password') ?>"
              class="d-inline"
              onsubmit="return confirm('Réinitialiser le mot de passe de <?= e($user->getFullName()) ?> ?\n\nUn mot de passe temporaire sera généré.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-warning btn-sm">
                <i class="bi bi-key"></i> Réinitialiser mot de passe
            </button>
        </form>

        <a href="<?= url('users/' . $user->id . '/edit') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>

        <?php if ($user->id !== (int) $_SESSION['user_id']): ?>
            <form method="POST"
                  action="<?= url('users/' . $user->id . '/delete') ?>"
                  class="d-inline"
                  onsubmit="return confirm('⚠️ Supprimer l\'utilisateur <?= e($user->getFullName()) ?> ?\n\nCette action est réversible par un administrateur.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Informations générales -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle text-primary"></i> Informations générales
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Nom d'utilisateur</label>
                        <strong>@<?= e($user->username) ?></strong>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Email</label>
                        <a href="mailto:<?= e($user->email) ?>" class="text-decoration-none">
                            <?= e($user->email) ?>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Prénom</label>
                        <?= e($user->firstName) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Nom</label>
                        <?= e($user->lastName) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Téléphone</label>
                        <?= e($user->phone ?? '—') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Compte créé le</label>
                        <?= e(date('d/m/Y à H:i', strtotime($user->createdAt))) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des connexions -->
        <div class="card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-clock-history text-primary"></i> Historique des connexions
                </h6>
                <span class="badge bg-secondary"><?= count($loginHistory) ?> dernières</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($loginHistory)): ?>
                    <p class="text-muted text-center py-4 mb-0">
                        <i class="bi bi-inbox"></i> Aucun historique de connexion.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="180">Date</th>
                                    <th width="130">IP</th>
                                    <th>User-Agent</th>
                                    <th width="100" class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loginHistory as $log): ?>
                                    <tr>
                                        <td>
                                            <small><?= e(date('d/m/Y H:i:s', strtotime($log['attempted_at']))) ?></small>
                                        </td>
                                        <td>
                                            <code class="small"><?= e($log['ip_address']) ?></code>
                                        </td>
                                        <td>
                                            <small class="text-muted text-truncate d-block" style="max-width: 400px;"
                                                   title="<?= e($log['user_agent']) ?>">
                                                <?= e($log['user_agent']) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ((int) $log['success'] === 1): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check"></i> Succès
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x"></i> Échec
                                                </span>
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

    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        <!-- Rôles -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-shield-check text-primary"></i> Rôles
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($user->roles)): ?>
                    <p class="text-muted small mb-0">Aucun rôle assigné.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($user->roles as $role): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="bi bi-shield-fill"></i>
                                <?= e($role['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Permissions -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-key text-primary"></i> Permissions
                    <span class="badge bg-secondary"><?= count($user->permissions) ?></span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($user->permissions)): ?>
                    <p class="text-muted small mb-0">Aucune permission.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($user->permissions as $perm): ?>
                            <span class="badge bg-light text-dark border" style="font-size: 0.7rem;">
                                <?= e($perm) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Métadonnées -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-square text-primary"></i> Métadonnées
                </h6>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Créé le</span>
                    <span><?= e(date('d/m/Y H:i', strtotime($user->createdAt))) ?></span>
                </div>
                <?php if ($user->updatedAt): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Modifié le</span>
                        <span><?= e(date('d/m/Y H:i', strtotime($user->updatedAt))) ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Dernière connexion</span>
                    <span>
                        <?= $user->lastLoginAt
                            ? e(date('d/m/Y H:i', strtotime($user->lastLoginAt)))
                            : '—' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-0">
                    <span class="text-muted">ID interne</span>
                    <code>#<?= $user->id ?></code>
                </div>
            </div>
        </div>

    </div>
</div>