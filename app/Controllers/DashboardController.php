<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Controlador del dashboard principal.
 */
class DashboardController extends Controller
{
    /**
     * Página principal del dashboard.
     */
    public function index(): void
    {
        $user = Auth::user();

        // Estadísticas rápidas
        $stats = [
            'customers' => $this->db->fetch("SELECT COUNT(*) as total FROM customers")['total'] ?? 0,
            'suppliers' => $this->db->fetch("SELECT COUNT(*) as total FROM suppliers")['total'] ?? 0,
            'products' => $this->db->fetch("SELECT COUNT(*) as total FROM products")['total'] ?? 0,
            'documents' => $this->db->fetch("SELECT COUNT(*) as total FROM documents")['total'] ?? 0,
        ];

        // Documentos recientes
        $recentDocs = $this->db->fetchAll(
            "SELECT d.*, c.name as customer_name 
             FROM documents d 
             LEFT JOIN customers c ON d.customer_id = c.id 
             ORDER BY d.created_at DESC 
             LIMIT 10"
        );

        $this->view('dashboard/index', [
            'user' => $user,
            'stats' => $stats,
            'recentDocs' => $recentDocs,
        ]);
    }
}
