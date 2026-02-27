<?php
/* Standalone print view — no layout, self-contained HTML */
$settings = get_settings();
$curr = $doc['currency'] ?? 'DOP';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Factura <?= e($doc['sequence_code']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 30px 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .page {
            width: 210mm;
            min-height: 280mm;
            background: #fff;
            padding: 25mm 20mm 20mm 20mm;
            box-shadow: 0 1px 12px rgba(0, 0, 0, .12);
        }

        /* Header */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .doc-header-right {
            text-align: right;
            font-size: 12px;
            color: #4b5563;
            line-height: 1.7;
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
            border-top: 2px solid #d1d5db;
            margin: 12px 0;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 15px 0;
        }

        /* Info grid */
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

        /* Items table */
        .doc-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .doc-items-table thead {
            background: #f0f4f8 !important;
        }

        .doc-items-table th {
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
            text-align: left;
        }

        .doc-items-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .num {
            text-align: right !important;
            white-space: nowrap;
        }

        /* Totals & Payment */
        .totals-table,
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .totals-table thead,
        .payment-table thead {
            background: #1e3a5f !important;
            color: #fff !important;
        }

        .totals-table th,
        .payment-table th {
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 600;
            color: #fff !important;
        }

        .totals-table td,
        .payment-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        /* Print media */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }

            @page {
                margin: 12mm 15mm;
                size: A4 portrait;
            }

            /* Force all backgrounds to render in print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .totals-table thead,
            .payment-table thead {
                background: #1e3a5f !important;
            }

            .totals-table th,
            .payment-table th {
                color: #fff !important;
            }

            .doc-items-table thead {
                background: #f0f4f8 !important;
            }
        }
    </style>
</head>

<body>
    <div class="page">

        <!-- Company Header -->
        <div class="doc-header">
            <div>
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
                    <p><?= e($settings['phone']) ?><?= !empty($settings['email']) ? ' · ' . e($settings['email']) : '' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="doc-divider">

        <h3 class="doc-title">Factura: <?= e($doc['sequence_code']) ?></h3>

        <!-- Client & Date -->
        <div class="doc-info-grid">
            <div>
                <table class="doc-info-table">
                    <tr>
                        <td class="doc-info-label">Cliente:</td>
                        <td><?= e($doc['customer_name'] ?? '—') ?></td>
                    </tr>
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
                    <?php if (!empty($doc['ncf'])): ?>
                        <tr>
                            <td class="doc-info-label">NCF:</td>
                            <td><b><?= e($doc['ncf']) ?></b></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Items -->
        <table class="doc-items-table">
            <thead>
                <tr>
                    <th>Referencia - Descripción</th>
                    <th class="num">Cantidad</th>
                    <th class="num">Precio</th>
                    <th class="num">Dscto.</th>
                    <th class="num">Neto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['description']) ?></td>
                        <td class="num"><?= number_format((float) $item['quantity'], 2) ?></td>
                        <td class="num"><?= number_format((float) $item['unit_price'], 2) ?></td>
                        <td class="num">
                            <?= ($item['discount_percentage'] ?? 0) > 0 ? number_format((float) $item['discount_percentage'], 2) . '%' : '—' ?>
                        </td>
                        <td class="num" style="font-weight:600;"><?= number_format((float) $item['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
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
                    <?php if (($doc['retention_amount'] ?? 0) > 0): ?>
                        <th style="text-align:right;">Retención
                            (<?= number_format((float) $doc['retention_percentage'], 2) ?>%)</th>
                    <?php endif; ?>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= e($curr) ?></td>
                    <td style="text-align:right;"><?= number_format((float) ($doc['subtotal'] ?? 0), 2) ?></td>
                    <?php if (($doc['discount_total'] ?? 0) > 0): ?>
                        <td style="text-align:right;color:#dc2626;">-
                            <?= number_format((float) $doc['discount_total'], 2) ?>
                        </td>
                    <?php endif; ?>
                    <?php if (($doc['tax'] ?? 0) > 0): ?>
                        <td style="text-align:right;"><?= number_format((float) $doc['tax'], 2) ?></td>
                    <?php endif; ?>
                    <?php if (($doc['retention_amount'] ?? 0) > 0): ?>
                        <td style="text-align:right;color:#dc2626;">
                            -<?= number_format((float) $doc['retention_amount'], 2) ?></td>
                    <?php endif; ?>
                    <td style="text-align:right;font-weight:700;"><?= number_format((float) $doc['total'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Payment -->
        <?php if (!empty($settings['bank_accounts'])): ?>
            <table class="payment-table">
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
        <?php endif; ?>

    </div>

    <script>window.onload = function () { window.print(); };</script>
</body>

</html>