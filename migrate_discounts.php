<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    echo "Aplicando cambios a la base de datos...\n";

    // 1. Agregar is_taxable a products
    echo "Agregando is_taxable a la tabla products...\n";
    $db->execute("ALTER TABLE products ADD COLUMN is_taxable BOOLEAN DEFAULT TRUE AFTER is_service");

    // 2. Agregar descuento a document_items
    echo "Agregando discount_amount e is_taxable a document_items...\n";
    $db->execute("ALTER TABLE document_items ADD COLUMN discount_amount DECIMAL(15,2) DEFAULT 0 AFTER unit_price");
    $db->execute("ALTER TABLE document_items ADD COLUMN is_taxable BOOLEAN DEFAULT TRUE AFTER discount_amount");

    // 3. Agregar discount_total a documents
    echo "Agregando discount_total a documents...<br>";
    $db->execute("ALTER TABLE documents ADD COLUMN discount_total DECIMAL(15,2) DEFAULT 0 AFTER subtotal");

    // 4. Agregar bank_accounts a settings
    echo "Agregando bank_accounts a settings...<br>";
    $db->execute("ALTER TABLE settings ADD COLUMN bank_accounts TEXT AFTER email");

    echo "<h2>¡Cambios aplicados con éxito!</h2>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";

} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "<p style='color:orange'>Aviso: Algunas columnas ya existían. Continuando...</p>";
        // Intentar la siguiente si esta falló por existir
    } else {
        echo "<h2 style='color:red'>Error</h2>";
        echo "<pre>" . $e->getMessage() . "</pre>";
    }
}
