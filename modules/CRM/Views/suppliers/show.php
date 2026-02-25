<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>
            <?= e($supplier['name']) ?>
        </h1>
        <p>Perfil del Proveedor</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= url('suppliers/edit/' . $supplier['id']) ?>" class="btn btn-primary">✏️ Editar</a>
        <a href="<?= url('suppliers') ?>" class="btn" style="background:var(--border);color:var(--dark);">← Volver</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Información de Contacto</h2>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <div style="margin-bottom:16px;">
                    <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">RNC / Cédula</span>
                    <p style="font-weight:600;font-size:16px;">
                        <?= e($supplier['rnc'] ?: 'No registrado') ?>
                    </p>
                </div>
                <div style="margin-bottom:16px;">
                    <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Teléfono</span>
                    <p style="font-weight:600;font-size:16px;">
                        <?= e($supplier['phone'] ?: '—') ?>
                    </p>
                </div>
            </div>
            <div>
                <div style="margin-bottom:16px;">
                    <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Email</span>
                    <p style="font-weight:600;font-size:16px;">
                        <?= e($supplier['email'] ?: '—') ?>
                    </p>
                </div>
                <div style="margin-bottom:16px;">
                    <span style="font-size:12px;color:var(--secondary);text-transform:uppercase;">Dirección</span>
                    <p style="font-weight:600;font-size:16px;">
                        <?= e($supplier['address'] ?: '—') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php \Core\View::endSection(); ?>