<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/helpers.php';
require_once BASE_PATH . '/core/Database.php';

use Core\Database;

try {
    $db = Database::getInstance();

    echo "Iniciando migración de tabla 'documents'...\n";

    // Lista de columnas a agregar
    $columns = [
        'ncf' => "VARCHAR(20) DEFAULT NULL AFTER status",
        'retention_percentage' => "DECIMAL(5,2) DEFAULT 0 AFTER subtotal",
        'retention_amount' => "DECIMAL(15,2) DEFAULT 0 AFTER retention_percentage"
    ];

    foreach ($columns as $column => $definition) {
        $exists = $db->fetch("SHOW COLUMNS FROM documents LIKE :col", ['col' => $column]);
        if (!$exists) {
            $db->execute("ALTER TABLE documents ADD COLUMN $column $definition");
            echo "Columna '$column' agregada.\n";
        } else {
            echo "La columna '$column' ya existe.\n";
        }
    }

    echo "Migración completada exitosamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
