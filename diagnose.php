<?php
/**
 * Diagnóstico completo del ERP — Acceder via navegador
 * URL: http://localhost/erprd/diagnose.php
 * 
 * ELIMINAR ESTE ARCHIVO DESPUÉS DE DIAGNOSTICAR
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', __DIR__);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico ERP</title>
<style>body{font-family:system-ui;max-width:900px;margin:40px auto;padding:0 20px;background:#f8f9fa;}
h2{color:#1e293b;border-bottom:2px solid #3b82f6;padding-bottom:8px;}
.ok{color:#22c55e;font-weight:bold;} .fail{color:#ef4444;font-weight:bold;} .warn{color:#f59e0b;font-weight:bold;}
pre{background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:8px;overflow-x:auto;}
table{width:100%;border-collapse:collapse;} td,th{border:1px solid #e2e8f0;padding:8px 12px;text-align:left;}
th{background:#f1f5f9;}</style></head><body>";

echo "<h1>🔍 Diagnóstico ERP Propietario RD</h1>";
echo "<p>Ejecutado: " . date('Y-m-d H:i:s') . "</p>";

// ═══════════════════════════════════════════════════
// 1. VERSIONES
// ═══════════════════════════════════════════════════
echo "<h2>1. Versiones del Sistema</h2>";
echo "<table>";
echo "<tr><td>PHP</td><td>" . phpversion() . "</td><td>" . (version_compare(PHP_VERSION, '8.0.0', '>=') ? "<span class='ok'>✅ OK</span>" : "<span class='fail'>❌ Requiere PHP 8.0+</span>") . "</td></tr>";
echo "<tr><td>Sistema Operativo</td><td>" . PHP_OS . "</td><td>—</td></tr>";
echo "<tr><td>Servidor</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "</td><td>—</td></tr>";
echo "<tr><td>mod_rewrite</td><td>" . (in_array('mod_rewrite', apache_get_modules() ?? []) ? 'Habilitado' : 'No detectado') . "</td><td>" . (in_array('mod_rewrite', apache_get_modules() ?? []) ? "<span class='ok'>✅</span>" : "<span class='warn'>⚠️ Verificar</span>") . "</td></tr>";
echo "</table>";

// ═══════════════════════════════════════════════════
// 2. ARCHIVOS DE CONFIGURACIÓN
// ═══════════════════════════════════════════════════
echo "<h2>2. Archivos de Configuración</h2>";
$configFiles = ['config/app.php', 'config/database.php', 'config/modules.php', 'config/installed.lock'];
echo "<table>";
foreach ($configFiles as $cf) {
    $exists = file_exists(BASE_PATH . '/' . $cf);
    echo "<tr><td>{$cf}</td><td>" . ($exists ? filesize(BASE_PATH . '/' . $cf) . " bytes" : "—") . "</td><td>" . ($exists ? "<span class='ok'>✅ Existe</span>" : "<span class='fail'>❌ FALTA</span>") . "</td></tr>";
}
echo "</table>";

// ═══════════════════════════════════════════════════
// 3. BASE DE DATOS
// ═══════════════════════════════════════════════════
echo "<h2>3. Conexión a Base de Datos</h2>";
$dbOk = false;
$pdo = null;
try {
    $dbConfig = require BASE_PATH . '/config/database.php';
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $mysqlVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "<p><span class='ok'>✅ Conexión exitosa</span> — MySQL {$mysqlVersion}</p>";
    echo "<p>BD: <strong>{$dbConfig['database']}</strong> | Host: {$dbConfig['host']} | User: {$dbConfig['username']}</p>";
    $dbOk = true;
} catch (Exception $e) {
    echo "<p><span class='fail'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

// ═══════════════════════════════════════════════════
// 4. TABLAS CRÍTICAS
// ═══════════════════════════════════════════════════
if ($dbOk) {
    echo "<h2>4. Tablas en la Base de Datos</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['settings', 'roles', 'users', 'modules', 'module_license', 'customers', 'suppliers', 'products', 'documents', 'document_items'];

    echo "<table><tr><th>Tabla</th><th>Estado</th><th>Registros</th></tr>";
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "<tr><td>{$table}</td><td><span class='ok'>✅ Existe</span></td><td>{$count}</td></tr>";
        } else {
            echo "<tr><td>{$table}</td><td><span class='fail'>❌ NO EXISTE</span></td><td>—</td></tr>";
        }
    }
    echo "</table>";

    // ═══════════════════════════════════════════════════
    // 5. MÓDULOS Y LICENCIAS (CLAVE DEL PROBLEMA)
    // ═══════════════════════════════════════════════════
    echo "<h2>5. 🔑 Módulos y Licencias (PROBABLE CAUSA)</h2>";

    if (in_array('modules', $tables) && in_array('module_license', $tables)) {
        $moduleData = $pdo->query(
            "SELECT m.id, m.name, m.is_premium, ml.is_enabled, ml.activated_at 
             FROM modules m 
             LEFT JOIN module_license ml ON m.id = ml.module_id 
             ORDER BY m.id"
        )->fetchAll(PDO::FETCH_ASSOC);

        if (empty($moduleData)) {
            echo "<p><span class='fail'>❌ ¡LA TABLA modules ESTÁ VACÍA! Las rutas de CRM, Inventario, etc. NO se cargarán.</span></p>";
        } else {
            echo "<table><tr><th>ID</th><th>Módulo</th><th>Premium</th><th>Habilitado</th><th>Activado</th></tr>";
            foreach ($moduleData as $mod) {
                $enabled = $mod['is_enabled'] ?? null;
                $enabledLabel = $enabled === null
                    ? "<span class='fail'>❌ SIN LICENCIA</span>"
                    : ($enabled ? "<span class='ok'>✅ SÍ</span>" : "<span class='warn'>⚠️ NO</span>");
                echo "<tr><td>{$mod['id']}</td><td><strong>{$mod['name']}</strong></td><td>" . ($mod['is_premium'] ? 'Sí' : 'No') . "</td><td>{$enabledLabel}</td><td>" . ($mod['activated_at'] ?? '—') . "</td></tr>";
            }
            echo "</table>";
        }

        // Verificar si hay registros huérfanos
        $orphanLicenses = $pdo->query("SELECT COUNT(*) FROM module_license WHERE module_id NOT IN (SELECT id FROM modules)")->fetchColumn();
        if ($orphanLicenses > 0) {
            echo "<p><span class='warn'>⚠️ Hay {$orphanLicenses} licencias sin módulo asociado</span></p>";
        }
    } else {
        echo "<p><span class='fail'>❌ Las tablas modules/module_license no existen. Ejecuta schema.sql y seed.sql.</span></p>";
    }

    // ═══════════════════════════════════════════════════
    // 6. USUARIOS
    // ═══════════════════════════════════════════════════
    echo "<h2>6. Usuarios</h2>";
    if (in_array('users', $tables)) {
        $users = $pdo->query("SELECT u.id, u.name, u.email, r.name as role, u.is_active FROM users u LEFT JOIN roles r ON u.role_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
        echo "<table><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th></tr>";
        foreach ($users as $u) {
            echo "<tr><td>{$u['id']}</td><td>{$u['name']}</td><td>{$u['email']}</td><td>{$u['role']}</td><td>" . ($u['is_active'] ? '✅' : '❌') . "</td></tr>";
        }
        echo "</table>";
    }
}

// ═══════════════════════════════════════════════════
// 7. RESOLUCIÓN DE URL
// ═══════════════════════════════════════════════════
echo "<h2>7. Resolución de URL</h2>";
echo "<table>";
echo "<tr><td>\$_GET['url']</td><td>" . htmlspecialchars($_GET['url'] ?? '(vacío)') . "</td></tr>";
echo "<tr><td>REQUEST_URI</td><td>" . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . "</td></tr>";
echo "<tr><td>SCRIPT_NAME</td><td>" . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '') . "</td></tr>";
echo "<tr><td>DOCUMENT_ROOT</td><td>" . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? '') . "</td></tr>";
echo "<tr><td>BASE_PATH</td><td>" . htmlspecialchars(BASE_PATH) . "</td></tr>";
echo "</table>";

// ═══════════════════════════════════════════════════
// 8. RUTAS REGISTRADAS (simular carga)
// ═══════════════════════════════════════════════════
echo "<h2>8. Rutas que se Registrarían</h2>";
echo "<p><em>Simulando carga de rutas...</em></p>";

// Cargar helpers
require_once BASE_PATH . '/core/helpers.php';

// Mini autoloader para esta prueba
spl_autoload_register(function ($className) {
    $map = [
        'Core\\' => BASE_PATH . '/core/',
        'App\\Controllers\\' => BASE_PATH . '/app/Controllers/',
        'App\\Models\\' => BASE_PATH . '/app/Models/',
        'App\\Middleware\\' => BASE_PATH . '/app/Middleware/',
        'Modules\\' => BASE_PATH . '/modules/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($className, $prefix)) {
            $relativeClass = substr($className, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

try {
    Core\Session::start();
    $router = new Core\Router();

    // Cargar rutas base
    $routesFile = BASE_PATH . '/app/routes.php';
    if (file_exists($routesFile)) {
        require $routesFile;
    }

    // Cargar módulos
    $moduleLoader = new Core\ModuleLoader();
    $moduleLoader->loadModules($router);

    // Mostrar rutas registradas
    $reflection = new ReflectionClass($router);
    $prop = $reflection->getProperty('routes');
    $prop->setAccessible(true);
    $routes = $prop->getValue($router);

    echo "<table><tr><th>#</th><th>Método</th><th>Patrón</th><th>Acción</th></tr>";
    foreach ($routes as $i => $r) {
        $highlight = str_contains($r['action'], 'Customer') || str_contains($r['action'], 'Supplier') ? "style='background:#fef9c3;'" : "";
        echo "<tr {$highlight}><td>" . ($i + 1) . "</td><td>{$r['method']}</td><td>" . htmlspecialchars($r['raw_pattern']) . "</td><td>" . htmlspecialchars($r['action']) . "</td></tr>";
    }
    echo "</table>";

    $hasCrmRoutes = false;
    foreach ($routes as $r) {
        if (str_contains($r['action'], 'Customer') || str_contains($r['action'], 'Supplier')) {
            $hasCrmRoutes = true;
            break;
        }
    }

    if (!$hasCrmRoutes) {
        echo "<p><span class='fail'>❌ ¡NO HAY RUTAS CRM REGISTRADAS! El ModuleLoader no cargó el módulo CRM.</span></p>";
        echo "<p>Esto confirma que el problema está en la tabla <code>module_license</code> — el módulo CRM no está habilitado.</p>";
    } else {
        echo "<p><span class='ok'>✅ Las rutas CRM están registradas correctamente.</span></p>";
    }

} catch (Throwable $e) {
    echo "<p><span class='fail'>❌ Error al cargar rutas: " . htmlspecialchars($e->getMessage()) . "</span></p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// ═══════════════════════════════════════════════════
// 9. VERIFICAR VISTAS CRM
// ═══════════════════════════════════════════════════
echo "<h2>9. Vistas del Módulo CRM</h2>";
$crmViews = [
    'modules/CRM/Views/customers/index.php',
    'modules/CRM/Views/customers/form.php',
    'modules/CRM/Views/customers/show.php',
    'modules/CRM/Views/suppliers/index.php',
    'modules/CRM/Views/suppliers/form.php',
    'modules/CRM/Views/suppliers/show.php',
];
echo "<table>";
foreach ($crmViews as $v) {
    $exists = file_exists(BASE_PATH . '/' . $v);
    echo "<tr><td>{$v}</td><td>" . ($exists ? "<span class='ok'>✅</span>" : "<span class='fail'>❌ FALTA</span>") . "</td></tr>";
}
echo "</table>";

echo "<hr><p><strong>NOTA:</strong> Elimina este archivo después de diagnosticar: <code>diagnose.php</code></p>";
echo "</body></html>";
