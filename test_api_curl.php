<?php
// Test del API usando cURL

$qr_code = '1ec4d8bff3b031c5dc9b1d3d5b3e02d62c08765d'; // QR de prueba
$url = 'http://localhost/claudeson4-qr/api/verificar_qr.php';

$data = json_encode(['codigo_qr' => $qr_code]);

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

echo "=== TESTING API CON CURL ===" . PHP_EOL;
echo "URL: $url" . PHP_EOL;
echo "Data: $data" . PHP_EOL . PHP_EOL;

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

if (curl_error($ch)) {
    echo "CURL ERROR: " . curl_error($ch) . PHP_EOL;
} else {
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "HTTP CODE: $httpCode" . PHP_EOL;
    echo "HEADERS:" . PHP_EOL . $headers . PHP_EOL;
    echo "BODY:" . PHP_EOL . $body . PHP_EOL;
    
    // Intentar parsear JSON
    $json = json_decode($body, true);
    if ($json === null) {
        echo "JSON PARSE ERROR: " . json_last_error_msg() . PHP_EOL;
    } else {
        echo "JSON PARSED OK:" . PHP_EOL;
        print_r($json);
    }
}

curl_close($ch);
?>
