<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

\App\Core\Database::configure(require dirname(__DIR__) . '/config/database.php');
$pdo = \App\Core\Database::getInstance();

$password = 'Admin@2026';
$hash = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 1,
]);

$stmt = $pdo->prepare('UPDATE users SET password_hash = :hash, must_change_password = 1 WHERE username = :username');
$stmt->execute(['hash' => $hash, 'username' => 'admin']);

// Vérifier que la mise à jour a bien eu lieu
$stmt = $pdo->prepare('SELECT id, username, email, first_name, last_name FROM users WHERE username = :username');
$stmt->execute(['username' => 'admin']);
$user = $stmt->fetch();

echo "========================================\n";
echo "  MOT DE PASSE ADMIN RÉGÉNÉRÉ\n";
echo "========================================\n";
echo "Login    : admin\n";
echo "Password : $password\n";
echo "Hash     : $hash\n";
echo "----------------------------------------\n";
echo "Utilisateur en base :\n";
echo "  ID    : {$user['id']}\n";
echo "  Login : {$user['username']}\n";
echo "  Email : {$user['email']}\n";
echo "  Nom   : {$user['first_name']} {$user['last_name']}\n";
echo "========================================\n";