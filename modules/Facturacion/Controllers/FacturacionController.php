<?php

namespace Modules\Facturacion\Controllers;

use Core\Controller;
use Core\View;

class FacturacionController extends Controller
{
    /**
     * Listado de Cotizaciones.
     */
    public function index(): void
    {
        $docs = $this->db->fetchAll(
            "SELECT d.*, c.name as customer_name 
             FROM documents d 
             LEFT JOIN customers c ON d.customer_id = c.id 
             WHERE d.document_type = 'COT' 
             ORDER BY d.created_at DESC"
        );

        View::module('Facturacion', 'quotations/index', ['documents' => $docs]);
    }

    /**
     * Formulario de nueva Cotización.
     */
    public function create(): void
    {
        $customers = $this->db->fetchAll("SELECT id, name FROM customers ORDER BY name");
        $products = $this->db->fetchAll("SELECT id, name, price FROM products ORDER BY name");

        View::module('Facturacion', 'quotations/create', [
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Guardar Cotización con ítems.
     */
    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $customerId = (int) $this->input('customer_id', 0);
        $issueDate = $this->input('issue_date', date('Y-m-d'));

        // Obtener ítems del formulario
        $descriptions = $_POST['item_description'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $prices = $_POST['item_price'] ?? [];

        if (empty($descriptions) || $customerId === 0) {
            flash('error', 'Selecciona un cliente y agrega al menos un ítem.');
            redirect('quotations/create');
        }

        try {
            $this->db->beginTransaction();

            // Generar secuencia COT
            $year = date('y');
            $this->db->execute(
                "UPDATE document_sequences SET current_number = current_number + 1 
                 WHERE document_type = 'COT' AND year = :year",
                ['year' => $year]
            );

            $seq = $this->db->fetch(
                "SELECT prefix, current_number FROM document_sequences 
                 WHERE document_type = 'COT' AND year = :year",
                ['year' => $year]
            );

            $code = $seq['prefix'] . $year . '-' . str_pad((string) $seq['current_number'], 5, '0', STR_PAD_LEFT);

            // Calcular totales
            $subtotal = 0;
            $items = [];
            foreach ($descriptions as $i => $desc) {
                if (empty(trim($desc)))
                    continue;
                $qty = (float) ($quantities[$i] ?? 0);
                $price = (float) ($prices[$i] ?? 0);
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;
                $items[] = [
                    'line_number' => $i + 1,
                    'description' => trim($desc),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $lineTotal,
                    'product_id' => !empty($_POST['item_product_id'][$i]) ? (int) $_POST['item_product_id'][$i] : null,
                ];
            }

            $tax = $subtotal * 0.18; // ITBIS 18%
            $total = $subtotal + $tax;

            // Insertar documento
            $docId = $this->db->insert(
                "INSERT INTO documents (document_type, sequence_code, customer_id, status, subtotal, tax, total, issue_date)
                 VALUES ('COT', :code, :customer, 'DRAFT', :subtotal, :tax, :total, :date)",
                [
                    'code' => $code,
                    'customer' => $customerId,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'date' => $issueDate,
                ]
            );

            // Insertar ítems
            foreach ($items as $item) {
                $this->db->insert(
                    "INSERT INTO document_items (document_id, line_number, product_id, description, quantity, unit_price, total)
                     VALUES (:doc_id, :line, :prod, :desc, :qty, :price, :total)",
                    [
                        'doc_id' => $docId,
                        'line' => $item['line_number'],
                        'prod' => $item['product_id'],
                        'desc' => $item['description'],
                        'qty' => $item['quantity'],
                        'price' => $item['unit_price'],
                        'total' => $item['total'],
                    ]
                );
            }

            $this->db->commit();
            flash('success', "Cotización {$code} creada correctamente.");
            redirect('quotations/view/' . $docId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al crear cotización: ' . $e->getMessage());
            redirect('quotations/create');
        }
    }

    /**
     * Ver detalle de Cotización.
     */
    public function show(string $id): void
    {
        $doc = $this->db->fetch(
            "SELECT d.*, c.name as customer_name, c.rnc as customer_rnc, c.address as customer_address 
             FROM documents d LEFT JOIN customers c ON d.customer_id = c.id WHERE d.id = :id",
            ['id' => (int) $id]
        );

        if (!$doc) {
            flash('error', 'Documento no encontrado.');
            redirect('quotations');
        }

        $items = $this->db->fetchAll(
            "SELECT di.*, p.name as product_name FROM document_items di 
             LEFT JOIN products p ON di.product_id = p.id 
             WHERE di.document_id = :id ORDER BY di.line_number",
            ['id' => (int) $id]
        );

        // Verificar si ya tiene una factura generada
        $hasInvoice = $this->db->fetch(
            "SELECT id, sequence_code FROM documents WHERE reference_document_id = :id AND document_type = 'FAC'",
            ['id' => (int) $id]
        );

        View::module('Facturacion', 'quotations/show', [
            'doc' => $doc,
            'items' => $items,
            'hasInvoice' => $hasInvoice,
        ]);
    }

    /**
     * Aprobar Cotización.
     */
    public function approve(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $this->db->execute(
            "UPDATE documents SET status = 'APPROVED' WHERE id = :id AND document_type = 'COT' AND status = 'DRAFT'",
            ['id' => (int) $id]
        );

        flash('success', 'Cotización aprobada.');
        redirect('quotations/view/' . $id);
    }

    /**
     * Convertir Cotización aprobada en Factura.
     */
    public function convertToInvoice(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $cotId = (int) $id;
        $cot = $this->db->fetch(
            "SELECT * FROM documents WHERE id = :id AND document_type = 'COT'",
            ['id' => $cotId]
        );

        if (!$cot || $cot['status'] !== 'APPROVED') {
            flash('error', 'Solo se pueden convertir cotizaciones aprobadas.');
            redirect('quotations/view/' . $cotId);
        }

        // Verificar que no se haya convertido ya
        $existing = $this->db->fetch(
            "SELECT id FROM documents WHERE reference_document_id = :id AND document_type = 'FAC'",
            ['id' => $cotId]
        );

        if ($existing) {
            flash('error', 'Esta cotización ya fue convertida a factura.');
            redirect('quotations/view/' . $cotId);
        }

        try {
            $this->db->beginTransaction();

            $year = date('y');
            $this->db->execute(
                "UPDATE document_sequences SET current_number = current_number + 1 
                 WHERE document_type = 'FAC' AND year = :year",
                ['year' => $year]
            );

            $seq = $this->db->fetch(
                "SELECT prefix, current_number FROM document_sequences 
                 WHERE document_type = 'FAC' AND year = :year",
                ['year' => $year]
            );

            $newCode = $seq['prefix'] . $year . '-' . str_pad((string) $seq['current_number'], 5, '0', STR_PAD_LEFT);

            $facId = $this->db->insert(
                "INSERT INTO documents (document_type, sequence_code, customer_id, reference_document_id, status, subtotal, tax, total, issue_date) 
                 VALUES ('FAC', :code, :customer, :ref, 'DRAFT', :sub, :tax, :total, :date)",
                [
                    'code' => $newCode,
                    'customer' => $cot['customer_id'],
                    'ref' => $cotId,
                    'sub' => $cot['subtotal'],
                    'tax' => $cot['tax'],
                    'total' => $cot['total'],
                    'date' => date('Y-m-d'),
                ]
            );

            $items = $this->db->fetchAll(
                "SELECT * FROM document_items WHERE document_id = :id",
                ['id' => $cotId]
            );

            foreach ($items as $item) {
                $this->db->insert(
                    "INSERT INTO document_items (document_id, line_number, product_id, description, quantity, unit_price, total) 
                     VALUES (:doc_id, :line, :prod, :desc, :qty, :price, :total)",
                    [
                        'doc_id' => $facId,
                        'line' => $item['line_number'],
                        'prod' => $item['product_id'],
                        'desc' => $item['description'],
                        'qty' => $item['quantity'],
                        'price' => $item['unit_price'],
                        'total' => $item['total'],
                    ]
                );
            }

            $this->db->commit();
            flash('success', "Factura {$newCode} generada desde cotización.");
            redirect('invoices/view/' . $facId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error: ' . $e->getMessage());
            redirect('quotations/view/' . $cotId);
        }
    }

    /**
     * Listado de Facturas.
     */
    public function invoices(): void
    {
        $docs = $this->db->fetchAll(
            "SELECT d.*, c.name as customer_name 
             FROM documents d LEFT JOIN customers c ON d.customer_id = c.id 
             WHERE d.document_type = 'FAC' ORDER BY d.created_at DESC"
        );

        View::module('Facturacion', 'invoices/index', ['documents' => $docs]);
    }

    /**
     * Ver detalle de Factura.
     */
    public function showInvoice(string $id): void
    {
        $doc = $this->db->fetch(
            "SELECT d.*, c.name as customer_name, c.rnc as customer_rnc, c.address as customer_address 
             FROM documents d LEFT JOIN customers c ON d.customer_id = c.id WHERE d.id = :id",
            ['id' => (int) $id]
        );

        $items = $this->db->fetchAll(
            "SELECT di.*, p.name as product_name FROM document_items di 
             LEFT JOIN products p ON di.product_id = p.id 
             WHERE di.document_id = :id ORDER BY di.line_number",
            ['id' => (int) $id]
        );

        $refDoc = null;
        if ($doc['reference_document_id']) {
            $refDoc = $this->db->fetch(
                "SELECT id, sequence_code FROM documents WHERE id = :id",
                ['id' => $doc['reference_document_id']]
            );
        }

        View::module('Facturacion', 'invoices/show', [
            'doc' => $doc,
            'items' => $items,
            'refDoc' => $refDoc,
        ]);
    }
}
