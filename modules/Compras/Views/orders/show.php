<?php \Core\View::startSection('content'); ?>
<?php $settings = get_settings();
$curr = $doc['currency'] ?? 'DOP'; ?>

<!-- Screen UI Header -->
<div class="page-header d-print-none" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Orden de Compra:
            <?= e($doc['sequence_code']) ?>
        </h1>
        <p><a href="<?= url('purchases') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Órdenes</a>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if ($doc['status'] === 'DRAFT'): ?>
            <form method="POST" action="<?= url('purchases/approve/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--success);color:#fff;"
                    onclick="return confirm('¿Aprobar esta orden de compra?')">✅ Aprobar</button>
            </form>
            <form method="POST" action="<?= url('purchases/cancel/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;"
                    onclick="return confirm('¿Anular esta orden de compra?')">❌ Anular</button>
            </form>
        <?php endif; ?>
        <?php if ($doc['status'] === 'APPROVED' && !$hasInvoice): ?>
            <form method="POST" action="<?= url('purchases/convert/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <div
                    style="display:inline-block; background:#f1f5f9; padding:4px 8px; border-radius:4px; margin-right:5px;">
                    <label style="font-size:12px; font-weight:600; color:#475569;">Retención %:</label>
                    <input type="number" name="retention_percentage" value="0" step="0.01" min="0" max="100"
                        style="width:60px; border:1px solid #cbd5e1; border-radius:4px; padding:4px;">
                </div>
                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('¿Generar factura desde esta orden de compra?')">📄 Convertir a Factura</button>
            </form>
            <form method="POST" action="<?= url('purchases/cancel/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;"
                    onclick="return confirm('¿Anular esta orden de compra?')">❌ Anular</button>
            </form>
        <?php endif; ?>
        <?php if ($hasInvoice): ?>
            <a href="<?= url('invoices/view/' . $hasInvoice['id']) ?>" class="btn"
                style="background:#fef3c7;color:#92400e;">
                Ver Factura <?= e($hasInvoice['sequence_code']) ?>
            </a>
        <?php endif; ?>
        <a href="<?= url('purchases/print/' . $doc['id']) ?>" target="_blank" class="btn"
            style="background:#4b5563;color:#fff;">🖨️ Imprimir</a>
        <a href="<?= url('purchases') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
    </div>
</div>

<!-- ==================== PRINTABLE DOCUMENT ==================== -->
<div id="printable-document">
    <!-- Company Header -->
    <div class="doc-header">
        <div class="doc-header-left">
            <?php if (!empty($settings['logo'])): ?>
                <img src="<?= url($settings['logo']) ?>" alt="Logo" class="doc-logo">
            <?php endif; ?>
        </div>
        <div class="doc-header-right">
            <h2 class="doc-company"><?= e($settings['company_name'] ?? '') ?></h2>
            <?php if (!empty($settings['rnc'])): ?>
                <p><?= e($settings['rnc']) ?></p>
            <?php endif; ?>
            <?php if (!empty($settings['address'])): ?>
                <p><?= e($settings['address']) ?></p>
            <?php endif; ?>
            <?php if (!empty($settings['phone'])): ?>
                <p><?= e($settings['phone']) ?><?= !empty($settings['email']) ? ' · ' . e($settings['email']) : '' ?></p>
            <?php endif; ?>
        </div>
    </div>

    <hr class="doc-divider">

    <!-- Document Title -->
    <h3 class="doc-title">Orden de Compra: <?= e($doc['sequence_code']) ?></h3>

    <!-- Client & Date Info -->
    <div class="doc-info-grid">
        <div style="flex:1;">
            <h3 style="color:var(--secondary);font-size:12px;text-transform:uppercase;margin-bottom:8px;">Datos del
                Proveedor</h3>
            <p style="margin-bottom:4px;font-weight:600;">
                <?= e($doc['supplier_name'] ?? '') ?>
            </p>
            <p style="color:var(--secondary);font-size:14px;margin-bottom:4px;">RNC:
                <?= e($doc['supplier_rnc'] ?? 'N/D') ?>
            </p>
            <p style="color:var(--secondary);font-size:14px;margin-bottom:4px;">
                <?= e($doc['supplier_address'] ?? '') ?>
            </p>
            <p style="color:var(--secondary);font-size:14px;">
                <?= e($doc['supplier_phone'] ?? '') ?> |
                <?= e($doc['supplier_email'] ?? '') ?>
            </p>
        </div>
        <div style="flex:1;">
            <h3 style="color:var(--secondary);font-size:12px;text-transform:uppercase;margin-bottom:8px;">Detalles de la
                Orden
            </h3>
            <table style="width:100%;font-size:14px;">
                <tr>
                    <td style="color:var(--secondary);padding:4px 0;">Orden N°:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= e($doc['sequence_code']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--secondary);padding:4px 0;">Fecha de Emisión:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= date('d-m-Y', strtotime($doc['issue_date'] ?? 'now')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--secondary);padding:4px 0;">Fecha de Vencimiento:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= date('d-m-Y', strtotime($doc['due_date'] ?? 'now')) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--secondary);padding:4px 0;">Moneda:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= e($curr) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color:var(--secondary);padding:4px 0;">Estado:</td>
                    <td style="text-align:right;font-weight:600;">
                        <span class="status status-<?= strtolower($doc['status']) ?>"><?= e($doc['status']) ?></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <table class="doc-items-table">
        <thead>
            <tr>
                <th class="doc-col-desc">Referencia - Descripción</th>
                <th class="doc-col-num">Cantidad</th>
                <th class="doc-col-num">Precio</th>
                <th class="doc-col-num">Dscto.</th>
                <th class="doc-col-num">Neto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['description']) ?></td>
                    <td class="doc-col-num"><?= number_format((float) $item['quantity'], 2) ?></td>
                    <td class="doc-col-num"><?= number_format((float) $item['unit_price'], 2) ?></td>
                    <td class="doc-col-num">
                        <?= ($item['discount_percentage'] ?? 0) > 0 ? number_format((float) $item['discount_percentage'], 2) . '%' : '—' ?>
                    </td>
                    <td class="doc-col-num" style="font-weight:600;"><?= number_format((float) $item['total'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totals -->
    <div class="doc-totals-section">
        <table class="doc-totals-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Divisa</th>
                    <th style="text-align:right;">Neto</th>
                    <?php if (($doc['discount_total'] ?? 0) > 0): ?>
                        <th style="text-align:right;">Descuento</th>
                    <?php endif; ?>
                    <?php if (($doc['tax'] ?? 0) > 0): ?>
                        <th style="text-align:right;">ITBIS (18%)</th>
                    <?php endif; ?>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= e($curr) ?></td>
                    <td style="text-align:right;"><?= number_format((float) ($doc['subtotal'] ?? 0), 2) ?></td>
                    <?php if (($doc['discount_total'] ?? 0) > 0): ?>
                        <td style="text-align:right;color:#dc2626;">-<?= number_format((float) $doc['discount_total'], 2) ?>
                        </td>
                    <?php endif; ?>
                    <?php if (($doc['tax'] ?? 0) > 0): ?>
                        <td style="text-align:right;"><?= number_format((float) $doc['tax'], 2) ?></td>
                    <?php endif; ?>
                    <td style="text-align:right;font-weight:700;"><?= number_format((float) $doc['total'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Payment Info -->
    <?php if (!empty($settings['bank_accounts'])): ?>
        <div class="doc-payment-section">
            <table class="doc-payment-table">
                <thead>
                    <tr>
                        <th style="text-align:left;">Forma de pago</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="white-space:pre-wrap;"><?= e($settings['bank_accounts']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Document Styles -->
<style>
    #printable-document {
        background: #fff;
        max-width: 800px;
        margin: 20px auto;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 13px;
        color: #1f2937;
    }

    .doc-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .doc-header-left {
        flex: 0 0 auto;
    }

    .doc-header-right {
        text-align: right;
        font-size: 12px;
        color: #4b5563;
        line-height: 1.6;
    }

    .doc-logo {
        max-height: 70px;
        max-width: 180px;
    }

    .doc-company {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 4px 0;
    }

    .doc-divider {
        border: none;
        border-top: 2px solid #e5e7eb;
        margin: 15px 0;
    }

    .doc-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 15px 0;
    }

    .doc-info-grid {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .doc-info-table {
        border-collapse: collapse;
        font-size: 13px;
    }

    .doc-info-table td {
        padding: 2px 8px 2px 0;
        vertical-align: top;
    }

    .doc-info-label {
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    .doc-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .doc-items-table thead {
        background: #f0f4f8;
    }

    .doc-items-table th {
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #d1d5db;
        text-align: left;
    }

    .doc-items-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .doc-col-desc {
        width: auto;
    }

    .doc-col-num {
        text-align: right !important;
        white-space: nowrap;
    }

    .doc-totals-section {
        margin-bottom: 20px;
    }

    .doc-totals-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-totals-table thead {
        background: #1e40af;
        color: #fff;
    }

    .doc-totals-table th {
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .doc-totals-table td {
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
    }

    .doc-payment-section {
        margin-top: 10px;
    }

    .doc-payment-table {
        width: 100%;
        border-collapse: collapse;
    }

    .doc-payment-table thead {
        background: #1e40af;
        color: #fff;
    }

    .doc-payment-table th {
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .doc-payment-table td {
        padding: 8px 10px;
        font-size: 12px;
        color: #4b5563;
    }

    @media print {
        body {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .no-print {
            display: none !important;
        }

        .sidebar,
        nav,
        .page-header.no-print {
            display: none !important;
        }

        #printable-document {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 20px !important;
            max-width: 100% !important;
            border-radius: 0 !important;
        }
    }
</style>

<?php \Core\View::endSection(); ?>