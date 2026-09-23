<?php
declare(strict_types=1);

// ============================================
// DÉFINIR LE CHEMIN DE BASE DE L'APPLICATION
// ============================================
// Ce chemin est utilisé partout dans l'application
// pour construire les URLs (redirections, liens, assets...)
define('BASE_PATH', '/parc-informatique/public');

// ============================================
// SESSION SÉCURISÉE
// ============================================
session_name('PARC_SESSION');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ============================================
// AUTOLOAD COMPOSER
// ============================================
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    die("Composer n'est pas installé. Lancez 'composer install' à la racine du projet.");
}
require $composerAutoload;

// ============================================
// CHARGEMENT DU .ENV
// ============================================
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// ============================================
// CONFIGURATION GLOBALE
// ============================================
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Africa/Algiers');
mb_internal_encoding('UTF-8');

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-errors.log');
error_reporting(E_ALL);

// ============================================
// CONFIGURATION DE LA BASE DE DONNÉES
// ============================================
\App\Core\Database::configure(require dirname(__DIR__) . '/config/database.php');

// ============================================
// ROUTAGE
// ============================================
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;

try {
    $router = new Router();
    require dirname(__DIR__) . '/routes/web.php';
    require dirname(__DIR__) . '/routes/api.php';

    $request = Request::capture();
    $response = $router->dispatch($request);
    $response->send();

} catch (\Throwable $e) {
    http_response_code(500);

    if ($debug) {
        echo '<pre style="background:#1e1e1e;color:#f88;padding:20px;font-family:monospace;">';
        echo '<h2>Erreur attrapée :</h2>';
        echo htmlspecialchars($e->getMessage()) . "\n\n";
        echo '<strong>Fichier :</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n\n";
        echo '<strong>Trace :</strong>' . "\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        require dirname(__DIR__) . '/resources/views/errors/500.php';
    }

    $logDir = dirname(__DIR__) . '/storage/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        file_put_contents(
            $logDir . '/app-' . date('Y-m-d') . '.log',
            '[' . date('Y-m-d H:i:s') . '] ERROR: ' . $e->getMessage()
            . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL,
            FILE_APPEND
        );
    }
}