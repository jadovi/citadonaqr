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
    
    if ($datosDecodificados && isset($datosDecodificados['codigo_qr'])) {
        // Nuevo formato JSON con validación de timestamp y hash
        $resultado = verificarQRJSON($datosDecodificados);
    } else {
        // Formato legacy (código QR simple)
        $inscripcion = new Inscripcion();
        $resultado = $inscripcion->verificarAcceso($codigoQR);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ]);

} catch (Exception $e) {
    error_log("Error en verificar_qr.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

/**
 * Verificar QR en formato JSON con validación de timestamp y hash
 */
function verificarQRJSON(array $datosQR): array {
    try {
        // Validar campos requeridos
    $camposRequeridos = ['codigo_qr', 'timestamp', 'hash', 'inscripcion_id'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($datosQR[$campo])) {
                return ['valido' => false, 'mensaje' => 'Formato de QR inválido: falta campo ' . $campo];
            }
        }
        
    // Verificar que el timestamp no sea muy antiguo (máximo 30 segundos de tolerancia)
        $timestampActual = time();
        $timestampQR = $datosQR['timestamp'];
        $diferencia = abs($timestampActual - $timestampQR);
        
        if ($diferencia > 30) {
            return ['valido' => false, 'mensaje' => 'Código QR expirado o con timestamp inválido'];
        }
        
    // Verificar hash de seguridad
    $hashEsperado = hash('sha256', $datosQR['codigo_qr'] . $timestampQR . 'eventaccess_salt');
        if ($datosQR['hash'] !== $hashEsperado) {
            return ['valido' => false, 'mensaje' => 'Código QR falsificado o corrupto'];
        }
        
        // Verificar inscripción usando código QR legacy
        $inscripcion = new Inscripcion();
        $resultadoLegacy = $inscripcion->verificarAcceso($datosQR['codigo_qr']);
        
        if (!$resultadoLegacy['valido']) {
            return $resultadoLegacy;
        }
        
        // Agregar información adicional del JSON
        $resultadoLegacy['formato'] = 'json_dinamico';
        $resultadoLegacy['timestamp_qr'] = $timestampQR;
        $resultadoLegacy['timestamp_verificacion'] = $timestampActual;
        $resultadoLegacy['diferencia_tiempo'] = $diferencia;
        $resultadoLegacy['hash_valido'] = true;
        
        // Adjuntar mesa/asiento/lugar/zona si existen en la inscripción
        if (isset($resultadoLegacy['visitante'])) {
            $resultadoLegacy['visitante']['mesa'] = $resultadoLegacy['visitante']['mesa'] ?? null;
            $resultadoLegacy['visitante']['asiento'] = $resultadoLegacy['visitante']['asiento'] ?? null;
            $resultadoLegacy['visitante']['lugar'] = $resultadoLegacy['visitante']['lugar'] ?? null;
            $resultadoLegacy['visitante']['zona'] = $resultadoLegacy['visitante']['zona'] ?? null;
        }

        return $resultadoLegacy;
        
    } catch (Exception $e) {
        return ['valido' => false, 'mensaje' => 'Error procesando QR JSON: ' . $e->getMessage()];
    }
}
