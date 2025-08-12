<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'models/Inscripcion.php';

$qr = $_GET['qr'] ?? '';

echo "<h2>Test Verificar QR: $qr</h2>";

if (empty($qr)) {
    echo "<p style='color: red;'>No se proporcionó código QR</p>";
    exit;
}

$modelo = new Inscripcion();
$resultado = $modelo->verificarAcceso($qr);

echo "<h3>Resultado:</h3>";
echo "<pre>";
print_r($resultado);
echo "</pre>";

if ($resultado['valido']) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>✓ QR VÁLIDO</strong><br>";
    echo "Visitante: {$resultado['data']['nombre']}<br>";
    echo "Estado: {$resultado['data']['estado']}<br>";
    echo "Evento: {$resultado['data']['evento_nombre']}";
    echo "</div>";
    
    echo "<p><a href='test_confirmar.php?qr=$qr' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Confirmar Acceso</a></p>";
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>✗ QR INVÁLIDO</strong><br>";
    echo "Mensaje: {$resultado['mensaje']}";
    echo "</div>";
}

echo "<p><a href='test_debug.php'>← Volver al test principal</a></p>";
?>
