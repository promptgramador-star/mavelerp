<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Órdenes de Compra</h1>
        <p>Gestión de pedidos a proveedores</p>
    </div>
    <a href="<?= url('purchases/create') ?>" class="btn btn-primary">+ Nueva Orden (PO)</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($documents)): ?>
            <p style="color:var(--secondary);text-align:center;padding:40px;">No hay órdenes de compra registradas.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Orden (PO)</th>
                        <th>Fecha Emisión</th>
                        <th>Proveedor</th>
                        <th style="text-align:right;">Total</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><strong>
                                    <?= e($doc['sequence_code']) ?>
                                </strong></td>
                            <td>
                                <?= date('d/m/Y', strtotime($doc['issue_date'])) ?>
                            </td>
                            <td>
                                <?= e($doc['supplier_name'] ?? '—') ?>
                            </td>
                            <td style="text-align:right;">
                                <?= money((float) $doc['total'], $doc['currency']) ?>
                            </td>
                            <td>
                                <!-- Colores pre-definidos en app.css para estados -->
                                <span class="status status-<?= strtolower($doc['status']) ?>">
                                    <?= e($doc['status']) ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <a href="<?= url('purchases/show/' . $doc['id']) ?>" class="btn-action"
                                    title="Ver / Imprimir">👁️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php \Core\View::endSection(); ?>