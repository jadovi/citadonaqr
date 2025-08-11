<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['codigo_qr']) || empty($input['codigo_qr'])) {
        echo json_encode(['success' => false, 'message' => 'Código QR requerido']);
        exit;
    }

    $codigoQR = $input['codigo_qr'];
    
    // Verificar si es JSON (nuevo formato) o código simple (formato legacy)
    $datosDecodificados = json_decode($codigoQR, true);
    $codigoQRReal = $codigoQR;
    
    if ($datosDecodificados && isset($datosDecodificados['codigo_qr'])) {
        // Extraer código QR real del JSON
        $codigoQRReal = $datosDecodificados['codigo_qr'];
    }
    
    // Obtener IP y User Agent
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    // Confirmar acceso
    $inscripcion = new Inscripcion();
    $resultado = $inscripcion->marcarIngreso($codigoQRReal, $ipAddress, $userAgent);
    
    if ($resultado) {
        // Obtener información del visitante para el mensaje
        $info = $inscripcion->obtenerPorCodigoQR($codigoQR);
        
        echo json_encode([
            'success' => true,
            'message' => "Acceso confirmado para {$info['nombre']} {$info['apellido']} al evento {$info['evento_nombre']}"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo confirmar el acceso'
        ]);
    }

} catch (Exception $e) {
    error_log("Error en confirmar_acceso.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
