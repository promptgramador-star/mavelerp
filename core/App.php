<?php

namespace Core;

/**
 * Kernel de la aplicación ERP.
 * Inicializa configuración, sesión, DB, módulos y router.
 *
 * @package ERP\Core
 */
class App
{
    private Router $router;

    public function __construct()
    {
        $this->bootstrap();
    }

    /**
     * Proceso de arranque de la aplicación.
     */
    private function bootstrap(): void
    {
        // 1. Configurar zona horaria
        $timezone = config('app', 'timezone') ?? 'America/Santo_Domingo';
        date_default_timezone_set($timezone);

        // 2. Configurar reporte de errores según modo debug
        if (config('app', 'debug')) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // 3. Autoloader de clases
        spl_autoload_register([$this, 'autoload']);

        // 4. Iniciar sesión
        Session::start();

        // 5. Inicializar router
        $this->router = new Router();

        // 6. Registrar rutas de la aplicación base
        $this->loadRoutes();

        // 7. Cargar módulos activos
        $moduleLoader = new ModuleLoader();
        $moduleLoader->loadModules($this->router);
    }

    /**
     * Autoloader PSR-4 simplificado.
     */
    private function autoload(string $className): void
    {
        // Core\ClassName → core/ClassName.php
        // App\Controllers\XController → app/Controllers/XController.php
        // App\Models\XModel → app/Models/XModel.php
        // App\Middleware\XMiddleware → app/Middleware/XMiddleware.php
        // Modules\ModuleName\Controllers\X → modules/ModuleName/Controllers/X.php

        $map = [
            'Core\\' => BASE_PATH . '/core/',
            'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
            'App\\Models\\' => BASE_PATH . '/app/Models/',
            'App\\Middleware\\' => BASE_PATH . '/app/Middleware/',
            'Modules\\' => BASE_PATH . '/modules/',
        ];

        foreach ($map as $prefix => $dir) {
            if (str_starts_with($className, $prefix)) {
                $relativeClass = substr($className, strlen($prefix));
                $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }

    /**
     * Carga las rutas principales de la aplicación.
     */
    private function loadRoutes(): void
    {
        $routesFile = BASE_PATH . '/app/routes.php';
        if (file_exists($routesFile)) {
            $router = $this->router;
            require $routesFile;
        }
    }

    /**
     * Ejecuta la aplicación (despacha la ruta actual).
     */
    public function run(): void
    {
        $this->router->dispatch();
    }
}
