<?php
// Script temporal para autocompletar la activación del módulo usando la API nativa de PDO
define('BASE_PATH', __DIR__);

// Mock environment and load settings
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

try {
    // Attempting to read installed config if any
    $configPath = BASE_PATH . '/config/database.php';
    if (!file_exists($configPath) || empty((include $configPath)['database'])) {
        // Fallback for this specific user's environment if config is empty 
        // We know from early context it's a test environment mapped to Hostinger maybe via local MySQL
        $dsn = "mysql:host=localhost;port=3306;dbname=mavelerp_db;charset=utf8mb4";
        $user = 'root';
        $pass = ''; // Assuming xampp default for local mapping
    } else {
        $cfg = include $configPath;
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
        $user = $cfg['username'];
        $pass = $cfg['password'];
    }

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Conexión a BD SQLite o MySQL lograda.\n";

    // Insert module
    $pdo->exec("INSERT IGNORE INTO modules (name, display_name, description, version, is_core) 
                VALUES ('m_compras', 'Compras', 'Módulo de compras y órdenes', '1.0.0', 0)");

    // Get ID
    $stmt = $pdo->query("SELECT id FROM modules WHERE name = 'm_compras'");
    $mod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mod) {
        // Activate
        $stmt = $pdo->prepare("INSERT IGNORE INTO module_license (module_id, is_enabled, activated_at) VALUES (?, 1, NOW())");
        $stmt->execute([$mod['id']]);
        echo "✅ Módulo activado en DB.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
