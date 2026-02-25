<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>
        <?= e($title) ?>
    </h1>
    <p><a href="<?= url('products') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Productos</a></p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url($product ? 'products/update/' . $product['id'] : 'products/store') ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre del Producto *</label>
                    <input type="text" id="name" name="name" value="<?= e($product['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="sku">SKU / Código</label>
                    <input type="text" id="sku" name="sku" value="<?= e($product['sku'] ?? '') ?>"
                        placeholder="PRD-001">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cost">Costo (DOP)</label>
                    <input type="number" id="cost" name="cost" step="0.01" min="0"
                        value="<?= e($product['cost'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label for="price">Precio de Venta (DOP)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0"
                        value="<?= e($product['price'] ?? '0') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="stock">Stock Inicial</label>
                    <input type="number" id="stock" name="stock" step="0.01" min="0"
                        value="<?= e($product['stock'] ?? '0') ?>" id="stockField">
                </div>
                <div class="form-group" style="display:flex;flex-direction:column;gap:15px;padding-top:10px;">
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:0;">
                        <input type="checkbox" name="is_service" value="1" <?= !empty($product['is_service']) ? 'checked' : '' ?> style="width:auto;"
                            onchange="const s = document.getElementById('stock'); if(s) s.disabled = this.checked;">
                        Es un servicio (sin stock)
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:0;">
                        <input type="checkbox" name="is_taxable" value="1" <?= (!isset($product['is_taxable']) || $product['is_taxable']) ? 'checked' : '' ?> style="width:auto;">
                        Aplica ITBIS (18%)
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $product ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="<?= url('products') ?>" class="btn"
                    style="background:var(--border);color:var(--dark);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php \Core\View::endSection(); ?>