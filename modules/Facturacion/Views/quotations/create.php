<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Nueva Cotización</h1>
    <p><a href="<?= url('quotations') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Cotizaciones</a>
    </p>
</div>

<form method="POST" action="<?= url('quotations/store') ?>" id="cotForm">
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h2>Datos Generales</h2>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Cliente *</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">— Seleccionar cliente —</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="issue_date">Fecha de Emisión</label>
                    <input type="date" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Ítems</h2>
            <button type="button" onclick="addLine()" class="btn btn-primary" style="padding:6px 12px;font-size:13px;">+
                Agregar Línea</button>
        </div>
        <div class="card-body" style="overflow-x:auto;">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Producto (opcional)</th>
                        <th>Descripción</th>
                        <th style="width:100px;">Cant.</th>
                        <th style="width:130px;">Precio Unit.</th>
                        <th style="width:130px;">Total</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <!-- Se llena con JS -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right;font-weight:600;">Subtotal:</td>
                        <td id="subtotalDisplay" style="font-weight:600;">DOP 0.00</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="text-align:right;font-weight:600;">ITBIS (18%):</td>
                        <td id="taxDisplay" style="font-weight:600;">DOP 0.00</td>
                        <td></td>
                    </tr>
                    <tr style="font-size:18px;">
                        <td colspan="5" style="text-align:right;font-weight:700;">Total:</td>
                        <td id="totalDisplay" style="font-weight:700;color:var(--primary);">DOP 0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar Cotización</button>
        <a href="<?= url('quotations') ?>" class="btn" style="background:var(--border);color:var(--dark);">Cancelar</a>
    </div>
</form>

<script>
    const products = <?= json_encode($products) ?>;
    let lineCount = 0;

    function addLine() {
        lineCount++;
        const row = document.createElement('tr');
        row.id = 'line-' + lineCount;

        let productOptions = '<option value="">—</option>';
        products.forEach(p => {
            productOptions += `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`;
        });

        row.innerHTML = `
        <td style="text-align:center;color:var(--secondary);">${lineCount}</td>
        <td>
            <select name="item_product_id[]" onchange="selectProduct(this, ${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;">
                ${productOptions}
            </select>
        </td>
        <td><input type="text" name="item_description[]" id="desc-${lineCount}" required style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;"></td>
        <td><input type="number" name="item_quantity[]" id="qty-${lineCount}" value="1" step="0.01" min="0.01" onchange="calcLine(${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;"></td>
        <td><input type="number" name="item_price[]" id="price-${lineCount}" value="0" step="0.01" min="0" onchange="calcLine(${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;"></td>
        <td id="lineTotal-${lineCount}" style="padding-top:14px;font-weight:500;">DOP 0.00</td>
        <td><button type="button" onclick="removeLine(${lineCount})" style="background:none;border:none;cursor:pointer;font-size:16px;">❌</button></td>
    `;

        document.getElementById('itemsBody').appendChild(row);
    }

    function selectProduct(select, line) {
        const opt = select.options[select.selectedIndex];
        const price = opt.dataset.price || 0;
        const name = opt.text !== '—' ? opt.text : '';
        document.getElementById('desc-' + line).value = name;
        document.getElementById('price-' + line).value = price;
        calcLine(line);
    }

    function calcLine(line) {
        const qty = parseFloat(document.getElementById('qty-' + line).value) || 0;
        const price = parseFloat(document.getElementById('price-' + line).value) || 0;
        const total = qty * price;
        document.getElementById('lineTotal-' + line).textContent = 'DOP ' + total.toFixed(2);
        calcTotals();
    }

    function removeLine(line) {
        const row = document.getElementById('line-' + line);
        if (row) row.remove();
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        document.querySelectorAll('[id^="lineTotal-"]').forEach(el => {
            subtotal += parseFloat(el.textContent.replace('DOP ', '').replace(',', '')) || 0;
        });
        const tax = subtotal * 0.18;
        const total = subtotal + tax;
        document.getElementById('subtotalDisplay').textContent = 'DOP ' + subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = 'DOP ' + tax.toFixed(2);
        document.getElementById('totalDisplay').textContent = 'DOP ' + total.toFixed(2);
    }

    // Agregar primera línea automáticamente
    addLine();
</script>

<?php \Core\View::endSection(); ?>