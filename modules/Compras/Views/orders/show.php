<?php \Core\View::startSection('content'); ?>
<?php $settings = get_settings();
$curr = $doc['currency'] ?? 'DOP'; ?>

<!-- Screen UI Header -->
<div class="page-header no-print" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Orden de Compra: <?= e($doc['sequence_code']) ?></h1>
        <p>Proveedor: <span style="font-weight:600;"><?= e($doc['supplier_name'] ?? '—') ?></span> | Estado:
            <span class="status status-<?= strtolower($doc['status']) ?>"><?= e($doc['status']) ?></span>
        </p>
    </div>
    <div style="display:flex;gap:10px;">
        <?php $st = trim($doc['status'] ?? 'DRAFT'); ?>

        <?php if ($st === 'DRAFT'): ?>
            <form method="POST" action="<?= url('purchases/approve/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--success);color:#fff;"
                    onclick="return confirm('¿Aprobar esta orden de compra y marcarla como enviada?')">✅ Aprobar y Enviar</button>
            </form>
            <form method="POST" action="<?= url('purchases/paid/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--primary);color:#fff;"
                    onclick="return confirm('¿Marcar orden como pagada y mercancia recibida?')">💰 Marcar Pagada / Recibida</button>
            </form>
            <form method="POST" action="<?= url('purchases/cancel/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;"
                    onclick="return confirm('¿Anular esta orden de compra?')">❌ Anular</button>
            </form>

        <?php elseif ($st === 'SENT'): ?>
            <form method="POST" action="<?= url('purchases/paid/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--primary);color:#fff;"
                    onclick="return confirm('¿Marcar orden como pagada y mercancia recibida?')">💰 Marcar Pagada / Recibida</button>
            </form>
            <form method="POST" action="<?= url('purchases/cancel/' . $doc['id']) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn" style="background:var(--danger);color:#fff;"
                    onclick="return confirm('¿Anular esta orden de compra?')">❌ Anular</button>
            </form>

        <?php elseif ($st === 'PAID'): ?>
            <span class="btn" style="background:var(--primary);color:#fff;cursor:default;">✅ Pagada / Recibida</span>

        <?php elseif ($st === 'CANCELLED'): ?>
            <span class="btn" style="background:var(--danger);color:#fff;cursor:default;">❌ Anulada</span>
        <?php endif; ?>

        <a href="<?= url('purchases/print/' . $doc['id']) ?>" target="_blank" class="btn"
            style="background:#4b5563;color:#fff;">🖨️ Imprimir</a>
        <a href="<?= url('purchases') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
    </div>
</div>

<!-- ==================== UI DOCUMENT ==================== -->
<div id="ui-document">
    <div class="doc-info-grid">
        <div>
            <table class="doc-info-table">
                <tr>
                    <td class="doc-info-label">Proveedor:</td>
                    <td><strong><?= e($doc['supplier_name'] ?? '—') ?></strong></td>
                </tr>
                <?php if (!empty($doc['rnc'])): ?>
                    <tr>
                        <td class="doc-info-label">RNC:</td>
                        <td><?= e($doc['rnc']) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if (!empty($doc['address'])): ?>
                    <tr>
                        <td class="doc-info-label">Dirección:</td>
                        <td><?= e($doc['address']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
        <div style="text-align:right;">
            <table class="doc-info-table" style="margin-left:auto;">
                <tr>
                    <td class="doc-info-label">Fecha de Emisión:</td>
                    <td><?= date('d-m-Y', strtotime($doc['issue_date'] ?? 'now')) ?></td>
                </tr>
                <tr>
                    <td class="doc-info-label">Moneda:</td>
                    <td><?= e($curr) ?></td>
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
</div>

<!-- Document Styles -->
<style>
    /* === UI Document Styles === */
    #ui-document {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        font-family: inherit;
        font-size: 14px;
        color: #1f2937;
    }

    .doc-info-grid {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
    }

    .doc-info-table td {
        padding: 4px;
        color: #374151;
    }

    .doc-info-label {
        color: #6b7280;
        text-align: right;
        padding-right: 15px !important;
        font-weight: 500;
    }

    .doc-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .doc-items-table thead {
        background: #f8fafc;
    }

    .doc-items-table th {
        padding: 12px 15px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .doc-items-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #e5e7eb;
    }

    .doc-col-num {
        text-align: right !important;
        white-space: nowrap;
    }

    .doc-totals-section {
        display: flex;
        justify-content: flex-end;
    }

    .doc-totals-table {
        min-width: 300px;
        width: auto;
        border-collapse: collapse;
    }

    .doc-totals-table th {
        padding: 10px 15px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        font-size: 13px;
        color: #475569;
    }

    .doc-totals-table td {
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        font-size: 14px;
        text-align: right;
    }
</style>

<?php \Core\View::endSection(); ?>