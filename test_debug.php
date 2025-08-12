<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'models/Inscripcion.php';

echo "<h2>Test del Sistema de Debug</h2>";

echo "<h3>Configuración Debug:</h3>";
echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "<br>";
echo "SKIP_DATE_VALIDATION: " . (SKIP_DATE_VALIDATION ? 'true' : 'false') . "<br>";
echo "SKIP_TIME_VALIDATION: " . (SKIP_TIME_VALIDATION ? 'true' : 'false') . "<br>";
echo "ALLOW_ALL_STATES: " . (ALLOW_ALL_STATES ? 'true' : 'false') . "<br>";

echo "<hr>";

// Obtener algunas inscripciones para testing
$db = Database::getInstance();
$inscripciones = $db->query("
    SELECT i.id, i.codigo_qr, v.nombre, v.email, i.estado, i.evento_id, e.nombre as evento_nombre
    FROM inscripciones i 
    LEFT JOIN visitantes v ON i.visitante_id = v.id 
    LEFT JOIN eventos e ON i.evento_id = e.id
    LIMIT 5
")->fetchAll();

echo "<h3>Inscripciones Disponibles para Testing:</h3>";
echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>ID</th><th>Código QR</th><th>Nombre</th><th>Email</th><th>Estado</th><th>Evento</th><th>Acción</th></tr>";

foreach ($inscripciones as $ins) {
    echo "<tr>";
    echo "<td>{$ins['id']}</td>";
    echo "<td>{$ins['codigo_qr']}</td>";
    echo "<td>{$ins['nombre']}</td>";
    echo "<td>{$ins['email']}</td>";
    echo "<td><strong>{$ins['estado']}</strong></td>";
    echo "<td>{$ins['evento_nombre']}</td>";
    echo "<td>";
    echo "<a href='test_verificar.php?qr={$ins['codigo_qr']}' target='_blank' style='margin: 2px; padding: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;'>Verificar</a>";
    echo "<a href='test_confirmar.php?qr={$ins['codigo_qr']}' target='_blank' style='margin: 2px; padding: 5px; background: #28a745; color: white; text-decoration: none; border-radius: 3px;'>Confirmar</a>";
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Test QR Simple:</h3>";
if (count($inscripciones) > 0) {
    $qr_test = $inscripciones[0]['codigo_qr'];
    echo "<p>Testing con QR: <strong>$qr_test</strong></p>";
    
    // Test directo del modelo
    $modelo = new Inscripcion();
    $resultado = $modelo->verificarAcceso($qr_test);
    
    echo "<h4>Resultado verificarAcceso():</h4>";
    echo "<pre>";
    print_r($resultado);
    echo "</pre>";
}

echo "<hr>";
echo "<h3>Enlaces de Test:</h3>";
echo "<p><a href='scanner.php' target='_blank'>Abrir Scanner</a></p>";
echo "<p><a href='test_simple.php' target='_blank'>Test Simple API</a></p>";
?>
