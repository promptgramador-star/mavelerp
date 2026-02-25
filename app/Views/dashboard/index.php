<?php \Core\View::startSection('content'); ?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Bienvenido, <?= e(\Core\Session::get('user_name', '')) ?></p>
</div>

<!-- Estadísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <h3><?= (int) ($stats['customers'] ?? 0) ?></h3>
            <p>Clientes</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏭</div>
        <div class="stat-info">
            <h3><?= (int) ($stats['suppliers'] ?? 0) ?></h3>
            <p>Proveedores</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <h3><?= (int) ($stats['products'] ?? 0) ?></h3>
            <p>Productos</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-info">
            <h3><?= (int) ($stats['documents'] ?? 0) ?></h3>
            <p>Documentos</p>
        </div>
    </div>
</div>

<!-- Documentos Recientes -->
<div class="card">
    <div class="card-header">
        <h2>Documentos Recientes</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recentDocs)): ?>
            <p class="text-muted">No hay documentos aún.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentDocs as $doc): ?>
                        <tr>
                            <td><strong><?= e($doc['sequence_code'] ?? '-') ?></strong></td>
                            <td>
                                <span class="badge badge-<?= strtolower($doc['document_type'] ?? '') ?>">
                                    <?= e($doc['document_type'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= e($doc['customer_name'] ?? 'Sin cliente') ?></td>
                            <td><?= money((float) ($doc['total'] ?? 0)) ?></td>
                            <td>
                                <span class="status status-<?= strtolower($doc['status'] ?? 'draft') ?>">
                                    <?= e($doc['status'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= e($doc['issue_date'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php \Core\View::endSection(); ?>