<?php
declare(strict_types=1);

return [
    'name'     => $_ENV['APP_NAME'] ?? 'Parc Informatique',
    'env'      => $_ENV['APP_ENV'] ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Africa/Algiers',
    'locale'   => $_ENV['APP_LOCALE'] ?? 'fr',

    'paths' => [
        'base'      => dirname(__DIR__),
        'app'       => dirname(__DIR__) . '/app',
        'config'    => dirname(__DIR__) . '/config',
        'database'  => dirname(__DIR__) . '/database',
        'public'    => dirname(__DIR__) . '/public',
        'resources' => dirname(__DIR__) . '/resources',
        'storage'   => dirname(__DIR__) . '/storage',
        'views'     => dirname(__DIR__) . '/resources/views',
        'uploads'   => dirname(__DIR__) . '/public/uploads',
        'documents' => dirname(__DIR__) . '/public/documents',
    ],

    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 1800),
        'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Strict',
    ],

    'uploads' => [
        'max_size'   => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760),
        'extensions' => explode(',', $_ENV['UPLOAD_ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,pdf'),
    ],
];