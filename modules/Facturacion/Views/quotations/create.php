<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Nueva Cotización</h1>
    <p><a href="<?= url('quotations') ?>" style="color:var(--primary);text-decoration:none;">← Volver a Cotizaciones</a>
    </p>
</div>

<?php
$appSettings = get_settings();
$currencies = explode(',', $appSettings['currency'] ?? 'DOP');
$defaultCurrency = $appSettings['default_currency'] ?? 'DOP';
?>
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
                <div class="form-group">
                    <label for="currency">Moneda</label>
                    <select id="currency" name="currency" onchange="updateCurrencySymbol()">
                        <?php foreach ($currencies as $curr):
                            $curr = trim($curr);
                            if (!$curr)
                                continue;
                            ?>
                            <option value="<?= $curr ?>" <?= $curr === $defaultCurrency ? 'selected' : '' ?>><?= $curr ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h2>Ítems</h2>
        </div>
        <div class="card-body" style="overflow-x:auto;">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th style="width:250px;">Producto / SKU</th>
                        <th>Descripción</th>
                        <th style="width:80px;">Cant.</th>
                        <th style="width:120px;">Precio Unit.</th>
                        <th style="width:100px;">Desc. (%)</th>
                        <th style="width:80px;">ITBIS</th>
                        <th style="width:130px;">Total</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <!-- Se llena con JS -->
                </tbody>
                <tbody>
                    <tr>
                        <td colspan="9" style="padding: 10px 0;">
                            <button type="button" onclick="addLine()" class="btn"
                                style="background:var(--primary);color:#fff;border-radius:50%;width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;margin-left:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"
                                title="Agregar Línea">
                                <span style="font-size:20px;font-weight:bold;">+</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" style="text-align:right;padding:8px 20px;">Subtotal Bruto:</td>
                        <td id="subtotalDisplay" style="text-align:right;padding:8px 20px;">--</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align:right;padding:8px 20px;color:var(--danger);">(-) Descuento
                            Total:</td>
                        <td id="discountDisplay" style="text-align:right;padding:8px 20px;color:var(--danger);">--
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="7" style="text-align:right;padding:8px 20px;">ITBIS (18%) s/ Base:</td>
                        <td id="taxDisplay" style="text-align:right;padding:8px 20px;">--</td>
                        <td></td>
                    </tr>
                    <tr style="font-size:1.2rem;background:var(--bg-light);">
                        <td colspan="7" style="text-align:right;padding:12px 20px;font-weight:700;">TOTAL FINAL:</td>
                        <td id="totalDisplay"
                            style="text-align:right;padding:12px 20px;font-weight:800;color:var(--primary);">--
                        </td>
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

<style>
    .search-wrapper {
        position: relative;
    }

    .search-wrapper::before {
        content: '🔍';
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--secondary);
        font-size: 14px;
        pointer-events: none;
        z-index: 5;
    }

    .search-wrapper input {
        padding-left: 32px !important;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        margin-top: 4px;
    }

    .search-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .search-item:hover {
        background: #f8fafc;
    }

    .search-item .sku {
        font-size: 11px;
        color: var(--secondary);
        background: #eee;
        padding: 2px 4px;
        border-radius: 4px;
    }

    .search-item .price {
        font-weight: 600;
        color: var(--primary);
    }
</style>

<script>
    let lineCount = 0;

    function addLine() {
        lineCount++;
        const row = document.createElement('tr');
        row.id = 'line-' + lineCount;
        row.innerHTML = `
        <td style="text-align:center;color:var(--secondary);">${lineCount}</td>
        <td>
            <div class="search-wrapper">
                <input type="text" placeholder="Buscar por nombre o SKU..." 
                    onkeyup="searchProduct(this, ${lineCount})" 
                    onfocus="searchProduct(this, ${lineCount})"
                    style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;" autocomplete="off">
                <div id="results-${lineCount}" class="search-results"></div>
                <input type="hidden" name="item_product_id[]" id="prodId-${lineCount}">
            </div>
        </td>
        <td><input type="text" name="item_description[]" id="desc-${lineCount}" required style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;"></td>
        <td><input type="number" name="item_quantity[]" id="qty-${lineCount}" value="1" step="1" min="1" onchange="calcLine(${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;text-align:center;"></td>
        <td><input type="number" name="item_price[]" id="price-${lineCount}" value="0" step="0.01" min="0" onchange="calcLine(${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;text-align:right;"></td>
        <td><input type="number" name="item_discount_percent[]" id="disc-${lineCount}" value="0" step="0.01" min="0" max="100" onchange="calcLine(${lineCount})" style="width:100%;padding:8px;border:1px solid var(--border);border-radius:6px;text-align:right;"></td>
        <td style="text-align:center;">
            <input type="checkbox" name="item_is_taxable[]" id="tax-${lineCount}" value="1" checked onchange="calcLine(${lineCount})">
            <input type="hidden" name="item_is_taxable_hidden[]" value="1">
        </td>
        <td id="lineTotal-${lineCount}" style="text-align:right;font-weight:600;padding-right:20px;">--</td>
        <td><button type="button" onclick="removeLine(${lineCount})" style="background:none;border:none;cursor:pointer;font-size:16px;">❌</button></td>
    `;
        document.getElementById('itemsBody').appendChild(row);
    }

    async function searchProduct(input, line) {
        const query = input.value.trim();
        const resultsDiv = document.getElementById('results-' + line);

        try {
            const resp = await fetch('<?= url("api/products/search") ?>?q=' + encodeURIComponent(query));
            const products = await resp.json();

            if (products.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            resultsDiv.innerHTML = '';
            products.forEach(p => {
                const item = document.createElement('div');
                item.className = 'search-item';
                item.innerHTML = `
                    <div style="flex:1;">
                        <div style="font-weight:500;">${p.name}</div>
                        <span class="sku" style="font-size:10px;padding:1px 4px;">${p.sku || 'S/N'}</span>
                    </div>
                    <div class="price" style="font-weight:700;color:var(--primary);">${currentCurrency} ${parseFloat(p.price).toLocaleString(undefined, {minimumFractionDigits:2})}</div>
                `;
                item.onclick = () => selectProduct(p, line);
                resultsDiv.appendChild(item);
            });
            resultsDiv.style.display = 'block';
        } catch (e) {
            console.error(e);
        }
    }

    function selectProduct(p, line) {
        document.getElementById('prodId-' + line).value = p.id;
        document.getElementById('desc-' + line).value = p.name;
        document.getElementById('price-' + line).value = p.price;
        document.getElementById('tax-' + line).checked = parseInt(p.is_taxable) === 1;

        const wrapper = document.querySelector(`#line-${line} .search-wrapper input[type="text"]`);
        wrapper.value = p.name + (p.sku ? ` (${p.sku})` : '');

        document.getElementById('results-' + line).style.display = 'none';
        calcLine(line);
    }

    let currentCurrency = 'DOP';

    function updateCurrencySymbol() {
        const currSelect = document.getElementById('currency');
        if (currSelect) {
            currentCurrency = currSelect.value;
        }
        calcTotals();
        // Update all line totals
        for (let i = 1; i <= lineCount; i++) {
            if (document.getElementById('lineTotal-' + i)) calcLine(i);
        }
    }

    function calcLine(line) {
        const qty = parseFloat(document.getElementById('qty-' + line).value) || 0;
        const price = parseFloat(document.getElementById('price-' + line).value) || 0;
        const discPercent = parseFloat(document.getElementById('disc-' + line).value) || 0;

        const discAmount = (qty * price) * (discPercent / 100);
        const total = (qty * price) - discAmount;
        document.getElementById('lineTotal-' + line).textContent = currentCurrency + ' ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
        calcTotals();
    }

    function removeLine(line) {
        const row = document.getElementById('line-' + line);
        if (row) row.remove();
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        let totalDiscount = 0;
        let taxableSubtotal = 0;

        document.querySelectorAll('#itemsBody tr').forEach(row => {
            const line = row.id.split('-')[1];
            const qty = parseFloat(document.getElementById('qty-' + line).value) || 0;
            const price = parseFloat(document.getElementById('price-' + line).value) || 0;
            const discPercent = parseFloat(document.getElementById('disc-' + line).value) || 0;
            const isTaxable = document.getElementById('tax-' + line).checked;

            const lineSubtotal = qty * price;
            const discAmount = lineSubtotal * (discPercent / 100);

            subtotal += lineSubtotal;
            totalDiscount += discAmount;

            if (isTaxable) {
                taxableSubtotal += (lineSubtotal - discAmount);
            }
        });

        const tax = taxableSubtotal * 0.18;
        const finalTotal = (subtotal - totalDiscount) + tax;

        document.getElementById('subtotalDisplay').textContent = currentCurrency + ' ' + subtotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('discountDisplay').textContent = currentCurrency + ' ' + totalDiscount.toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('taxDisplay').textContent = currentCurrency + ' ' + tax.toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('totalDisplay').textContent = currentCurrency + ' ' + finalTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Cerrar resultados al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-wrapper')) {
            document.querySelectorAll('.search-results').forEach(r => r.style.display = 'none');
        }
    });

    addLine();
</script>

<?php \Core\View::endSection(); ?>