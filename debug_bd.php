<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Database.php';
try {
    $db = \Core\Database::getInstance();
    $res = $db->fetch("SHOW COLUMNS FROM settings LIKE 'logo'");
    if ($res) {
        echo "COL_EXISTS";
    } else {
        echo "COL_MISSING";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
