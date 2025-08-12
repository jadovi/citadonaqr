<?php
require_once 'config/config.php';

echo "<h2>Prueba de configuración API</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>HOST:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p><strong>HTTPS:</strong> " . (isset($_SERVER['HTTPS']) ? 'Sí' : 'No') . "</p>";
echo "<p><strong>X-Forwarded-Proto:</strong> " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'No definido') . "</p>";

echo "<h3>Probar APIs:</h3>";
echo "<p><a href='" . BASE_URL . "/api/verificar_qr.php' target='_blank'>Verificar QR API</a></p>";
echo "<p><a href='" . BASE_URL . "/api/confirmar_acceso.php' target='_blank'>Confirmar Acceso API</a></p>";

echo "<h3>Información del servidor:</h3>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
