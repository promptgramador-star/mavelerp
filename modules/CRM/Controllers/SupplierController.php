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

        $name = trim($this->input('name', ''));
        $rnc = trim($this->input('rnc', ''));
        $phone = trim($this->input('phone', ''));
        $email = trim($this->input('email', ''));
        $address = trim($this->input('address', ''));

        if ($err = validate_entity_name($name)) {
            flash('error', $err);
            redirect('suppliers/create');
            return;
        }

        if ($err = validate_rnc_cedula($rnc)) {
            flash('error', $err);
            redirect('suppliers/create');
            return;
        }

        if ($err = validate_phone($phone)) {
            flash('error', $err);
            redirect('suppliers/create');
            return;
        }

        if (!empty($rnc) && preg_match('/^(.)\1+$/', $rnc)) {
            flash('error', 'El RNC o cédula introducido no es válido.');
            redirect('suppliers/create');
            return;
        }

        // Si el usuario introduce RNC, checar si ya existe alguien con ese documento
        if (!empty($rnc)) {
            $existing = $this->db->fetch("SELECT id FROM suppliers WHERE rnc = :rnc", ['rnc' => $rnc]);
            if ($existing) {
                flash('error', "Ya existe un proveedor registrado con la c&eacute;dula o RNC: {$rnc}");
                redirect('suppliers/create');
                return;
            }
        }

        // Si no introducen RNC, evitamos crear múltiples con el mismo nombre
        if (empty($rnc)) {
            $existingName = $this->db->fetch("SELECT id FROM suppliers WHERE name = :name", ['name' => $name]);
            if ($existingName) {
                flash('error', "Ya existe un proveedor registrado llamado '{$name}'. Si es distinto, por favor identifíquelo con un RNC o Cédula.");
                redirect('suppliers/create');
                return;
            }
        }

        $this->db->insert(
            "INSERT INTO suppliers (name, rnc, phone, email, address) VALUES (:name, :rnc, :phone, :email, :address)",
            [
                'name' => $name,
                'rnc' => $rnc,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
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

        $name = trim($this->input('name', ''));
        $rnc = trim($this->input('rnc', ''));
        $phone = trim($this->input('phone', ''));
        $email = trim($this->input('email', ''));
        $address = trim($this->input('address', ''));

        if ($err = validate_entity_name($name)) {
            flash('error', $err);
            redirect('suppliers/edit/' . $id);
            return;
        }

        if ($err = validate_rnc_cedula($rnc)) {
            flash('error', $err);
            redirect('suppliers/edit/' . $id);
            return;
        }

        if ($err = validate_phone($phone)) {
            flash('error', $err);
            redirect('suppliers/edit/' . $id);
            return;
        }

        if (!empty($rnc) && preg_match('/^(.)\1+$/', $rnc)) {
            flash('error', 'El RNC o cédula introducido no es válido.');
            redirect('suppliers/edit/' . $id);
            return;
        }

        $this->db->execute(
            "UPDATE suppliers SET name = :name, rnc = :rnc, phone = :phone, email = :email, address = :address WHERE id = :id",
            [
                'id' => (int) $id,
                'name' => $name,
                'rnc' => $rnc,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
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

    /**
     * Perfil / Detalle del Proveedor.
     */
    public function show(string $id): void
    {
        $supplier = $this->db->fetch("SELECT * FROM suppliers WHERE id = :id", ['id' => (int) $id]);
        if (!$supplier) {
            flash('error', 'Proveedor no encontrado.');
            redirect('suppliers');
        }

        View::module('CRM', 'suppliers/show', [
            'supplier' => $supplier,
        ]);
    }
}
