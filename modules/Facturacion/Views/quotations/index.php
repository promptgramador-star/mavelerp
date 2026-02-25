<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Cotizaciones</h1>
        <p>Listado de cotizaciones emitidas</p>
    </div>
    <a href="<?= url('quotations/create') ?>" class="btn btn-primary">+ Nueva Cotización</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($documents)): ?>
            <p style="color:var(--secondary);text-align:center;padding:40px;">No hay cotizaciones aún.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th style="text-align:right;">Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><strong>
                                    <?= e($doc['sequence_code']) ?>
                                </strong></td>
                            <td>
                                <?= e($doc['customer_name'] ?? '—') ?>
                            </td>
                            <td style="text-align:right;">
                                <?= money((float) $doc['total'], $doc['currency'] ?? 'DOP') ?>
                            </td>
                            <td><span class="status status-<?= strtolower($doc['status']) ?>">
                                    <?= e($doc['status']) ?>
                                </span></td>
                            <td>
                                <?= e($doc['issue_date'] ?? '') ?>
                            </td>
                            <td><a href="<?= url('quotations/view/' . $doc['id']) ?>" class="btn"
                                    style="padding:4px 12px;font-size:13px;background:var(--primary);color:#fff;">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php \Core\View::endSection(); ?>