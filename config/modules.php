<?php
/**
 * Registro de módulos disponibles en el sistema.
 * 
 * Cada módulo se define con su directorio base y metadatos.
 * La activación real se verifica contra la tabla module_license en la DB.
 */

return [
    'Facturacion' => [
        'name' => 'Facturación',
        'description' => 'Gestión de cotizaciones, facturas y conduces',
        'path' => BASE_PATH . '/modules/Facturacion',
        'is_premium' => false,
    ],
    'Contabilidad' => [
        'name' => 'Contabilidad',
        'description' => 'Plan contable y asientos contables',
        'path' => BASE_PATH . '/modules/Contabilidad',
        'is_premium' => true,
    ],
    'Inventario' => [
        'name' => 'Inventario',
        'description' => 'Gestión de productos y stock',
        'path' => BASE_PATH . '/modules/Inventario',
        'is_premium' => false,
    ],
    'CRM' => [
        'name' => 'Clientes y Proveedores',
        'description' => 'Gestión de clientes y proveedores',
        'path' => BASE_PATH . '/modules/CRM',
        'is_premium' => false,
    ],
];
