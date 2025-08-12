<?php
// Fix temporal para forzar BASE_URL correcto
// Este archivo se puede usar para override manual

// Detectar el entorno
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Si es un túnel de VS Code Dev Tunnels
if (strpos($host, '.devtunnels.ms') !== false) {
    define('BASE_URL_OVERRIDE', 'https://' . $host);
    echo "<!-- OVERRIDE: Usando túnel VS Code: https://$host -->";
} else {
    // Para desarrollo local
    define('BASE_URL_OVERRIDE', 'http://localhost/claudeson4-qr');
    echo "<!-- OVERRIDE: Usando localhost -->";
}

// Esta variable se puede usar en lugar de BASE_URL
define('BASE_URL_FIXED', BASE_URL_OVERRIDE);
?>
