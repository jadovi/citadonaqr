<?php
require_once '../config/config.php';

$auth = new Auth();
$auth->requireLogin();

$visitante = new Visitante();
$user = $auth->getUser();

$mensaje = '';
$tipoMensaje = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'crear':
                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'email' => trim($_POST['email']),
                    'telefono' => trim($_POST['telefono']) ?: null,
                    'empresa' => trim($_POST['empresa']) ?: null,
                    'cargo' => trim($_POST['cargo']) ?: null,
                    'rut' => trim($_POST['rut']) ?: null
                ];
                
                if (empty($data['nombre']) || empty($data['apellido']) || empty($data['email'])) {
                    throw new Exception('Los campos nombre, apellido y email son obligatorios');
                }
                
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('El email no es válido');
                }
                
                // Verificar que el email no existe
                if ($visitante->obtenerPorEmail($data['email'])) {
                    throw new Exception('Ya existe un visitante con este email');
                }
                
                $visitanteId = $visitante->crear($data);
                $mensaje = "Visitante creado exitosamente con ID: $visitanteId";
                $tipoMensaje = 'success';
                break;
                
            case 'actualizar':
                $id = (int)$_POST['id'];
                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'apellido' => trim($_POST['apellido']),
                    'email' => trim($_POST['email']),
                    'telefono' => trim($_POST['telefono']) ?: null,
                    'empresa' => trim($_POST['empresa']) ?: null,
                    'cargo' => trim($_POST['cargo']) ?: null,
                    'rut' => trim($_POST['rut']) ?: null
                ];
                
                if (empty($data['nombre']) || empty($data['apellido']) || empty($data['email'])) {
                    throw new Exception('Los campos nombre, apellido y email son obligatorios');
                }
                
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('El email no es válido');
                }
                
                // Verificar que el email no existe en otro visitante
                $existente = $visitante->obtenerPorEmail($data['email']);
                if ($existente && $existente['id'] != $id) {
                    throw new Exception('Ya existe otro visitante con este email');
                }
                
                $visitante->actualizar($id, $data);
                $mensaje = "Visitante actualizado exitosamente";
                $tipoMensaje = 'success';
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $visitante->eliminar($id);
                $mensaje = "Visitante eliminado exitosamente";
                $tipoMensaje = 'success';
                break;
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = 'danger';
    }
}

// Obtener lista de visitantes con información de inscripciones
$visitantes = $visitante->obtenerConInscripciones();

$pageTitle = 'Gestión de Visitantes';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        
        .visitor-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.8rem;
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
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/">
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
                            <a class="nav-link active" href="<?= BASE_URL ?>/admin/visitantes.php">
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
                                <h1 class="h2 mb-0">Gestión de Visitantes</h1>
                                <p class="text-muted mb-0">Crear, editar y gestionar visitantes</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <a href="<?= BASE_URL ?>/buscar.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-search"></i> Buscar
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/importar.php" class="btn btn-outline-primary">
                                        <i class="bi bi-upload"></i> Importar CSV
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visitanteModal" onclick="nuevoVisitante()">
                                        <i class="bi bi-plus-lg"></i> Nuevo Visitante
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Visitors Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list"></i>
                                Lista de Visitantes
                                <span class="badge bg-primary ms-2"><?= count($visitantes) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="visitantesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Visitante</th>
                                            <th>Email</th>
                                            <th>Empresa</th>
                                            <th>Teléfono</th>
                                            <th>RUT</th>
                                            <th>Inscripciones</th>
                                            <th>Accesos</th>
                                            <th>Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($visitantes as $v): ?>
                                            <tr>
                                                <td><?= $v['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="visitor-avatar me-2">
                                                            <?= strtoupper(substr($v['nombre'], 0, 1) . substr($v['apellido'], 0, 1)) ?>
                                                        </div>
                                                        <div>
                                                            <strong><?= htmlspecialchars($v['nombre'] . ' ' . $v['apellido']) ?></strong>
                                                            <?php if ($v['cargo']): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars($v['cargo']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?= htmlspecialchars($v['email']) ?>" class="text-decoration-none">
                                                        <?= htmlspecialchars($v['email']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($v['empresa'] ?: '-') ?></td>
                                                <td>
                                                    <?php if ($v['telefono']): ?>
                                                        <a href="tel:<?= htmlspecialchars($v['telefono']) ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($v['telefono']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($v['rut'] ?: '-') ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $v['total_inscripciones'] ?></span>
                                                    <?php if ($v['inscripciones_confirmadas'] > 0): ?>
                                                        <span class="badge bg-success ms-1"><?= $v['inscripciones_confirmadas'] ?> confirmadas</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $v['total_accesos'] ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y', strtotime($v['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarVisitante(<?= htmlspecialchars(json_encode($v)) ?>)" data-bs-toggle="modal" data-bs-target="#visitanteModal">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="verInscripciones(<?= $v['id'] ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarVisitante(<?= $v['id'] ?>, '<?= htmlspecialchars($v['nombre'] . ' ' . $v['apellido']) ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Visitor Modal -->
    <div class="modal fade" id="visitanteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="visitanteForm" method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Visitante</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="id" id="visitanteId" value="">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido *</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label">Empresa</label>
                                <input type="text" class="form-control" id="empresa" name="empresa">
                            </div>
                            <div class="col-md-6">
                                <label for="cargo" class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo">
                            </div>
                            <div class="col-12">
                                <label for="rut" class="form-label">RUT</label>
                                <input type="text" class="form-control" id="rut" name="rut" placeholder="12345678-9">
                                <div class="form-text">Formato: 12345678-9 (sin puntos, con guión)</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Crear Visitante</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar al visitante <strong id="deleteVisitorName"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer y eliminará todas las inscripciones asociadas.</small></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" id="deleteVisitorId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#visitantesTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [9] }
                ]
            });
        });

        function nuevoVisitante() {
            document.getElementById('modalTitle').textContent = 'Nuevo Visitante';
            document.getElementById('submitBtn').textContent = 'Crear Visitante';
            document.getElementById('accion').value = 'crear';
            document.getElementById('visitanteForm').reset();
            document.getElementById('visitanteId').value = '';
        }

        function editarVisitante(visitante) {
            document.getElementById('modalTitle').textContent = 'Editar Visitante';
            document.getElementById('submitBtn').textContent = 'Actualizar Visitante';
            document.getElementById('accion').value = 'actualizar';
            
            document.getElementById('visitanteId').value = visitante.id;
            document.getElementById('nombre').value = visitante.nombre;
            document.getElementById('apellido').value = visitante.apellido;
            document.getElementById('email').value = visitante.email;
            document.getElementById('telefono').value = visitante.telefono || '';
            document.getElementById('empresa').value = visitante.empresa || '';
            document.getElementById('cargo').value = visitante.cargo || '';
            document.getElementById('rut').value = visitante.rut || '';
        }

        function eliminarVisitante(id, nombre) {
            document.getElementById('deleteVisitorId').value = id;
            document.getElementById('deleteVisitorName').textContent = nombre;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function verInscripciones(visitanteId) {
            window.location.href = `<?= BASE_URL ?>/admin/inscripciones.php?visitante=${visitanteId}`;
        }

        // RUT validation and formatting
        document.getElementById('rut').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9kK-]/g, '');
            
            // Add dash before last digit if not present
            if (value.length > 1 && !value.includes('-')) {
                value = value.slice(0, -1) + '-' + value.slice(-1);
            }
            
            e.target.value = value.toUpperCase();
        });

        // Email validation
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    </script>
</body>
</html>
