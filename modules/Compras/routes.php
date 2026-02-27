<?php

/**
 * Rutas del Módulo: Compras
 * 
 * @var \Core\Router $router
 */

// Órdenes de Compra (PO)
$router->get('purchases', 'Modules\\Compras\\Controllers\\ComprasController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('purchases/create', 'Modules\\Compras\\Controllers\\ComprasController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('purchases/store', 'Modules\\Compras\\Controllers\\ComprasController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('purchases/show/{id}', 'Modules\\Compras\\Controllers\\ComprasController@show', ['App\\Middleware\\AuthMiddleware']);
$router->get('purchases/print/{id}', 'Modules\\Compras\\Controllers\\ComprasController@printOrder', ['App\\Middleware\\AuthMiddleware']);
$router->post('purchases/approve/{id}', 'Modules\\Compras\\Controllers\\ComprasController@approve', ['App\\Middleware\\AuthMiddleware']);
$router->post('purchases/paid/{id}', 'Modules\\Compras\\Controllers\\ComprasController@markPaid', ['App\\Middleware\\AuthMiddleware']);
$router->post('purchases/cancel/{id}', 'Modules\\Compras\\Controllers\\ComprasController@cancel', ['App\\Middleware\\AuthMiddleware']);
