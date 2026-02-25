<?php \Core\View::startSection('content'); ?>

<div class="content-header">
    <h1>Editar Usuario:
        <?= e($user['name']) ?>
    </h1>
    <a href="<?= url('users') ?>" class="btn btn-outline">Volver</a>
</div>

<div class="card card-form">
    <form action="<?= url('users/update/' . $user['id']) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" value="<?= e($user['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= e($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>Nueva Contraseña (dejar en blanco para mantener actual)</label>
            <input type="password" name="password">
        </div>

        <div class="form-group">
            <label>Rol de Usuario</label>
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                        <?= e($role['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>> Usuario Activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
        </div>
    </form>
</div>

<?php \Core\View::endSection(); ?>