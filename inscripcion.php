<?php
require_once 'config/config.php';

$mensaje = '';
$tipoMensaje = '';
$eventoData = null;
$mostrarFormulario = true;

// Verificar código/hash del evento (soporta ambos formatos)
$codigoEvento = null;
$hashEvento = null;
$evento = new Evento();

if (isset($_GET['event']) && !empty($_GET['event'])) {
    // Nuevo formato con hash del evento
    $hashEvento = $_GET['event'];
    $eventoData = $evento->obtenerPorHashAcceso($hashEvento);
} elseif (isset($_GET['codigo']) && !empty($_GET['codigo'])) {
    // Formato legacy con código
    $codigoEvento = $_GET['codigo'];
    $eventoData = $evento->obtenerPorCodigo($codigoEvento);
} else {
    $mensaje = 'Enlace de inscripción no válido';
    $tipoMensaje = 'danger';
    $mostrarFormulario = false;
}

if ($mostrarFormulario !== false && !$eventoData) {
    $mensaje = 'El evento no existe o el enlace ha expirado';
    $tipoMensaje = 'danger';
    $mostrarFormulario = false;
} else if ($mostrarFormulario !== false && !$eventoData['activo']) {
    $mensaje = 'Este evento no está disponible para inscripciones';
    $tipoMensaje = 'warning';
    $mostrarFormulario = false;
} else if ($mostrarFormulario !== false && date('Y-m-d') > $eventoData['fecha_fin']) {
    $mensaje = 'Este evento ya ha finalizado';
    $tipoMensaje = 'warning';
    $mostrarFormulario = false;
}

// Procesar inscripción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mostrarFormulario) {
    try {
        // Validar datos
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');
        $rut = trim($_POST['rut'] ?? '');
        
        if (empty($nombre) || empty($apellido) || empty($email)) {
            throw new Exception('Los campos nombre, apellido y email son obligatorios');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email no es válido');
        }
        
        $visitante = new Visitante();
        $inscripcion = new Inscripcion();
        
        // Verificar si el visitante ya existe
        $visitanteExistente = $visitante->obtenerPorEmail($email);
        
        if ($visitanteExistente) {
            $visitanteId = $visitanteExistente['id'];
            
            // Actualizar datos del visitante si es necesario
            $datosActualizar = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'cargo' => $cargo,
                'rut' => $rut
            ];
            $visitante->actualizar($visitanteId, $datosActualizar);
        } else {
            // Crear nuevo visitante
            $datosVisitante = [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'telefono' => $telefono,
                'empresa' => $empresa,
                'cargo' => $cargo,
                'rut' => $rut
            ];
            $visitanteId = $visitante->crear($datosVisitante);
        }
        
        // Crear inscripción
        $inscripcionId = $inscripcion->crear($eventoData['id'], $visitanteId);
        
        // Confirmar automáticamente la inscripción
        $inscripcion->confirmar($inscripcionId);
        
        // Enviar email de confirmación
        try {
            $mailer = new Mailer();
            $datosInscripcion = $inscripcion->obtenerPorCodigoQR(
                Database::getInstance()->fetch("SELECT codigo_qr FROM inscripciones WHERE id = ?", [$inscripcionId])['codigo_qr']
            );
            $mailer->enviarConfirmacionInscripcion($datosInscripcion);
            $inscripcion->marcarEmailConfirmado($inscripcionId);
        } catch (Exception $e) {
            error_log("Error enviando email de confirmación: " . $e->getMessage());
        }
        
        $mensaje = '¡Inscripción exitosa! Se ha enviado un email de confirmación con su código QR.';
        if ($hashEvento) {
            $enlaceQR = $evento->obtenerEnlaceQRPersonal($hashEvento);
            $mensaje .= '<br><br><strong>¿Quiere ver todos los visitantes del evento?</strong><br>';
            $mensaje .= '<a href="' . $enlaceQR . '" class="btn btn-sm btn-outline-primary mt-2" target="_blank">';
            $mensaje .= '<i class="bi bi-qr-code"></i> Ver Página QR del Evento</a>';
        }
        $tipoMensaje = 'success';
        $mostrarFormulario = false;
        
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = 'danger';
    }
}

$pageTitle = $eventoData ? 'Inscripción - ' . $eventoData['nombre'] : 'Inscripción a Evento';
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .company-logo {
            max-height: 80px;
            max-width: 200px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .event-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            z-index: 0;
        }
        
        .event-header > * {
            position: relative;
            z-index: 1;
        }
        
        .form-floating > label {
            color: #6c757d;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            transition: transform 0.2s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .floating-shapes::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="%23ffffff20"/><circle cx="80" cy="40" r="1.5" fill="%23ffffff15"/><circle cx="40" cy="80" r="1" fill="%23ffffff10"/></svg>');
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px); }
            100% { transform: translateX(100px) translateY(100px); }
        }
        
        .success-icon {
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
<body>
    <div class="floating-shapes"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-container">
                    <?php if ($eventoData): ?>
                        <!-- Event Header -->
                        <div class="event-header text-center">
                            <!-- Logo de la empresa (simulado) -->
                            <?php 
                            // Generar logo placeholder basado en la empresa
                            $logoUrl = "https://ui-avatars.com/api/?name=" . urlencode($eventoData['empresa']) . 
                                      "&size=200&background=ffffff&color=667eea&bold=true&format=png";
                            ?>
                            <img src="<?= $logoUrl ?>" alt="Logo <?= htmlspecialchars($eventoData['empresa']) ?>" 
                                 class="company-logo" onerror="this.style.display='none'">
                            
                            <h1 class="mb-3">
                                <i class="bi bi-calendar-event"></i>
                                <?= htmlspecialchars($eventoData['nombre']) ?>
                            </h1>
                            <h4 class="mb-3 opacity-75">
                                <i class="bi bi-building me-2"></i>
                                <?= htmlspecialchars($eventoData['empresa']) ?>
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <strong>Inicio:</strong> <?= date('d/m/Y', strtotime($eventoData['fecha_inicio'])) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <i class="bi bi-calendar-x me-1"></i>
                                        <strong>Fin:</strong> <?= date('d/m/Y', strtotime($eventoData['fecha_fin'])) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($eventoData['descripcion']): ?>
                                <div class="mt-3 p-3" style="background: rgba(255,255,255,0.1); border-radius: 10px;">
                                    <p class="mb-0 opacity-90">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <?= htmlspecialchars($eventoData['descripcion']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Hash del evento para referencia -->
                            <?php if ($hashEvento): ?>
                                <div class="mt-3">
                                    <small class="opacity-75">
                                        <i class="bi bi-hash me-1"></i>
                                        ID del evento: <?= substr($hashEvento, 0, 8) ?>...
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                                <?php if ($tipoMensaje === 'success'): ?>
                                    <div class="text-center">
                                        <i class="bi bi-check-circle-fill success-icon" style="font-size: 3rem;"></i>
                                    </div>
                                <?php elseif ($tipoMensaje === 'danger'): ?>
                                    <i class="bi bi-exclamation-triangle"></i>
                                <?php elseif ($tipoMensaje === 'warning'): ?>
                                    <i class="bi bi-exclamation-circle"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($mostrarFormulario): ?>
                            <h3 class="text-center mb-4">
                                <i class="bi bi-person-plus text-primary"></i>
                                Formulario de Inscripción
                            </h3>
                            
                            <form method="POST" action="" id="registrationForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                                   placeholder="Nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                                            <label for="nombre">
                                                <i class="bi bi-person"></i> Nombre *
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="apellido" name="apellido" 
                                                   placeholder="Apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                                            <label for="apellido">
                                                <i class="bi bi-person"></i> Apellido *
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                            <label for="email">
                                                <i class="bi bi-envelope"></i> Email *
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                                   placeholder="Teléfono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                            <label for="telefono">
                                                <i class="bi bi-telephone"></i> Teléfono
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="empresa" name="empresa" 
                                                   placeholder="Empresa" value="<?= htmlspecialchars($_POST['empresa'] ?? '') ?>">
                                            <label for="empresa">
                                                <i class="bi bi-building"></i> Empresa
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="cargo" name="cargo" 
                                                   placeholder="Cargo" value="<?= htmlspecialchars($_POST['cargo'] ?? '') ?>">
                                            <label for="cargo">
                                                <i class="bi bi-briefcase"></i> Cargo
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="rut" name="rut" 
                                                   placeholder="RUT" value="<?= htmlspecialchars($_POST['rut'] ?? '') ?>">
                                            <label for="rut">
                                                <i class="bi bi-card-text"></i> RUT (sin puntos, con guión)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" required>
                                        <label class="form-check-label" for="terms">
                                            Acepto los términos y condiciones del evento *
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg btn-register">
                                        <i class="bi bi-check-circle"></i>
                                        Inscribirse al Evento
                                    </button>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Los campos marcados con * son obligatorios
                                    </small>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <?php if ($tipoMensaje === 'success'): ?>
                                    <h4 class="text-success mb-3">¡Inscripción Completada!</h4>
                                    <p>Recibirá un email con su código QR para el evento.</p>
                                    <p class="text-muted">
                                        Guarde este código QR, lo necesitará para ingresar al evento.
                                    </p>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-house"></i>
                                    Ir al Sitio Principal
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bg-light border-top p-3 text-center" style="border-radius: 0 0 20px 20px;">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i>
                            Sus datos están protegidos - EventAccess &copy; <?= date('Y') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const rutInput = document.getElementById('rut');
            
            // RUT validation and formatting
            if (rutInput) {
                rutInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/[^0-9kK-]/g, '');
                    
                    // Add dash before last digit if not present
                    if (value.length > 1 && !value.includes('-')) {
                        value = value.slice(0, -1) + '-' + value.slice(-1);
                    }
                    
                    e.target.value = value.toUpperCase();
                });
            }
            
            // Form submission enhancement
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
                    submitBtn.disabled = true;
                    
                    // Re-enable after 5 seconds in case of error
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Inscribirse al Evento';
                        submitBtn.disabled = false;
                    }, 5000);
                });
            }
            
            // Auto-dismiss success alerts
            const alerts = document.querySelectorAll('.alert-success');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode && alert.classList.contains('show')) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 8000);
            });
        });
        
        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        // Real-time validation feedback
        document.querySelectorAll('input[required]').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.type === 'email') {
                    if (this.value && !validateEmail(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                } else if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        });
    </script>
</body>
</html>
