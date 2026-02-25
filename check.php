<?php
/**
 * Diagnóstico Extremo v2
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', __DIR__);

echo "<h1>DEPURACIÓN ERP</h1>";
echo "PHP: " . PHP_VERSION . "<br>";
echo "Ruta Base: " . BASE_PATH . "<br>";

// 1. Verificar archivos críticos
$files = [
    '/config/app.php',
    '/config/database.php',
    '/core/helpers.php',
    '/core/Database.php',
    '/core/App.php'
];

echo "<h2>Verificando Archivos:</h2>";
foreach ($files as $f) {
    $exists = file_exists(BASE_PATH . $f);
    echo ($exists ? "✅" : "❌") . " $f<br>";
}

// 2. Intentar cargar helpers
require_once BASE_PATH . '/core/helpers.php';
echo "✅ Helpers cargados<br>";

// 3. Verificar Configuración de Base de Datos (SIN mostrar password)
$dbConfig = require BASE_PATH . '/config/database.php';
echo "<h2>Configuración DB:</h2>";
echo "Host: " . $dbConfig['host'] . "<br>";
echo "DB: " . $dbConfig['database'] . "<br>";
echo "User: " . $dbConfig['username'] . "<br>";

// 4. Intento de Conexión Manual (para ver errores de PDO)
echo "<h2>Iniciando Autoload y App:</h2>";
try {
    require_once BASE_PATH . '/core/App.php';
    $app = new \Core\App();
    echo "✅ App instanciada correctamente<br>";
} catch (Throwable $e) {
    echo "❌ ERROR EN APP: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine() . "<br>";
}

echo "<h2>Rutas registradas:</h2>";
try {
    $ref = new ReflectionProperty($app, 'router');
    $ref->setAccessible(true);
    $router = $ref->getValue($app);

    $ref2 = new ReflectionProperty($router, 'routes');
    $ref2->setAccessible(true);
    $routes = $ref2->getValue($router);

    foreach ($routes as $r) {
        echo "[{$r['method']}] " . $r['raw_pattern'] . " -> " . $r['action'] . "<br>";
    }
} catch (Throwable $e) {
    echo "No se pudieron listar las rutas: " . $e->getMessage();
}
