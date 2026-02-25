<?php

namespace Core;

/**
 * Clase base para todos los controladores.
 * Provee acceso a DB, sesión, renderizado de vistas y redirecciones.
 *
 * @package ERP\Core
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Renderiza una vista con layout.
     */
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Redirige a una URL interna.
     */
    protected function redirect(string $path = ''): void
    {
        redirect($path);
    }

    /**
     * Retorna una respuesta JSON.
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Valida que la solicitud sea POST.
     */
    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método no permitido.');
        }
    }

    /**
     * Valida el token CSRF.
     */
    protected function validateCsrf(): void
    {
        if (!csrf_verify()) {
            http_response_code(403);
            die('Token CSRF inválido.');
        }
    }

    /**
     * Obtiene un valor POST sanitizado.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtiene un valor GET sanitizado.
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }
}
