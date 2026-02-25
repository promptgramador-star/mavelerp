<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador de gestión de usuarios.
 */
class UserController extends Controller
{
    public function index(): void
    {
        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $users = $this->db->fetchAll(
            "SELECT u.*, r.name as role_name 
             FROM users u 
             JOIN roles r ON u.role_id = r.id 
             ORDER BY u.created_at DESC"
        );

        $this->view('users/index', ['users' => $users]);
    }

    public function create(): void
    {
        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users/create', ['roles' => $roles]);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $name = trim($this->input('name', ''));
        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $roleId = (int) $this->input('role_id', 0);

        if (empty($name) || empty($email) || empty($password) || $roleId === 0) {
            flash('error', 'Todos los campos son requeridos.');
            $this->redirect('users/create');
        }

        // Verificar email único
        $existing = $this->db->fetch("SELECT id FROM users WHERE email = :email", ['email' => $email]);
        if ($existing) {
            flash('error', 'Ya existe un usuario con ese email.');
            $this->redirect('users/create');
        }

        $this->db->insert(
            "INSERT INTO users (role_id, name, email, password, is_active, created_at) 
             VALUES (:role_id, :name, :email, :password, 1, NOW())",
            [
                'role_id' => $roleId,
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
            ]
        );

        flash('success', 'Usuario creado correctamente.');
        $this->redirect('users');
    }

    public function edit(string $id): void
    {
        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE id = :id", ['id' => (int) $id]);
        if (!$user) {
            flash('error', 'Usuario no encontrado.');
            $this->redirect('users');
        }

        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users/edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $data = [
            'name' => trim($this->input('name', '')),
            'email' => trim($this->input('email', '')),
            'role_id' => (int) $this->input('role_id', 0),
            'is_active' => $this->input('is_active') ? 1 : 0,
        ];

        $sql = "UPDATE users SET name = :name, email = :email, role_id = :role_id, is_active = :is_active WHERE id = :id";
        $data['id'] = (int) $id;

        // Si se proporcionó una nueva contraseña
        $newPassword = $this->input('password', '');
        if (!empty($newPassword)) {
            $sql = "UPDATE users SET name = :name, email = :email, role_id = :role_id, is_active = :is_active, password = :password WHERE id = :id";
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        $this->db->execute($sql, $data);

        flash('success', 'Usuario actualizado correctamente.');
        $this->redirect('users');
    }
}
