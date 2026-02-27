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

    // 1. Migraciones de Base de Datos (PRIORIDAD para arreglar el Dashboard)
    echo "1. Aplicando cambios estructurales en tablas...\n";

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

    // document_items - discount_percentage
    try {
        $db->execute("ALTER TABLE document_items ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0.00 AFTER unit_price");
        echo "✅ Columna 'discount_percentage' añadida a items.\n";
    } catch (Exception $e) {
        echo "ℹ️ discount_percentage ya existe.\n";
    }

    // customer_id nullable
    $db->execute("ALTER TABLE documents MODIFY COLUMN customer_id INT NULL");
    echo "✅ Tabla documents actualizada (customer_id nullable).\n";

    // supplier_id
    try {
        $db->execute("ALTER TABLE documents ADD COLUMN supplier_id INT NULL AFTER customer_id");
        echo "✅ Columna 'supplier_id' añadida a documents.\n";
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
        echo "ℹ️ Nota: Si el ENUM falló es posible que ya esté aplicado.\n";
    }

    // Secuencia PO
    $db->execute("INSERT INTO document_sequences (document_type, prefix, year, current_number) 
                  VALUES ('ORD', 'PO', DATE_FORMAT(NOW(), '%y'), 0)
                  ON DUPLICATE KEY UPDATE prefix = 'PO'");
    echo "✅ Secuencia 'PO' inicializada.\n";

    // 2. Registro del Módulo (Ajustado a la estructura real de la tabla modules)
    echo "\n2. Registrando módulo Compras...\n";
    try {
        // Según schema.sql, la tabla modules solo tiene id, name, is_premium
        $db->execute("INSERT IGNORE INTO modules (name, is_premium) VALUES ('Compras', 0)");
        echo "✅ Módulo 'Compras' registrado en tabla 'modules'.\n";
    } catch (Exception $e) {
        echo "❌ Error al insertar módulo: " . $e->getMessage() . "\n";
    }

    $mod = $db->fetch("SELECT id FROM modules WHERE name = 'Compras'");
    if ($mod) {
        $db->execute("INSERT IGNORE INTO module_license (module_id, is_enabled, activated_at) 
                      VALUES (:id, 1, NOW())", ['id' => $mod['id']]);
        $db->execute("UPDATE module_license SET is_enabled = 1 WHERE module_id = :id", ['id' => $mod['id']]);
        echo "✅ Módulo 'Compras' habilitado en licencias.\n";
    }

    echo "\n--- TODO LISTO ---\n";
    echo "1. El Dashboard ya debería funcionar (las columnas de stock ya existen).\n";
    echo "2. Ya puedes entrar a la sección de compras.";

} catch (Exception $e) {
    echo "\n❌ ERROR CRÍTICO: " . $e->getMessage();
}
