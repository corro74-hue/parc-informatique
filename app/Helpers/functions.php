<?php
declare(strict_types=1);

// ============================================
// ENVIRONNEMENT
// ============================================
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

// ============================================
// CONFIGURATION
// ============================================
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($configs[$file])) {
            $filePath = dirname(__DIR__, 2) . "/config/$file.php";
            $configs[$file] = is_file($filePath) ? require $filePath : [];
        }

        if ($path === null) {
            return $configs[$file];
        }

        $value = $configs[$file];
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

// ============================================
// CHEMINS
// ============================================
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/\\') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? '/' . ltrim($path, '/\\') : ''));
    }
}

// ============================================
// URLs
// ============================================
if (!function_exists('url')) {
    /**
     * Génère une URL complète pour l'application.
     *
     * - Si APP_URL est défini et commence par http:// ou https://,
     *   on utilise cette URL comme base (recommandé en production).
     * - Sinon, on utilise la constante BASE_PATH définie dans public/index.php.
     *
     * @param string $path  Chemin relatif (ex: 'login', 'dashboard', 'equipment/42')
     * @return string       URL absolue complète
     */
    function url(string $path = ''): string
    {
        // Priorité 1 : APP_URL complet depuis le .env
        $appUrl = (string) env('APP_URL', '');
        if ($appUrl !== '' && preg_match('#^https?://#', $appUrl)) {
            $base = rtrim($appUrl, '/');
            return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
        }

        // Priorité 2 : BASE_PATH défini dans public/index.php
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

// ============================================
// ÉCHAPPEMENT (protection XSS)
// ============================================
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// ============================================
// ANCIENNES VALEURS DE FORMULAIRE
// ============================================
if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

// ============================================
// CSRF (protection contre les attaques Cross-Site Request Forgery)
// ============================================
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(?string $token): bool
    {
        return !empty($token)
            && !empty($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

// ============================================
// MESSAGES FLASH (affichés une seule fois)
// ============================================
if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

// ============================================
// RACCOURCI DE REDIRECTION
// ============================================
if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): \App\Core\Response
    {
        return \App\Core\Response::redirect($url, $status);
    }
}

// ============================================
// DEBUG (dump & die)
// ============================================
if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#1e1e1e;color:#eee;padding:15px;font-family:monospace;border-radius:6px;">';
        foreach ($vars as $v) {
            var_dump($v);
            echo "\n" . str_repeat('-', 80) . "\n";
        }
        echo '</pre>';
        exit(1);
    }
}