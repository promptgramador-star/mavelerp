<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Configuración General</h1>
    <p>Datos de la empresa</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('settings') ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_name">Nombre de la Empresa *</label>
                    <input type="text" id="company_name" name="company_name"
                        value="<?= e($settings['company_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="rnc">RNC</label>
                    <input type="text" id="rnc" name="rnc" value="<?= e($settings['rnc'] ?? '') ?>"
                        placeholder="000-00000-0">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Dirección</label>
                <textarea id="address" name="address" rows="2"><?= e($settings['address'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="<?= e($settings['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($settings['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="bank_accounts">Cuentas Bancarias (Visible en facturas)</label>
                <textarea id="bank_accounts" name="bank_accounts" rows="3"
                    placeholder="Ej: Banco Popular: 000000000&#10;Banco Reservas: 000000000"><?= e($settings['bank_accounts'] ?? '') ?></textarea>
                <small style="color:var(--secondary);">Este texto se mostrará en el pie de página de sus facturas y
                    cotizaciones.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Monedas Habilitadas (Facturación)</label>
                    <?php
                    $currencies = explode(',', $settings['currency'] ?? 'DOP');
                    ?>
                    <div style="display:flex;gap:15px;margin-top:8px;">
                        <label><input type="checkbox" name="currency[]" value="DOP" <?= in_array('DOP', $currencies) ? 'checked' : '' ?>> DOP - Peso Dominicano</label>
                        <label><input type="checkbox" name="currency[]" value="USD" <?= in_array('USD', $currencies) ? 'checked' : '' ?>> USD - Dólar</label>
                        <label><input type="checkbox" name="currency[]" value="EUR" <?= in_array('EUR', $currencies) ? 'checked' : '' ?>> EUR - Euro</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="fiscal_year_start">Inicio Año Fiscal</label>
                    <input type="date" id="fiscal_year_start" name="fiscal_year_start"
                        value="<?= e($settings['fiscal_year_start'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php \Core\View::endSection(); ?>