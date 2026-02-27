<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    echo "Registrando módulo Compras...\n";

    $db->execute("INSERT IGNORE INTO modules (name, display_name, description, version, is_core) 
                  VALUES ('m_compras', 'Compras', 'Módulo de compras y órdenes a proveedores', '1.0.0', 0)");

    $modId = $db->fetch("SELECT id FROM modules WHERE name = 'm_compras'")['id'];

    $db->execute("INSERT IGNORE INTO module_license (module_id, is_enabled, activated_at) 
                  VALUES (:id, 1, NOW())", ['id' => $modId]);

    echo "✅ Módulo m_compras registrado y activado con éxito.\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
