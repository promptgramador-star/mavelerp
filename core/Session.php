<?php

namespace Core;

/**
 * Wrapper de sesiones PHP con soporte de flash messages.
 *
 * @package ERP\Core
 */
class Session
{
    /**
     * Inicia la sesión si no está activa.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('ERP_SESSION');
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict',
                'use_strict_mode' => true,
            ]);
        }
    }

    /**
     * Obtiene un valor de la sesión.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Establece un valor en la sesión.
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Verifica si existe una clave en la sesión.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Elimina una clave de la sesión.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Establece un mensaje flash (disponible solo en la siguiente petición).
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Obtiene y elimina un mensaje flash.
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Destruye completamente la sesión.
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Regenera el ID de sesión (para prevenir session fixation).
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}
