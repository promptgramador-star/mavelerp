<?php
/**
 * Script de migración: Añadir NCF y Retenciones
 */
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = \Core\Database::getInstance()->getConnection();

    // 1. Añadir NCF y Retenciones a documentos
    $sql1 = "ALTER TABLE documents 
             ADD COLUMN ncf VARCHAR(20) DEFAULT NULL AFTER status,
             ADD COLUMN retention_percentage DECIMAL(5,2) DEFAULT 0 AFTER subtotal,
             ADD COLUMN retention_amount DECIMAL(15,2) DEFAULT 0 AFTER retention_percentage";

    $db->exec($sql1);

    echo "<h2>Migración completada con éxito</h2>";
    echo "<p>Se añadieron las columnas 'ncf', 'retention_percentage' y 'retention_amount' a la tabla 'documents'.</p>";
    echo "<a href='index.php'>Volver al inicio</a>";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "<h2>Las columnas ya existen</h2>";
        echo "<p>La base de datos ya está actualizada.</p>";
        echo "<a href='index.php'>Volver al inicio</a>";
    } else {
        echo "<h2>Error en la migración:</h2>";
        echo "<p>" . $e->getMessage() . "</p>";
    }
}
