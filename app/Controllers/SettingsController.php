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
            'default_currency' => trim($this->input('default_currency', 'DOP')),
            'fiscal_year_start' => $this->input('fiscal_year_start'),
        ];

        // Handle logo upload
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'];
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

            $mime = 'unknown';
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($_FILES['logo']['tmp_name']);
            } else {
                $imgInfo = @getimagesize($_FILES['logo']['tmp_name']);
                if ($imgInfo)
                    $mime = $imgInfo['mime'];
            }

            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

            if (in_array($mime, $allowedTypes) || in_array($ext, $allowedExtensions)) {
                $destDir = BASE_PATH . '/uploads/logo';
                $filename = 'company_logo.' . $ext;
                $destFile = $destDir . '/' . $filename;

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                // Clear old logos
                $oldFiles = glob($destDir . '/company_logo.*');
                if ($oldFiles) {
                    foreach ($oldFiles as $old) {
                        @unlink($old);
                    }
                }

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $destFile)) {
                    $data['logo'] = 'uploads/logo/' . $filename;
                } else {
                    flash('error', 'Error: No se pudo mover el archivo. Verifique permisos de: ' . $destDir);
                }
            } else {
                flash('error', 'Formato no permitido: ' . $mime . ' (Ext: ' . $ext . ')');
            }
        } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            flash('error', 'Error en la subida: ' . $_FILES['logo']['error']);
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
