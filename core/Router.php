<?php

namespace Core;

/**
 * Enrutador simple basado en $_GET['url'].
 * Mapea URLs a Controller@method con soporte de parámetros.
 *
 * @package ERP\Core
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];

    /**
     * Registra una ruta GET.
     */
    public function get(string $pattern, string $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $pattern, $action, $middleware);
    }

    /**
     * Registra una ruta POST.
     */
    public function post(string $pattern, string $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $pattern, $action, $middleware);
    }

    /**
     * Registra una ruta para cualquier método.
     */
    public function any(string $pattern, string $action, array $middleware = []): self
    {
        $this->addRoute('GET', $pattern, $action, $middleware);
        $this->addRoute('POST', $pattern, $action, $middleware);
        return $this;
    }

    /**
     * Agrega una ruta al registro interno.
     */
    private function addRoute(string $method, string $pattern, string $action, array $middleware): self
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->compilePattern($pattern),
            'action' => $action,
            'middleware' => $middleware,
            'raw_pattern' => $pattern,
        ];
        return $this;
    }

    /**
     * Convierte un patrón de ruta en regex.
     * Ej: /users/{id} → /^users\/(\d+)$/
     */
    private function compilePattern(string $pattern): string
    {
        $pattern = trim($pattern, '/');
        // Parámetros dinámicos: {param} → (\w+)
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([\\w-]+)', $pattern);
        return '/^' . str_replace('/', '\\/', $pattern) . '$/';
    }

    /**
     * Resuelve la URL actual y ejecuta la acción correspondiente.
     */
    public function dispatch(): void
    {
        $url = $this->getUrl();
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $url, $matches)) {
                array_shift($matches); // Quitar el match completo

                // Ejecutar middleware
                foreach ($route['middleware'] as $mw) {
                    $middlewareClass = $mw;
                    if (class_exists($middlewareClass)) {
                        $middlewareInstance = new $middlewareClass();
                        $middlewareInstance->handle();
                    }
                }

                // Ejecutar acción
                $this->executeAction($route['action'], $matches);
                return;
            }
        }

        // 404 — Ruta no encontrada
        http_response_code(404);
        echo $this->render404();
    }

    /**
     * Ejecuta Controller@method con parámetros.
     */
    private function executeAction(string $action, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $action);

        if (!class_exists($controllerName)) {
            http_response_code(500);
            die("Controller no encontrado: {$controllerName}");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            die("Método no encontrado: {$controllerName}@{$methodName}");
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Obtiene la URL limpia desde $_GET['url'].
     */
    private function getUrl(): string
    {
        // 1. Intentar desde $_GET['url'] (poblado por .htaccess)
        $url = $_GET['url'] ?? '';

        // 2. Si está vacío, intentar desde PATH_INFO o REQUEST_URI
        if (empty($url)) {
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

            // Eliminar el nombre del script (index.php) y la base_url
            $url = str_replace($scriptName, '', $uri);
            $url = explode('?', $url)[0]; // Quitar query string
        }

        return trim(filter_var($url, FILTER_SANITIZE_URL), '/');
    }

    /**
     * Renderiza página 404.
     */
    private function render404(): string
    {
        $viewPath = BASE_PATH . '/app/Views/errors/404.php';
        if (file_exists($viewPath)) {
            ob_start();
            include $viewPath;
            return ob_get_clean();
        }

        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>404</title>
        <style>body{font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f0f2f5;color:#333;}
        .box{text-align:center;}.box h1{font-size:72px;margin:0;color:#3498db;}.box p{font-size:18px;color:#7f8c8d;}</style></head>
        <body><div class="box"><h1>404</h1><p>Página no encontrada</p></div></body></html>';
    }
}
