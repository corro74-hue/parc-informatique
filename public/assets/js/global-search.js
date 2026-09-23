/**
 * Recherche globale dans la navbar.
 *
 * Dépend de :
 *   - window.APP_CONFIG.baseUrl    (injecté par le layout)
 *   - Un input avec l'id "global-search-input"
 *   - Un conteneur avec l'id "global-search-results"
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const input    = document.getElementById('global-search-input');
        const results  = document.getElementById('global-search-results');
        if (!input || !results) return;

        const cfg     = window.APP_CONFIG || {};
        const baseUrl = (cfg.baseUrl || '').replace(/\/$/, '');

        let debounceTimer = null;
        let lastQuery     = '';
        let currentController = null;

        // ----- Affichage / masquage -----
        function show() { results.classList.add('show'); }
        function hide() { results.classList.remove('show'); }

        function clear() {
            results.innerHTML = '';
            hide();
        }

        // ----- Recherche AJAX -----
        function performSearch(q) {
            // Annuler la requête précédente si en cours
            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();

            fetch(baseUrl + '/search?q=' + encodeURIComponent(q), {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: currentController.signal
            })
            .then(function (r) {
                return r.text().then(function (text) {
                    try { return JSON.parse(text); }
                    catch (e) {
                        console.error('[GlobalSearch] Réponse non-JSON :', text);
                        throw new Error('Réponse invalide du serveur.');
                    }
                });
            })
            .then(function (data) {
                if (!data.success) {
                    console.warn('[GlobalSearch]', data.message);
                    clear();
                    return;
                }
                renderResults(data.results || [], q);
            })
            .catch(function (err) {
                if (err.name === 'AbortError') return; // requête annulée, on ignore
                console.error('[GlobalSearch]', err);
                results.innerHTML = '<div class="gs-empty text-danger">Erreur de recherche.</div>';
                show();
            })
            .finally(function () {
                currentController = null;
            });
        }

        // ----- Rendu des résultats -----
        function renderResults(items, q) {
            if (items.length === 0) {
                results.innerHTML = '<div class="gs-empty">Aucun résultat pour « ' + escapeHtml(q) + ' »</div>';
                show();
                return;
            }

            let html = '<div class="gs-header">' + items.length + ' résultat' + (items.length > 1 ? 's' : '') + '</div>';
            html += '<div class="gs-list">';

            items.forEach(function (item) {
                const color = item.status_color || '#6c757d';
                const status = item.status_name || '—';

                html += '<a href="' + escapeHtml(item.url) + '" class="gs-item">';
                html +=   '<div class="gs-item-icon" style="background:' + escapeHtml(color) + '">';
                html +=     '<i class="bi bi-box-seam"></i>';
                html +=   '</div>';
                html +=   '<div class="gs-item-body">';
                html +=     '<div class="gs-item-title">' + escapeHtml(item.inventory_number) + '</div>';
                html +=     '<div class="gs-item-sub">' + escapeHtml(item.designation) + '</div>';
                html +=   '</div>';
                html +=   '<div class="gs-item-badge" style="background:' + escapeHtml(color) + ';">' + escapeHtml(status) + '</div>';
                html += '</a>';
            });

            html += '</div>';
            results.innerHTML = html;
            show();
        }

        // ----- Échappement HTML -----
        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ----- Écoute de la saisie (avec debounce 250ms) -----
        input.addEventListener('input', function () {
            const q = this.value.trim();

            if (debounceTimer) clearTimeout(debounceTimer);

            if (q.length < 2) {
                clear();
                lastQuery = '';
                return;
            }

            if (q === lastQuery) return;

            debounceTimer = setTimeout(function () {
                lastQuery = q;
                performSearch(q);
            }, 250);
        });

        // ----- Touche Échap → fermer -----
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                clear();
                input.blur();
            }
        });

        // ----- Clic en dehors → fermer -----
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                hide();
            }
        });

        // ----- Refocus → réafficher si on a des résultats -----
        input.addEventListener('focus', function () {
            if (results.innerHTML.trim() !== '') {
                show();
            }
        });
    });
})();