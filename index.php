<?php
require_once 'config/config.php';

$pageTitle = 'Sistema de Acreditación de Visitantes';
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
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .qr-scanner-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
    </style>
</head>
<body>
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
                        <a class="nav-link active" href="<?= BASE_URL ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/scanner.php">Escáner QR</a>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Sistema de Acreditación de Visitantes</h1>
                    <p class="lead mb-4">
                        Gestiona eventos, inscripciones y acreditaciones de manera eficiente con nuestro sistema integral de códigos QR.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="<?= BASE_URL ?>/scanner.php" class="btn btn-light btn-lg">
                            <i class="bi bi-camera"></i> Escanear QR
                        </a>
                        <a href="<?= BASE_URL ?>/buscar.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-search"></i> Buscar Visitante
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="bi bi-qr-code display-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="fw-bold">Características Principales</h2>
                    <p class="text-muted">Todo lo que necesitas para gestionar eventos y visitantes</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-qr-code-scan display-4 text-primary mb-3"></i>
                            <h5 class="card-title">Escáner QR</h5>
                            <p class="card-text">
                                Escanea códigos QR directamente desde la cámara del móvil para acreditar visitantes al instante.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-people-fill display-4 text-success mb-3"></i>
                            <h5 class="card-title">Gestión de Visitantes</h5>
                            <p class="card-text">
                                CRUD completo para gestionar visitantes, inscripciones y búsquedas avanzadas por múltiples criterios.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="bi bi-calendar-event display-4 text-warning mb-3"></i>
                            <h5 class="card-title">Gestión de Eventos</h5>
                            <p class="card-text">
                                Crea eventos, genera formularios de inscripción y envía correos de confirmación automáticamente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- QR Scanner Section -->
    <section class="qr-scanner-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h3 class="fw-bold mb-4">Acceso Rápido</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/scanner.php" class="btn btn-primary btn-lg w-100 p-4">
                                <i class="bi bi-camera display-6 d-block mb-2"></i>
                                <strong>Escanear Código QR</strong><br>
                                <small>Acreditar visitantes</small>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/buscar.php" class="btn btn-outline-primary btn-lg w-100 p-4">
                                <i class="bi bi-search display-6 d-block mb-2"></i>
                                <strong>Buscar Visitante</strong><br>
                                <small>Por nombre, empresa, RUT</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-4">
                    <h3 class="fw-bold">Estado del Sistema</h3>
                </div>
            </div>
            <div class="row g-4" id="statistics">
                <!-- Statistics will be loaded via AJAX -->
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-calendar-event text-primary display-4"></i>
                            <h4 class="mt-2" id="total-eventos">-</h4>
                            <p class="text-muted">Eventos Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-people text-success display-4"></i>
                            <h4 class="mt-2" id="total-visitantes">-</h4>
                            <p class="text-muted">Visitantes Registrados</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-check-circle text-warning display-4"></i>
                            <h4 class="mt-2" id="total-inscripciones">-</h4>
                            <p class="text-muted">Inscripciones Confirmadas</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-door-open text-info display-4"></i>
                            <h4 class="mt-2" id="total-accesos">-</h4>
                            <p class="text-muted">Accesos Hoy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5>EventAccess</h5>
                    <p class="text-muted">Sistema de acreditación de visitantes con códigos QR.</p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <p class="text-muted">&copy; <?= date('Y') ?> EventAccess. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar estadísticas
        document.addEventListener('DOMContentLoaded', function() {
            fetch('<?= BASE_URL ?>/api/estadisticas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-eventos').textContent = data.data.eventos_activos || 0;
                        document.getElementById('total-visitantes').textContent = data.data.total_visitantes || 0;
                        document.getElementById('total-inscripciones').textContent = data.data.inscripciones_confirmadas || 0;
                        document.getElementById('total-accesos').textContent = data.data.accesos_hoy || 0;
                    }
                })
                .catch(error => {
                    console.error('Error cargando estadísticas:', error);
                });
        });
    </script>
</body>
</html>
