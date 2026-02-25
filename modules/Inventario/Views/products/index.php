<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Productos y Servicios</h1>
        <p>Catálogo de la empresa</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?= url('products/import') ?>" class="btn">📥 Importar</a>
        <a href="<?= url('products/create') ?>" class="btn btn-primary">+ Nuevo Producto</a>
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div class="card-body" style="padding:12px 20px;">
        <form method="GET" action="<?= url('products') ?>" style="display:flex;gap:10px;">
            <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Buscar por nombre o SKU..."
                style="flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:8px;">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
            <p style="color:var(--secondary);text-align:center;padding:40px;">No hay productos registrados.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th>Tipo</th>
                        <th style="text-align:right;">Costo</th>
                        <th style="text-align:right;">Precio</th>
                        <th style="text-align:right;">Stock</th>
                        <th style="width:120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><strong>
                                    <?= e($p['name']) ?>
                                </strong></td>
                            <td><code
                                    style="background:#f1f5f9;padding:2px 6px;border-radius:4px;font-size:12px;"><?= e($p['sku'] ?: '—') ?></code>
                            </td>
                            <td>
                                <?php if ($p['is_service']): ?>
                                    <span class="badge" style="background:#fef3c7;color:#92400e;">Servicio</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;">Producto</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <?= money((float) $p['cost']) ?>
                            </td>
                            <td style="text-align:right;">
                                <?= money((float) $p['price']) ?>
                            </td>
                            <td style="text-align:right;">
                                <?php if (!$p['is_service']): ?>
                                    <?php if ((float) $p['stock'] <= 0): ?>
                                        <span style="color:var(--danger);font-weight:600;">
                                            <?= number_format((float) $p['stock'], 2) ?>
                                        </span>
                                    <?php elseif ((float) $p['stock'] <= 5): ?>
                                        <span style="color:var(--warning);font-weight:600;">
                                            <?= number_format((float) $p['stock'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <?= number_format((float) $p['stock'], 2) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--secondary);">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('products/edit/' . $p['id']) ?>" class="btn-action" title="Editar">✏️</a>
                                <form method="POST" action="<?= url('products/delete/' . $p['id']) ?>" style="display:inline;"
                                    onsubmit="return confirm('¿Eliminar este producto?')">
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