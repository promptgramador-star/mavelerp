<?php \Core\View::startSection('content'); ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h1>Importar Productos y Servicios</h1>
        <p>Sube un archivo CSV para cargar artículos de forma masiva</p>
    </div>
    <a href="<?= url('products') ?>" class="btn">Volver al Inventario</a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

    <!-- Formulario de carga -->
    <div class="card">
        <div class="card-header">
            <h2>Subir Archivo</h2>
        </div>
        <div class="card-body">
            <form action="<?= url('products/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group" style="margin-bottom:20px;">
                    <label>Selecciona Archivo (.csv)</label>
                    <input type="file" name="csv_file" accept=".csv" required
                        style="width:100%;padding:10px;border:2px dashed var(--border);border-radius:8px;background:#f9fafb;">
                </div>

                <div
                    style="background:#eff6ff;padding:15px;border-radius:8px;margin-bottom:20px;font-size:14px;color:#1e40af;">
                    <strong>💡 Tip:</strong> Asegúrate de que el archivo use codificación UTF-8 para evitar errores con
                    caracteres especiales.
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Iniciar Importación</button>
            </form>
        </div>
    </div>

    <!-- Instrucciones y Plantilla -->
    <div class="card">
        <div class="card-header">
            <h2>Instrucciones</h2>
        </div>
        <div class="card-body">
            <p>Sigue estos pasos para una importación exitosa:</p>
            <ol style="margin-left:20px;line-height:1.6;">
                <li>Descarga la <strong>plantilla de ejemplo</strong>.</li>
                <li>Llena los campos respetando las cabeceras.</li>
                <li>El campo <code>es_servicio</code> debe ser <strong>1</strong> para servicios y <strong>0</strong>
                    para productos físicos.</li>
                <li>Guarda el archivo en formato <strong>CSV (delimitado por comas)</strong>.</li>
                <li>Sube el archivo en el formulario de la izquierda.</li>
            </ol>

            <div
                style="margin-top:30px;text-align:center;padding:20px;border:1px solid var(--border);border-radius:12px;background:#fdfdfd;">
                <p style="margin-bottom:15px;">¿No tienes la plantilla?</p>
                <a href="<?= url('products/template') ?>" class="btn" style="background:var(--success);color:#fff;">
                    📥 Descargar Plantilla CSV
                </a>
            </div>
        </div>
    </div>

</div>

<?php \Core\View::endSection(); ?>