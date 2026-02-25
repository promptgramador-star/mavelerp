<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>
            <?= e($doc['sequence_code']) ?>
        </h1>
        <p>Cotización — <span class="status status-<?= strtolower($doc['status']) ?>">
                <?= e($doc['status']) ?>
            </span></p>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if ($doc['status'] === 'DRAFT'): ?>
            <form method="POST" action="<?= url('quotations/approve/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--success);color:#fff;"
                    onclick="return confirm('¿Aprobar esta cotización?')">✅ Aprobar</button>
            </form>
        <?php endif; ?>
        <?php if ($doc['status'] === 'APPROVED' && !$hasInvoice): ?>
            <form method="POST" action="<?= url('quotations/convert/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary"
                    onclick="return confirm('¿Generar factura desde esta cotización?')">📄 Convertir a Factura</button>
            </form>
        <?php endif; ?>
        <?php if ($hasInvoice): ?>
            <a href="<?= url('invoices/view/' . $hasInvoice['id']) ?>" class="btn"
                style="background:#fef3c7;color:#92400e;">
                Ver Factura
                <?= e($hasInvoice['sequence_code']) ?>
            </a>
        <?php endif; ?>
        <a href="<?= url('quotations') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
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
                <p style="color:var(--secondary);font-size:13px;">FECHA EMISIÓN</p>
                <p style="font-weight:600;">
                    <?= e($doc['issue_date'] ?? '') ?>
                </p>
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
                            <?= money((float) $item['unit_price']) ?>
                        </td>
                        <td style="text-align:right;color:var(--danger);">
                            <?= $item['discount_amount'] > 0 ? '-' . money((float) $item['discount_amount']) : '—' ?>
                        </td>
                        <td style="text-align:right;font-weight:500;">
                            <?= money((float) $item['total']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:600;">Subtotal Bruto:</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= money((float) $doc['subtotal']) ?>
                    </td>
                </tr>
                <?php if ($doc['discount_total'] > 0): ?>
                    <tr>
                        <td colspan="4" style="text-align:right;font-weight:600;color:var(--danger);">Total Descuento:</td>
                        <td style="text-align:right;font-weight:600;color:var(--danger);">
                            - <?= money((float) $doc['discount_total']) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="4" style="text-align:right;font-weight:600;">ITBIS (18%):</td>
                    <td style="text-align:right;font-weight:600;">
                        <?= money((float) $doc['tax']) ?>
                    </td>
                </tr>
                <tr style="font-size:18px;background:var(--bg-light);">
                    <td colspan="4" style="text-align:right;font-weight:700;">Total:</td>
                    <td style="text-align:right;font-weight:700;color:var(--primary);">
                        <?= money((float) $doc['total']) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php \Core\View::endSection(); ?>