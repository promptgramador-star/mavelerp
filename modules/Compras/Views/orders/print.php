<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Orden de Compra <?= e($doc['sequence_code']) ?></title>
    <style>
        /* Base Reset & Print Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #e2e8f0;
            color: #1e293b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #printable-document {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Ocultar botones al imprimir */
        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            #printable-document {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
                border-radius: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Top Bar de acciones solo pantalla */
        .print-actions {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-secondary {
            background: #64748b;
        }
    </style>
</head>

<body>

    <?php $settings = get_settings();
    $curr = $doc['currency'] ?? 'DOP'; ?>

    <!-- Botones de Acción (No se imprimen) -->
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
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
                    <p><?= e($settings['phone']) ?><?= !empty($settings['email']) ? ' · ' . e($settings['email']) : '' ?>
                    </p>
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
                <h3 style="color:var(--secondary);font-size:12px;text-transform:uppercase;margin-bottom:8px;">Detalles
                    de la
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
                            <span
                                class="status status-<?= strtolower($doc['status']) ?>"><?= e($doc['status']) ?></span>
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
                        <td class="doc-col-num" style="font-weight:600;"><?= number_format((float) $item['total'], 2) ?>
                        </td>
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
                            <td style="text-align:right;color:#dc2626;">
                                -<?= number_format((float) $doc['discount_total'], 2) ?>
                            </td>
                        <?php endif; ?>
                        <?php if (($doc['tax'] ?? 0) > 0): ?>
                            <td style="text-align:right;"><?= number_format((float) $doc['tax'], 2) ?></td>
                        <?php endif; ?>
                        <td style="text-align:right;font-weight:700;"><?= number_format((float) $doc['total'], 2) ?>
                        </td>
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
    </style>
    </div>

</body>

</html>