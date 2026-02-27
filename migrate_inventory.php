<?php
/**
 * Script de migración: Inventario Inteligente (Stock Propio y Alertas)
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    echo "Iniciando migración para Inventario Inteligente...\n";

    // 1. Agregar is_own_stock a products
    try {
        $db->execute("ALTER TABLE products ADD COLUMN is_own_stock BOOLEAN DEFAULT TRUE AFTER is_taxable");
        echo "✅ Columna 'is_own_stock' añadida exitosamente a 'products'.\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ La columna 'is_own_stock' ya existe.\n";
        } else {
            throw $e;
        }
    }

    // 2. Agregar low_stock_threshold a products
    try {
        $db->execute("ALTER TABLE products ADD COLUMN low_stock_threshold DECIMAL(15,2) DEFAULT 5.00 AFTER is_own_stock");
        echo "✅ Columna 'low_stock_threshold' añadida exitosamente a 'products'.\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ La columna 'low_stock_threshold' ya existe.\n";
        } else {
            throw $e;
        }
    }

    echo "\n🎉 ¡Migración completada con éxito!\n";
    echo "Puedes borrar este archivo de forma segura.\n";

} catch (\Exception $e) {
    echo "❌ Error durante la migración:\n";
    echo $e->getMessage() . "\n";
}
