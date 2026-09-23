<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= url('equipment') ?>">

            <!-- Ligne 1 : Filtres rapides -->
            <div class="row g-2 align-items-end mb-3">
                <!-- Recherche -->
                <div class="col-md-4">
                    <label class="form-label small text-muted">Recherche</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="N° inventaire, désignation, N° série..."
                               value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                </div>

                <!-- Catégorie -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Catégorie</label>
                    <select name="category_id" class="form-select">
                        <option value="">Toutes</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= (($filters['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Statut -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Statut</label>
                    <select name="status_id" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                <?= (($filters['status_id'] ?? '') == $st['id']) ? 'selected' : '' ?>>
                                <?= e($st['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Service -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Service</label>
                    <select name="service_id" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($services as $sv): ?>
                            <option value="<?= $sv['id'] ?>"
                                <?= (($filters['service_id'] ?? '') == $sv['id']) ? 'selected' : '' ?>>
                                <?= e($sv['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bouton toggle filtres avancés -->
                <div class="col-md-2 d-flex gap-1">
                    <button type="button" class="btn btn-outline-primary flex-fill"
                            data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="bi bi-sliders"></i> Avancés
                    </button>
                </div>
            </div>

            <!-- Ligne 2 : Filtres avancés (cachés par défaut) -->
            <div class="collapse <?= !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['value_min']) || !empty($filters['value_max']) || !empty($filters['under_warranty']) || !empty($filters['without_serial']) ? 'show' : '' ?>"
                 id="advancedFilters">
                <div class="border-top pt-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <!-- Date acquisition du -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">
                                <i class="bi bi-calendar-event"></i> Acquisition du
                            </label>
                            <input type="date" name="date_from" class="form-control"
                                   value="<?= e($filters['date_from'] ?? '') ?>">
                        </div>

                        <!-- Date acquisition au -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">
                                <i class="bi bi-calendar-event"></i> au
                            </label>
                            <input type="date" name="date_to" class="form-control"
                                   value="<?= e($filters['date_to'] ?? '') ?>">
                        </div>

                        <!-- Valeur min -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">
                                <i class="bi bi-cash-coin"></i> Valeur min (DA)
                            </label>
                            <input type="number" name="value_min" class="form-control"
                                   min="0" step="1000" placeholder="0"
                                   value="<?= e($filters['value_min'] ?? '') ?>">
                        </div>

                        <!-- Valeur max -->
                        <div class="col-md-2">
                            <label class="form-label small text-muted">
                                <i class="bi bi-cash-coin"></i> Valeur max (DA)
                            </label>
                            <input type="number" name="value_max" class="form-control"
                                   min="0" step="1000" placeholder="99999999"
                                   value="<?= e($filters['value_max'] ?? '') ?>">
                        </div>

                        <!-- Cases à cocher -->
                        <div class="col-md-4">
                            <label class="form-label small text-muted d-block">Options</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="under_warranty" id="under_warranty" value="1"
                                           <?= !empty($filters['under_warranty']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="under_warranty">
                                        <i class="bi bi-shield-check text-success"></i> Sous garantie
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="without_serial" id="without_serial" value="1"
                                           <?= !empty($filters['without_serial']) ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="without_serial">
                                        <i class="bi bi-exclamation-circle text-warning"></i> Sans N° série
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ligne 3 : Boutons d'action -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filtrer
                </button>
                <a href="<?= url('equipment') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                </a>
            </div>

        </form>
    </div>
</div>