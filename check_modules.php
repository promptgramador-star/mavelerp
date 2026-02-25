<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

use Core\Database;

$db = Database::getInstance();
$modules = $db->fetchAll("SELECT m.name, ml.is_enabled FROM modules m LEFT JOIN module_license ml ON m.id = ml.module_id");

echo "Módulos en DB:\n";
foreach ($modules as $m) {
    echo "- " . $m['name'] . ": " . ($m['is_enabled'] ? 'HABILITADO' : 'DESHABILITADO') . "\n";
}

$activeModules = array_filter(glob(BASE_PATH . '/modules/*'), 'is_dir');
echo "\nCarpetas en /modules:\n";
foreach ($activeModules as $dir) {
    echo "- " . basename($dir) . "\n";
    $manifest = $dir . '/module.json';
    if (file_exists($manifest)) {
        $json = json_decode(file_get_contents($manifest), true);
        echo "  Manifest Name: " . ($json['name'] ?? 'N/A') . "\n";
    } else {
        echo "  Manifest NO ENCONTRADO\n";
    }
}
