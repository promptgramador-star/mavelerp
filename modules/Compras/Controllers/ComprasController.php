<?php

namespace Modules\Compras\Controllers;

use Core\Controller;
use Core\View;

class ComprasController extends Controller
{
    /**
     * Listado de Órdenes de Compra.
     */
    public function index(): void
    {
        $docs = $this->db->fetchAll(
            "SELECT d.*, s.name as supplier_name 
             FROM documents d 
             LEFT JOIN suppliers s ON d.supplier_id = s.id 
             WHERE d.document_type = 'ORD' 
             ORDER BY d.created_at DESC"
        );

        View::module('Compras', 'orders/index', ['documents' => $docs]);
    }

    /**
     * Formulario de nueva Orden de Compra.
     */
    public function create(): void
    {
        $suppliers = $this->db->fetchAll("SELECT id, name FROM suppliers ORDER BY name");
        $products = $this->db->fetchAll("SELECT id, name, sku, cost, is_taxable FROM products ORDER BY name");

        View::module('Compras', 'orders/create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    /**
     * Guardar Orden de Compra con ítems.
     */
    public function store(): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $supplierId = (int) $this->input('supplier_id', 0);
        $issueDate = $this->input('issue_date', date('Y-m-d'));

        // Obtener ítems del formulario
        $descriptions = $_POST['item_description'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $prices = $_POST['item_price'] ?? []; // Usaremos 'cost' del producto como price de compra

        if (empty($descriptions) || $supplierId === 0) {
            flash('error', 'Selecciona un proveedor y agrega al menos un ítem.');
            redirect('purchases/create');
        }

        try {
            $this->db->beginTransaction();

            // Generar secuencia ORD (PO)
            $year = date('y');
            $this->db->execute(
                "UPDATE document_sequences SET current_number = current_number + 1 
                 WHERE document_type = 'ORD' AND year = :year",
                ['year' => $year]
            );

            $seq = $this->db->fetch(
                "SELECT prefix, current_number FROM document_sequences 
                 WHERE document_type = 'ORD' AND year = :year",
                ['year' => $year]
            );

            $code = $seq['prefix'] . $year . '-' . str_pad((string) $seq['current_number'], 5, '0', STR_PAD_LEFT);

            // Calcular totales
            $subtotal = 0;
            $taxableSubtotal = 0;
            $items = [];

            $currency = $this->input('currency', 'DOP');
            $taxableFlags = $_POST['item_is_taxable'] ?? [];
            $productIds = $_POST['item_product_id'] ?? [];

            foreach ($descriptions as $i => $desc) {
                if (empty(trim($desc)))
                    continue;

                $qty = (float) ($quantities[$i] ?? 0);
                $price = (float) ($prices[$i] ?? 0);
                $isTaxable = (bool) ($taxableFlags[$i] ?? 1);
                $prodId = (int) ($productIds[$i] ?? 0);
                $prodId = $prodId > 0 ? $prodId : null;

                $lineTotal = $qty * $price;

                $subtotal += $lineTotal;
                if ($isTaxable) {
                    $taxableSubtotal += $lineTotal;
                }

                $items[] = [
                    'product_id' => $prodId,
                    'description' => trim($desc),
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'is_taxable' => $isTaxable ? 1 : 0,
                    'total' => $lineTotal
                ];
            }

            $taxTotal = $taxableSubtotal * 0.18; // ITBIS
            $grandTotal = $subtotal + $taxTotal;

            // Insertar Cabecera de PO
            $docId = $this->db->insert(
                "INSERT INTO documents 
                (document_type, sequence_code, supplier_id, status, currency, subtotal, tax, total, issue_date, created_at) 
                VALUES 
                ('ORD', :code, :sup_id, 'DRAFT', :curr, :sub, :tax, :total, :issue, NOW())",
                [
                    'code' => $code,
                    'sup_id' => $supplierId,
                    'curr' => $currency,
                    'sub' => $subtotal,
                    'tax' => $taxTotal,
                    'total' => $grandTotal,
                    'issue' => $issueDate
                ]
            );

            // Insertar Ítems
            foreach ($items as $idx => $item) {
                $this->db->insert(
                    "INSERT INTO document_items 
                    (document_id, line_number, product_id, description, quantity, unit_price, is_taxable, total) 
                    VALUES 
                    (:doc_id, :line, :prod_id, :desc, :qty, :price, :taxable, :total)",
                    [
                        'doc_id' => $docId,
                        'line' => $idx + 1,
                        'prod_id' => $item['product_id'],
                        'desc' => $item['description'],
                        'qty' => $item['quantity'],
                        'price' => $item['unit_price'],
                        'taxable' => $item['is_taxable'],
                        'total' => $item['total']
                    ]
                );
            }

            $this->db->commit();
            flash('success', "Orden de Compra {$code} registrada con éxito.");
            redirect('purchases');

        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al crear la orden: ' . $e->getMessage());
            redirect('purchases/create');
        }
    }

    /**
     * Muestra una Orden de Compra para imprimir.
     */
    public function show(string $id): void
    {
        $doc = $this->db->fetch(
            "SELECT d.*, s.name as supplier_name, s.rnc, s.address, s.phone, s.email 
             FROM documents d 
             LEFT JOIN suppliers s ON d.supplier_id = s.id 
             WHERE d.id = :id AND d.document_type = 'ORD'",
            ['id' => (int) $id]
        );

        if (!$doc) {
            flash('error', 'Orden de Compra no encontrada.');
            redirect('purchases');
        }

        $items = $this->db->fetchAll(
            "SELECT i.*, p.sku 
             FROM document_items i 
             LEFT JOIN products p ON i.product_id = p.id 
             WHERE i.document_id = :id 
             ORDER BY i.line_number ASC",
            ['id' => (int) $id]
        );

        View::module('Compras', 'orders/show', [
            'document' => $doc,
            'items' => $items,
            'company' => get_settings()
        ]);
    }
}
