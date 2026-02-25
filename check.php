<?php
/**
 * Script de Diagnóstico de Emergencia
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/App.php';

echo "<h1>Diagnóstico de ERP</h1>";

// 1. Verificar PHP
echo "PHP Version: " . PHP_VERSION . "<br>";

// 2. Verificar Base de Datos
try {
    $db = \Core\Database::getInstance();
    echo "✅ Conexión a DB: OK<br>";
    $users = $db->fetch("SELECT COUNT(*) as total FROM users");
    echo "Usuarios en DB: " . $users['total'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error de DB: " . $e->getMessage() . "<br>";
}

// 3. Verificar Módulos
$modulesDir = BASE_PATH . '/modules';
echo "<h2>Módulos Instalados</h2>";
if (is_dir($modulesDir)) {
    foreach (scandir($modulesDir) as $item) {
        if ($item == '.' || $item == '..')
            continue;
        echo "- $item ";
        if (file_exists("$modulesDir/$item/module.json")) {
            echo "✅ (Manifest OK)";
        } else {
            echo "❌ (No manifest)";
        }
        echo "<br>";
    }
}

// 4. Verificar Rutas
echo "<h2>Rutas Registradas</h2>";
$app = new \Core\App();
$refProperty = new ReflectionProperty($app, 'router');
$refProperty->setAccessible(true);
$router = $refProperty->getValue($app);

$refRoutes = new ReflectionProperty($router, 'routes');
$refRoutes->setAccessible(true);
$routes = $refRoutes->getValue($router);

foreach ($routes as $r) {
    echo "Method: " . $r['method'] . " | Pattern: <strong>" . $r['raw_pattern'] . "</strong><br>";
}
echo "<br><a href='index.php'>Volver al ERP</a>";
