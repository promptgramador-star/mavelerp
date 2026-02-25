<?php
/**
 * Script de migración para añadir soporte multi-moneda y descuentos por porcentaje.
 * Acceder desde el navegador: http://tu-dominio.com/migrate_currency.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    // 1. Ampliar el campo currency de la tabla settings a VARCHAR(50)
    echo "Ampliando campo currency de settings a VARCHAR(50)...<br>";
    $db->execute("ALTER TABLE settings MODIFY COLUMN currency VARCHAR(50) DEFAULT 'DOP'");

    // 2. Añadir la moneda de la transacción en la tabla documents
    echo "Agregando columna currency a documents...<br>";
    $db->execute("ALTER TABLE documents ADD COLUMN currency VARCHAR(10) DEFAULT 'DOP' AFTER sequence_code");

    // 3. Añadir columna de discount_percentage en document_items
    echo "Agregando columna discount_percentage a document_items...<br>";
    $db->execute("ALTER TABLE document_items ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER unit_price");

    echo "<h2>¡Cambios aplicados con éxito!</h2>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";

} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "<p style='color:orange'>Aviso: Algunas columnas ya existían. Continuando...</p>";
    } else {
        echo "<h2 style='color:red'>Error</h2>";
        echo "<pre>" . $e->getMessage() . "</pre>";
    }
}
