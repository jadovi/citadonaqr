<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/config.php';

try {
    $db = Database::getInstance();
    
    // Eventos activos
    $eventosActivos = $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE activo = 1");
    
    // Total visitantes
    $totalVisitantes = $db->fetch("SELECT COUNT(*) as total FROM visitantes");
    
    // Inscripciones confirmadas
    $inscripcionesConfirmadas = $db->fetch("SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'confirmado'");
    
    // Accesos hoy
    $accesosHoy = $db->fetch("SELECT COUNT(*) as total FROM accesos WHERE DATE(fecha_ingreso) = CURDATE()");
    
    echo json_encode([
        'success' => true,
        'data' => [
            'eventos_activos' => (int)$eventosActivos['total'],
            'total_visitantes' => (int)$totalVisitantes['total'],
            'inscripciones_confirmadas' => (int)$inscripcionesConfirmadas['total'],
            'accesos_hoy' => (int)$accesosHoy['total']
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en estadisticas.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
