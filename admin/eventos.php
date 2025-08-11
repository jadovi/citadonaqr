<?php
require_once '../config/config.php';

$auth = new Auth();
$auth->requireLogin();

$evento = new Evento();
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
                    'empresa' => trim($_POST['empresa']),
                    'fecha_inicio' => $_POST['fecha_inicio'],
                    'fecha_fin' => $_POST['fecha_fin'],
                    'descripcion' => trim($_POST['descripcion']) ?: null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                if (empty($data['nombre']) || empty($data['empresa']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
                    throw new Exception('Todos los campos obligatorios deben ser completados');
                }
                
                if ($data['fecha_inicio'] > $data['fecha_fin']) {
                    throw new Exception('La fecha de inicio no puede ser posterior a la fecha de fin');
                }
                
                $eventoId = $evento->crear($data);
                $mensaje = "Evento creado exitosamente con ID: $eventoId";
                $tipoMensaje = 'success';
                break;
                
            case 'actualizar':
                $id = (int)$_POST['id'];
                $data = [
                    'nombre' => trim($_POST['nombre']),
                    'empresa' => trim($_POST['empresa']),
                    'fecha_inicio' => $_POST['fecha_inicio'],
                    'fecha_fin' => $_POST['fecha_fin'],
                    'descripcion' => trim($_POST['descripcion']) ?: null,
                    'activo' => isset($_POST['activo']) ? 1 : 0
                ];
                
                if (empty($data['nombre']) || empty($data['empresa']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
                    throw new Exception('Todos los campos obligatorios deben ser completados');
                }
                
                if ($data['fecha_inicio'] > $data['fecha_fin']) {
                    throw new Exception('La fecha de inicio no puede ser posterior a la fecha de fin');
                }
                
                $evento->actualizar($id, $data);
                $mensaje = "Evento actualizado exitosamente";
                $tipoMensaje = 'success';
                break;
                
            case 'eliminar':
                $id = (int)$_POST['id'];
                $evento->eliminar($id);
                $mensaje = "Evento eliminado exitosamente";
                $tipoMensaje = 'success';
                break;
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = 'danger';
    }
}

// Obtener lista de eventos
$eventos = $evento->obtenerTodos();

// Verificar eventos sin hash_acceso (necesitan migración)
$eventosSinHash = array_filter($eventos, function($e) {
    return empty($e['hash_acceso']);
});
$cantidadSinHash = count($eventosSinHash);

$pageTitle = 'Gestión de Eventos';
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
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 0.2rem;
        }
        
        .btn-group-vertical .btn {
            min-width: 35px;
        }
        
        .alert-migration {
            background: linear-gradient(135deg, #ffc107 0%, #ffca2c 100%);
            border: none;
            color: #212529;
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
                            <a class="nav-link active" href="<?= BASE_URL ?>/admin/eventos.php">
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
                                <h1 class="h2 mb-0">Gestión de Eventos</h1>
                                <p class="text-muted mb-0">Crear, editar y gestionar eventos</p>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventoModal" onclick="nuevoEvento()">
                                    <i class="bi bi-plus-lg"></i> Nuevo Evento
                                </button>
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

                    <?php if ($cantidadSinHash > 0): ?>
                        <div class="alert alert-migration alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.5rem;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-1">⚠️ Migración de Hash Requerida</h5>
                                    <p class="mb-2">
                                        Hay <strong><?= $cantidadSinHash ?> evento(s)</strong> sin hash_acceso. 
                                        Los eventos sin hash solo tienen enlaces legacy y no pueden usar las nuevas funcionalidades de QR personal.
                                    </p>
                                    <div>
                                        <a href="<?= BASE_URL ?>/migrar_hash_eventos.php" target="_blank" class="btn btn-dark btn-sm me-2">
                                            <i class="bi bi-gear-fill me-1"></i>
                                            Ejecutar Migración
                                        </a>
                                        <small class="text-muted">
                                            Esto agregará hash_acceso único a todos los eventos y habilitará las nuevas funcionalidades.
                                        </small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Events Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-list"></i>
                                Lista de Eventos
                                <span class="badge bg-primary ms-2"><?= count($eventos) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="eventosTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Empresa</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Estado</th>
                                            <th>Inscripciones</th>
                                            <th>Enlaces</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eventos as $e): ?>
                                            <?php
                                            $inscripciones = Database::getInstance()->fetch(
                                                "SELECT COUNT(*) as total FROM inscripciones WHERE evento_id = ?", 
                                                [$e['id']]
                                            );
                                            ?>
                                            <tr>
                                                <td><?= $e['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($e['nombre']) ?></strong>
                                                    <?php if ($e['descripcion']): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars(substr($e['descripcion'], 0, 50)) ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($e['empresa']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($e['fecha_inicio'])) ?></td>
                                                <td><?= date('d/m/Y', strtotime($e['fecha_fin'])) ?></td>
                                                <td>
                                                    <?php if ($e['activo']): ?>
                                                        <span class="badge bg-success status-badge">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary status-badge">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $inscripciones['total'] ?></span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($e['hash_acceso'])): ?>
                                                        <!-- Nuevos enlaces con hash -->
                                                        <div class="btn-group-vertical" role="group">
                                                            <button class="btn btn-xs btn-outline-success mb-1" onclick="copiarEnlace('<?= $evento->obtenerEnlaceInscripcionPorHash($e['hash_acceso']) ?>')" title="Copiar enlace de inscripción">
                                                                <i class="bi bi-person-plus"></i>
                                                            </button>
                                                            <button class="btn btn-xs btn-outline-info" onclick="copiarEnlace('<?= $evento->obtenerEnlaceQRPersonal($e['hash_acceso']) ?>')" title="Copiar enlace QR visitantes">
                                                                <i class="bi bi-qr-code"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <!-- Enlace legacy -->
                                                        <button class="btn btn-sm btn-outline-warning" onclick="copiarEnlace('<?= $evento->obtenerEnlaceInscripcion($e['link_codigo']) ?>')" title="Enlace legacy - Migrar evento">
                                                            <i class="bi bi-link-45deg"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editarEvento(<?= htmlspecialchars(json_encode($e)) ?>)" data-bs-toggle="modal" data-bs-target="#eventoModal">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="verDetalles(<?= $e['id'] ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarEvento(<?= $e['id'] ?>, '<?= htmlspecialchars($e['nombre']) ?>')">
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

    <!-- Event Modal -->
    <div class="modal fade" id="eventoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="eventoForm" method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="id" id="eventoId" value="">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre del Evento *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label">Empresa *</label>
                                <input type="text" class="form-control" id="empresa" name="empresa" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_fin" class="form-label">Fecha de Fin *</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            </div>
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                                    <label class="form-check-label" for="activo">
                                        Evento activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Crear Evento</button>
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
                    <p>¿Está seguro que desea eliminar el evento <strong id="deleteEventName"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer y eliminará todas las inscripciones asociadas.</small></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="id" id="deleteEventId">
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
            $('#eventosTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [7, 8] }
                ]
            });
        });

        function nuevoEvento() {
            document.getElementById('modalTitle').textContent = 'Nuevo Evento';
            document.getElementById('submitBtn').textContent = 'Crear Evento';
            document.getElementById('accion').value = 'crear';
            document.getElementById('eventoForm').reset();
            document.getElementById('eventoId').value = '';
            document.getElementById('activo').checked = true;
        }

        function editarEvento(evento) {
            document.getElementById('modalTitle').textContent = 'Editar Evento';
            document.getElementById('submitBtn').textContent = 'Actualizar Evento';
            document.getElementById('accion').value = 'actualizar';
            
            document.getElementById('eventoId').value = evento.id;
            document.getElementById('nombre').value = evento.nombre;
            document.getElementById('empresa').value = evento.empresa;
            document.getElementById('fecha_inicio').value = evento.fecha_inicio;
            document.getElementById('fecha_fin').value = evento.fecha_fin;
            document.getElementById('descripcion').value = evento.descripcion || '';
            document.getElementById('activo').checked = evento.activo == 1;
        }

        function eliminarEvento(id, nombre) {
            document.getElementById('deleteEventId').value = id;
            document.getElementById('deleteEventName').textContent = nombre;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function verDetalles(eventoId) {
            window.location.href = `<?= BASE_URL ?>/admin/evento_detalle.php?id=${eventoId}`;
        }

        function copiarEnlace(enlace) {
            navigator.clipboard.writeText(enlace).then(function() {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'toast-container position-fixed top-0 end-0 p-3';
                toast.innerHTML = `
                    <div class="toast show" role="alert">
                        <div class="toast-header">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong class="me-auto">Copiado</strong>
                        </div>
                        <div class="toast-body">
                            Enlace copiado al portapapeles
                        </div>
                    </div>
                `;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 3000);
            });
        }

        // Form validation
        document.getElementById('eventoForm').addEventListener('submit', function(e) {
            const fechaInicio = new Date(document.getElementById('fecha_inicio').value);
            const fechaFin = new Date(document.getElementById('fecha_fin').value);
            
            if (fechaInicio > fechaFin) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser posterior a la fecha de fin');
                return false;
            }
        });
    </script>
</body>
</html>
