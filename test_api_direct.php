<?php
// Test directo del API verificar_qr.php

$qr_code = '1ec4d8bff3b031c5dc9b1d3d5b3e02d62c08765d'; // QR de prueba

$data = json_encode(['codigo_qr' => $qr_code]);

// Simular la llamada POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Capturar toda la salida
ob_start();

// Simular php://input
$GLOBALS['HTTP_RAW_POST_DATA'] = $data;

// Mockear file_get_contents('php://input')
function mock_file_get_contents($filename) {
    if ($filename === 'php://input') {
        return $GLOBALS['HTTP_RAW_POST_DATA'];
    }
    return file_get_contents($filename);
}

// Reemplazar temporalmente la función
eval('
function file_get_contents($filename) {
    return mock_file_get_contents($filename);
}
');

echo "=== TESTING API VERIFICAR_QR.PHP ===" . PHP_EOL;
echo "Input: $data" . PHP_EOL . PHP_EOL;

echo "=== OUTPUT ===" . PHP_EOL;

try {
    include 'api/verificar_qr.php';
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . PHP_EOL;
}

$output = ob_get_clean();
echo $output;

echo PHP_EOL . "=== END OUTPUT ===" . PHP_EOL;
?>
