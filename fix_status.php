<?php
/**
 * Script de reparación de facturas con estado vacío.
 * Acceder: https://mavelerp.e-tecsystem.com/fix_status.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', __DIR__);

// 1. Cargar helpers (contiene config())
if (file_exists(BASE_PATH . '/core/helpers.php')) {
    require_once BASE_PATH . '/core/helpers.php';
}

// 2. Cargar Database
if (file_exists(BASE_PATH . '/core/Database.php')) {
    require_once BASE_PATH . '/core/Database.php';
}

try {
    if (!class_exists('\Core\Database')) {
        throw new Exception("La clase \Core\Database no se encontró.");
    }

    $db = \Core\Database::getInstance();

    echo "<h2>Reparación de Facturas</h2>";

    // Convertir estados nulos o vacíos a DRAFT
    $count = $db->execute("UPDATE documents SET status = 'DRAFT' WHERE document_type = 'FAC' AND (status = '' OR status IS NULL OR status = 'SENT')");

    echo "<p style='color:green'>✅ Se han reparado <b>$count</b> facturas enviándolas de vuelta a estado 'DRAFT'.</p>";
    echo "<p>Ahora puedes volver a la factura y deberías ver los botones correctamente.</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr><p><a href='index.php'>Volver al inicio</a></p>";
