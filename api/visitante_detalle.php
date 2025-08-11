<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID de visitante requerido']);
        exit;
    }

    $visitanteId = (int)$_GET['id'];
    
    $visitante = new Visitante();
    $inscripcion = new Inscripcion();
    
    // Obtener datos del visitante
    $datosVisitante = $visitante->obtenerPorId($visitanteId);
    
    if (!$datosVisitante) {
        echo json_encode(['success' => false, 'message' => 'Visitante no encontrado']);
        exit;
    }
    
    // Obtener inscripciones del visitante
    $inscripciones = $inscripcion->obtenerPorVisitante($visitanteId);
    
    // Generar HTML para el modal
    $html = generarHtmlDetalle($datosVisitante, $inscripciones);
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    error_log("Error en visitante_detalle.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

function generarHtmlDetalle($visitante, $inscripciones) {
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-6">
            <h6 class="fw-bold text-primary">Información Personal</h6>
            <table class="table table-borderless table-sm">
                <tr>
                    <td class="fw-bold">Nombre:</td>
                    <td><?= htmlspecialchars($visitante['nombre'] . ' ' . $visitante['apellido']) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">Email:</td>
                    <td><?= htmlspecialchars($visitante['email']) ?></td>
                </tr>
                <?php if ($visitante['telefono']): ?>
                <tr>
                    <td class="fw-bold">Teléfono:</td>
                    <td><?= htmlspecialchars($visitante['telefono']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($visitante['empresa']): ?>
                <tr>
                    <td class="fw-bold">Empresa:</td>
                    <td><?= htmlspecialchars($visitante['empresa']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($visitante['cargo']): ?>
                <tr>
                    <td class="fw-bold">Cargo:</td>
                    <td><?= htmlspecialchars($visitante['cargo']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($visitante['rut']): ?>
                <tr>
                    <td class="fw-bold">RUT:</td>
                    <td><?= htmlspecialchars($visitante['rut']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="fw-bold">Registrado:</td>
                    <td><?= date('d/m/Y H:i', strtotime($visitante['created_at'])) ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="fw-bold text-success">Estadísticas</h6>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body py-2">
                            <h4 class="mb-0"><?= count($inscripciones) ?></h4>
                            <small>Inscripciones</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body py-2">
                            <h4 class="mb-0"><?= array_sum(array_column($inscripciones, 'total_accesos')) ?></h4>
                            <small>Accesos Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($inscripciones)): ?>
    <hr>
    <h6 class="fw-bold text-info">
        <i class="bi bi-calendar-event"></i>
        Inscripciones a Eventos
    </h6>
    
    <div class="event-list">
        <?php foreach ($inscripciones as $inscripcion): ?>
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-1"><?= htmlspecialchars($inscripcion['evento_nombre']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($inscripcion['empresa']) ?></small>
                    </div>
                    <div class="col-md-3 text-center">
                        <?php
                        $badgeClass = 'secondary';
                        $estadoTexto = $inscripcion['estado'];
                        
                        switch ($inscripcion['estado']) {
                            case 'confirmado':
                                $badgeClass = 'success';
                                $estadoTexto = 'Confirmado';
                                break;
                            case 'pendiente':
                                $badgeClass = 'warning';
                                $estadoTexto = 'Pendiente';
                                break;
                            case 'cancelado':
                                $badgeClass = 'danger';
                                $estadoTexto = 'Cancelado';
                                break;
                        }
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>"><?= $estadoTexto ?></span>
                    </div>
                    <div class="col-md-3 text-center">
                        <small class="text-muted d-block">Accesos</small>
                        <strong class="text-primary"><?= $inscripcion['total_accesos'] ?></strong>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i>
                            <?= date('d/m/Y', strtotime($inscripcion['fecha_inicio'])) ?> - 
                            <?= date('d/m/Y', strtotime($inscripcion['fecha_fin'])) ?>
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-clock"></i>
                            Inscrito: <?= date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])) ?>
                        </small>
                    </div>
                </div>
                
                <?php if ($inscripcion['ultimo_acceso']): ?>
                <div class="row mt-1">
                    <div class="col-12">
                        <small class="text-success">
                            <i class="bi bi-check-circle"></i>
                            Último acceso: <?= date('d/m/Y H:i', strtotime($inscripcion['ultimo_acceso'])) ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">
                            <strong>Código QR:</strong> 
                            <code><?= htmlspecialchars($inscripcion['codigo_qr']) ?></code>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <hr>
    <div class="text-center py-3">
        <i class="bi bi-calendar-x text-muted" style="font-size: 2rem;"></i>
        <p class="text-muted mt-2">Este visitante no tiene inscripciones a eventos</p>
    </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
