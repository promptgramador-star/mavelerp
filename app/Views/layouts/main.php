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
        <!-- Navbar / Navegación -->
        <div class="app-layout">
            <header class="top-navbar">
                <div class="navbar-brand">
                    <a href="<?= url('dashboard') ?>">ERP<span>RD</span></a>
                </div>
                
                <ul class="navbar-nav">
                    <li><a href="<?= url('dashboard') ?>" class="nav-link">Dashboard</a></li>

                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Administrador ▾</a>
                        <ul class="dropdown-menu">
                            <?php if (\Core\Auth::isAdmin()): ?>
                                <li><a href="<?= url('settings') ?>">Configuración</a></li>
                            <?php endif; ?>
                            <?php if (\Core\Auth::isSuperAdmin()): ?>
                                <li><a href="<?= url('users') ?>">Usuarios</a></li>
                                <li><a href="<?= url('modules') ?>">Módulos</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Almacén ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= url('products') ?>">Productos</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Compras ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= url('suppliers') ?>">Proveedores</a></li>
                        </ul>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Ventas ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= url('customers') ?>">Clientes</a></li>
                            <li><a href="<?= url('quotations') ?>">Cotizaciones</a></li>
                            <li><a href="<?= url('invoices') ?>">Facturas</a></li>
                        </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Contabilidad ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Próximamente</a></li>
                        </ul>
                    </li>
                    
                    <li class="dropdown">
                        <a href="#" class="nav-link dropdown-toggle">Informes ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Próximamente</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="navbar-footer">
                    <div class="user-info">
                        <span class="user-name">
                            <?= e(\Core\Session::get('user_name', '')) ?>
                        </span>
                    </div>
                    <a href="<?= url('logout') ?>" class="btn-logout">Cerrar sesión</a>
                </div>
            </header>

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