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

        $name = trim($this->input('name', ''));
        $rnc = trim($this->input('rnc', ''));
        $phone = trim($this->input('phone', ''));
        $email = trim($this->input('email', ''));
        $address = trim($this->input('address', ''));

        // Bloquear RNCs falsos como "11111111111"
        if (!empty($rnc) && preg_match('/^(.)\1+$/', $rnc)) {
            flash('error', 'El RNC o cédula introducido no es válido.');
            redirect('customers/create');
            return;
        }

        // Si el usuario introduce RNC, checar si ya existe alguien con ese documento
        if (!empty($rnc)) {
            $existing = $this->db->fetch("SELECT id FROM customers WHERE rnc = :rnc", ['rnc' => $rnc]);
            if ($existing) {
                flash('error', "Ya existe un cliente registrado con la c&eacute;dula o RNC: {$rnc}");
                redirect('customers/create');
                return;
            }
        }

        // Si no introducen RNC, evitamos crear múltiples clientes con el mismo nombre
        if (empty($rnc)) {
            $existingName = $this->db->fetch("SELECT id FROM customers WHERE name = :name", ['name' => $name]);
            if ($existingName) {
                flash('error', "Ya existe un cliente registrado llamado '{$name}'. Si es distinto, por favor identifíquelo con un RNC o Cédula.");
                redirect('customers/create');
                return;
            }
        }

        $this->db->insert(
            "INSERT INTO customers (name, rnc, phone, email, address) VALUES (:name, :rnc, :phone, :email, :address)",
            [
                'name' => $name,
                'rnc' => $rnc,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
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

    /**
     * Perfil / Detalle del Cliente con historial de documentos.
     */
    public function show(string $id): void
    {
        $customer = $this->db->fetch("SELECT * FROM customers WHERE id = :id", ['id' => (int) $id]);
        if (!$customer) {
            flash('error', 'Cliente no encontrado.');
            redirect('customers');
        }

        $documents = $this->db->fetchAll(
            "SELECT id, document_type, sequence_code, status, total, issue_date 
             FROM documents WHERE customer_id = :id ORDER BY created_at DESC",
            ['id' => (int) $id]
        );

        $totals = $this->db->fetch(
            "SELECT 
                COALESCE(SUM(CASE WHEN document_type = 'FAC' THEN total END), 0) as invoiced,
                COALESCE(SUM(CASE WHEN document_type = 'COT' THEN total END), 0) as quoted,
                COUNT(*) as doc_count
             FROM documents WHERE customer_id = :id",
            ['id' => (int) $id]
        );

        View::module('CRM', 'customers/show', [
            'customer' => $customer,
            'documents' => $documents,
            'totals' => $totals,
        ]);
    }
}
