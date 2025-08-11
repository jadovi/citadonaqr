<?php
require_once 'config/config.php';

$pageTitle = 'Escáner QR - Acreditación';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        #scanner-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #video {
            width: 100%;
            height: auto;
            border-radius: 8px;
            background: #000;
        }
        
        #canvas {
            display: none;
        }
        
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid #28a745;
            border-radius: 8px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        }
        
        .scanner-corners {
            position: absolute;
            width: 20px;
            height: 20px;
        }
        
        .scanner-corners::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 3px;
            background: #28a745;
        }
        
        .scanner-corners::after {
            content: '';
            position: absolute;
            width: 3px;
            height: 20px;
            background: #28a745;
        }
        
        .corner-tl { top: -3px; left: -3px; }
        .corner-tr { top: -3px; right: -3px; }
        .corner-bl { bottom: -3px; left: -3px; }
        .corner-br { bottom: -3px; right: -3px; }
        
        .corner-tr::before { right: 0; }
        .corner-tr::after { right: 0; }
        .corner-bl::before { bottom: 0; }
        .corner-bl::after { bottom: 0; }
        .corner-br::before { right: 0; bottom: 0; }
        .corner-br::after { right: 0; bottom: 0; }
        
        .status-card {
            transition: all 0.3s ease;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .visitor-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .success-animation {
            animation: successBounce 0.6s ease;
        }
        
        @keyframes successBounce {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0, -10px, 0); }
            70% { transform: translate3d(0, -5px, 0); }
            90% { transform: translate3d(0, -2px, 0); }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">
                <i class="bi bi-qr-code"></i> EventAccess
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/scanner.php">Escáner QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/buscar.php">Buscar Visitante</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/admin/">
                            <i class="bi bi-gear"></i> Administración
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold">
                        <i class="bi bi-camera text-primary"></i>
                        Escáner de Códigos QR
                    </h2>
                    <p class="text-muted">Escanea el código QR del visitante para acreditarlo</p>
                </div>

                <!-- Scanner Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div id="scanner-container">
                            <video id="video" autoplay muted playsinline></video>
                            <canvas id="canvas"></canvas>
                            <div class="scanner-overlay">
                                <div class="scanner-corners corner-tl"></div>
                                <div class="scanner-corners corner-tr"></div>
                                <div class="scanner-corners corner-bl"></div>
                                <div class="scanner-corners corner-br"></div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button id="start-scanner" class="btn btn-success btn-lg me-2">
                                <i class="bi bi-camera"></i> Iniciar Cámara
                            </button>
                            <button id="stop-scanner" class="btn btn-danger btn-lg" style="display: none;">
                                <i class="bi bi-camera-video-off"></i> Detener Cámara
                            </button>
                        </div>
                        
                        <div id="scanner-status" class="alert alert-info mt-3" style="display: none;">
                            <i class="bi bi-info-circle"></i>
                            <span id="status-text">Iniciando cámara...</span>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div id="result-section" style="display: none;">
                    <!-- Visitor Info Card -->
                    <div id="visitor-card" class="card mb-4">
                        <div class="card-header visitor-info">
                            <h5 class="mb-0 text-center">
                                <i class="bi bi-person-check"></i>
                                Información del Visitante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre:</strong> <span id="visitor-name"></span></p>
                                    <p><strong>Email:</strong> <span id="visitor-email"></span></p>
                                    <p><strong>Empresa:</strong> <span id="visitor-empresa"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Evento:</strong> <span id="visitor-evento"></span></p>
                                    <p><strong>Estado:</strong> <span id="visitor-estado"></span></p>
                                    <p><strong>Código QR:</strong> <span id="visitor-codigo"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mb-4">
                        <button id="confirm-access" class="btn btn-success btn-lg me-3" style="display: none;">
                            <i class="bi bi-check-circle"></i> Confirmar Acceso
                        </button>
                        <button id="scan-another" class="btn btn-primary btn-lg">
                            <i class="bi bi-camera"></i> Escanear Otro
                        </button>
                    </div>
                </div>

                <!-- Manual Input -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-keyboard"></i>
                            Entrada Manual de Código
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" id="manual-code" class="form-control" placeholder="Ingrese el código QR manualmente">
                            <button id="check-manual" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Verificar
                            </button>
                        </div>
                        <small class="text-muted">Puede ingresar el código manualmente si no puede escanearlo</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> ¡Acceso Confirmado!
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <div class="success-animation">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="mt-3">Visitante Acreditado</h4>
                    <p id="success-message" class="text-muted"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continuar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Error
                    </h5>
                </div>
                <div class="modal-body">
                    <p id="error-message"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        class QRScanner {
            constructor() {
                this.video = document.getElementById('video');
                this.canvas = document.getElementById('canvas');
                this.context = this.canvas.getContext('2d');
                this.scanning = false;
                this.stream = null;
                
                this.initEventListeners();
            }

            initEventListeners() {
                document.getElementById('start-scanner').addEventListener('click', () => this.startScanner());
                document.getElementById('stop-scanner').addEventListener('click', () => this.stopScanner());
                document.getElementById('check-manual').addEventListener('click', () => this.checkManualCode());
                document.getElementById('confirm-access').addEventListener('click', () => this.confirmAccess());
                document.getElementById('scan-another').addEventListener('click', () => this.scanAnother());
                
                // Detectar Enter en input manual
                document.getElementById('manual-code').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.checkManualCode();
                    }
                });
            }

            async startScanner() {
                try {
                    this.showStatus('Solicitando acceso a la cámara...', 'info');
                    
                    const constraints = {
                        video: {
                            facingMode: 'environment', // Cámara trasera en móviles
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    };

                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.video.srcObject = this.stream;
                    
                    this.video.addEventListener('loadedmetadata', () => {
                        this.canvas.width = this.video.videoWidth;
                        this.canvas.height = this.video.videoHeight;
                        this.scanning = true;
                        this.scan();
                        
                        this.showStatus('Escaneando... Acerque el código QR a la cámara', 'success');
                        document.getElementById('start-scanner').style.display = 'none';
                        document.getElementById('stop-scanner').style.display = 'inline-block';
                    });
                } catch (error) {
                    console.error('Error accessing camera:', error);
                    this.showStatus('Error al acceder a la cámara. Verifique los permisos.', 'danger');
                }
            }

            stopScanner() {
                this.scanning = false;
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }
                this.video.srcObject = null;
                
                document.getElementById('start-scanner').style.display = 'inline-block';
                document.getElementById('stop-scanner').style.display = 'none';
                this.hideStatus();
            }

            scan() {
                if (!this.scanning) return;

                if (this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
                    this.context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
                    const imageData = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
                    
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    
                    if (code) {
                        this.processQRCode(code.data);
                        return;
                    }
                }

                requestAnimationFrame(() => this.scan());
            }

            async processQRCode(qrData) {
                this.scanning = false;
                this.showStatus('Código QR detectado. Verificando...', 'warning');
                
                try {
                    const response = await fetch('<?= BASE_URL ?>/api/verificar_qr.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ codigo_qr: qrData })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        if (result.data.valido) {
                            this.showVisitorInfo(result.data);
                        } else {
                            this.showError(result.data.mensaje);
                        }
                    } else {
                        this.showError(result.message || 'Error al verificar el código QR');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error de conexión al verificar el código QR');
                }
            }

            async checkManualCode() {
                const code = document.getElementById('manual-code').value.trim();
                if (!code) {
                    this.showError('Por favor ingrese un código QR');
                    return;
                }

                this.processQRCode(code);
            }

            showVisitorInfo(data) {
                const visitante = data.visitante;
                
                document.getElementById('visitor-name').textContent = `${visitante.nombre} ${visitante.apellido}`;
                document.getElementById('visitor-email').textContent = visitante.email;
                document.getElementById('visitor-empresa').textContent = visitante.empresa || 'N/A';
                document.getElementById('visitor-evento').textContent = visitante.evento_nombre;
                document.getElementById('visitor-estado').textContent = visitante.estado;
                document.getElementById('visitor-codigo').textContent = visitante.codigo_qr;

                // Mostrar información del visitante
                document.getElementById('result-section').style.display = 'block';
                
                // Verificar si ya ingresó
                if (data.ya_ingreso_hoy) {
                    document.getElementById('visitor-card').className = 'card mb-4 border-warning';
                    this.showStatus(`Visitante ya ingresó hoy (${data.total_ingresos_hoy} veces)`, 'warning');
                    document.getElementById('confirm-access').style.display = 'none';
                } else {
                    document.getElementById('visitor-card').className = 'card mb-4 border-success';
                    this.showStatus('Visitante válido. Puede confirmar el acceso.', 'success');
                    document.getElementById('confirm-access').style.display = 'inline-block';
                }

                // Guardar código para confirmación
                this.currentCode = visitante.codigo_qr;
                this.hideStatus();
            }

            async confirmAccess() {
                try {
                    const response = await fetch('<?= BASE_URL ?>/api/confirmar_acceso.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ codigo_qr: this.currentCode })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        this.showSuccessModal(result.message);
                        this.resetScanner();
                    } else {
                        this.showError(result.message || 'Error al confirmar el acceso');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('Error de conexión al confirmar el acceso');
                }
            }

            scanAnother() {
                this.resetScanner();
                if (!this.scanning) {
                    this.startScanner();
                }
            }

            resetScanner() {
                document.getElementById('result-section').style.display = 'none';
                document.getElementById('manual-code').value = '';
                this.currentCode = null;
                this.hideStatus();
            }

            showStatus(message, type) {
                const statusDiv = document.getElementById('scanner-status');
                const statusText = document.getElementById('status-text');
                
                statusDiv.className = `alert alert-${type} mt-3`;
                statusText.textContent = message;
                statusDiv.style.display = 'block';
            }

            hideStatus() {
                document.getElementById('scanner-status').style.display = 'none';
            }

            showError(message) {
                document.getElementById('error-message').textContent = message;
                new bootstrap.Modal(document.getElementById('errorModal')).show();
                this.resetScanner();
            }

            showSuccessModal(message) {
                document.getElementById('success-message').textContent = message;
                new bootstrap.Modal(document.getElementById('successModal')).show();
            }
        }

        // Inicializar scanner cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            new QRScanner();
        });
    </script>
</body>
</html>
