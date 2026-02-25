<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

use Core\Database;

$db = Database::getInstance();

echo "Verificando módulos...\n";

// Asegurar que los módulos existen en 'modules'
$modules = ['CRM', 'Inventario', 'Facturacion'];
foreach ($modules as $name) {
    $m = $db->fetch("SELECT id FROM modules WHERE name = :name", ['name' => $name]);
    if ($m) {
        $id = $m['id'];
        echo "- Módulo {$name} encontrado (ID: {$id})\n";

        // Upsert license
        $lic = $db->fetch("SELECT id FROM module_license WHERE module_id = :id", ['id' => $id]);
        if ($lic) {
            $db->execute("UPDATE module_license SET is_enabled = 1 WHERE module_id = :id", ['id' => $id]);
            echo "  Licencia actualizada a ACTIVADA.\n";
        } else {
            $db->execute("INSERT INTO module_license (module_id, is_enabled, activated_at) VALUES (:id, 1, NOW())", ['id' => $id]);
            echo "  Licencia CREADA y ACTIVADA.\n";
        }
    } else {
        echo "- Módulo {$name} NO ENCONTRADO en tabla 'modules'.\n";
    }
}

echo "\nLimpiando caché de configuración si existe...\n";
// (No hay caché persistente en disco en este framework simplificado, solo estática en helpers.php)

echo "Done.\n";
