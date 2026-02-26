<?php
/**
 * Rutas del Módulo Facturación
 * @var \Core\Router $router
 */

// Cotizaciones
$router->get('quotations', 'Modules\\Facturacion\\Controllers\\FacturacionController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('quotations/create', 'Modules\\Facturacion\\Controllers\\FacturacionController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('quotations/store', 'Modules\\Facturacion\\Controllers\\FacturacionController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('quotations/view/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@show', ['App\\Middleware\\AuthMiddleware']);
$router->post('quotations/approve/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@approve', ['App\\Middleware\\AuthMiddleware']);
$router->post('quotations/convert/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@convertToInvoice', ['App\\Middleware\\AuthMiddleware']);

// Facturas (vista listado)
$router->get('invoices', 'Modules\\Facturacion\\Controllers\\FacturacionController@invoices', ['App\\Middleware\\AuthMiddleware']);
$router->get('invoices/view/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@showInvoice', ['App\\Middleware\\AuthMiddleware']);
$router->post('invoices/pay/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@markPaid', ['App\\Middleware\\AuthMiddleware']);
$router->post('invoices/cancel/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@cancelInvoice', ['App\\Middleware\\AuthMiddleware']);
$router->get('invoices/print/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@printInvoice', ['App\\Middleware\\AuthMiddleware']);
$router->post('quotations/cancel/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@cancelQuotation', ['App\\Middleware\\AuthMiddleware']);
$router->get('quotations/print/{id}', 'Modules\\Facturacion\\Controllers\\FacturacionController@printQuotation', ['App\\Middleware\\AuthMiddleware']);
