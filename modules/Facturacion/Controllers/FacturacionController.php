<?php

namespace Modules\Facturacion\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador de Facturación que maneja el flujo de conversión COT -> FAC.
 */
class FacturacionController extends Controller
{
    /**
     * Convierte una Cotización aprobada en una Factura.
     */
    public function convertToInvoice(string $id): void
    {
        $this->requirePost();
        $this->validateCsrf();

        $cotId = (int) $id;

        // 1. Obtener la COT original
        $cot = $this->db->fetch(
            "SELECT * FROM documents WHERE id = :id AND document_type = 'COT'",
            ['id' => $cotId]
        );

        if (!$cot || $cot['status'] !== 'APPROVED') {
            flash('error', 'Solo se pueden convertir cotizaciones aprobadas.');
            $this->redirect('facturacion/view/' . $cotId);
        }

        try {
            $this->db->beginTransaction();

            // 2. Obtener nueva secuencia para FAC (FAC26-XXXXX)
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

            // 3. Crear nuevo documento FAC
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
                    'date' => date('Y-m-d')
                ]
            );

            // 4. Clonar ítems (con line_number para numeración visual)
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
                        'total' => $item['total']
                    ]
                );
            }

            // 5. Opcional: Bloquear la COT (estado puede ser 'CONVERTED' si se añade al ENUM)
            // El modelo pide trazabilidad COT -> FAC, lo cual ya se cumple con reference_document_id.

            $this->db->commit();
            flash('success', "Factura {$newCode} generada correctamente.");
            $this->redirect('facturacion/invoice/' . $facId);

        } catch (\Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error durante la conversión: ' . $e->getMessage());
            $this->redirect('facturacion/view/' . $cotId);
        }
    }
}
