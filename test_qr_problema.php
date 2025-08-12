<?php
// Test con el QR que causó el problema

$qr_json = '{"codigo_qr":"8ebbd5201af960e116eee9cb436e4de4508d47c2","inscripcion_id":"8","timestamp":1755019259,"evento_hash":"b788e0b4f660efc3c1f03fc6b3a853e39e885a278db3b53aaa976b2e5e3cd474","hash":"662d1b60940549568fa88a3376bd2db6224e81742134d4bfa66ac05523402dc5"}';

$url = 'http://localhost/claudeson4-qr/api/verificar_qr.php';
$data = json_encode(['codigo_qr' => $qr_json]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

echo "=== TESTING QR PROBLEMÁTICO ===" . PHP_EOL;
echo "QR JSON: $qr_json" . PHP_EOL . PHP_EOL;

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

if (curl_error($ch)) {
    echo "CURL ERROR: " . curl_error($ch) . PHP_EOL;
} else {
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "HTTP CODE: $httpCode" . PHP_EOL;
    echo "BODY:" . PHP_EOL . $body . PHP_EOL;
    
    // Verificar si hay warnings de PHP
    if (strpos($body, '<br />') !== false) {
        echo "⚠️  CONTIENE WARNINGS DE PHP" . PHP_EOL;
    } else {
        echo "✅ SIN WARNINGS DE PHP" . PHP_EOL;
    }
    
    // Intentar parsear JSON
    $json = json_decode($body, true);
    if ($json === null) {
        echo "❌ JSON PARSE ERROR: " . json_last_error_msg() . PHP_EOL;
    } else {
        echo "✅ JSON PARSED OK:" . PHP_EOL;
        print_r($json);
    }
}

curl_close($ch);
?>
