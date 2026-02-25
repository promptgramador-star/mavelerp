<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>
            <?= e($doc['sequence_code']) ?>
        </h1>
        <p>Factura
            <?php if ($refDoc): ?>
                — Generada desde <a href="<?= url('quotations/view/' . $refDoc['id']) ?>" style="color:var(--primary);">
                    <?= e($refDoc['sequence_code']) ?>
                </a>
            <?php endif; ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" class="btn" style="background:#4b5563;color:#fff;">🖨️ Imprimir</button>
        <a href="<?= url('invoices') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            <div>
                <p style="color:var(--secondary);font-size:13px;">CLIENTE</p>
                <p style="font-weight:600;">
                    <?= e($doc['customer_name'] ?? '—') ?>
                </p>
                <p style="font-size:14px;color:var(--secondary);">RNC:
                    <?= e($doc['customer_rnc'] ?? 'N/A') ?>
                </p>
            </div>
            <div style="text-align:right;">
                <p style="color:var(--secondary);font-size:13px;">FECHA</p>
                <p style="font-weight:600;">
                    <?= e($doc['issue_date'] ?? '') ?>
                </p>
                <p style="font-size:14px;"><span class="status status-<?= strtolower($doc['status']) ?>">
                        <?= e($doc['status']) ?>
                    </span></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Descripción</th>
                    <th style="text-align:right;">Cant.</th>
                    <th style="text-align:right;">P. Unit.</th>
                    <th style="text-align:right;">Desc.</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td style="color:var(--secondary);">
                            <?= $item['line_number'] ?>
                        </td>
                        <td>
                            <?= e($item['description']) ?>
                        </td>
                        <td style="text-align:right;">
                            <?= number_format((float) $item['quantity'], 2) ?>
                        </td>
                        <td style="text-align:right;">
                            <?= money((float) $item['unit_price'], $doc['currency'] ?? 'DOP') ?>
                        </td>
                        <td style="text-align:right;color:var(--danger);">
                            <?php if (($item['discount_amount'] ?? 0) > 0): ?>
                                <?= ($item['discount_percentage'] ?? 0) > 0 ? '(' . (float) $item['discount_percentage'] . '%) ' : '' ?>-<?= money((float) $item['discount_amount'], $doc['currency'] ?? 'DOP') ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;font-weight:500;">
                            <?= money((float) $item['total'], $doc['currency'] ?? 'DOP') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:600;">Subtotal Bruto:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= money((float) ($doc['subtotal'] ?? 0), $doc['currency'] ?? 'DOP') ?>
                    </td>
                </tr>
                <?php if (($doc['discount_total'] ?? 0) > 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:600;color:var(--danger);">Total Descuento:</td>
                        <td style="text-align:right;font-weight:600;color:var(--danger);">
                            - <?= money((float) $doc['discount_total'], $doc['currency'] ?? 'DOP') ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:600;">ITBIS (18%):</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= money((float) $doc['tax'], $doc['currency'] ?? 'DOP') ?>
                    </td>
                </tr>
                <tr style="font-size:18px;background:var(--bg-light);">
                    <td colspan="4" style="text-align:right;font-weight:700;">Total:</td>
                    <td style="text-align:right;font-weight:700;color:var(--primary);">
                        <?= money((float) $doc['total'], $doc['currency'] ?? 'DOP') ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php
$settings = get_settings();
if (!empty($settings['bank_accounts'])):
    ?>
    <div class="card" style="margin-top:20px;border-top:3px solid var(--primary);">
        <div class="card-body">
            <h4 style="margin-top:0;color:var(--primary);">💳 Información de Pago</h4>
            <div style="white-space:pre-wrap;color:var(--secondary);font-size:14px;"><?= e($settings['bank_accounts']) ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    @media print {

        .btn,
        .page-header a,
        .page-header div:last-child {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .page-header {
            margin-bottom: 20px !important;
        }
    }
</style>

<?php \Core\View::endSection(); ?>