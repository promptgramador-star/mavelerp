<?php
/**
 * Script para establecer el logo de Mavel ERP
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();
    $path = 'uploads/logo/company_logo.png';

    // Validar si existe registro en settings
    $settings = $db->fetch("SELECT id FROM settings LIMIT 1");

    if ($settings) {
        $db->execute("UPDATE settings SET logo = :logo WHERE id = :id", [
            'logo' => $path,
            'id' => $settings['id']
        ]);
        echo "<h2>✅ Logo de Mavel ERP establecido correctamente</h2>";
    } else {
        $db->execute("INSERT INTO settings (company_name, logo) VALUES (:name, :logo)", [
            'name' => 'Mavel ERP',
            'logo' => $path
        ]);
        echo "<h2>✅ Novo registro de configuración creado con el logo de Mavel ERP</h2>";
    }
    echo "<p>El logo ahora debería estar visible en el sistema.</p>";
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
echo "<p><a href='index.php'>Volver al ERP</a></p>";
