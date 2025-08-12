<?php
require_once 'config/config.php';

echo "<h2>Test Sistema QR - Flujo Completo</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";

echo "<h3>Test API verificar_qr.php:</h3>";
echo "<form method='post'>";
echo "<input type='text' name='codigo_test' placeholder='Código QR de prueba' value='" . ($_POST['codigo_test'] ?? 'TEST123') . "'>";
echo "<button type='submit' name='accion' value='verificar'>1. Verificar QR</button>";
echo "<button type='submit' name='accion' value='confirmar'>2. Confirmar Acceso</button>";
echo "</form>";

if ($_POST['codigo_test'] && $_POST['accion']) {
    $codigo = $_POST['codigo_test'];
    $accion = $_POST['accion'];
    
    echo "<h4>Resultado:</h4>";
    
    if ($accion === 'verificar') {
        // Test verificar QR
        $api_url = BASE_URL . '/api/verificar_qr.php';
        echo "<p><strong>URL de API:</strong> $api_url</p>";
        
        $data = json_encode(['codigo_qr' => $codigo]);
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $data,
            ],
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($api_url, false, $context);
        
        if ($result === FALSE) {
            echo "<p style='color: red;'>ERROR: No se pudo conectar a la API</p>";
        } else {
            echo "<p style='color: green;'>Respuesta de verificar_qr.php:</p>";
            echo "<pre>" . htmlspecialchars($result) . "</pre>";
            
            $data_decoded = json_decode($result, true);
            if ($data_decoded && $data_decoded['success'] && $data_decoded['data']['valido']) {
                echo "<p style='color: blue;'>✓ QR válido. Estado: " . $data_decoded['data']['estado_original'] . "</p>";
                if (!$data_decoded['data']['ya_ingreso_hoy']) {
                    echo "<p style='color: orange;'>→ Puede proceder a confirmar acceso</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Ya ingresó hoy (" . $data_decoded['data']['total_ingresos_hoy'] . " veces)</p>";
                }
            }
        }
    }
    
    if ($accion === 'confirmar') {
        // Test confirmar acceso
        $api_url = BASE_URL . '/api/confirmar_acceso.php';
        echo "<p><strong>URL de API:</strong> $api_url</p>";
        
        $data = json_encode(['codigo_qr' => $codigo]);
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $data,
            ],
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($api_url, false, $context);
        
        if ($result === FALSE) {
            echo "<p style='color: red;'>ERROR: No se pudo conectar a la API</p>";
        } else {
            echo "<p style='color: green;'>Respuesta de confirmar_acceso.php:</p>";
            echo "<pre>" . htmlspecialchars($result) . "</pre>";
            
            $data_decoded = json_decode($result, true);
            if ($data_decoded && $data_decoded['success']) {
                echo "<p style='color: blue;'>✓ Acceso confirmado exitosamente</p>";
                echo "<p style='color: green;'>→ Estado cambiado a 'ingresado'</p>";
            } else {
                echo "<p style='color: red;'>✗ Error al confirmar acceso</p>";
            }
        }
    }
}

echo "<h3>Estados válidos del sistema:</h3>";
echo "<ul>";
echo "<li><strong>pendiente:</strong> Usuario registrado, puede ingresar</li>";
echo "<li><strong>confirmado:</strong> Usuario confirmado, puede ingresar</li>";
echo "<li><strong>ingresado:</strong> Usuario ya ingresó al evento</li>";
echo "</ul>";

echo "<h3>Archivos necesarios:</h3>";
$files_to_check = [
    'api/verificar_qr.php',
    'api/confirmar_acceso.php',
    'config/database.php',
    'models/Inscripcion.php',
    'classes/Database.php'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ $file existe</p>";
    } else {
        echo "<p style='color: red;'>✗ $file NO EXISTE</p>";
    }
}
?>
