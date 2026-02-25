<?php

namespace Core;

/**
 * Helper de autenticación.
 * Maneja login, logout, verificación de roles y acceso al usuario autenticado.
 *
 * @package ERP\Core
 */
class Auth
{
    /**
     * Verifica si hay un usuario autenticado.
     */
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Retorna los datos del usuario autenticado.
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $userId = Session::get('user_id');
        $db = Database::getInstance();

        return $db->fetch(
            "SELECT u.*, r.name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id = :id AND u.is_active = 1",
            ['id' => $userId]
        ) ?: null;
    }

    /**
     * Retorna el ID del usuario autenticado.
     */
    public static function id(): ?int
    {
        return Session::has('user_id') ? (int) Session::get('user_id') : null;
    }

    /**
     * Retorna el nombre del rol del usuario autenticado.
     */
    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    /**
     * Verifica si el usuario tiene un rol específico.
     */
    public static function hasRole(string ...$roles): bool
    {
        $userRole = self::role();
        if ($userRole === null) {
            return false;
        }
        return in_array($userRole, $roles, true);
    }

    /**
     * Verifica si el usuario es super admin.
     */
    public static function isSuperAdmin(): bool
    {
        return self::hasRole('SUPER_ADMIN');
    }

    /**
     * Verifica si el usuario es admin o superior.
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('SUPER_ADMIN', 'ADMIN');
    }

    /**
     * Inicia sesión para un usuario.
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role_name'] ?? '');
        Session::set('login_time', time());
    }

    /**
     * Cierra la sesión del usuario.
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Intenta autenticar con email y contraseña.
     * Retorna datos del usuario o false.
     */
    public static function attempt(string $email, string $password): array|false
    {
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT u.*, r.name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.email = :email AND u.is_active = 1",
            ['email' => $email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        self::login($user);
        return $user;
    }
}
