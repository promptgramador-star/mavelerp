<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($title ?? 'ERP Propietario RD') ?>
    </title>
    <link rel="stylesheet" href="<?= url('public/css/app.css') ?>">
</head>

<body>
    <?php if (\Core\Auth::check()): ?>
        <!-- Sidebar / Navegación -->
        <div class="app-layout">
            <nav class="sidebar">
                <div class="sidebar-header">
                    <h2 class="sidebar-brand">ERP<span>RD</span></h2>
                </div>
                <ul class="sidebar-nav">
                    <li><a href="<?= url('dashboard') ?>" class="nav-link"><span class="nav-icon">📊</span> Dashboard</a>
                    </li>

                    <!-- CRM -->
                    <li><a href="<?= url('customers') ?>" class="nav-link"><span class="nav-icon">👤</span> Clientes</a>
                    </li>
                    <li><a href="<?= url('suppliers') ?>" class="nav-link"><span class="nav-icon">🏭</span> Proveedores</a>
                    </li>

                    <!-- Inventario -->
                    <li><a href="<?= url('products') ?>" class="nav-link"><span class="nav-icon">📦</span> Productos</a>
                    </li>

                    <!-- Facturación -->
                    <li><a href="<?= url('quotations') ?>" class="nav-link"><span class="nav-icon">📋</span>
                            Cotizaciones</a></li>
                    <li><a href="<?= url('invoices') ?>" class="nav-link"><span class="nav-icon">🧾</span> Facturas</a></li>

                    <?php if (\Core\Auth::isAdmin()): ?>
                        <li><a href="<?= url('settings') ?>" class="nav-link"><span class="nav-icon">⚙️</span> Configuración</a>
                        </li>
                    <?php endif; ?>
                    <?php if (\Core\Auth::isSuperAdmin()): ?>
                        <li><a href="<?= url('users') ?>" class="nav-link"><span class="nav-icon">👥</span> Usuarios</a></li>
                        <li><a href="<?= url('modules') ?>" class="nav-link"><span class="nav-icon">🧩</span> Módulos</a></li>
                    <?php endif; ?>
                </ul>
                <div class="sidebar-footer">
                    <div class="user-info">
                        <span class="user-name">
                            <?= e(\Core\Session::get('user_name', '')) ?>
                        </span>
                        <span class="user-role">
                            <?= e(\Core\Session::get('user_role', '')) ?>
                        </span>
                    </div>
                    <a href="<?= url('logout') ?>" class="btn-logout">Cerrar sesión</a>
                </div>
            </nav>

            <main class="main-content">
                <!-- Flash messages -->
                <?php if ($success = flash('success')): ?>
                    <div class="alert alert-success">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>
                <?php if ($error = flash('error')): ?>
                    <div class="alert alert-error">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <?= \Core\View::section('content') ?>
            </main>
        </div>
    <?php else: ?>
        <?= \Core\View::section('content') ?>
    <?php endif; ?>

    <script src="<?= url('public/js/app.js') ?>"></script>
</body>

</html>