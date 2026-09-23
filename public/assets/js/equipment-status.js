/**
 * Gestion du changement rapide de statut depuis la liste des équipements.
 *
 * Dépend de :
 *   - window.APP_CONFIG.csrfToken  (injecté par le layout)
 *   - window.APP_CONFIG.baseUrl    (injecté par le layout)
 *   - Éléments HTML : <select class="status-select" data-id="..." data-original="...">
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const selects = document.querySelectorAll('.status-select');
        if (selects.length === 0) {
            return;
        }

        const cfg = window.APP_CONFIG || {};
        const csrfToken = cfg.csrfToken || '';
        const baseUrl   = (cfg.baseUrl || '').replace(/\/$/, '');

        selects.forEach(function (select) {
            select.addEventListener('change', function () {
                handleStatusChange(this, csrfToken, baseUrl);
            });
        });
    });

    /**
     * Envoie la requête AJAX de mise à jour du statut.
     *
     * @param {HTMLSelectElement} el
     * @param {string} csrfToken
     * @param {string} baseUrl
     */
    function handleStatusChange(el, csrfToken, baseUrl) {
        const id       = el.dataset.id;
        const statusId = el.value;
        const original = el.dataset.original;

        el.disabled = true;

        const body = new URLSearchParams();
        body.append('_token', csrfToken);
        body.append('status_id', statusId);

        fetch(baseUrl + '/equipment/' + id + '/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body.toString()
        })
        .then(function (response) {
            return response.text().then(function (text) {
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Réponse non-JSON du serveur :', text);
                    throw new Error('Le serveur a renvoyé une réponse invalide (code ' + response.status + ').');
                }
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Erreur serveur (code ' + response.status + ').');
                }
                return data;
            });
        })
        .then(function (data) {
            el.style.borderLeftColor = data.status_color || '#6c757d';
            el.dataset.original = statusId;

            el.classList.add('border-success');
            setTimeout(function () {
                el.classList.remove('border-success');
            }, 1000);
        })
        .catch(function (err) {
            console.error('[EquipmentStatus]', err);
            alert('Erreur : ' + err.message);
            el.value = original;
        })
        .finally(function () {
            el.disabled = false;
        });
    }
})();