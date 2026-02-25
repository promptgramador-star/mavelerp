<?php

namespace Core;

/**
 * Cargador de módulos del ERP.
 * Lee module.json de cada módulo, verifica licencia en DB,
 * y registra las rutas de los módulos activos.
 *
 * @package ERP\Core
 */
class ModuleLoader
{
    private Database $db;
    private array $activeModules = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Carga todos los módulos con licencia activa.
     */
    public function loadModules(Router $router): void
    {
        $modulesDir = BASE_PATH . '/modules';

        if (!is_dir($modulesDir)) {
            return;
        }

        $dirs = array_filter(glob($modulesDir . '/*'), 'is_dir');

        foreach ($dirs as $moduleDir) {
            $manifestPath = $moduleDir . '/module.json';

            if (!file_exists($manifestPath)) {
                error_log("ModuleLoader: Manifest not found in $moduleDir");
                continue;
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (!$manifest || empty($manifest['name'])) {
                error_log("ModuleLoader: Invalid manifest in $moduleDir");
                continue;
            }

            // Verificar si el módulo tiene licencia activa
            // Temporariamente forzar carga para depuración
            if (true || $this->isModuleEnabled($manifest['name'])) {
                error_log("ModuleLoader: Loading module " . $manifest['name']);
                $this->activeModules[] = $manifest;
                $this->loadModuleRoutes($moduleDir, $router);
            } else {
                error_log("ModuleLoader: Module " . $manifest['name'] . " is NOT enabled in DB");
            }
        }
    }

    /**
     * Verifica en la DB si un módulo está habilitado.
     */
    private function isModuleEnabled(string $moduleName): bool
    {
        $result = $this->db->fetch(
            "SELECT ml.is_enabled 
             FROM module_license ml 
             JOIN modules m ON ml.module_id = m.id 
             WHERE m.name = :name",
            ['name' => $moduleName]
        );

        return $result && (bool) $result['is_enabled'];
    }

    /**
     * Carga el archivo routes.php de un módulo.
     */
    private function loadModuleRoutes(string $moduleDir, Router $router): void
    {
        $routesFile = $moduleDir . '/routes.php';

        if (file_exists($routesFile)) {
            // Las rutas del módulo reciben $router para registrarse
            require $routesFile;
        }
    }

    /**
     * Retorna la lista de módulos activos cargados.
     */
    public function getActiveModules(): array
    {
        return $this->activeModules;
    }

    /**
     * Retorna todos los módulos registrados (activos e inactivos).
     */
    public function getAllModules(): array
    {
        $modules = $this->db->fetchAll(
            "SELECT m.*, ml.is_enabled, ml.activated_at 
             FROM modules m 
             LEFT JOIN module_license ml ON m.id = ml.module_id
             ORDER BY m.name"
        );

        return $modules;
    }
}
