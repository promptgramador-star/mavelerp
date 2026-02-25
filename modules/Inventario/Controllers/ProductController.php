<?php

namespace Modules\Inventario\Controllers;

use Core\Controller;
use Core\View;
use ZipArchive;
use SimpleXMLElement;

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

    public function importForm(): void
    {
        View::module('Inventario', 'products/import', ['title' => 'Importar Productos/Servicios']);
    }

    public function downloadTemplate(): void
    {
        ob_clean();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=plantilla_productos.csv');

        $output = fopen('php://output', 'w');
        fwrite($output, "sep=,\n");
        fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['nombre', 'sku', 'costo', 'precio', 'stock', 'es_servicio']);
        fputcsv($output, ['Producto de Ejemplo', 'PROD-001', '500.00', '1200.00', '10', '0']);
        fputcsv($output, ['Servicio Técnico', 'SERV-001', '0.00', '2500.00', '0', '1']);
        fclose($output);
        exit;
    }

    public function importProcess(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Error al subir el archivo.');
            redirect('products/import');
        }

        $filename = $_FILES['csv_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $tmpFile = $_FILES['csv_file']['tmp_name'];

        $rows = [];

        if ($ext === 'xlsx') {
            $rows = $this->parseXlsx($tmpFile);
        } else {
            $rows = $this->parseCsv($tmpFile);
        }

        if (empty($rows)) {
            flash('error', 'El archivo está vacío o no tiene el formato correcto.');
            redirect('products/import');
        }

        $imported = 0;
        $errors = 0;
        $this->db->beginTransaction();

        try {
            foreach ($rows as $index => $data) {
                // Saltamos la cabecera si el nombre es 'nombre'
                if ($index === 0 && strtolower($data[0] ?? '') === 'nombre')
                    continue;
                if (empty($data[0])) {
                    $errors++;
                    continue;
                }

                $this->db->insert(
                    "INSERT INTO products (name, sku, cost, price, stock, is_service, created_at) 
                     VALUES (:name, :sku, :cost, :price, :stock, :is_service, NOW())",
                    [
                        'name' => trim($data[0]),
                        'sku' => trim($data[1] ?? ''),
                        'cost' => (float) ($data[2] ?? 0),
                        'price' => (float) ($data[3] ?? 0),
                        'stock' => (float) ($data[4] ?? 0),
                        'is_service' => (int) ($data[5] ?? 0)
                    ]
                );
                $imported++;
            }
            $this->db->commit();
            flash('success', "Importación completada: {$imported} registros creados. " . ($errors > 0 ? "{$errors} omitidos." : ""));
        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error: ' . $e->getMessage());
        }

        redirect('products');
    }

    private function parseCsv(string $file): array
    {
        $handle = fopen($file, 'r');
        $rows = [];
        $firstLine = fgets($handle);
        if (!str_starts_with($firstLine, 'sep=')) {
            rewind($handle);
        }
        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $file): array
    {
        $zip = new ZipArchive();
        if ($zip->open($file) !== true)
            return [];

        // 1. Leer Shared Strings (Textos)
        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string) ($si->t ?: $si->r->t);
            }
        }

        // 2. Leer Hoja 1
        $rows = [];
        if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $c) {
                    $val = (string) $c->v;
                    $type = (string) $c['t'];
                    if ($type === 's') { // Shared String
                        $val = $sharedStrings[(int) $val] ?? '';
                    }
                    // Mapear por columna (A, B, C...)
                    $col = preg_replace('/[0-9]/', '', (string) $c['r']);
                    $colIdx = $this->columnToIndex($col);
                    $rowData[$colIdx] = $val;
                }
                // Rellenar huecos
                for ($i = 0; $i < 6; $i++)
                    if (!isset($rowData[$i]))
                        $rowData[$i] = '';
                ksort($rowData);
                $rows[] = $rowData;
            }
        }
        $zip->close();
        return $rows;
    }

    private function columnToIndex(string $col): int
    {
        $index = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $index = $index * 26 + ord($col[$i]) - 0x40;
        }
        return $index - 1;
    }
}
