<?php
/**
 * Rutas del Módulo Inventario
 * @var \Core\Router $router
 */

$router->get('products', 'Modules\\Inventario\\Controllers\\ProductController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('products/create', 'Modules\\Inventario\\Controllers\\ProductController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('products/store', 'Modules\\Inventario\\Controllers\\ProductController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('products/edit/{id}', 'Modules\\Inventario\\Controllers\\ProductController@edit', ['App\\Middleware\\AuthMiddleware']);
$router->post('products/update/{id}', 'Modules\\Inventario\\Controllers\\ProductController@update', ['App\\Middleware\\AuthMiddleware']);
$router->post('products/delete/{id}', 'Modules\\Inventario\\Controllers\\ProductController@delete', ['App\\Middleware\\AuthMiddleware']);

// Rutas de Importación
$router->get('products/import', 'Modules\\Inventario\\Controllers\\ProductController@importForm', ['App\\Middleware\\AuthMiddleware']);
$router->post('products/import', 'Modules\\Inventario\\Controllers\\ProductController@importProcess', ['App\\Middleware\\AuthMiddleware']);
$router->get('products/template', 'Modules\\Inventario\\Controllers\\ProductController@downloadTemplate', ['App\\Middleware\\AuthMiddleware']);

// API
$router->get('api/products/search', 'Modules\\Inventario\\Controllers\\ProductController@apiSearch', ['App\\Middleware\\AuthMiddleware']);
