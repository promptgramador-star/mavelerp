<?php

namespace App\Middleware;

use Core\Middleware;
use Core\Auth;
use Core\Database;

/**
 * Middleware de verificación de licencia de módulo.
 * Comprueba que un módulo esté habilitado antes de permitir acceso.
 */
class LicenseMiddleware extends Middleware
{
    private string $moduleName;

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    public function handle(): void
    {
        $db = Database::getInstance();

        $result = $db->fetch(
            "SELECT ml.is_enabled 
             FROM module_license ml 
             JOIN modules m ON ml.module_id = m.id 
             WHERE m.name = :name",
            ['name' => $this->moduleName]
        );

        if (!$result || !(bool) $result['is_enabled']) {
            http_response_code(403);
            die('Este módulo no está habilitado. Contacte al administrador.');
        }
    }
}
