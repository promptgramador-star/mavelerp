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
        $salesMonthRecords = $this->db->fetchAll(
            "SELECT currency, COALESCE(SUM(total), 0) as total, COUNT(*) as count 
             FROM documents 
             WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end
             GROUP BY currency",
            ['start' => $currentMonth, 'end' => $currentMonthEnd]
        );
        $salesByCurrency = ['DOP' => 0, 'USD' => 0];
        $salesCount = 0;
        foreach ($salesMonthRecords as $row) {
            $curr = $row['currency'] ?: 'DOP';
            $salesByCurrency[$curr] = ($salesByCurrency[$curr] ?? 0) + (float) $row['total'];
            $salesCount += (int) $row['count'];
        }

        $pendingQuotations = $this->db->fetch(
            "SELECT COUNT(*) as total FROM documents 
             WHERE document_type = 'COT' AND status = 'DRAFT'"
        );

        $approvedQuotations = $this->db->fetch(
            "SELECT COUNT(*) as total FROM documents 
             WHERE document_type = 'COT' AND status = 'APPROVED'"
        );

        $receivableRecords = $this->db->fetchAll(
            "SELECT currency, COALESCE(SUM(total), 0) as total FROM documents 
             WHERE document_type = 'FAC' AND status = 'DRAFT'
             GROUP BY currency"
        );
        $receivableByCurrency = ['DOP' => 0, 'USD' => 0];
        foreach ($receivableRecords as $row) {
            $curr = $row['currency'] ?: 'DOP';
            $receivableByCurrency[$curr] = ($receivableByCurrency[$curr] ?? 0) + (float) $row['total'];
        }

        // Delta: comparar con mes anterior
        $prevMonthStart = date('Y-m-01', strtotime('-1 month'));
        $prevMonthEnd = date('Y-m-t', strtotime('-1 month'));
        $salesPrevRecords = $this->db->fetchAll(
            "SELECT currency, COALESCE(SUM(total), 0) as total FROM documents 
             WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end
             GROUP BY currency",
            ['start' => $prevMonthStart, 'end' => $prevMonthEnd]
        );
        $salesPrevByCurrency = ['DOP' => 0, 'USD' => 0];
        foreach ($salesPrevRecords as $row) {
            $curr = $row['currency'] ?: 'DOP';
            $salesPrevByCurrency[$curr] = ($salesPrevByCurrency[$curr] ?? 0) + (float) $row['total'];
        }

        $kpis = [
            'sales_month' => $salesByCurrency,
            'sales_count' => $salesCount,
            'sales_prev' => $salesPrevByCurrency,
            'pending_cot' => (int) $pendingQuotations['total'],
            'approved_cot' => (int) $approvedQuotations['total'],
            'receivable' => $receivableByCurrency,
        ];

        // ── NIVEL 2: Tendencia 6 Meses ─────────────────────────
        $trendData = [
            'labels' => [],
            'dop' => [],
            'usd' => []
        ];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            $monthLabel = date('M Y', strtotime("-{$i} months"));

            $trendData['labels'][] = $monthLabel;

            $records = $this->db->fetchAll(
                "SELECT currency, COALESCE(SUM(total), 0) as total FROM documents 
                 WHERE document_type = 'FAC' AND issue_date BETWEEN :start AND :end
                 GROUP BY currency",
                ['start' => $monthStart, 'end' => $monthEnd]
            );

            $dopTotal = 0;
            $usdTotal = 0;
            foreach ($records as $row) {
                if (($row['currency'] ?? 'DOP') === 'USD') {
                    $usdTotal += (float) $row['total'];
                } else {
                    $dopTotal += (float) $row['total'];
                }
            }
            $trendData['dop'][] = $dopTotal;
            $trendData['usd'][] = $usdTotal;
        }

        // Top 5 Clientes por facturación
        $topCustomers = $this->db->fetchAll(
            "SELECT c.name, d.currency, COALESCE(SUM(d.total), 0) as revenue 
             FROM documents d 
             INNER JOIN customers c ON d.customer_id = c.id 
             WHERE d.document_type = 'FAC' 
             GROUP BY c.id, c.name, d.currency 
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
            "SELECT id, name, sku, stock, low_stock_threshold FROM products 
             WHERE is_service = 0 AND is_own_stock = 1 AND stock <= low_stock_threshold 
             ORDER BY (stock - low_stock_threshold) ASC 
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
