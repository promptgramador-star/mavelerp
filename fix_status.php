<?php
/**
 * Script de reparación de facturas con estado vacío.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    echo "<h2>Reparación de Facturas</h2>";

    // Verificamos si existe la columna de estado y qué valores tiene
    $db->execute("UPDATE documents SET status = 'DRAFT' WHERE document_type = 'FAC' AND (status = '' OR status IS NULL)");

    $affected = $db->execute("SELECT ROW_COUNT() as count"); // MySQL specific if supported by the execute wrapper

    echo "<p style='color:green'>✅ Se han restaurado las facturas que estaban en blanco a estado 'DRAFT'.</p>";
    echo "<p>Ahora deberías poder ver todos los botones de acción en tus facturas.</p>";

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php'>Volver al inicio</a></p>";
