<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Configuración General</h1>
    <p>Datos de la empresa</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('settings') ?>" enctype="multipart/form-data">
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

            <!-- Logo Upload -->
            <div class="form-group">
                <label>Logo de la Empresa</label>
                <div style="display:flex;align-items:center;gap:20px;margin-top:8px;">
                    <?php if (!empty($settings['logo'])): ?>
                        <div style="border:2px dashed var(--border);border-radius:8px;padding:10px;background:#fafafa;">
                            <img src="<?= url($settings['logo']) ?>" alt="Logo"
                                style="max-height:80px;max-width:200px;display:block;">
                        </div>
                    <?php else: ?>
                        <div
                            style="border:2px dashed var(--border);border-radius:8px;padding:20px 30px;background:#fafafa;color:var(--secondary);font-size:13px;">
                            Sin logo
                        </div>
                    <?php endif; ?>
                    <div>
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/gif,image/webp,image/svg+xml"
                            style="font-size:14px;">
                        <br>
                        <small style="color:var(--secondary);">PNG, JPG, SVG o WebP. Aparecerá en facturas y
                            cotizaciones.</small>
                    </div>
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
                    <?php $currencies = explode(',', $settings['currency'] ?? 'DOP'); ?>
                    <div style="display:flex;gap:15px;margin-top:8px;">
                        <label><input type="checkbox" name="currency[]" value="DOP" <?= in_array('DOP', $currencies) ? 'checked' : '' ?>> DOP - Peso Dominicano</label>
                        <label><input type="checkbox" name="currency[]" value="USD" <?= in_array('USD', $currencies) ? 'checked' : '' ?>> USD - Dólar</label>
                        <label><input type="checkbox" name="currency[]" value="EUR" <?= in_array('EUR', $currencies) ? 'checked' : '' ?>> EUR - Euro</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="default_currency">Moneda Predeterminada</label>
                    <?php $defaultCurr = $settings['default_currency'] ?? 'DOP'; ?>
                    <select name="default_currency" id="default_currency" class="form-control"
                        style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px; background: white;">
                        <option value="DOP" <?= $defaultCurr === 'DOP' ? 'selected' : '' ?>>DOP - Peso Dominicano</option>
                        <option value="USD" <?= $defaultCurr === 'USD' ? 'selected' : '' ?>>USD - Dólar Estadounidense
                        </option>
                        <option value="EUR" <?= $defaultCurr === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                    </select>
                    <small style="color:var(--secondary);display:block;margin-top:4px;">Esta moneda tendrá prioridad
                        visual en el Dashboard y será la seleccionada por defecto al crear nuevos documentos.</small>
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