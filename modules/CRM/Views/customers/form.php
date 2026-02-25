<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>
        <?= e($title) ?>
    </h1>
    <p><a href="<?= url('customers') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Clientes</a></p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url($customer ? 'customers/update/' . $customer['id'] : 'customers/store') ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre / Razón Social *</label>
                    <input type="text" id="name" name="name" value="<?= e($customer['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="rnc">RNC / Cédula</label>
                    <input type="text" id="rnc" name="rnc" value="<?= e($customer['rnc'] ?? '') ?>"
                        placeholder="000-00000-0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="<?= e($customer['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($customer['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Dirección</label>
                <textarea id="address" name="address" rows="2"><?= e($customer['address'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $customer ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="<?= url('customers') ?>" class="btn"
                    style="background:var(--border);color:var(--dark);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php \Core\View::endSection(); ?>