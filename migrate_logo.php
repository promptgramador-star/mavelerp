<?php
/**
 * Migración: Agregar campo logo a settings.
 * Acceder: http://tu-dominio.com/migrate_logo.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "<h2>Migración: Campo Logo</h2>";
    $db->execute("ALTER TABLE settings ADD COLUMN logo VARCHAR(255) AFTER bank_accounts");
    echo "<p style='color:green'>✅ Campo 'logo' agregado a settings.</p>";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "<p style='color:orange'>⚠️ La columna 'logo' ya existe.</p>";
    } else {
        echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
echo "<p><a href='index.php'>Volver al inicio</a></p>";
