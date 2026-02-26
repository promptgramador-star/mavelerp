<?php
/**
 * Migración: Actualizar ENUM de status en documentos.
 * Acceder: https://mavelerp.e-tecsystem.com/migrate_status.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "<h2>Migración: Actualizar ENUM Status</h2>";

    // Cambiar la columna ENUM para asegurar que incluya SENT y APPROVED
    $db->execute("ALTER TABLE documents MODIFY COLUMN status ENUM('DRAFT', 'APPROVED', 'SENT', 'PAID', 'CANCELLED') DEFAULT 'DRAFT'");

    echo "<p style='color:green'>✅ Columna 'status' actualizada correctamente con los estados SENT y APPROVED.</p>";

    // Restaurar cualquier estado corrupto por el ENUM anterior
    $count = $db->execute("UPDATE documents SET status = 'DRAFT' WHERE status = '' OR status IS NULL");
    if ($count > 0) {
        echo "<p style='color:orange'>⚠️ Se han restaurado $count documentos con estado corrupto a 'DRAFT'.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
echo "<p><a href='index.php'>Volver al inicio</a></p>";
