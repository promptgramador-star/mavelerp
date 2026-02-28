<?php
/**
 * Funciones helper globales del ERP.
 * Este archivo se carga antes de todo lo demás.
 *
 * @package ERP\Core
 */

declare(strict_types=1);

/**
 * Genera una URL completa basada en la URL base de la aplicación.
 */
function url(string $path = ''): string
{
    $config = require BASE_PATH . '/config/app.php';
    $base = rtrim($config['base_url'] ?? '', '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Redirige a una URL interna.
 */
function redirect(string $path = ''): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Obtiene un valor de configuración.
 */
function config(string $file, ?string $key = null): mixed
{
    static $cache = [];

    if (!isset($cache[$file])) {
        $path = BASE_PATH . '/config/' . $file . '.php';
        if (!file_exists($path)) {
            return null;
        }
        $cache[$file] = require $path;
    }

    if ($key === null) {
        return $cache[$file];
    }

    return $cache[$file][$key] ?? null;
}

/**
 * Debug dump and die.
 */
function dd(mixed ...$vars): void
{
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;border-radius:8px;overflow:auto;">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n---\n";
    }
    echo '</pre>';
    die();
}

/**
 * Escapa HTML para prevenir XSS.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un token CSRF y lo almacena en sesión.
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

/**
 * Genera un campo hidden con el token CSRF.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Valida el token CSRF de un formulario POST.
 */
function csrf_verify(): bool
{
    $token = $_POST['_csrf_token'] ?? '';
    return hash_equals(csrf_token(), $token);
}

/**
 * Obtiene un valor de la sesión flash (mensaje de una sola vez).
 */
function flash(?string $key = null, mixed $value = null): mixed
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Setter
    if ($key !== null && $value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    // Getter
    if ($key !== null) {
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    return null;
}

/**
 * Devuelve la fecha/hora actual en formato MySQL.
 */
function now(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Formatea un número como moneda dominicana.
 */
function money(float|int $amount, string $currency = 'DOP'): string
{
    return $currency . ' ' . number_format((float) $amount, 2, '.', ',');
}
/**
 * Obtiene la configuración de la empresa desde la base de datos.
 */
function get_settings(): array
{
    static $settings = null;
    if ($settings === null) {
        try {
            $db = \Core\Database::getInstance();
            $settings = $db->fetch("SELECT * FROM settings LIMIT 1") ?: [];
        } catch (\Exception $e) {
            $settings = [];
        }
    }
    return $settings;
}
/**
 * Comprueba si la ruta actual coincide con la dada.
 */
function is_active(string $path): string
{
    $currentUrl = $_GET['url'] ?? '';
    $path = trim($path, '/');
    return (strpos($currentUrl, $path) === 0) ? 'active' : '';
}

/**
 * Valida el nombre o razon social de un cliente/proveedor
 */
function validate_entity_name(string $name): ?string {
    $cleanName = trim($name);
    if (strlen($cleanName) < 3) {
        return 'Nombre inválido. Debe contener un nombre o razón social válida.';
    }
    if (preg_match('/^\d+$/', $cleanName)) {
        return 'Nombre inválido. Debe contener un nombre o razón social válida.';
    }
    if (in_array(strtolower($cleanName), ['me', 'yo'])) {
        return 'Nombre inválido. Debe contener un nombre o razón social válida.';
    }
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $cleanName)) {
        return 'Nombre inválido. Debe contener un nombre o razón social válida.';
    }
    return null;
}

/**
 * Valida el RNC o Cédula
 */
function validate_rnc_cedula(string $rnc): ?string {
    $rnc = trim($rnc);
    if (empty($rnc)) return null;

    if (!preg_match('/^[0-9\-]+$/', $rnc)) {
        return 'Caracteres inválidos en el documento (solo números y guiones).';
    }

    $cleanRnc = str_replace('-', '', $rnc);
    $len = strlen($cleanRnc);

    if ($len === 9) {
        if (strpos($rnc, '-') !== false && !preg_match('/^\d{3}-\d{5}-\d{1}$/', $rnc)) {
            return 'RNC inválido. Verifique el formato.';
        }
        return null;
    } elseif ($len === 11) {
        if (strpos($rnc, '-') !== false && !preg_match('/^\d{3}-\d{7}-\d{1}$/', $rnc)) {
            return 'Cédula inválida. Debe contener 11 dígitos.';
        }
        return null;
    }

    return ($len < 11 && $len !== 9) ? 'RNC inválido. Verifique el formato.' : 'Cédula inválida. Debe contener 11 dígitos.';
}

/**
 * Valida un teléfono
 */
function validate_phone(string $phone): ?string {
    if (empty(trim($phone))) return null;
    if (strlen(trim($phone)) > 20) {
        return 'El teléfono no puede exceder los 20 caracteres.';
    }
    return null;
}

