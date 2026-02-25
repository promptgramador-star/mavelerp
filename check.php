<?php
/**
 * Diagnóstico Final v3 — Test de enrutamiento real
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/App.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diag</title></head><body>";
echo "<h1>Diagnóstico de Enrutamiento</h1>";

// 1. Mostrar qué ve el servidor
echo "<h2>Variables del Servidor</h2>";
echo "<pre>";
echo "REQUEST_URI:  " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME:  " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'N/A') . "\n";
echo "PATH_INFO:    " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "\n";
echo "\$_GET['url']: " . ($_GET['url'] ?? '(vacío)') . "\n";
echo "DOCUMENT_ROOT:" . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "</pre>";

// 2. Mostrar qué genera url()
echo "<h2>URLs Generadas por url()</h2>";
$paths = ['dashboard', 'customers', 'suppliers', 'products', 'quotations', 'invoices', 'users', 'settings'];
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Ruta</th><th>URL Generada</th></tr>";
foreach ($paths as $p) {
    echo "<tr><td>$p</td><td>" . url($p) . "</td></tr>";
}
echo "</table>";

// 3. Simular clic en "customers"
echo "<h2>Test de Links</h2>";
foreach ($paths as $p) {
    echo "<a href='" . url($p) . "'>Ir a $p</a> | ";
}

// 4. Config base_url
$config = require BASE_PATH . '/config/app.php';
echo "<h2>Config app.php</h2>";
echo "base_url: '" . ($config['base_url'] ?? '') . "'";

echo "</body></html>";
