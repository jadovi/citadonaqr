<?php
require_once 'config/config.php';

$error = null;
$datosQR = null;
$eventoData = null;
$visitantesEvento = [];

// Verificar hash de acceso del evento
if (!isset($_GET['access']) || empty($_GET['access'])) {
    $error = 'Enlace de acceso no válido';
} else {
    $hashAcceso = $_GET['access'];
    
    try {
        $evento = new Evento();
        $eventoData = $evento->obtenerPorHashAcceso($hashAcceso);
        
        if (!$eventoData) {
            $error = 'Evento no encontrado o no activo';
        } else {
            // Obtener visitantes del evento
            $visitantesEvento = $evento->obtenerVisitantesDelEvento($hashAcceso);
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = $eventoData ? $eventoData['nombre'] . ' - Visitantes QR' : 'Visitantes QR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 0;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .qr-container {
            background: white;
            border-radius: 20px;
            margin: 20px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            min-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .event-header {
            margin-bottom: 30px;
        }
        
        .event-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .event-company {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .event-date {
            font-size: 0.9rem;
            color: #95a5a6;
        }
        
        .qr-wrapper {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 3px solid #e9ecef;
        }
        
        #qrcode {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 250px;
        }
        
        #qrcode canvas {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .visitor-info {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #ecf0f1;
        }
        
        .visitor-name {
            font-size: 1.4rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .visitor-company {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .qr-status {
            background: #e8f5e8;
            color: #2d5a2d;
            padding: 10px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            display: inline-block;
            margin: 15px 0;
            border: 1px solid #c3e6c3;
        }
        
        .refresh-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 1000;
        }
        
        .error-container {
            text-align: center;
            padding: 40px 20px;
            color: #e74c3c;
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .security-info {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-top: 20px;
            border: 1px solid #ffeaa7;
        }

        /* Estilos para lista de visitantes */
        .visitors-list {
            margin-top: 30px;
        }
        
        .visitor-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .visitor-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .visitor-name {
            color: #2c3e50;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .visitor-company {
            color: #7f8c8d;
            font-size: 0.95rem;
        }
        
        .btn-qr {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-qr:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
        }
        
        /* Modal QR */
        .qr-container-modal {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 3px solid #e9ecef;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            border-bottom: 2px solid #ecf0f1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .loading-qr {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 250px;
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        /* Optimizaciones móviles específicas */
        @media (max-width: 576px) {
            .qr-container {
                margin: 10px;
                padding: 20px 15px;
                border-radius: 15px;
                min-height: calc(100vh - 20px);
            }
            
            .event-title {
                font-size: 1.3rem;
            }
            
            .visitor-name {
                font-size: 1.2rem;
            }
            
            #qrcode {
                min-height: 220px;
            }
            
            .qr-wrapper {
                padding: 15px;
                margin: 15px 0;
            }
        }
        
        /* Prevenir zoom en iOS */
        input, select, textarea {
            font-size: 16px;
        }
        
        /* Mejorar contraste para accesibilidad */
        @media (prefers-contrast: high) {
            .qr-wrapper {
                border-color: #000;
            }
        }
        
        /* Modo oscuro */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            }
        }
    </style>
</head>
<body>
    <!-- Indicador de actualización -->
    <div class="refresh-indicator" id="refreshIndicator" style="display: none;">
        <i class="bi bi-arrow-clockwise"></i>
        <span id="refreshText">Actualizando...</span>
    </div>

    <div class="qr-container">
        <?php if ($error): ?>
            <!-- Error -->
            <div class="error-container">
                <div class="error-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h3>Acceso No Válido</h3>
                <p><?= htmlspecialchars($error) ?></p>
                <small class="text-muted">
                    Verifica que el enlace sea correcto o contacta al organizador del evento.
                </small>
            </div>
        <?php else: ?>
            <!-- Información del evento -->
            <div class="event-header">
                <div class="event-title">
                    <?= htmlspecialchars($eventoData['nombre']) ?>
                </div>
                <div class="event-company">
                    <?= htmlspecialchars($eventoData['empresa']) ?>
                </div>
                <div class="event-date">
                    <?= date('d/m/Y', strtotime($eventoData['fecha_inicio'])) ?>
                    <?php if ($eventoData['fecha_inicio'] !== $eventoData['fecha_fin']): ?>
                        - <?= date('d/m/Y', strtotime($eventoData['fecha_fin'])) ?>
                    <?php endif; ?>
                </div>
                <div class="mt-3">
                    <span class="badge bg-primary">
                        <i class="bi bi-people me-1"></i>
                        <?= count($visitantesEvento) ?> visitantes confirmados
                    </span>
                </div>
            </div>

            <!-- Lista de visitantes -->
            <?php if (empty($visitantesEvento)): ?>
                <div class="text-center my-5">
                    <i class="bi bi-person-x" style="font-size: 3rem; color: #ccc;"></i>
                    <h4 class="mt-3 text-muted">No hay visitantes confirmados</h4>
                    <p class="text-muted">Aún no hay visitantes confirmados para este evento.</p>
                </div>
            <?php else: ?>
                <div class="visitors-list">
                    <h4 class="mb-3">
                        <i class="bi bi-people me-2"></i>
                        Visitantes del Evento
                    </h4>
                    
                    <?php foreach ($visitantesEvento as $visitante): ?>
                        <div class="visitor-card mb-3" data-codigo-qr="<?= htmlspecialchars($visitante['codigo_qr']) ?>">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="visitor-name mb-1">
                                        <?= htmlspecialchars($visitante['nombre'] . ' ' . $visitante['apellido']) ?>
                                    </h5>
                                    <?php if ($visitante['empresa']): ?>
                                        <p class="visitor-company mb-1">
                                            <i class="bi bi-building me-1"></i>
                                            <?= htmlspecialchars($visitante['empresa']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($visitante['cargo']): ?>
                                        <p class="text-muted mb-1">
                                            <i class="bi bi-person-badge me-1"></i>
                                            <?= htmlspecialchars($visitante['cargo']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="bi bi-envelope me-1"></i>
                                        <?= htmlspecialchars($visitante['email']) ?>
                                    </small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <button class="btn btn-primary btn-qr" onclick="generarQRVisitante('<?= htmlspecialchars($visitante['codigo_qr']) ?>', '<?= htmlspecialchars($visitante['nombre'] . ' ' . $visitante['apellido']) ?>')">
                                        <i class="bi bi-qr-code me-1"></i>
                                        Ver QR
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Modal QR Individual -->
            <div class="modal fade" id="qrModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-qr-code me-2"></i>
                                QR Personal
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="qr-visitante-nombre" class="mb-3"></div>
                            <div id="qrcode" class="qr-container-modal">
                                <div class="loading-qr pulse">
                                    <i class="bi bi-qr-code me-2"></i>
                                    Generando código QR...
                                </div>
                            </div>
                            <div class="qr-status mt-3">
                                <i class="bi bi-shield-check me-1"></i>
                                Código QR dinámico y seguro
                            </div>
                            <div class="security-info mt-3">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Código seguro:</strong> Este QR se actualiza automáticamente cada 7 segundos.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQRCode = null;
        let refreshTimer = null;
        let qrModal = null;

        // Inicializar modal
        document.addEventListener('DOMContentLoaded', function() {
            qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        });

        function generarQRVisitante(codigoQR, nombreVisitante) {
            // Mostrar nombre del visitante
            document.getElementById('qr-visitante-nombre').innerHTML = `
                <h5><strong>${nombreVisitante}</strong></h5>
                <p class="text-muted">QR Personal Dinámico</p>
            `;
            
            // Limpiar QR anterior
            document.getElementById('qrcode').innerHTML = `
                <div class="loading-qr pulse">
                    <i class="bi bi-qr-code me-2"></i>
                    Generando código QR...
                </div>
            `;
            
            // Mostrar modal
            qrModal.show();
            
            // Generar QR inicial
            currentQRCode = codigoQR;
            generateQRWithData(codigoQR);
            
            // Iniciar ciclo de renovación
            startQRRefreshCycle(codigoQR);
        }

        function generateQRWithData(codigoQR) {
            // Generar timestamp que cambia cada 7 segundos
            const timestamp = Math.floor(Date.now() / (7 * 1000)) * 7;
            
            // Crear datos del QR JSON
            const qrData = {
                codigo_qr: codigoQR,
                timestamp: timestamp,
                evento_hash: '<?= $error ? '' : htmlspecialchars($hashAcceso) ?>',
                hash: generateSecurityHash(codigoQR, timestamp)
            };
            
            generateQR(JSON.stringify(qrData));
        }

        function generateSecurityHash(codigoQR, timestamp) {
            // Simulación de hash de seguridad (en producción se haría en servidor)
            return btoa(codigoQR + timestamp + 'eventaccess_salt').substring(0, 32);
        }

        function generateQR(data) {
            const qrContainer = document.getElementById('qrcode');
            qrContainer.innerHTML = '';
            
            QRCode.toCanvas(qrContainer, data, {
                width: 250,
                height: 250,
                margin: 2,
                color: {
                    dark: '#2c3e50',
                    light: '#ffffff'
                },
                errorCorrectionLevel: 'M'
            }, function (error) {
                if (error) {
                    console.error('Error generando QR:', error);
                    qrContainer.innerHTML = '<div class="text-danger">Error generando QR</div>';
                }
            });
        }

        function startQRRefreshCycle(codigoQR) {
            // Limpiar timer anterior si existe
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
            
            // Renovar cada 7 segundos
            refreshTimer = setInterval(() => {
                if (currentQRCode === codigoQR) {
                    generateQRWithData(codigoQR);
                }
            }, 7000);
        }

        // Limpiar timer cuando se cierra el modal
        document.getElementById('qrModal').addEventListener('hidden.bs.modal', function () {
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
            currentQRCode = null;
        });

        // Prevenir zoom en iOS
        document.addEventListener('gesturestart', function (e) {
            e.preventDefault();
        });

        // Mantener pantalla activa cuando el modal está abierto
        if ('wakeLock' in navigator) {
            let wakeLock = null;
            
            const requestWakeLock = async () => {
                try {
                    wakeLock = await navigator.wakeLock.request('screen');
                    console.log('Wake lock activo');
                } catch (err) {
                    console.log('Wake lock no disponible:', err);
                }
            };

            // Activar wake lock al abrir modal QR
            document.getElementById('qrModal').addEventListener('shown.bs.modal', () => {
                requestWakeLock();
            });

            // Liberar wake lock al cerrar modal
            document.getElementById('qrModal').addEventListener('hidden.bs.modal', () => {
                if (wakeLock) {
                    wakeLock.release();
                    wakeLock = null;
                }
            });

            // Re-activar al volver a la pestaña
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && currentQRCode && !wakeLock) {
                    requestWakeLock();
                }
            });
        }

        // Búsqueda en tiempo real de visitantes
        function filtrarVisitantes(texto) {
            const visitantes = document.querySelectorAll('.visitor-card');
            
            visitantes.forEach(card => {
                const nombre = card.querySelector('.visitor-name').textContent.toLowerCase();
                const empresa = card.querySelector('.visitor-company')?.textContent.toLowerCase() || '';
                const email = card.querySelector('small').textContent.toLowerCase();
                
                const coincide = nombre.includes(texto.toLowerCase()) || 
                                empresa.includes(texto.toLowerCase()) || 
                                email.includes(texto.toLowerCase());
                
                card.style.display = coincide ? 'block' : 'none';
            });
        }

        // Agregar buscador si hay muchos visitantes
        <?php if (count($visitantesEvento) > 5): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const visitantesContainer = document.querySelector('.visitors-list');
            if (visitantesContainer) {
                const searchHTML = `
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Buscar visitante..." 
                                   onkeyup="filtrarVisitantes(this.value)">
                        </div>
                    </div>
                `;
                visitantesContainer.insertAdjacentHTML('afterbegin', searchHTML);
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
