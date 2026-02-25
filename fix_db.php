<?php
/**
 * Limpieza de base de datos — Elimina licencias duplicadas
 */
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = \Core\Database::getInstance();

    echo "Limpiando duplicados de licencias...\n";

    // 1. Crear tabla temporal con una sola licencia activa por módulo
    $db->execute("CREATE TEMPORARY TABLE temp_licenses AS 
                  SELECT MIN(id) as id FROM module_license GROUP BY module_id");

    // 2. Borrar todas las licencias que no estén en la temporal
    $count = $db->execute("DELETE FROM module_license WHERE id NOT IN (SELECT id FROM temp_licenses)");

    echo "¡Hecho! Se eliminaron {$count} registros duplicados.\n";

    // 3. Asegurar que los módulos base estén activos
    $db->execute("UPDATE module_license SET is_enabled = 1");

    echo "Todos los módulos han sido reactivados correctamente.\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
