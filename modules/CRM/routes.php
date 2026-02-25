<?php
/**
 * Rutas del Módulo CRM — Clientes y Proveedores
 * @var \Core\Router $router
 */

// Clientes
$router->get('customers', 'Modules\\CRM\\Controllers\\CustomerController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('customers/create', 'Modules\\CRM\\Controllers\\CustomerController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('customers/store', 'Modules\\CRM\\Controllers\\CustomerController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('customers/edit/{id}', 'Modules\\CRM\\Controllers\\CustomerController@edit', ['App\\Middleware\\AuthMiddleware']);
$router->post('customers/update/{id}', 'Modules\\CRM\\Controllers\\CustomerController@update', ['App\\Middleware\\AuthMiddleware']);
$router->post('customers/delete/{id}', 'Modules\\CRM\\Controllers\\CustomerController@delete', ['App\\Middleware\\AuthMiddleware']);

// Proveedores
$router->get('suppliers', 'Modules\\CRM\\Controllers\\SupplierController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('suppliers/create', 'Modules\\CRM\\Controllers\\SupplierController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('suppliers/store', 'Modules\\CRM\\Controllers\\SupplierController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('suppliers/edit/{id}', 'Modules\\CRM\\Controllers\\SupplierController@edit', ['App\\Middleware\\AuthMiddleware']);
$router->post('suppliers/update/{id}', 'Modules\\CRM\\Controllers\\SupplierController@update', ['App\\Middleware\\AuthMiddleware']);
$router->post('suppliers/delete/{id}', 'Modules\\CRM\\Controllers\\SupplierController@delete', ['App\\Middleware\\AuthMiddleware']);
