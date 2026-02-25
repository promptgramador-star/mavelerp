<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>
            <?= e($customer['name']) ?>
        </h1>
        <p>Perfil del Cliente</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= url('customers/edit/' . $customer['id']) ?>" class="btn btn-primary">✏️ Editar</a>
        <a href="<?= url('quotations/create') ?>" class="btn" style="background:var(--success);color:#fff;">+ Nueva
            Cotización</a>
        <a href="<?= url('customers') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
    </div>
</div>

<!-- Info + Stats -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;margin-bottom:24px;">

    <!-- Datos de Contacto -->
    <div class="card">
        <div class="card-header">
            <h2>Contacto</h2>
        </div>
        <div class="card-body">
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">RNC / Cédula</span>
                <p style="font-weight:600;">
                    <?= e($customer['rnc'] ?: 'No registrado') ?>
                </p>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Teléfono</span>
                <p style="font-weight:600;">
                    <?= e($customer['phone'] ?: '—') ?>
                </p>
            </div>
            <div style="margin-bottom:12px;">
                <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Email</span>
                <p style="font-weight:600;">
                    <?= e($customer['email'] ?: '—') ?>
                </p>
            </div>
            <div>
                <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Dirección</span>
                <p style="font-weight:600;">
                    <?= e($customer['address'] ?: '—') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- KPI: Total Facturado -->
    <div class="card">
        <div class="card-body"
            style="display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:40px;">
            <span style="font-size:36px;">🧾</span>
            <h3 style="font-size:28px;margin:8px 0 4px;">
                <?= money((float) ($totals['invoiced'] ?? 0)) ?>
            </h3>
            <p style="color:var(--secondary);">Total Facturado</p>
        </div>
    </div>

    <!-- KPI: Total Cotizado + Documentos -->
    <div class="card">
        <div class="card-body"
            style="display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:40px;">
            <span style="font-size:36px;">📋</span>
            <h3 style="font-size:28px;margin:8px 0 4px;">
                <?= money((float) ($totals['quoted'] ?? 0)) ?>
            </h3>
            <p style="color:var(--secondary);">Total Cotizado</p>
            <span style="font-size:13px;color:var(--secondary);margin-top:8px;">
                <?= (int) ($totals['doc_count'] ?? 0) ?> documentos
            </span>
        </div>
    </div>

</div>

<!-- Historial de Documentos -->
<div class="card">
    <div class="card-header">
        <h2>Historial de Documentos</h2>
    </div>
    <div class="card-body">
        <?php if (empty($documents)): ?>
            <p style="color:var(--secondary);text-align:center;padding:30px;">Este cliente no tiene documentos asociados.
            </p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
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
                            <td><span class="badge badge-<?= strtolower($doc['document_type']) ?>">
                                    <?= e($doc['document_type']) ?>
                                </span></td>
                            <td style="text-align:right;">
                                <?= money((float) $doc['total']) ?>
                            </td>
                            <td><span class="status status-<?= strtolower($doc['status']) ?>">
                                    <?= e($doc['status']) ?>
                                </span></td>
                            <td>
                                <?= e($doc['issue_date'] ?? '') ?>
                            </td>
                            <td>
                                <?php
                                $viewUrl = $doc['document_type'] === 'FAC'
                                    ? url('invoices/view/' . $doc['id'])
                                    : url('quotations/view/' . $doc['id']);
                                ?>
                                <a href="<?= $viewUrl ?>" class="btn"
                                    style="padding:4px 12px;font-size:13px;background:var(--primary);color:#fff;">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php \Core\View::endSection(); ?>