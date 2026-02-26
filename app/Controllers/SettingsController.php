<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador de configuración general del sistema.
 */
class SettingsController extends Controller
{
    public function index(): void
    {
        if (!Auth::isAdmin()) {
            $this->redirect('dashboard');
        }

        $settings = $this->db->fetch("SELECT * FROM settings LIMIT 1");

        $this->view('settings/index', [
            'settings' => $settings ?: [],
        ]);
    }

    public function update(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!Auth::isAdmin()) {
            $this->redirect('dashboard');
        }

        $data = [
            'company_name' => trim($this->input('company_name', '')),
            'rnc' => trim($this->input('rnc', '')),
            'address' => trim($this->input('address', '')),
            'phone' => trim($this->input('phone', '')),
            'email' => trim($this->input('email', '')),
            'bank_accounts' => trim($this->input('bank_accounts', '')),
            'currency' => is_array($this->input('currency')) ? implode(',', $this->input('currency')) : trim($this->input('currency', 'DOP')),
            'fiscal_year_start' => $this->input('fiscal_year_start'),
        ];

        // Handle logo upload
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'];
            $mime = mime_content_type($_FILES['logo']['tmp_name']);

            if (in_array($mime, $allowed)) {
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'company_logo.' . strtolower($ext);
                $dest = BASE_PATH . '/uploads/logo/' . $filename;

                // Remove old logos
                $oldFiles = glob(BASE_PATH . '/uploads/logo/company_logo.*');
                foreach ($oldFiles as $old) {
                    @unlink($old);
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                    $data['logo'] = 'uploads/logo/' . $filename;
                }
            }
        }

        $existing = $this->db->fetch("SELECT id FROM settings LIMIT 1");

        if ($existing) {
            $setClauses = [];
            foreach (array_keys($data) as $key) {
                $setClauses[] = "{$key} = :{$key}";
            }
            $setString = implode(', ', $setClauses);

            $this->db->execute(
                "UPDATE settings SET {$setString} WHERE id = :id",
                array_merge($data, ['id' => $existing['id']])
            );
        } else {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $this->db->insert(
                "INSERT INTO settings ({$columns}) VALUES ({$placeholders})",
                $data
            );
        }

        flash('success', 'Configuración actualizada correctamente.');
        $this->redirect('settings');
    }
}
