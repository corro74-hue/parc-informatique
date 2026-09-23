/**
 * Actions groupées (bulk) sur la liste des équipements.
 *
 * Dépend de :
 *   - window.APP_CONFIG.csrfToken / baseUrl (layout)
 *   - Checkboxes .bulk-checkbox avec data-id
 *   - Checkbox #bulk-check-all (tout sélectionner)
 *   - Barre flottante #bulk-actions-bar
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const checkAll       = document.getElementById('bulk-check-all');
        const checkboxes     = document.querySelectorAll('.bulk-checkbox');
        const bar            = document.getElementById('bulk-actions-bar');
        const countLabel     = document.getElementById('bulk-count');
        const btnDeselect    = document.getElementById('bulk-deselect');
        const btnDelete      = document.getElementById('bulk-delete');
        const btnExport      = document.getElementById('bulk-export');
        const btnApplyStatus = document.getElementById('bulk-apply-status');
        const statusSelect   = document.getElementById('bulk-status-select');

        if (!checkboxes.length || !bar) return;

        const cfg = window.APP_CONFIG || {};
        const csrfToken = cfg.csrfToken || '';
        const baseUrl   = (cfg.baseUrl || '').replace(/\/$/, '');

        // ============================================
        // Helpers
        // ============================================
        function getSelectedIds() {
            const ids = [];
            checkboxes.forEach(cb => {
                if (cb.checked) ids.push(parseInt(cb.dataset.id, 10));
            });
            return ids.filter(id => id > 0);
        }

        function updateBar() {
            const ids = getSelectedIds();
            const count = ids.length;

            if (count > 0) {
                bar.classList.add('show');
                countLabel.textContent = count + ' équipement' + (count > 1 ? 's' : '') + ' sélectionné' + (count > 1 ? 's' : '');
            } else {
                bar.classList.remove('show');
            }

            // Synchroniser la case "tout sélectionner"
            if (checkAll) {
                checkAll.checked = (count === checkboxes.length && count > 0);
                checkAll.indeterminate = (count > 0 && count < checkboxes.length);
            }
        }

        function clearSelection() {
            checkboxes.forEach(cb => cb.checked = false);
            if (checkAll) {
                checkAll.checked = false;
                checkAll.indeterminate = false;
            }
            updateBar();
        }

        function showError(msg) {
            alert('Erreur : ' + msg);
        }

        function showSuccess(msg) {
            // Bandeau temporaire en haut
            const flash = document.createElement('div');
            flash.className = 'alert alert-success alert-dismissible fade show position-fixed';
            flash.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            flash.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + msg
                + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.body.appendChild(flash);
            setTimeout(() => flash.remove(), 3000);
        }

        function postAction(url, body) {
            const params = new URLSearchParams();
            params.append('_token', csrfToken);
            Object.keys(body).forEach(k => {
                if (Array.isArray(body[k])) {
                    body[k].forEach(v => params.append(k + '[]', v));
                } else {
                    params.append(k, body[k]);
                }
            });

            return fetch(baseUrl + url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params.toString()
            })
            .then(r => r.text().then(text => {
                try {
                    const data = JSON.parse(text);
                    if (!r.ok || !data.success) throw new Error(data.message || 'Erreur serveur.');
                    return data;
                } catch (e) {
                    console.error('[Bulk] Réponse non-JSON :', text);
                    throw new Error('Réponse invalide du serveur.');
                }
            }));
        }

        // ============================================
        // Événements
        // ============================================

        // Clic sur une checkbox individuelle
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBar);
        });

        // Clic sur "tout sélectionner"
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBar();
            });
        }

        // Bouton "Désélectionner"
        if (btnDeselect) {
            btnDeselect.addEventListener('click', clearSelection);
        }

        // Bouton "Appliquer le statut"
        if (btnApplyStatus && statusSelect) {
            btnApplyStatus.addEventListener('click', function () {
                const ids = getSelectedIds();
                if (!ids.length) return;

                const statusId = statusSelect.value;
                if (!statusId) {
                    showError('Veuillez choisir un statut.');
                    return;
                }

                const statusName = statusSelect.options[statusSelect.selectedIndex].text;
                if (!confirm('Changer le statut de ' + ids.length + ' équipement(s) en "' + statusName + '" ?')) {
                    return;
                }

                btnApplyStatus.disabled = true;

                postAction('/equipment/bulk/status', { ids: ids, status_id: statusId })
                    .then(data => {
                        showSuccess(data.message);
                        clearSelection();
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch(err => {
                        showError(err.message);
                    })
                    .finally(() => {
                        btnApplyStatus.disabled = false;
                    });
            });
        }

        // Bouton "Mettre à la corbeille"
        if (btnDelete) {
            btnDelete.addEventListener('click', function () {
                const ids = getSelectedIds();
                if (!ids.length) return;

                if (!confirm('⚠️ Placer ' + ids.length + ' équipement(s) dans la corbeille ?\n\nIls pourront être restaurés.')) {
                    return;
                }

                btnDelete.disabled = true;

                postAction('/equipment/bulk/delete', { ids: ids })
                    .then(data => {
                        showSuccess(data.message);
                        clearSelection();
                        setTimeout(() => window.location.reload(), 800);
                    })
                    .catch(err => {
                        showError(err.message);
                    })
                    .finally(() => {
                        btnDelete.disabled = false;
                    });
            });
        }

        // Bouton "Exporter la sélection"
        if (btnExport) {
            btnExport.addEventListener('click', function () {
                const ids = getSelectedIds();
                if (!ids.length) return;

                // Construire l'URL avec les IDs
                const params = new URLSearchParams();
                ids.forEach(id => params.append('ids[]', id));

                window.location.href = baseUrl + '/equipment/bulk/export?' + params.toString();
            });
        }

        // Initialiser
        updateBar();
    });
})();