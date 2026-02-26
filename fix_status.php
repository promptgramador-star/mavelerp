<?php
require_once __DIR__ . '/vendor/autoload.php';

use Core\Database;

try {
    $db = Database::getInstance();

    // Convert empty status back to DRAFT for documents that are FAC
    $db->execute("UPDATE documents SET status = 'DRAFT' WHERE document_type = 'FAC' AND (status = '' OR status IS NULL)");

    echo "<h1>✓ Facturas reparadas correctamente</h1>";
    echo "<p>Las facturas con estado en blanco han sido devueltas a DRAFT.</p>";
    echo "<p><a href='/invoices'>Volver a Facturacion</a></p>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
