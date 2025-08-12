<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'models/Inscripcion.php';

$qr = $_GET['qr'] ?? '';

echo "<h2>Test Confirmar Acceso: $qr</h2>";

if (empty($qr)) {
    echo "<p style='color: red;'>No se proporcionó código QR</p>";
    exit;
}

$modelo = new Inscripcion();

// Primero verificar
$verificacion = $modelo->verificarAcceso($qr);
echo "<h3>Verificación previa:</h3>";
echo "<pre>";
print_r($verificacion);
echo "</pre>";

if (!$verificacion['valido']) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>✗ No se puede confirmar acceso</strong><br>";
    echo "El QR no es válido: {$verificacion['mensaje']}";
    echo "</div>";
    echo "<p><a href='test_debug.php'>← Volver al test principal</a></p>";
    exit;
}

// Confirmar acceso
$confirmacion = $modelo->marcarIngreso($qr);
echo "<h3>Resultado de confirmar acceso:</h3>";
echo "<pre>";
print_r($confirmacion);
echo "</pre>";

if ($confirmacion['exito']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>✓ ACCESO CONFIRMADO</strong><br>";
    echo "El visitante ha sido marcado como 'ingresado'";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>✗ ERROR AL CONFIRMAR</strong><br>";
    echo "Mensaje: {$confirmacion['mensaje']}";
    echo "</div>";
}

echo "<p><a href='test_debug.php'>← Volver al test principal</a></p>";
?>
