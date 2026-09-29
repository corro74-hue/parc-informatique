/**
 * Autocomplete pour la recherche d'équipements dans le formulaire d'affectation.
 */
(function () {
    'use strict';

    const input     = document.getElementById('equipment_search');
    const hidden    = document.getElementById('equipment_id');
    const container = document.getElementById('equipment_suggestions');

    if (!input || !hidden || !container) return;

    const API_URL    = (window.APP_CONFIG?.baseUrl || '') + '/api/equipment/search';
    const MIN_LENGTH = 2;
    const DEBOUNCE   = 250;

    let debounceTimer  = null;
    let currentRequest = null;

    // --------------------------------------------
    // Rendu du menu de suggestions
    // --------------------------------------------
    function renderResults(results) {
        container.innerHTML = '';

        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="list-group-item text-muted text-center small py-3">
                    <i class="bi bi-search"></i> Aucun équipement trouvé
                </div>`;
            container.style.display = 'block';
            return;
        }

        results.forEach(function (eq) {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action';
            item.style.cursor = 'pointer';
            item.dataset.id = eq.id;

            const statusBadge = eq.status_name
                ? `<span class="badge ms-2" style="background-color: ${eq.status_color}; font-size: 0.65rem;">${eq.status_name}</span>`
                : '';

            const availability = eq.is_available
                ? '<i class="bi bi-check-circle-fill text-success" title="Disponible"></i>'
                : '<i class="bi bi-exclamation-triangle-fill text-warning" title="Non disponible"></i>';

            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">
                            ${availability}
                            ${escapeHtml(eq.inventory_number)}
                            ${statusBadge}
                        </div>
                        <div class="text-muted small">${escapeHtml(eq.designation)}</div>
                        ${eq.serial_number ? `<div class="text-muted" style="font-size: 0.7rem;">S/N : ${escapeHtml(eq.serial_number)}</div>` : ''}
                    </div>
                    ${eq.brand_name ? `<small class="text-muted">${escapeHtml(eq.brand_name)}</small>` : ''}
                </div>
            `;

            // ⚠️ IMPORTANT : mousedown au lieu de click
            // mousedown se déclenche AVANT le blur/click extérieur
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();      // Empêche la perte de focus
                e.stopPropagation();     // Empêche la propagation
                selectEquipment(eq);
            });

            container.appendChild(item);
        });

        container.style.display = 'block';
    }

    // --------------------------------------------
    // Sélection d'un équipement
    // --------------------------------------------
    function selectEquipment(eq) {
        hidden.value = eq.id;
        input.value  = eq.inventory_number + ' — ' + eq.designation;
        container.innerHTML = '';
        container.style.display = 'none';
        input.focus();
    }

    // --------------------------------------------
    // Échappement HTML
    // --------------------------------------------
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // --------------------------------------------
    // Requête AJAX
    // --------------------------------------------
    function fetchResults(term) {
        if (currentRequest) {
            currentRequest.abort();
        }

        const controller = new AbortController();
        currentRequest = controller;

        const url = API_URL + '?q=' + encodeURIComponent(term);

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            signal: controller.signal,
        })
        .then(function (response) {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                renderResults(data.results);
            }
        })
        .catch(function (err) {
            if (err.name !== 'AbortError') {
                console.error('[Autocomplete]', err);
            }
        })
        .finally(function () {
            currentRequest = null;
        });
    }

    // --------------------------------------------
    // Écoute de la saisie
    // --------------------------------------------
    input.addEventListener('input', function () {
        const term = input.value.trim();

        clearTimeout(debounceTimer);

        // Réinitialiser la sélection si l'utilisateur modifie le texte
        if (hidden.value && !input.value.includes('—')) {
            hidden.value = '';
        }

        if (term.length < MIN_LENGTH) {
            container.innerHTML = '';
            container.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetchResults(term);
        }, DEBOUNCE);
    });

    // --------------------------------------------
    // Masquer au clic en dehors
    // --------------------------------------------
    document.addEventListener('click', function (e) {
        if (!container.contains(e.target) && e.target !== input) {
            container.style.display = 'none';
        }
    });

    // --------------------------------------------
    // Navigation clavier
    // --------------------------------------------
    input.addEventListener('keydown', function (e) {
        const items = container.querySelectorAll('.list-group-item-action');
        if (items.length === 0) return;

        const active = container.querySelector('.list-group-item-action.active');
        let index = Array.from(items).indexOf(active);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            index = (index + 1) % items.length;
            items.forEach(i => i.classList.remove('active'));
            items[index].classList.add('active');
            items[index].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            index = index <= 0 ? items.length - 1 : index - 1;
            items.forEach(i => i.classList.remove('active'));
            items[index].classList.add('active');
            items[index].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && active) {
            e.preventDefault();
            // Récupérer l'équipement depuis les data (on doit le retrouver)
            const eqId = active.dataset.id;
            // On déclenche le mousedown programmatiquement
            active.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        } else if (e.key === 'Escape') {
            container.style.display = 'none';
        }
    });

})();