<?php
/**
 * Script de migración: Órdenes de Compra (PO)
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "Iniciando migración para Órdenes de Compra...\n";

    // 1. Modificar tabla documents
    echo "\nActualizando tabla documents...\n";
    // Hacer customer_id nullable
    $db->execute("ALTER TABLE documents MODIFY COLUMN customer_id INT NULL");

    // Añadir supplier_id si no existe
    try {
        $db->execute("ALTER TABLE documents ADD COLUMN supplier_id INT NULL AFTER customer_id");
        $db->execute("ALTER TABLE documents ADD CONSTRAINT fk_doc_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id)");
        echo "✅ Columna 'supplier_id' añadida exitosamente a 'documents'.\n";
    } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "ℹ️ La columna 'supplier_id' ya existe.\n";
        } else {
            throw $e;
        }
    }

    // Actualizar ENUM de document_type
    try {
        $db->execute("ALTER TABLE documents MODIFY COLUMN document_type ENUM('COT','FAC','COND','ORD')");
        $db->execute("ALTER TABLE document_sequences MODIFY COLUMN document_type VARCHAR(20)"); // 'ORD'
        echo "✅ Tipos de documento actualizados para soportar 'ORD' (Órdenes de Compra).\n";
    } catch (\Exception $e) {
        echo "ℹ️ Error actualizando ENUM: " . $e->getMessage() . "\n";
    }

    // 2. Insertar Secuencia Inicial
    echo "\nInsertando secuencia base...\n";
    try {
        $db->insert("INSERT INTO document_sequences (document_type, prefix, year, current_number) 
                     VALUES ('ORD', 'PO', DATE_FORMAT(NOW(), '%y'), 0)
                     ON DUPLICATE KEY UPDATE prefix = 'PO'");
        echo "✅ Secuencia 'PO' añadida.\n";
    } catch (\Exception $e) {
        echo "ℹ️ Error con secuencia: " . $e->getMessage() . "\n";
    }

    echo "\n🎉 ¡Migración de Compras completada con éxito!\n";

} catch (\Exception $e) {
    echo "❌ Error durante la migración:\n";
    echo $e->getMessage() . "\n";
}
