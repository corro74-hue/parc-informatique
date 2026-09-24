<?php
declare(strict_types=1);

namespace App\Middleware;

final class SecurityHeadersMiddleware
{
    /**
     * Envoie tous les headers de sécurité HTTP.
     * Ce middleware est appelé AVANT chaque réponse.
     */
    public function handle(): void
    {
        // Évite les erreurs si les headers sont déjà envoyés
        if (headers_sent()) {
            return;
        }

        // ============================================
        // 1. Protection contre le Clickjacking
        // ============================================
        // Empêche l'intégration de l'application dans une iframe externe.
        // 'SAMEORIGIN' = seul notre domaine peut l'intégrer (safe pour PDF/QR).
        header('X-Frame-Options: SAMEORIGIN');

        // ============================================
        // 2. Protection contre le MIME Sniffing
        // ============================================
        // Empêche le navigateur de deviner le type MIME d'un fichier
        // (protège contre les faux fichiers .jpg qui contiennent du JS).
        header('X-Content-Type-Options: nosniff');

        // ============================================
        // 3. Protection XSS (mode "block")
        // ============================================
        // Active le filtre anti-XSS du navigateur (legacy, mais utile pour vieux navigateurs).
        // Les navigateurs modernes utilisent CSP à la place.
        header('X-XSS-Protection: 1; mode=block');

        // ============================================
        // 4. Politique de référent (Referrer-Policy)
        // ============================================
        // Contrôle les informations envoyées dans le header Referer
        // quand on clique sur un lien sortant.
        // 'strict-origin-when-cross-origin' = URL complète en interne, seulement l'origine en externe.
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // ============================================
        // 5. Politique de permissions (Permissions-Policy)
        // ============================================
        // Désactive les fonctionnalités du navigateur non utilisées
        // (géolocalisation, caméra, micro, etc.) pour éviter les abus.
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=()');

        // ============================================
        // 6. Content Security Policy (CSP)
        // ============================================
        // La protection la plus puissante contre XSS.
        // Définit les sources autorisées pour scripts, styles, images, etc.
        //
        // NOTE : On autorise 'unsafe-inline' pour les styles (car Bootstrap utilise
        // des styles inline) et 'unsafe-eval' pour Bootstrap (car il utilise eval()).
        // En production stricte, on pourrait durcir cette politique.
        //
        // CORRECTION : connect-src autorise https://cdn.jsdelivr.net pour les fetch AJAX
        // de Bootstrap (tooltips, popovers, etc.).
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "font-src 'self' https://cdn.jsdelivr.net data:; " .
               "img-src 'self' data: blob:; " .
               "connect-src 'self' https://cdn.jsdelivr.net; " .
               "frame-ancestors 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "object-src 'none'";
        header('Content-Security-Policy: ' . $csp);

        // ============================================
        // 7. Strict Transport Security (HSTS)
        // ============================================
        // Force les navigateurs à utiliser HTTPS pendant 1 an.
        // IMPORTANT : à activer UNIQUEMENT si l'application tourne en HTTPS.
        // En local (http://localhost), on le désactive pour éviter les problèmes.
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['SERVER_PORT'] ?? null) === '443')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        if ($isHttps) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        // ============================================
        // 8. Désactive la mise en cache des pages sensibles
        // ============================================
        // Empêche le navigateur de stocker les pages en cache (login, dashboard, etc.).
        // Un utilisateur qui se déconnecte ne pourra pas revenir en arrière.
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // ============================================
        // 9. Supprime le header "X-Powered-By"
        // ============================================
        // Cache la version de PHP pour ne pas donner d'indices aux attaquants.
        header_remove('X-Powered-By');
    }
}