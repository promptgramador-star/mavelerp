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

    /**
     * Muestra el formulario de importación.
     */
    public function importForm(): void
    {
        View::module('Inventario', 'products/import', ['title' => 'Importar Productos/Servicios']);
    }

    /**
     * Descarga la plantilla CSV de ejemplo.
     */
    public function downloadTemplate(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_productos.csv');

        $output = fopen('php://output', 'w');
        // BOM para Excel en Windows
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeceras
        fputcsv($output, ['nombre', 'sku', 'costo', 'precio', 'stock', 'es_servicio']);

        // Ejemplos
        fputcsv($output, ['Producto de Ejemplo', 'PROD-001', '500.00', '1200.00', '10', '0']);
        fputcsv($output, ['Servicio Técnico', 'SERV-001', '0.00', '2500.00', '0', '1']);

        fclose($output);
        exit;
    }

    /**
     * Procesa el archivo CSV subido.
     */
    public function importProcess(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Error al subir el archivo.');
            redirect('products/import');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        if ($handle === false) {
            flash('error', 'No se pudo abrir el archivo.');
            redirect('products/import');
        }

        // Leer cabeceras
        $headers = fgetcsv($handle);

        $imported = 0;
        $errors = 0;

        $this->db->beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                // Mapear datos (asumiendo orden de la plantilla)
                // nombre, sku, costo, precio, stock, es_servicio
                if (count($data) < 2)
                    continue;

                $product = [
                    'name' => trim($data[0]),
                    'sku' => trim($data[1] ?? ''),
                    'cost' => (float) ($data[2] ?? 0),
                    'price' => (float) ($data[3] ?? 0),
                    'stock' => (float) ($data[4] ?? 0),
                    'is_service' => (int) ($data[5] ?? 0)
                ];

                if (empty($product['name'])) {
                    $errors++;
                    continue;
                }

                $this->db->insert(
                    "INSERT INTO products (name, sku, cost, price, stock, is_service, created_at) 
                     VALUES (:name, :sku, :cost, :price, :stock, :is_service, NOW())",
                    $product
                );
                $imported++;
            }

            $this->db->commit();
            flash('success', "Importación completada: {$imported} registros importados. " . ($errors > 0 ? "{$errors} omitidos por errores." : ""));

        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error durante la importación: ' . $e->getMessage());
        }

        fclose($handle);
        redirect('products');
    }
}
