<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Clientes</h1>
        <p>Gestión de clientes registrados</p>
    </div>
    <a href="<?= url('customers/create') ?>" class="btn btn-primary">+ Nuevo Cliente</a>
</div>

<!-- Búsqueda -->
<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:12px 20px;">
        <form method="GET" action="<?= url('customers') ?>" style="display:flex;gap:10px;">
            <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Buscar por nombre o RNC..."
                style="flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:8px;">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <p style="color:var(--secondary);text-align:center;padding:40px;">No hay clientes registrados.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>RNC</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><strong>
                                    <?= e($c['name']) ?>
                                </strong></td>
                            <td>
                                <?= e($c['rnc'] ?: '—') ?>
                            </td>
                            <td>
                                <?= e($c['phone'] ?: '—') ?>
                            </td>
                            <td>
                                <?= e($c['email'] ?: '—') ?>
                            </td>
                            <td>
                                <a href="<?= url('customers/edit/' . $c['id']) ?>" class="btn-action" title="Editar">✏️</a>
                                <form method="POST" action="<?= url('customers/delete/' . $c['id']) ?>" style="display:inline;"
                                    onsubmit="return confirm('¿Eliminar este cliente?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-action btn-danger-action" title="Eliminar">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php \Core\View::endSection(); ?>