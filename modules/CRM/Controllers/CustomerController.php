<?php

namespace Modules\CRM\Controllers;

use Core\Controller;
use Core\View;

class CustomerController extends Controller
{
    public function index(): void
    {
        $search = $this->query('q', '');

        if (!empty($search)) {
            $customers = $this->db->fetchAll(
                "SELECT * FROM customers WHERE name LIKE :q OR rnc LIKE :q2 ORDER BY name",
                ['q' => "%{$search}%", 'q2' => "%{$search}%"]
            );
        } else {
            $customers = $this->db->fetchAll("SELECT * FROM customers ORDER BY created_at DESC");
        }

        View::module('CRM', 'customers/index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        View::module('CRM', 'customers/form', ['customer' => null, 'title' => 'Nuevo Cliente']);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->insert(
            "INSERT INTO customers (name, rnc, phone, email, address) VALUES (:name, :rnc, :phone, :email, :address)",
            [
                'name' => trim($this->input('name', '')),
                'rnc' => trim($this->input('rnc', '')),
                'phone' => trim($this->input('phone', '')),
                'email' => trim($this->input('email', '')),
                'address' => trim($this->input('address', '')),
            ]
        );

        flash('success', 'Cliente creado correctamente.');
        redirect('customers');
    }

    public function edit(string $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = :id", ['id' => (int) $id]);
        if (!$customer) {
            flash('error', 'Cliente no encontrado.');
            redirect('customers');
        }

        View::module('CRM', 'customers/form', ['customer' => $customer, 'title' => 'Editar Cliente']);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute(
            "UPDATE customers SET name = :name, rnc = :rnc, phone = :phone, email = :email, address = :address WHERE id = :id",
            [
                'id' => (int) $id,
                'name' => trim($this->input('name', '')),
                'rnc' => trim($this->input('rnc', '')),
                'phone' => trim($this->input('phone', '')),
                'email' => trim($this->input('email', '')),
                'address' => trim($this->input('address', '')),
            ]
        );

        flash('success', 'Cliente actualizado.');
        redirect('customers');
    }

    public function delete(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute("DELETE FROM customers WHERE id = :id", ['id' => (int) $id]);
        flash('success', 'Cliente eliminado.');
        redirect('customers');
    }
}
