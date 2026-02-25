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

        $items = scandir($modulesDir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;

            $moduleDir = $modulesDir . '/' . $item;
            if (!is_dir($moduleDir))
                continue;

            $manifestPath = $moduleDir . '/module.json';

            if (!file_exists($manifestPath)) {
                continue;
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (!$manifest || empty($manifest['name'])) {
                continue;
            }

            // Verificar si el módulo tiene licencia activa
            if ($this->isModuleEnabled($manifest['name'])) {
                $this->activeModules[] = $manifest;
                $this->loadModuleRoutes($moduleDir, $router);
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
