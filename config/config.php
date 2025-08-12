<?php
/**
 * Configuración general del sistema
 */

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de sesiones
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Santiago');

// Configuraciones generales: BASE_URL dinámico
// Detectar esquema (http/https)
$scheme = 'http';
if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $scheme = 'https';
}

// Host actual
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Base del proyecto (carpeta raíz del repo)
$projectBase = '/' . basename(realpath(__DIR__ . '/..'));

define('BASE_URL', $scheme . '://' . $host . $projectBase);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('QR_PATH', __DIR__ . '/../qr_codes/');

// Crear directorios si no existen
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

if (!file_exists(QR_PATH)) {
    mkdir(QR_PATH, 0755, true);
}

// Autoload simple
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../classes/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});
