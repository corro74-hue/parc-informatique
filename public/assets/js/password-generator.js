/**
 * Générateur de mot de passe côté client
 * Utilisé pour les formulaires de création/édition d'utilisateur
 */
(function() {
    'use strict';

    const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz';
    const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const DIGITS    = '23456789';
    const SPECIAL   = '!@#$%^&*-_=+';

    /**
     * Génère un mot de passe fort
     */
    function generatePassword(length = 16) {
        const pool = LOWERCASE + UPPERCASE + DIGITS + SPECIAL;
        const guaranteed = [
            LOWERCASE[Math.floor(Math.random() * LOWERCASE.length)],
            UPPERCASE[Math.floor(Math.random() * UPPERCASE.length)],
            DIGITS[Math.floor(Math.random() * DIGITS.length)],
            SPECIAL[Math.floor(Math.random() * SPECIAL.length)],
        ];

        const password = [...guaranteed];
        for (let i = password.length; i < length; i++) {
            password.push(pool[Math.floor(Math.random() * pool.length)]);
        }

        // Mélange (Fisher-Yates)
        for (let i = password.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [password[i], password[j]] = [password[j], password[i]];
        }

        return password.join('');
    }

    /**
     * Initialise le générateur sur un input donné
     */
    function initGenerator(inputId, generateBtnId, copyBtnId, regenerateBtnId) {
        const input       = document.getElementById(inputId);
        const generateBtn = document.getElementById(generateBtnId);
        const copyBtn     = document.getElementById(copyBtnId);
        const regenBtn    = document.getElementById(regenerateBtnId);

        if (!input) return;

        // Afficher le mot de passe en clair (type="text") quand on génère
        function generate() {
            const pwd = generatePassword(16);
            input.value = pwd;
            input.type = 'text';  // Affiche en clair pour que l'admin puisse le noter
            input.classList.add('bg-light');
        }

        if (generateBtn) {
            generateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                generate();
            });
        }

        if (regenBtn) {
            regenBtn.addEventListener('click', function(e) {
                e.preventDefault();
                generate();
            });
        }

        if (copyBtn) {
            copyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (!input.value) {
                    alert('Générez un mot de passe d\'abord.');
                    return;
                }
                navigator.clipboard.writeText(input.value).then(() => {
                    const original = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copié !';
                    copyBtn.classList.remove('btn-outline-secondary');
                    copyBtn.classList.add('btn-success');
                    setTimeout(() => {
                        copyBtn.innerHTML = original;
                        copyBtn.classList.remove('btn-success');
                        copyBtn.classList.add('btn-outline-secondary');
                    }, 2000);
                });
            });
        }
    }

    // Expose la fonction globalement
    window.initPasswordGenerator = initGenerator;
})();