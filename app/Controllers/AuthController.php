<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador de autenticación (login/logout).
 */
class AuthController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login', [], null);
    }

    /**
     * Procesa el login.
     */
    public function login(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');

        if (empty($email) || empty($password)) {
            flash('error', 'Email y contraseña son requeridos.');
            $this->redirect('login');
        }

        $user = Auth::attempt($email, $password);

        if (!$user) {
            // Registrar intento fallido en audit_log
            $this->logAction('LOGIN_FAILED', "Intento fallido para: {$email}");
            flash('error', 'Credenciales incorrectas.');
            $this->redirect('login');
        }

        // Registrar login exitoso
        $this->logAction('LOGIN_SUCCESS', "Usuario {$user['email']} inició sesión");
        $this->redirect('dashboard');
    }

    /**
     * Cierra la sesión.
     */
    public function logout(): void
    {
        $this->logAction('LOGOUT', 'Usuario cerró sesión');
        Auth::logout();
        $this->redirect('login');
    }

    /**
     * Registra una acción en el log de auditoría.
     */
    private function logAction(string $action, string $detail): void
    {
        try {
            $this->db->insert(
                "INSERT INTO audit_log (user_id, action, detail, ip_address, created_at) 
                 VALUES (:user_id, :action, :detail, :ip, NOW())",
                [
                    'user_id' => Auth::id(),
                    'action' => $action,
                    'detail' => $detail,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ]
            );
        } catch (\Exception $e) {
            // No bloquear el flujo si falla el log
        }
    }
}
