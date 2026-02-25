<?php
/**
 * Rutas principales de la aplicación ERP.
 * 
 * Se usa $router (inyectado desde App.php) para registrar rutas.
 * Las rutas de módulos se registran en modules/{Modulo}/routes.php
 *
 * @var \Core\Router $router
 */

// =================================================================
// RUTAS PÚBLICAS (sin autenticación)
// =================================================================

$router->get('login', 'App\\Controllers\\AuthController@loginForm');
$router->post('login', 'App\\Controllers\\AuthController@login');
$router->get('logout', 'App\\Controllers\\AuthController@logout');

// =================================================================
// RUTAS PROTEGIDAS (requieren autenticación)
// =================================================================

$router->get('', 'App\\Controllers\\DashboardController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('dashboard', 'App\\Controllers\\DashboardController@index', ['App\\Middleware\\AuthMiddleware']);

// Configuración (solo SUPER_ADMIN y ADMIN)
$router->get('settings', 'App\\Controllers\\SettingsController@index', ['App\\Middleware\\AuthMiddleware']);
$router->post('settings', 'App\\Controllers\\SettingsController@update', ['App\\Middleware\\AuthMiddleware']);

// Gestión de usuarios (solo SUPER_ADMIN)
$router->get('users', 'App\\Controllers\\UserController@index', ['App\\Middleware\\AuthMiddleware']);
$router->get('users/create', 'App\\Controllers\\UserController@create', ['App\\Middleware\\AuthMiddleware']);
$router->post('users/store', 'App\\Controllers\\UserController@store', ['App\\Middleware\\AuthMiddleware']);
$router->get('users/edit/{id}', 'App\\Controllers\\UserController@edit', ['App\\Middleware\\AuthMiddleware']);
$router->post('users/update/{id}', 'App\\Controllers\\UserController@update', ['App\\Middleware\\AuthMiddleware']);

// Módulos (solo SUPER_ADMIN)
$router->get('modules', 'App\\Controllers\\ModuleController@index', ['App\\Middleware\\AuthMiddleware']);
$router->post('modules/toggle/{id}', 'App\\Controllers\\ModuleController@toggle', ['App\\Middleware\\AuthMiddleware']);
