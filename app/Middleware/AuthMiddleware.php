<?php

namespace App\Middleware;

use Core\Middleware;
use Core\Auth;

/**
 * Middleware de autenticación.
 * Verifica que haya un usuario con sesión activa.
 */
class AuthMiddleware extends Middleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            flash('error', 'Debes iniciar sesión para acceder.');
            redirect('login');
        }
    }
}
