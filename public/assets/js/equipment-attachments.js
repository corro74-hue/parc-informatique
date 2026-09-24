/**
 * Drag & drop pour l'upload de pièces jointes.
 *
 * Dépend de :
 *   - #attachment-dropzone (zone de drop)
 *   - #attachment-file-input (input file caché)
 *   - #attachment-form (formulaire)
 *   - #attachment-filename (affichage du nom sélectionné)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const dropzone  = document.getElementById('attachment-dropzone');
        const fileInput = document.getElementById('attachment-file-input');
        const filename  = document.getElementById('attachment-filename');

        if (!dropzone || !fileInput) return;

        // Clic sur la dropzone → ouvre le sélecteur de fichier
        dropzone.addEventListener('click', function (e) {
            if (e.target.tagName !== 'INPUT') {
                fileInput.click();
            }
        });

        // Fichier sélectionné via le clic
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                showFileName(this.files[0].name);
            }
        });

        // ----- Drag & drop -----
        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showFileName(files[0].name);
            }
        });

        // ----- Helper -----
        function showFileName(name) {
            if (filename) {
                filename.textContent = '📎 ' + name;
                filename.classList.remove('d-none');
            }
        }
    });
})();