<?php

namespace App\Middleware;

use Core\Middleware;
use Core\Auth;

/**
 * Middleware de verificación de roles.
 * Se usa para restringir acceso a rutas según el rol del usuario.
 */
class RoleMiddleware extends Middleware
{
    private array $allowedRoles;

    public function __construct(string ...$roles)
    {
        $this->allowedRoles = $roles;
    }

    public function handle(): void
    {
        if (!Auth::check()) {
            redirect('login');
        }

        if (!Auth::hasRole(...$this->allowedRoles)) {
            http_response_code(403);
            die('No tienes permisos para acceder a esta sección.');
        }
    }
}
