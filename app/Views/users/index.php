<?php \Core\View::startSection('content'); ?>

<div class="content-header">
    <h1>Gestión de Usuarios</h1>
    <a href="<?= url('users/create') ?>" class="btn btn-primary">+ Nuevo Usuario</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?= e($user['name']) ?>
                    </td>
                    <td>
                        <?= e($user['email']) ?>
                    </td>
                    <td><span class="badge badge-info">
                            <?= e($user['role_name']) ?>
                        </span></td>
                    <td>
                        <?php if ($user['is_active']): ?>
                            <span class="badge badge-success">Activo</span>
                        <?php else: ?>
                            <span class="badge badge-error">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php \Core\View::endSection(); ?>