<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    $db->execute("ALTER TABLE settings ADD COLUMN default_currency VARCHAR(50) DEFAULT 'DOP' AFTER currency;");
    echo 'Columna default_currency agregada exitosamente.';
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo 'La columna default_currency ya existe.';
    } else {
        echo 'Error: ' . $e->getMessage();
    }
}

