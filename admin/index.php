<?php
require_once '../config/config.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();

// Obtener estadísticas para el dashboard
$db = Database::getInstance();

$stats = [
    'eventos_activos' => $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE activo = 1")['total'],
    'total_eventos' => $db->fetch("SELECT COUNT(*) as total FROM eventos")['total'],
    'total_visitantes' => $db->fetch("SELECT COUNT(*) as total FROM visitantes")['total'],
    'inscripciones_confirmadas' => $db->fetch("SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'confirmado'")['total'],
    'inscripciones_pendientes' => $db->fetch("SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'pendiente'")['total'],
    'accesos_hoy' => $db->fetch("SELECT COUNT(*) as total FROM accesos WHERE DATE(fecha_ingreso) = CURDATE()")['total'],
    'accesos_semana' => $db->fetch("SELECT COUNT(*) as total FROM accesos WHERE fecha_ingreso >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['total']
];

// Eventos recientes
$eventosRecientes = $db->fetchAll("
    SELECT e.*, COUNT(i.id) as total_inscripciones 
    FROM eventos e 
    LEFT JOIN inscripciones i ON e.id = i.evento_id 
    GROUP BY e.id 
    ORDER BY e.created_at DESC 
    LIMIT 5
");

// Inscripciones recientes
$inscripcionesRecientes = $db->fetchAll("
    SELECT i.*, v.nombre, v.apellido, v.email, e.nombre as evento_nombre
    FROM inscripciones i
    JOIN visitantes v ON i.visitante_id = v.id
    JOIN eventos e ON i.evento_id = e.id
    ORDER BY i.fecha_inscripcion DESC
    LIMIT 5
");

// Accesos recientes
$accesosRecientes = $db->fetchAll("
    SELECT a.fecha_ingreso, v.nombre, v.apellido, e.nombre as evento_nombre
    FROM accesos a
    JOIN inscripciones i ON a.inscripcion_id = i.id
    JOIN visitantes v ON i.visitante_id = v.id
    JOIN eventos e ON i.evento_id = e.id
    ORDER BY a.fecha_ingreso DESC
    LIMIT 5
");

$pageTitle = 'Dashboard - Administración';
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
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }
        
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .content-wrapper {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .page-header {
            background: white;
            border-bottom: 1px solid #dee2e6;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .activity-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        
        .activity-item.success {
            border-left-color: #28a745;
        }
        
        .activity-item.warning {
            border-left-color: #ffc107;
        }
        
        .activity-item.info {
            border-left-color: #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar px-0">
                <div class="position-sticky pt-4">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-shield-check"></i>
                            EventAccess
                        </h4>
                        <small class="text-white-50">Panel de Administración</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= BASE_URL ?>/admin/">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/eventos.php">
                                <i class="bi bi-calendar-event"></i>
                                Eventos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/visitantes.php">
                                <i class="bi bi-people"></i>
                                Visitantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/importar.php">
                                <i class="bi bi-upload"></i>
                                Importar Visitantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/inscripciones.php">
                                <i class="bi bi-clipboard-check"></i>
                                Inscripciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/accesos.php">
                                <i class="bi bi-door-open"></i>
                                Accesos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/reportes.php">
                                <i class="bi bi-graph-up"></i>
                                Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/configuracion.php">
                                <i class="bi bi-gear"></i>
                                Configuración
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white-50 my-4">
                    
                    <div class="px-3">
                        <div class="text-white-50 small mb-2">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($user['nombre']) ?>
                        </div>
                        <a href="<?= BASE_URL ?>/admin/logout.php" class="btn btn-outline-light btn-sm w-100">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar Sesión
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto content-wrapper">
                <!-- Header -->
                <div class="page-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="h2 mb-0">Dashboard</h1>
                                <p class="text-muted mb-0">Resumen general del sistema</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-house"></i> Ver Sitio
                                    </a>
                                    <a href="<?= BASE_URL ?>/scanner.php" class="btn btn-primary">
                                        <i class="bi bi-camera"></i> Escáner QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['eventos_activos'] ?></h4>
                                            <p class="mb-0">Eventos Activos</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-calendar-event" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['total_visitantes'] ?></h4>
                                            <p class="mb-0">Visitantes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-people" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['inscripciones_confirmadas'] ?></h4>
                                            <p class="mb-0">Inscripciones</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6">
                            <div class="card stat-card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0"><?= $stats['accesos_hoy'] ?></h4>
                                            <p class="mb-0">Accesos Hoy</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Eventos</h6>
                                    <h3 class="text-primary"><?= $stats['total_eventos'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Pendientes</h6>
                                    <h3 class="text-warning"><?= $stats['inscripciones_pendientes'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Accesos (7 días)</h6>
                                    <h3 class="text-success"><?= $stats['accesos_semana'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Recent Events -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-calendar-event text-primary"></i>
                                        Eventos Recientes
                                    </h5>
                                    <a href="<?= BASE_URL ?>/admin/eventos.php" class="btn btn-sm btn-outline-primary">
                                        Ver todos
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($eventosRecientes)): ?>
                                        <p class="text-muted text-center py-3">No hay eventos registrados</p>
                                    <?php else: ?>
                                        <?php foreach ($eventosRecientes as $evento): ?>
                                            <div class="activity-item">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($evento['nombre']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($evento['empresa']) ?></small>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= $evento['total_inscripciones'] ?> inscripciones
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($evento['fecha_inicio'])) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Registrations -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-person-plus text-success"></i>
                                        Inscripciones Recientes
                                    </h5>
                                    <a href="<?= BASE_URL ?>/admin/inscripciones.php" class="btn btn-sm btn-outline-success">
                                        Ver todas
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($inscripcionesRecientes)): ?>
                                        <p class="text-muted text-center py-3">No hay inscripciones recientes</p>
                                    <?php else: ?>
                                        <?php foreach ($inscripcionesRecientes as $inscripcion): ?>
                                            <div class="activity-item success">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <?= htmlspecialchars($inscripcion['nombre'] . ' ' . $inscripcion['apellido']) ?>
                                                        </h6>
                                                        <small class="text-muted"><?= htmlspecialchars($inscripcion['evento_nombre']) ?></small>
                                                    </div>
                                                    <span class="badge bg-<?= $inscripcion['estado'] === 'confirmado' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($inscripcion['estado']) ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($inscripcion['fecha_inscripcion'])) ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Access -->
                    <?php if (!empty($accesosRecientes)): ?>
                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-door-open text-info"></i>
                                        Accesos Recientes
                                    </h5>
                                    <a href="<?= BASE_URL ?>/admin/accesos.php" class="btn btn-sm btn-outline-info">
                                        Ver todos
                                    </a>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach (array_slice($accesosRecientes, 0, 5) as $acceso): ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="activity-item info">
                                                    <h6 class="mb-1">
                                                        <?= htmlspecialchars($acceso['nombre'] . ' ' . $acceso['apellido']) ?>
                                                    </h6>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($acceso['evento_nombre']) ?></small>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock"></i>
                                                        <?= date('d/m/Y H:i', strtotime($acceso['fecha_ingreso'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh statistics every 30 seconds
        setInterval(function() {
            fetch('<?= BASE_URL ?>/api/estadisticas.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats without full page reload
                        // This could be implemented for live updates
                    }
                })
                .catch(error => console.log('Error updating stats:', error));
        }, 30000);

        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.transition = 'all 0.5s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>
