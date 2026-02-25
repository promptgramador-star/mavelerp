<?php
/**
 * ERP Propietario RD — Front Controller
 * 
 * Todas las peticiones HTTP pasan por este archivo.
 * Verifica instalación, carga el bootstrap y ejecuta la aplicación.
 * 
 * @version 0.1.0
 * @php     8.0+
 */
declare(strict_types=1);

// Forzar visibilidad de errores ANTES de cualquier otra cosa
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Definir constante de la raíz del proyecto
define('BASE_PATH', __DIR__);

// Verificar si el sistema está instalado
if (!file_exists(BASE_PATH . '/config/installed.lock')) {
    header('Location: install/index.php');
    exit;
}

// Cargar helpers globales
require_once BASE_PATH . '/core/helpers.php';

// Cargar y ejecutar la aplicación
require_once BASE_PATH . '/core/App.php';

$app = new Core\App();
$app->run();
