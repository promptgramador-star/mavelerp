<?php

namespace Modules\CRM\Controllers;

use Core\Controller;
use Core\View;

class SupplierController extends Controller
{
    public function index(): void
    {
        $search = $this->query('q', '');

        if (!empty($search)) {
            $suppliers = $this->db->fetchAll(
                "SELECT * FROM suppliers WHERE name LIKE :q OR rnc LIKE :q2 ORDER BY name",
                ['q' => "%{$search}%", 'q2' => "%{$search}%"]
            );
        } else {
            $suppliers = $this->db->fetchAll("SELECT * FROM suppliers ORDER BY created_at DESC");
        }

        View::module('CRM', 'suppliers/index', [
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        View::module('CRM', 'suppliers/form', ['supplier' => null, 'title' => 'Nuevo Proveedor']);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->insert(
            "INSERT INTO suppliers (name, rnc, phone, email, address) VALUES (:name, :rnc, :phone, :email, :address)",
            [
                'name' => trim($this->input('name', '')),
                'rnc' => trim($this->input('rnc', '')),
                'phone' => trim($this->input('phone', '')),
                'email' => trim($this->input('email', '')),
                'address' => trim($this->input('address', '')),
            ]
        );

        flash('success', 'Proveedor creado correctamente.');
        redirect('suppliers');
    }

    public function edit(string $id): void
    {
        $supplier = $this->db->fetch("SELECT * FROM suppliers WHERE id = :id", ['id' => (int) $id]);
        if (!$supplier) {
            flash('error', 'Proveedor no encontrado.');
            redirect('suppliers');
        }

        View::module('CRM', 'suppliers/form', ['supplier' => $supplier, 'title' => 'Editar Proveedor']);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute(
            "UPDATE suppliers SET name = :name, rnc = :rnc, phone = :phone, email = :email, address = :address WHERE id = :id",
            [
                'id' => (int) $id,
                'name' => trim($this->input('name', '')),
                'rnc' => trim($this->input('rnc', '')),
                'phone' => trim($this->input('phone', '')),
                'email' => trim($this->input('email', '')),
                'address' => trim($this->input('address', '')),
            ]
        );

        flash('success', 'Proveedor actualizado.');
        redirect('suppliers');
    }

    public function delete(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute("DELETE FROM suppliers WHERE id = :id", ['id' => (int) $id]);
        flash('success', 'Proveedor eliminado.');
        redirect('suppliers');
    }
}
