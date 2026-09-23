<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

\App\Core\Database::configure(require dirname(__DIR__) . '/config/database.php');
$pdo = \App\Core\Database::getInstance();

echo "=== Informations de connexion PDO ===\n\n";

// 1. Variables de charset MySQL
$vars = ['character_set_client', 'character_set_connection', 'character_set_results', 'character_set_database', 'character_set_server'];
foreach ($vars as $v) {
    $stmt = $pdo->query("SHOW VARIABLES LIKE '$v'");
    $row = $stmt->fetch();
    echo str_pad($v, 32) . " : " . ($row['Value'] ?? '?') . "\n";
}

echo "\n";

// 2. Tester une requête simple
$stmt = $pdo->query("SELECT 'Système' AS test1, 'Unité' AS test2, 'Écran' AS test3");
$row = $stmt->fetch();

echo "=== Test d'affichage direct ===\n\n";
echo "Système  : " . $row['test1'] . "\n";
echo "Unité    : " . $row['test2'] . "\n";
echo "Écran    : " . $row['test3'] . "\n";

echo "\n=== Vérification hexadécimale ===\n\n";
echo "Hex 'Système' (doit être 53797374 C3A8 6D65) : " . bin2hex('Système') . "\n";
echo "Hex reçu    : " . bin2hex($row['test1']) . "\n";

echo "\n";

// 3. Tester la lecture de l'utilisateur
$stmt = $pdo->query("SELECT first_name, last_name FROM users WHERE username = 'admin'");
$user = $stmt->fetch();

if ($user) {
    echo "=== Utilisateur admin ===\n\n";
    echo "Prénom : " . $user['first_name'] . "\n";
    echo "Nom    : " . $user['last_name'] . "\n";
    echo "Hex nom : " . bin2hex($user['last_name']) . "\n";
} else {
    echo "⚠️ Utilisateur 'admin' introuvable\n";
}