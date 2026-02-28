<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Dashboard</h1>
        <p>Bienvenido, <?= e(\Core\Session::get('user_name', '')) ?></p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= url('quotations/create') ?>" class="btn btn-primary">+ Nueva Cotización</a>
        <a href="<?= url('customers/create') ?>" class="btn" style="background:var(--success);color:#fff;">+ Nuevo
            Cliente</a>
    </div>
</div>

<!-- ═══════════════ NIVEL 1: KPIs Financieros ═══════════════ -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">

    <div class="stat-card" style="border-left: 4px solid var(--primary);">
        <div class="stat-icon" style="background:#eff6ff;">💰</div>
        <div class="stat-info">
            <h3 style="font-size:18px;margin-bottom:2px;">
                DOP <?= money($kpis['sales_month']['DOP'] ?? 0) ?><br>
                <span style="font-size:14px;color:var(--secondary);">USD
                    <?= money($kpis['sales_month']['USD'] ?? 0) ?></span>
            </h3>
            <p style="margin-top:2px;">Ventas del Mes</p>
            <?php
            $prevDop = $kpis['sales_prev']['DOP'] ?? 0;
            $currDop = $kpis['sales_month']['DOP'] ?? 0;
            $delta = $prevDop > 0
                ? round((($currDop - $prevDop) / $prevDop) * 100, 1)
                : 0;
            $deltaColor = $delta >= 0 ? 'var(--success)' : 'var(--danger)';
            $deltaIcon = $delta >= 0 ? '↑' : '↓';
            ?>
            <span style="font-size:12px;color:<?= $deltaColor ?>;font-weight:600;">
                <?= $deltaIcon ?> <?= abs($delta) ?>% (DOP) vs anterior
            </span>
        </div>
    </div>

    <!-- KPI: Cotizaciones Pendientes -->
    <div class="stat-card" style="border-left: 4px solid var(--warning);">
        <div class="stat-icon" style="background:#fefce8;">📋</div>
        <div class="stat-info">
            <h3><?= (int) $kpis['pending_cot'] ?></h3>
            <p>Cotizaciones Borrador</p>
            <span style="font-size:12px;color:var(--success);font-weight:500;">
                <?= (int) $kpis['approved_cot'] ?> aprobadas
            </span>
        </div>
    </div>

    <!-- KPI: Facturas Emitidas -->
    <div class="stat-card" style="border-left: 4px solid var(--success);">
        <div class="stat-icon" style="background:#f0fdf4;">🧾</div>
        <div class="stat-info">
            <h3><?= (int) $kpis['sales_count'] ?></h3>
            <p>Facturas este Mes</p>
            <span style="font-size:12px;color:var(--secondary);">
                <?= (int) $counts['customers'] ?> clientes · <?= (int) $counts['products'] ?> productos
            </span>
        </div>
    </div>

    <!-- KPI: Cuentas por Cobrar -->
    <div class="stat-card" style="border-left: 4px solid var(--danger);">
        <div class="stat-icon" style="background:#fef2f2;">📊</div>
        <div class="stat-info">
            <h3 style="font-size:18px;margin-bottom:2px;">
                DOP <?= money($kpis['receivable']['DOP'] ?? 0) ?><br>
                <span style="font-size:14px;color:var(--secondary);">USD
                    <?= money($kpis['receivable']['USD'] ?? 0) ?></span>
            </h3>
            <p style="margin-top:2px;">Cuentas por Cobrar</p>
            <span style="font-size:12px;color:var(--secondary);">Facturas pendientes</span>
        </div>
    </div>

</div>

<!-- ═══════════════ NIVEL 2: Gráficos ═══════════════ -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;">

    <!-- Tendencia de Ventas 6 Meses -->
    <div class="card">
        <div class="card-header">
            <h2>Tendencia de Ventas</h2>
            <span style="font-size:13px;color:var(--secondary);">Últimos 6 meses</span>
        </div>
        <div class="card-body" style="padding:16px 24px;">
            <canvas id="trendChart" height="220"></canvas>
        </div>
    </div>

    <!-- Top 5 Clientes -->
    <div class="card">
        <div class="card-header">
            <h2>Top Clientes</h2>
            <span style="font-size:13px;color:var(--secondary);">Por facturación</span>
        </div>
        <div class="card-body" style="padding:16px 24px;">
            <?php if (empty($topCustomers)): ?>
                <p style="color:var(--secondary);text-align:center;padding:40px 0;">Sin datos aún</p>
            <?php else: ?>
                <canvas id="topChart" height="220"></canvas>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ═══════════════ NIVEL 3: Detalles ═══════════════ -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

    <!-- Documentos Recientes -->
    <div class="card">
        <div class="card-header">
            <h2>Documentos Recientes</h2>
            <a href="<?= url('quotations') ?>" style="font-size:13px;color:var(--primary);">Ver todos →</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentDocs)): ?>
                <p style="color:var(--secondary);text-align:center;padding:30px;">No hay documentos aún.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Cliente</th>
                            <th style="text-align:right;">Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentDocs as $doc): ?>
                            <tr>
                                <td><strong><?= e($doc['sequence_code'] ?? '-') ?></strong></td>
                                <td><span
                                        class="badge badge-<?= strtolower($doc['document_type'] ?? '') ?>"><?= e($doc['document_type'] ?? '') ?></span>
                                </td>
                                <td><?= e($doc['customer_name'] ?? '—') ?></td>
                                <td style="text-align:right;"><?= money((float) ($doc['total'] ?? 0)) ?></td>
                                <td><span
                                        class="status status-<?= strtolower($doc['status'] ?? 'draft') ?>"><?= e($doc['status'] ?? '') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertas de Stock Bajo -->
    <div class="card">
        <div class="card-header">
            <h2>⚠️ Stock Bajo</h2>
            <a href="<?= url('products') ?>" style="font-size:13px;color:var(--primary);">Ver inventario →</a>
        </div>
        <div class="card-body">
            <?php if (empty($lowStock)): ?>
                <div style="text-align:center;padding:30px;color:var(--success);">
                    <p style="font-size:24px;">✅</p>
                    <p>Todo el inventario está en orden</p>
                </div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($lowStock as $p): ?>
                        <div
                            style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fef2f2;border-radius:8px;">
                            <div>
                                <strong style="font-size:14px;"><?= e($p['name']) ?></strong>
                                <span
                                    style="font-size:12px;color:var(--secondary);display:block;"><?= e($p['sku'] ?? '') ?></span>
                            </div>
                            <span
                                style="font-weight:700;color:<?= ((float) $p['stock'] <= 0) ? 'var(--danger)' : 'var(--warning)' ?>;font-size:16px;">
                                <?= number_format((float) $p['stock'], 0) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    // ── Tendencia de Ventas (Line Chart) ──
    const trendLabels = <?= json_encode($trendData['labels']) ?>;
    const trendDOP = <?= json_encode($trendData['dop']) ?>;
    const trendUSD = <?= json_encode($trendData['usd']) ?>;

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'DOP',
                    data: trendDOP,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#2563eb',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'USD',
                    data: trendUSD,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    fill: false,
                    tension: 0.4,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ' ' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v),
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // ── Top Clientes (Horizontal Bar Chart) ──
    <?php if (!empty($topCustomers)): ?>
        const topLabelsRaw = <?= json_encode(array_map(fn($c) => $c['name'] . ' (' . ($c['currency'] ?: 'DOP') . ')', $topCustomers)) ?>;
        const topValues = <?= json_encode(array_map(fn($c) => (float) $c['revenue'], $topCustomers)) ?>;

        new Chart(document.getElementById('topChart'), {
            type: 'bar',
            data: {
                labels: topLabelsRaw,
                datasets: [{
                    label: 'Facturación',
                    data: topValues,
                    backgroundColor: ['#2563eb', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
                    borderRadius: 6,
                    barThickness: 22,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'DOP ' + ctx.parsed.x.toLocaleString('en-US', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v),
                            font: { size: 11 }
                        },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    y: {
                        ticks: { font: { size: 12 } },
                        grid: { display: false }
                    }
                }
            }
        });
    <?php endif; ?>
</script>

<?php \Core\View::endSection(); ?>