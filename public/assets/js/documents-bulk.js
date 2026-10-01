/**
 * Actions groupées pour le module Documents
 * Gère : sélection multiple, compteur, barre flottante, inputs cachés
 */
(function () {
    'use strict';

    const selectAll  = document.getElementById('select-all-docs');
    const checkboxes = document.querySelectorAll('.doc-checkbox');
    const bar        = document.getElementById('doc-bulk-bar');
    const countEl    = document.getElementById('doc-selected-count');
    const clearBtn   = document.getElementById('doc-bulk-clear');
    const dlIdsBox   = document.getElementById('bulk-download-ids');
    const delIdsBox  = document.getElementById('bulk-delete-ids');

    // S'il n'y a pas de checkboxes, on ne fait rien
    if (!checkboxes.length) return;

    /**
     * Récupère les IDs cochés
     */
    function getSelected() {
        const ids = [];
        checkboxes.forEach(function (cb) {
            if (cb.checked) ids.push(cb.value);
        });
        return ids;
    }

    /**
     * Remplit un conteneur avec des inputs cachés `ids[]`
     */
    function fillIds(container, ids) {
        if (!container) return;
        container.innerHTML = '';
        ids.forEach(function (id) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = id;
            container.appendChild(inp);
        });
    }

    /**
     * Met à jour l'UI (compteur, barre, inputs cachés, état du select-all)
     */
    function updateUI() {
        const ids   = getSelected();
        const count = ids.length;

        // Compteur
        if (countEl) countEl.textContent = count;

        // Barre flottante
        if (bar) {
            if (count > 0) bar.classList.add('show');
            else bar.classList.remove('show');
        }

        // Inputs cachés pour les 2 formulaires
        fillIds(dlIdsBox, ids);
        fillIds(delIdsBox, ids);

        // État de la case "tout sélectionner"
        if (selectAll) {
            if (count === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (count === checkboxes.length) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }
    }

    // Toggle "tout sélectionner"
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
            updateUI();
        });
    }

    // Toggle individuel
    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', updateUI);
    });

    // Bouton "Désélectionner"
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            checkboxes.forEach(function (cb) { cb.checked = false; });
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            updateUI();
        });
    }

    // État initial
    updateUI();
})();