<?php \Core\View::startSection('content'); ?>
<?php $settings = get_settings();
$curr = $doc['currency'] ?? 'DOP'; ?>

<!-- Screen UI Header -->
<div class="page-header no-print" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1><?= e($doc['sequence_code']) ?></h1>
        <p>Factura — <span class="status status-<?= strtolower($doc['status']) ?>"><?= e($doc['status']) ?></span>
            <?php if ($refDoc): ?>
                — Desde <a href="<?= url('quotations/view/' . $refDoc['id']) ?>"
                    style="color:var(--primary);"><?= e($refDoc['sequence_code']) ?></a>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if ($doc['status'] === 'DRAFT'): ?>
            <form method="POST" action="<?= url('invoices/pay/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--success);color:#fff;"
                    onclick="return confirm('¿Marcar esta factura como pagada?')">💰 Marcar Pagada</button>
            </form>
        <?php endif; ?>
        <?php if ($doc['status'] !== 'PAID' && $doc['status'] !== 'CANCELLED'): ?>
            <form method="POST" action="<?= url('invoices/cancel/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;"
                    onclick="return confirm('¿Anular esta factura? Esta acción no se puede revertir.')">❌ Anular</button>
            </form>
        <?php endif; ?>
        <a href="<?= url('invoices/print/' . $doc['id']) ?>" target="_blank" class="btn"
            style="background:#4b5563;color:#fff;">🖨️ Imprimir</a>
        <a href="<?= url('invoices') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
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
    <h3 class="doc-title">Factura: <?= e($doc['sequence_code']) ?></h3>

    <!-- Client & Date Info -->
    <div class="doc-info-grid">
        <div>
            <table class="doc-info-table">
                <tr>
                    <td class="doc-info-label">Cliente:</td>
                    <td><?= e($doc['customer_name'] ?? '—') ?></td>
                </tr>
                <?php if (!empty($doc['customer_address'])): ?>
                    <tr>
                        <td class="doc-info-label">Dirección:</td>
                        <td><?= e($doc['customer_address']) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($doc['customer_rnc'])): ?>
                    <tr>
                        <td class="doc-info-label">RNC:</td>
                        <td><?= e($doc['customer_rnc']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div style="text-align:right;">
            <table class="doc-info-table" style="margin-left:auto;">
                <tr>
                    <td class="doc-info-label">Fecha:</td>
                    <td><?= date('d-m-Y', strtotime($doc['issue_date'] ?? 'now')) ?></td>
                </tr>
                <tr>
                    <td class="doc-info-label">Moneda:</td>
                    <td><?= e($curr) ?></td>
                </tr>
                <tr>
                    <td class="doc-info-label">Estado:</td>
                    <td><span class="status status-<?= strtolower($doc['status']) ?>"><?= e($doc['status']) ?></span>
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
    /* === Print Document Styles === */
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

    /* === Print Media === */
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