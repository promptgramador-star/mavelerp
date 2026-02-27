<?php

/**
 * Rutas del Módulo: Compras
 */

use Core\Router;
use Modules\Compras\Controllers\ComprasController;

// Órdenes de Compra (PO)
Router::get('purchases', [ComprasController::class, 'index']);
Router::get('purchases/create', [ComprasController::class, 'create']);
Router::post('purchases/store', [ComprasController::class, 'store']);
Router::get('purchases/show/{id}', [ComprasController::class, 'show']);
Router::get('purchases/print/{id}', [ComprasController::class, 'show']); // Usa la misma vista con d-print-none en CSS

// Acciones adicionales de PO (Aprobar, Anular, Convertir a FACTURA WIP)
// Se agregarán a medida que se completen esas features (Fase WIP)
