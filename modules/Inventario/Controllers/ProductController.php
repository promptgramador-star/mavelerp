<?php

namespace Modules\Inventario\Controllers;

use Core\Controller;
use Core\View;

class ProductController extends Controller
{
    public function index(): void
    {
        $search = $this->query('q', '');

        if (!empty($search)) {
            $products = $this->db->fetchAll(
                "SELECT * FROM products WHERE name LIKE :q OR sku LIKE :q2 ORDER BY name",
                ['q' => "%{$search}%", 'q2' => "%{$search}%"]
            );
        } else {
            $products = $this->db->fetchAll("SELECT * FROM products ORDER BY created_at DESC");
        }

        View::module('Inventario', 'products/index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        View::module('Inventario', 'products/form', ['product' => null, 'title' => 'Nuevo Producto']);
    }

    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->insert(
            "INSERT INTO products (name, sku, cost, price, stock, is_service) VALUES (:name, :sku, :cost, :price, :stock, :is_service)",
            [
                'name' => trim($this->input('name', '')),
                'sku' => trim($this->input('sku', '')),
                'cost' => (float) $this->input('cost', 0),
                'price' => (float) $this->input('price', 0),
                'stock' => (float) $this->input('stock', 0),
                'is_service' => $this->input('is_service') ? 1 : 0,
            ]
        );

        flash('success', 'Producto creado correctamente.');
        redirect('products');
    }

    public function edit(string $id): void
    {
        $product = $this->db->fetch("SELECT * FROM products WHERE id = :id", ['id' => (int) $id]);
        if (!$product) {
            flash('error', 'Producto no encontrado.');
            redirect('products');
        }

        View::module('Inventario', 'products/form', ['product' => $product, 'title' => 'Editar Producto']);
    }

    public function update(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute(
            "UPDATE products SET name = :name, sku = :sku, cost = :cost, price = :price, stock = :stock, is_service = :is_service WHERE id = :id",
            [
                'id' => (int) $id,
                'name' => trim($this->input('name', '')),
                'sku' => trim($this->input('sku', '')),
                'cost' => (float) $this->input('cost', 0),
                'price' => (float) $this->input('price', 0),
                'stock' => (float) $this->input('stock', 0),
                'is_service' => $this->input('is_service') ? 1 : 0,
            ]
        );

        flash('success', 'Producto actualizado.');
        redirect('products');
    }

    public function delete(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute("DELETE FROM products WHERE id = :id", ['id' => (int) $id]);
        flash('success', 'Producto eliminado.');
        redirect('products');
    }
}
