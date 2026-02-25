<?php
/**
 * Script de Activación Forzada de Módulos
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

use Core\Database;

$db = Database::getInstance();

echo "<h1>Activando Módulos...</h1>";

$modules = ['CRM', 'Inventario', 'Facturacion'];

foreach ($modules as $name) {
    // 1. Asegurar que el modulo existe en la tabla modules
    $m = $db->fetch("SELECT id FROM modules WHERE name = :name", ['name' => $name]);
    if (!$m) {
        $id = $db->insert("INSERT INTO modules (name, display_name, version, is_premium) VALUES (:name, :name, '1.0.0', 0)", ['name' => $name]);
        echo "✅ Módulo $name creado en la base de datos.<br>";
    } else {
        $id = $m['id'];
        echo "ℹ️ Módulo $name ya existe (ID: $id).<br>";
    }

    // 2. Asegurar que el modulo tiene licencia habilitada
    $lic = $db->fetch("SELECT id FROM module_license WHERE module_id = :id", ['id' => $id]);
    if ($lic) {
        $db->execute("UPDATE module_license SET is_enabled = 1 WHERE module_id = :id", ['id' => $id]);
        echo "✅ Licencia de $name actualizada a HABILITADA.<br>";
    } else {
        $db->insert("INSERT INTO module_license (module_id, is_enabled, activated_at) VALUES (:id, 1, NOW())", ['id' => $id]);
        echo "✅ Licencia de $name creada y HABILITADA.<br>";
    }
}

echo "<br><a href='index.php'>Volver al ERP</a>";
