<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    // Eliminar productos cuyo nombre contenga caracteres no imprimibles o patrones de zip/xml
    // (Que es lo que pasa cuando subes un XLSX como si fuera CSV)
    echo "Limpiando registros basura del inventario...\n";

    $count = $db->execute("DELETE FROM products WHERE name LIKE '%xml%' OR name LIKE '%_rels%' OR length(name) > 200");

    echo "¡Hecho! Se eliminaron {$count} registros de prueba/basura.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
