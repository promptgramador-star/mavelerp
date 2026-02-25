<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

/**
 * Dashboard principal — Diseñado con Dashboard Design Architect Skill.
 * Layout: Z-Pattern | Audiencia: Ejecutivo (estratégico)
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $currentMonth = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');

        // ── NIVEL 1: KPIs Financieros ──────────────────────────
        $salesMonth = $this->db->fetch(
            "SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as count 
             FROM documents 
             WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end",
            ['start' => $currentMonth, 'end' => $currentMonthEnd]
        );

        $pendingQuotations = $this->db->fetch(
            "SELECT COUNT(*) as total FROM documents 
             WHERE document_type = 'COT' AND status = 'DRAFT'"
        );

        $approvedQuotations = $this->db->fetch(
            "SELECT COUNT(*) as total FROM documents 
             WHERE document_type = 'COT' AND status = 'APPROVED'"
        );

        $accountsReceivable = $this->db->fetch(
            "SELECT COALESCE(SUM(total), 0) as total FROM documents 
             WHERE document_type = 'FAC' AND status = 'DRAFT'"
        );

        // Delta: comparar con mes anterior
        $prevMonthStart = date('Y-m-01', strtotime('-1 month'));
        $prevMonthEnd = date('Y-m-t', strtotime('-1 month'));
        $salesPrevMonth = $this->db->fetch(
            "SELECT COALESCE(SUM(total), 0) as total FROM documents 
             WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end",
            ['start' => $prevMonthStart, 'end' => $prevMonthEnd]
        );

        $kpis = [
            'sales_month' => (float) $salesMonth['total'],
            'sales_count' => (int) $salesMonth['count'],
            'sales_prev' => (float) $salesPrevMonth['total'],
            'pending_cot' => (int) $pendingQuotations['total'],
            'approved_cot' => (int) $approvedQuotations['total'],
            'receivable' => (float) $accountsReceivable['total'],
        ];

        // ── NIVEL 2: Tendencia 6 Meses ─────────────────────────
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            $row = $this->db->fetch(
                "SELECT COALESCE(SUM(total), 0) as total FROM documents 
                 WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end",
                ['start' => $monthStart, 'end' => $monthEnd]
            );

            $trendData[] = [
                'label' => $monthLabel,
                'value' => (float) $row['total'],
            ];
        }

        // Top 5 Clientes por facturación
        $topCustomers = $this->db->fetchAll(
            "SELECT c.name, COALESCE(SUM(d.total), 0) as revenue 
             FROM documents d 
             INNER JOIN customers c ON d.customer_id = c.id 
             WHERE d.document_type = 'FAC' 
             GROUP BY c.id, c.name 
             ORDER BY revenue DESC 
             LIMIT 5"
        );

        // ── NIVEL 3: Detalles ──────────────────────────────────
        $recentDocs = $this->db->fetchAll(
            "SELECT d.*, c.name as customer_name 
             FROM documents d 
             LEFT JOIN customers c ON d.customer_id = c.id 
             ORDER BY d.created_at DESC 
             LIMIT 5"
        );

        $lowStock = $this->db->fetchAll(
            "SELECT id, name, sku, stock FROM products 
             WHERE is_service = 0 AND stock <= 5 
             ORDER BY stock ASC 
             LIMIT 8"
        );

        // Contadores generales (para referencia)
        $counts = [
            'customers' => $this->db->fetch("SELECT COUNT(*) as total FROM customers")['total'] ?? 0,
            'products' => $this->db->fetch("SELECT COUNT(*) as total FROM products")['total'] ?? 0,
        ];

        $this->view('dashboard/index', [
            'user' => $user,
            'kpis' => $kpis,
            'trendData' => $trendData,
            'topCustomers' => $topCustomers,
            'recentDocs' => $recentDocs,
            'lowStock' => $lowStock,
            'counts' => $counts,
        ]);
    }
}
