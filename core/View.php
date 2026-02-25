<?php

namespace Core;

/**
 * Motor de renderizado de vistas PHP con soporte de layouts.
 *
 * @package ERP\Core
 */
class View
{
    private static ?string $layout = null;
    private static array $sections = [];
    private static ?string $currentSection = null;

    /**
     * Renderiza una vista con datos opcionales dentro de un layout.
     *
     * @param string $view    Ruta de la vista (ej: 'dashboard/index')
     * @param array  $data    Variables disponibles en la vista
     * @param string|null $layout Layout a usar (ej: 'layouts/main')
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        self::$layout = $layout;
        self::$sections = [];

        // Extraer variables para la vista
        extract($data);

        // Renderizar la vista
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$view}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // Si hay layout, insertar el contenido dentro del layout
        if (self::$layout !== null) {
            $layoutPath = BASE_PATH . '/app/Views/' . self::$layout . '.php';
            if (!file_exists($layoutPath)) {
                die("Layout no encontrado: " . self::$layout);
            }

            // [FIX] Solo asignar si la sección 'content' no fue poblada vía startSection
            if (empty(self::$sections['content'])) {
                self::$sections['content'] = $content;
            }
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Renderiza una vista de módulo.
     */
    public static function module(string $module, string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        self::$layout = $layout;
        self::$sections = [];

        extract($data);

        $viewPath = BASE_PATH . '/modules/' . $module . '/Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            die("Vista de módulo no encontrada: {$module}/{$view}");
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if (self::$layout !== null) {
            $layoutPath = BASE_PATH . '/app/Views/' . self::$layout . '.php';

            // [FIX] Solo asignar si la sección 'content' no fue poblada vía startSection
            if (empty(self::$sections['content'])) {
                self::$sections['content'] = $content;
            }
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Inicia una sección nombrada.
     */
    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * Termina la sección actual.
     */
    public static function endSection(): void
    {
        if (self::$currentSection !== null) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Recupera el contenido de una sección.
     */
    public static function section(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Renderiza sin layout (para AJAX, API, etc.).
     */
    public static function partial(string $view, array $data = []): void
    {
        self::render($view, $data, null);
    }
}
