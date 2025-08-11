<?php
require_once 'config/config.php';

$pageTitle = 'Buscar Visitante';
$resultados = [];
$termino = '';

// Procesar búsqueda
if (isset($_GET['buscar']) && !empty($_GET['termino'])) {
    $termino = trim($_GET['termino']);
    $visitante = new Visitante();
    $resultados = $visitante->buscar($termino);
}
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
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        
        .visitor-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        
        .visitor-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .search-stats {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .visitor-detail-modal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .event-list {
            max-height: 200px;
            overflow-y: auto;
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
                        <a class="nav-link" href="<?= BASE_URL ?>/scanner.php">Escáner QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= BASE_URL ?>/buscar.php">Buscar Visitante</a>
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

    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-5 fw-bold mb-4">
                        <i class="bi bi-search"></i>
                        Buscar Visitante
                    </h1>
                    <p class="lead">
                        Encuentra visitantes por nombre, apellido, empresa, RUT o email
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-4">
        <!-- Search Form -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" name="termino" class="form-control" 
                                               placeholder="Nombre, apellido, empresa, RUT o email..." 
                                               value="<?= htmlspecialchars($termino) ?>" autofocus>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" name="buscar" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Puede buscar por cualquier campo: nombre, apellido, empresa, RUT o email
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['buscar'])): ?>
            <!-- Search Results -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <!-- Search Stats -->
                    <div class="search-stats">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    <i class="bi bi-list-ul"></i>
                                    Resultados de búsqueda para: <strong>"<?= htmlspecialchars($termino) ?>"</strong>
                                </h6>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-primary">
                                    <?= count($resultados) ?> visitante(s) encontrado(s)
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($resultados)): ?>
                        <!-- No Results -->
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">No se encontraron resultados</h4>
                                <p class="text-muted">
                                    No hay visitantes que coincidan con "<strong><?= htmlspecialchars($termino) ?></strong>"
                                </p>
                                <a href="<?= BASE_URL ?>/buscar.php" class="btn btn-primary">
                                    <i class="bi bi-arrow-left"></i> Nueva Búsqueda
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Results Grid -->
                        <div class="row g-4">
                            <?php foreach ($resultados as $visitante): ?>
                                <div class="col-lg-6">
                                    <div class="card visitor-card h-100" onclick="mostrarDetalles(<?= $visitante['id'] ?>)">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title mb-0">
                                                    <?= htmlspecialchars($visitante['nombre'] . ' ' . $visitante['apellido']) ?>
                                                </h5>
                                                <?php if ($visitante['total_inscripciones'] > 0): ?>
                                                    <span class="badge bg-success status-badge">Inscrito</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary status-badge">No inscrito</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="row g-2 text-sm">
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope"></i> 
                                                        <?= htmlspecialchars($visitante['email']) ?>
                                                    </small>
                                                </div>
                                                <?php if ($visitante['empresa']): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted">
                                                            <i class="bi bi-building"></i> 
                                                            <?= htmlspecialchars($visitante['empresa']) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($visitante['rut']): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted">
                                                            <i class="bi bi-card-text"></i> 
                                                            RUT: <?= htmlspecialchars($visitante['rut']) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($visitante['telefono']): ?>
                                                    <div class="col-12">
                                                        <small class="text-muted">
                                                            <i class="bi bi-telephone"></i> 
                                                            <?= htmlspecialchars($visitante['telefono']) ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($visitante['total_inscripciones'] > 0): ?>
                                                <div class="mt-3 pt-3 border-top">
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Inscripciones</small>
                                                            <strong class="text-primary"><?= $visitante['total_inscripciones'] ?></strong>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Eventos</small>
                                                            <strong class="text-success">
                                                                <?= $visitante['eventos_inscritos'] ? count(explode(', ', $visitante['eventos_inscritos'])) : 0 ?>
                                                            </strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($visitante['eventos_inscritos']): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Eventos:</small>
                                                            <div class="mt-1">
                                                                <?php 
                                                                $eventos = explode(', ', $visitante['eventos_inscritos']);
                                                                foreach (array_slice($eventos, 0, 2) as $evento): 
                                                                ?>
                                                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($evento) ?></span>
                                                                <?php endforeach; ?>
                                                                <?php if (count($eventos) > 2): ?>
                                                                    <span class="badge bg-secondary">+<?= count($eventos) - 2 ?> más</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i>
                                                Registrado: <?= date('d/m/Y', strtotime($visitante['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Search Instructions -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-person-lines-fill text-primary" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Buscar Visitantes</h4>
                            <p class="text-muted">
                                Utilice el formulario de arriba para buscar visitantes en el sistema.
                                Puede buscar por cualquiera de los siguientes criterios:
                            </p>
                            <div class="row g-3 mt-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person text-primary me-2"></i>
                                        <span>Nombre y Apellido</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-envelope text-primary me-2"></i>
                                        <span>Email</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-building text-primary me-2"></i>
                                        <span>Empresa</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-card-text text-primary me-2"></i>
                                        <span>RUT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Visitor Detail Modal -->
    <div class="modal fade" id="visitorDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-circle"></i>
                        Detalles del Visitante
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="visitor-detail-content">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function mostrarDetalles(visitanteId) {
            const modal = new bootstrap.Modal(document.getElementById('visitorDetailModal'));
            modal.show();
            
            try {
                const response = await fetch(`<?= BASE_URL ?>/api/visitante_detalle.php?id=${visitanteId}`);
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('visitor-detail-content').innerHTML = result.html;
                } else {
                    document.getElementById('visitor-detail-content').innerHTML = 
                        '<div class="alert alert-danger">Error al cargar los detalles del visitante</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('visitor-detail-content').innerHTML = 
                    '<div class="alert alert-danger">Error de conexión</div>';
            }
        }

        // Highlight search terms
        document.addEventListener('DOMContentLoaded', function() {
            const termino = '<?= addslashes($termino) ?>';
            if (termino) {
                // Resaltar términos de búsqueda (implementación básica)
                // Se podría mejorar con una librería como mark.js
            }
        });
    </script>
</body>
</html>
