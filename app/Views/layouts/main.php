<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($title ?? 'ERP Propietario RD') ?>
    </title>
    <link rel="stylesheet" href="<?= url('public/css/app.css') ?>?v=<?= time() ?>">
</head>

<body>
    <?php if (\Core\Auth::check()): ?>
        <div class="app-layout">
            <!-- Sidebar Navigation -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <a href="<?= url('dashboard') ?>" class="sidebar-logo">
                        <?php
                        $settings = get_settings();
                        $logo = !empty($settings['logo']) ? url($settings['logo']) : null;
                        if ($logo): ?>
                            <img src="<?= $logo ?>" alt="Logo">
                        <?php else: ?>
                            ERP<span>RD</span>
                        <?php endif; ?>
                    </a>
                    <button class="mobile-close" id="mobileClose">&times;</button>
                </div>

                <nav class="sidebar-nav">
                    <a href="<?= url('dashboard') ?>" class="nav-item <?= is_active('dashboard') ?>">
                        <span class="nav-icon">📊</span>
                        <span class="nav-label">Dashboard</span>
                    </a>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-label">Administrador</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <?php if (\Core\Auth::isAdmin()): ?>
                                <a href="<?= url('settings') ?>" class="<?= is_active('settings') ?>">Configuración</a>
                            <?php endif; ?>
                            <?php if (\Core\Auth::isSuperAdmin()): ?>
                                <a href="<?= url('users') ?>" class="<?= is_active('users') ?>">Usuarios</a>
                                <a href="<?= url('modules') ?>" class="<?= is_active('modules') ?>">Módulos</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">📦</span>
                            <span class="nav-label">Almacén</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <a href="<?= url('products') ?>" class="<?= is_active('products') ?>">Productos</a>
                        </div>
                    </div>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">🛒</span>
                            <span class="nav-label">Compras</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <a href="<?= url('purchases') ?>" class="<?= is_active('purchases') ?>">Órdenes de Compra</a>
                            <a href="<?= url('suppliers') ?>" class="<?= is_active('suppliers') ?>">Proveedores</a>
                        </div>
                    </div>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">💰</span>
                            <span class="nav-label">Ventas</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <a href="<?= url('customers') ?>" class="<?= is_active('customers') ?>">Clientes</a>
                            <a href="<?= url('quotations') ?>" class="<?= is_active('quotations') ?>">Cotizaciones</a>
                            <a href="<?= url('invoices') ?>" class="<?= is_active('invoices') ?>">Facturas</a>
                        </div>
                    </div>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">⚖️</span>
                            <span class="nav-label">Contabilidad</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <a href="#">Próximamente</a>
                        </div>
                    </div>

                    <div class="nav-group">
                        <button class="nav-group-header">
                            <span class="nav-icon">📈</span>
                            <span class="nav-label">Informes</span>
                            <span class="nav-arrow">▾</span>
                        </button>
                        <div class="nav-group-items">
                            <a href="#">Próximamente</a>
                        </div>
                    </div>
                </nav>

                <div class="sidebar-footer">
                    <div class="user-block">
                        <div class="user-info">
                            <span class="user-name"><?= e(\Core\Session::get('user_name', '')) ?></span>
                        </div>
                        <a href="<?= url('logout') ?>" class="logout-link">Cerrar sesión</a>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="main-wrapper">
                <header class="main-header">
                    <button class="mobile-toggle" id="mobileToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <div class="breadcrumb">
                        <?= e($title ?? 'ERP Propietario RD') ?>
                    </div>
                </header>

                <main class="main-content">
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
        </div>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <?php else: ?>
        <?= \Core\View::section('content') ?>
    <?php endif; ?>

    <script src="<?= url('public/js/app.js') ?>"></script>
</body>

</html>