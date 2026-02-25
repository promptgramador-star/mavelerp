<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador de gestión de módulos y licencias.
 */
class ModuleController extends Controller
{
    public function index(): void
    {
        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $modules = $this->db->fetchAll(
            "SELECT m.*, ml.is_enabled, ml.activated_at 
             FROM modules m 
             LEFT JOIN module_license ml ON m.id = ml.module_id 
             ORDER BY m.name"
        );

        $this->view('modules/index', ['modules' => $modules]);
    }

    public function toggle(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!Auth::isSuperAdmin()) {
            $this->redirect('dashboard');
        }

        $moduleId = (int) $id;

        // Verificar que el módulo existe
        $module = $this->db->fetch("SELECT * FROM modules WHERE id = :id", ['id' => $moduleId]);
        if (!$module) {
            flash('error', 'Módulo no encontrado.');
            $this->redirect('modules');
        }

        // Verificar si ya tiene registro de licencia
        $license = $this->db->fetch(
            "SELECT * FROM module_license WHERE module_id = :module_id",
            ['module_id' => $moduleId]
        );

        if ($license) {
            // Toggle: cambiar estado
            $newState = $license['is_enabled'] ? 0 : 1;
            $this->db->execute(
                "UPDATE module_license SET is_enabled = :enabled, activated_at = IF(:enabled = 1, NOW(), activated_at) WHERE module_id = :module_id",
                ['enabled' => $newState, 'module_id' => $moduleId]
            );
        } else {
            // Crear registro de licencia (activar)
            $this->db->insert(
                "INSERT INTO module_license (module_id, is_enabled, activated_at) VALUES (:module_id, 1, NOW())",
                ['module_id' => $moduleId]
            );
        }

        flash('success', 'Estado del módulo actualizado.');
        $this->redirect('modules');
    }
}
