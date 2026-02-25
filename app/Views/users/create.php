<?php \Core\View::startSection('content'); ?>

<div class="content-header">
    <h1>Crear Usuario</h1>
    <a href="<?= url('users') ?>" class="btn btn-outline">Volver</a>
</div>

<div class="card card-form">
    <form action="<?= url('users/store') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" required placeholder="Ej: Juan Pérez">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="juan@ejemplo.com">
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Rol de Usuario</label>
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>">
                        <?= e($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
        </div>
    </form>
</div>

<?php \Core\View::endSection(); ?>