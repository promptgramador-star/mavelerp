<?php
/**
 * Instalador Web — ERP Propietario RD
 * Proceso simple de configuración para hosting compartido.
 */

session_start();
define('BASE_PATH', dirname(__DIR__));

$step = (int) ($_GET['step'] ?? 1);
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);

// Si ya está instalado, redirigir al index
if (file_exists(BASE_PATH . '/config/installed.lock') && $step !== 5) {
    header('Location: ../index.php');
    exit;
}

// Lógica de procesamiento por paso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        // Guardar DB Config
        $dbConfig = [
            'host' => $_POST['db_host'],
            'database' => $_POST['db_name'],
            'username' => $_POST['db_user'],
            'password' => $_POST['db_pass']
        ];

        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $_SESSION['db_config'] = $dbConfig;
            header('Location: index.php?step=3');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error de conexión: " . $e->getMessage();
            header('Location: index.php?step=2');
            exit;
        }
    }

    if ($step === 4) {
        // Pasos finales: Ejecutar SQL + Crear archivos
        $db = $_SESSION['db_config'];
        $company = $_POST;

        try {
            $dsn = "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['username'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // 1. Ejecutar Schema
            $schema = file_get_contents(BASE_PATH . '/database/schema.sql');
            $pdo->exec($schema);

            // 2. Ejecutar Seed
            $seed = file_get_contents(BASE_PATH . '/database/seed.sql');
            $pdo->exec($seed);

            // 3. Guardar Settings
            $stmt = $pdo->prepare("INSERT IGNORE INTO settings (company_name, rnc, address, phone, email, currency) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$company['company_name'], $company['rnc'], $company['address'], $company['phone'], $company['email'], 'DOP']);

            // 4. Crear SuperAdmin
            $stmt = $pdo->prepare("INSERT IGNORE INTO users (role_id, name, email, password, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([1, 'Administrador Maestro', $company['admin_email'], password_hash($company['admin_pass'], PASSWORD_BCRYPT), 1]);

            // 5. Calcular base_url automáticamente
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            $baseDir = rtrim(dirname($scriptPath), '/');
            $baseUrl = $protocol . "://" . $host . $baseDir . "/";

            // 6. Generar config/database.php
            $dbTemplate = "<?php\nreturn " . var_export([
                'driver' => 'mysql',
                'host' => $db['host'],
                'port' => 3306,
                'database' => $db['database'],
                'username' => $db['username'],
                'password' => $db['password'],
                'charset' => 'utf8mb4',
                'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            ], true) . ";";
            file_put_contents(BASE_PATH . '/config/database.php', $dbTemplate);

            // 7. Actualizar config/app.php con la base_url détectada
            $appConfig = require BASE_PATH . '/config/app.php';
            $appConfig['base_url'] = $baseUrl;
            $appTemplate = "<?php\nreturn " . var_export($appConfig, true) . ";";
            file_put_contents(BASE_PATH . '/config/app.php', $appTemplate);

            // 8. Crear lock file
            file_put_contents(BASE_PATH . '/config/installed.lock', date('Y-m-d H:i:s'));

            header('Location: index.php?step=5');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = "Error en la instalación: " . $e->getMessage();
            header('Location: index.php?step=4');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Instalador ERP RD</title>
    <style>
        body {
            font-family: system-ui;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .wizard {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            margin-top: 0;
            color: #1e293b;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
            color: #64748b;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .step-list {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .step.active {
            background: #2563eb;
            color: white;
        }
    </style>
</head>

<body>
    <div class="wizard">
        <div class="step-list">
            <div class="step <?= $step === 1 ? 'active' : '' ?>">1</div>
            <div class="step <?= $step === 2 ? 'active' : '' ?>">2</div>
            <div class="step <?= $step === 3 ? 'active' : '' ?>">3</div>
            <div class="step <?= $step === 4 ? 'active' : '' ?>">4</div>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <h2>Bienvenido</h2>
            <p>Este asistente configurará el ERP en tu servidor. Asegúrate de tener una base de datos MySQL creada.</p>
            <button class="btn" onclick="location.href='index.php?step=2'">Empezar</button>
        <?php elseif ($step === 2): ?>
            <h2>Base de Datos</h2>
            <form method="POST">
                <div class="form-group"><label>Host</label><input name="db_host" value="localhost"></div>
                <div class="form-group"><label>Nombre de la BD</label><input name="db_name" required></div>
                <div class="form-group"><label>Usuario</label><input name="db_user" required></div>
                <div class="form-group"><label>Contraseña</label><input type="password" name="db_pass"></div>
                <button class="btn">Probar Conexión</button>
            </form>
        <?php elseif ($step === 3): ?>
            <h2>Datos de la Empresa</h2>
            <form method="POST" action="index.php?step=4">
                <div class="form-group"><label>Nombre Comercial</label><input name="company_name" required></div>
                <div class="form-group"><label>RNC</label><input name="rnc"></div>
                <div class="form-group"><label>Email de Soporte</label><input name="email" type="email" required></div>
                <div class="form-group"><label>Dirección</label><input name="address"></div>
                <div class="form-group"><label>Teléfono</label><input name="phone"></div>
                <hr>
                <h2>Cuenta de Administrador</h2>
                <div class="form-group"><label>Email Admin</label><input name="admin_email" type="email" required></div>
                <div class="form-group"><label>Contraseña Admin</label><input name="admin_pass" type="password" required>
                </div>
                <button class="btn">Finalizar Instalación</button>
            </form>
        <?php elseif ($step === 5): ?>
            <h2>¡Listo!</h2>
            <p>La instalación se ha completado con éxito.</p>
            <p>Ya puedes acceder al sistema.</p>
            <button class="btn" onclick="location.href='../index.php'">Ir al Dashboard</button>
        <?php endif; ?>
    </div>
</body>

</html>