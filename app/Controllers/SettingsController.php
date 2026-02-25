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
            'currency' => trim($this->input('currency', 'DOP')),
            'fiscal_year_start' => $this->input('fiscal_year_start'),
        ];

        $existing = $this->db->fetch("SELECT id FROM settings LIMIT 1");

        if ($existing) {
            $this->db->execute(
                "UPDATE settings SET company_name = :company_name, rnc = :rnc, 
                 address = :address, phone = :phone, email = :email, 
                 bank_accounts = :bank_accounts, 
                 currency = :currency, fiscal_year_start = :fiscal_year_start 
                 WHERE id = :id",
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
