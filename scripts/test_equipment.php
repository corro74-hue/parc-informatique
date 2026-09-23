<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

\App\Core\Database::configure(require dirname(__DIR__) . '/config/database.php');

use App\Repositories\MySql\EquipmentRepository;

$repo = new EquipmentRepository();

echo "=== Test du Repository Equipment ===\n\n";

// 1. Test : nombre total d'équipements
$result = $repo->paginate([], 1, 15);
echo "1. Total équipements : " . $result['total'] . "\n";

// 2. Test : génération du prochain numéro
$next = $repo->generateNextInventoryNumber();
echo "2. Prochain numéro d'inventaire : " . $next . "\n";

// 3. Test : statistiques par statut
$stats = $repo->countByStatus();
echo "3. Équipements par statut :\n";
foreach ($stats as $code => $count) {
    echo "   - $code : $count\n";
}

// 4. Test : valeur totale
$value = $repo->getTotalValue();
echo "4. Valeur totale du parc : " . number_format($value, 2, ',', ' ') . " DA\n";

// 5. Test : recherche
$result = $repo->paginate(['search' => 'HP'], 1, 5);
echo "5. Recherche 'HP' : " . $result['total'] . " résultat(s)\n";

echo "\n✅ Tous les tests sont passés !\n";