<?php
/**
 * Script unificado de Instalación y Migración para Compras
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/Database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = \Core\Database::getInstance();
    echo "--- INICIANDO CONFIGURACIÓN DE COMPRAS ---\n\n";

    // 1. Registro del Módulo
    echo "1. Registrando módulo en la base de datos...\n";
    $db->execute("INSERT IGNORE INTO modules (name, display_name, description, version, is_core) 
                  VALUES ('Compras', 'Gestión de Compras', 'Módulo de órdenes de compra y gestión de proveedores.', '1.0.0', 0)");

    $mod = $db->fetch("SELECT id FROM modules WHERE name = 'Compras'");
    if ($mod) {
        $db->execute("INSERT IGNORE INTO module_license (module_id, is_enabled, activated_at) 
                      VALUES (:id, 1, NOW())", ['id' => $mod['id']]);
        $db->execute("UPDATE module_license SET is_enabled = 1 WHERE module_id = :id", ['id' => $mod['id']]);
        echo "✅ Módulo 'Compras' habilitado correctamente.\n";
    }

    // 2. Migraciones de Base de Datos
    echo "\n2. Aplicando cambios estructurales...\n";

    // Inventory Intelligence
    try {
        $db->execute("ALTER TABLE products ADD COLUMN is_own_stock BOOLEAN DEFAULT TRUE AFTER is_service");
        echo "✅ Columna 'is_own_stock' añadida a productos.\n";
    } catch (Exception $e) {
        echo "ℹ️ is_own_stock ya existe.\n";
    }

    try {
        $db->execute("ALTER TABLE products ADD COLUMN low_stock_threshold DECIMAL(15,2) DEFAULT 5.00 AFTER is_own_stock");
        echo "✅ Columna 'low_stock_threshold' añadida a productos.\n";
    } catch (Exception $e) {
        echo "ℹ️ low_stock_threshold ya existe.\n";
    }

    // document_items - discount_percentage (checking if it exists)
    try {
        $db->execute("ALTER TABLE document_items ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0.00 AFTER unit_price");
        echo "✅ Columna 'discount_percentage' añadida a items.\n";
    } catch (Exception $e) {
        echo "ℹ️ discount_percentage ya existe.\n";
    }

    // customer_id nullable
    $db->execute("ALTER TABLE documents MODIFY COLUMN customer_id INT NULL");

    // supplier_id
    try {
        $db->execute("ALTER TABLE documents ADD COLUMN supplier_id INT NULL AFTER customer_id");
        echo "✅ Columna 'supplier_id' añadida.\n";
    } catch (Exception $e) {
        echo "ℹ️ supplier_id ya existe.\n";
    }

    // Constraints
    try {
        $db->execute("ALTER TABLE documents ADD CONSTRAINT fk_doc_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)");
        echo "✅ Relación con proveedores establecida.\n";
    } catch (Exception $e) {
        echo "ℹ️ Constraint ya existe.\n";
    }

    // Enum ORD
    try {
        $db->execute("ALTER TABLE documents MODIFY COLUMN document_type ENUM('COT','FAC','COND','ORD')");
        $db->execute("ALTER TABLE document_sequences MODIFY COLUMN document_type VARCHAR(20)");
        echo "✅ Tipos de documento preparados para 'ORD'.\n";
    } catch (Exception $e) {
        echo "❌ Error en ENUM: " . $e->getMessage() . "\n";
    }

    // Secuencia PO
    $db->execute("INSERT INTO document_sequences (document_type, prefix, year, current_number) 
                  VALUES ('ORD', 'PO', DATE_FORMAT(NOW(), '%y'), 0)
                  ON DUPLICATE KEY UPDATE prefix = 'PO'");
    echo "✅ Secuencia 'PO' inicializada.\n";

    echo "\n--- TODO LISTO ---\n";
    echo "Ya puedes entrar a la sección de compras.";

} catch (Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage();
}
