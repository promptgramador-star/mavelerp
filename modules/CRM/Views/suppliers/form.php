<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>
        <?= e($title) ?>
    </h1>
    <p><a href="<?= url('suppliers') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Proveedores</a>
    </p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url($supplier ? 'suppliers/update/' . $supplier['id'] : 'suppliers/store') ?>">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre / Razón Social *</label>
                    <input type="text" id="name" name="name" value="<?= e($supplier['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="rnc">RNC / Cédula</label>
                    <input type="text" id="rnc" name="rnc" value="<?= e($supplier['rnc'] ?? '') ?>"
                        placeholder="000-00000-0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="<?= e($supplier['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($supplier['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Dirección</label>
                <textarea id="address" name="address" rows="2"><?= e($supplier['address'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $supplier ? 'Actualizar' : 'Guardar' ?>
                </button>
                <a href="<?= url('suppliers') ?>" class="btn"
                    style="background:var(--border);color:var(--dark);">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php \Core\View::endSection(); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const rncInput = document.getElementById('rnc');
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const form = document.querySelector('form');

    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            if (e.target.value.length > 20) {
                e.target.value = e.target.value.slice(0, 20);
            }
        });
    }

    if (rncInput) {
        rncInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, ''); // Solo numeros
            if (val.length > 11) val = val.slice(0, 11);

            let masked = val;
            if (val.length > 9) {
                // Formato Cédula: 000-0000000-0
                masked = val.replace(/^(\d{3})(\d{7})(\d{0,1}).*/, '$1-$2-$3').replace(/\-$/, '');
            } else if (val.length > 8) {
                // Formato RNC Empresas: 000-00000-0
                masked = val.replace(/^(\d{3})(\d{5})(\d{0,1}).*/, '$1-$2-$3').replace(/\-$/, '');
            } else if (val.length > 3) {
                masked = val.replace(/^(\d{3})(\d+)/, '$1-$2');
            }
            e.target.value = masked;
        });
    }

    form.addEventListener('submit', (e) => {
        // Name validation
        let nameVal = nameInput.value.trim();
        if (nameVal.length < 3 || /^\d+$/.test(nameVal) || ['yo', 'me'].includes(nameVal.toLowerCase()) || !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nameVal)) {
            e.preventDefault();
            alert('Nombre inválido. Debe contener un nombre o razón social válida.');
            nameInput.focus();
            return;
        }

        // RNC validation
        if (rncInput && rncInput.value.trim() !== '') {
            let rncVal = rncInput.value.trim();
            let rawLines = rncVal.replace(/\D/g, '');
            if (rawLines.length === 9) {
                if (!/^\d{3}-\d{5}-\d{1}$/.test(rncVal) && rncVal.includes('-')) {
                    e.preventDefault();
                    alert('RNC inválido. Verifique el formato.');
                    rncInput.focus();
                    return;
                }
            } else if (rawLines.length === 11) {
                if (!/^\d{3}-\d{7}-\d{1}$/.test(rncVal) && rncVal.includes('-')) {
                    e.preventDefault();
                    alert('Cédula inválida. Debe contener 11 dígitos.');
                    rncInput.focus();
                    return;
                }
            } else {
                e.preventDefault();
                alert(rawLines.length < 11 && rawLines.length !== 9 ? 'RNC inválido. Verifique el formato.' : 'Cédula inválida. Debe contener 11 dígitos.');
                rncInput.focus();
                return;
            }
        }
    });
});
</script>

