<?php
require_once '../config/config.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getUser();
$evento = new Evento();

$mensaje = '';
$tipoMensaje = '';
$resultadoImportacion = null;

// Obtener eventos para el select
$eventos = $evento->obtenerActivos();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    try {
        switch ($accion) {
            case 'descargar_template':
                $importador = new ImportadorVisitantes();
                $template = $importador->generarTemplateCSV();
                
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="template_visitantes.csv"');
                header('Content-Length: ' . strlen($template));
                echo "\xEF\xBB\xBF"; // BOM para UTF-8
                echo $template;
                exit;
                break;

            case 'descargar_ejemplo_excel':
                // Generar archivo Excel simple (HTML) con 3 filas de prueba
                $filename = 'ejemplo_visitantes.xls';
                header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                echo "<html><head><meta charset='UTF-8'></head><body>";
                echo "<table border='1'>";
                echo "<tr>"
                    ."<th>nombre</th>"
                    ."<th>apellido</th>"
                    ."<th>email</th>"
                    ."<th>telefono</th>"
                    ."<th>empresa</th>"
                    ."<th>cargo</th>"
                    ."<th>rut</th>"
                    ."<th>mesa</th>"
                    ."<th>asiento</th>"
                    ."<th>lugar</th>"
                    ."<th>zona</th>"
                    ."</tr>";
                echo "<tr><td>Juan</td><td>Pérez</td><td>juan.perez@empresa.com</td><td>+56912345678</td><td>Empresa ABC</td><td>Gerente</td><td>12345678-9</td><td>12</td><td>A3</td><td>Salón Principal</td><td>VIP</td></tr>";
                echo "<tr><td>María</td><td>González</td><td>maria.gonzalez@corp.cl</td><td>56987654321</td><td>Corporación XYZ</td><td>Directora</td><td>98765432-1</td><td>8</td><td>B1</td><td>Patio Central</td><td>General</td></tr>";
                echo "<tr><td>Carlos</td><td>López</td><td>carlos.lopez@startup.cl</td><td>912345678</td><td>Startup Tech</td><td>Desarrollador</td><td>11111111-1</td><td></td><td></td><td>Auditorio 2</td><td>Balcón</td></tr>";
                echo "</table></body></html>";
                exit;
                break;
                
            case 'procesar_archivo':
                if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error al subir el archivo');
                }
                
                $archivo = $_FILES['archivo']['tmp_name'];
                $nombreArchivo = $_FILES['archivo']['name'];
                $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
                
                if (!in_array($extension, ['csv', 'txt'])) {
                    throw new Exception('Solo se permiten archivos CSV o TXT');
                }
                
                // Configuración de importación
                $mapeoColumnas = [
                    'nombre' => (int)$_POST['col_nombre'],
                    'apellido' => (int)$_POST['col_apellido'],
                    'email' => (int)$_POST['col_email'],
                    'telefono' => isset($_POST['col_telefono']) && $_POST['col_telefono'] !== '' ? (int)$_POST['col_telefono'] : null,
                    'empresa' => isset($_POST['col_empresa']) && $_POST['col_empresa'] !== '' ? (int)$_POST['col_empresa'] : null,
                    'cargo' => isset($_POST['col_cargo']) && $_POST['col_cargo'] !== '' ? (int)$_POST['col_cargo'] : null,
                    'rut' => isset($_POST['col_rut']) && $_POST['col_rut'] !== '' ? (int)$_POST['col_rut'] : null,
                    'mesa' => isset($_POST['col_mesa']) && $_POST['col_mesa'] !== '' ? (int)$_POST['col_mesa'] : null,
                    'asiento' => isset($_POST['col_asiento']) && $_POST['col_asiento'] !== '' ? (int)$_POST['col_asiento'] : null,
                    'lugar' => isset($_POST['col_lugar']) && $_POST['col_lugar'] !== '' ? (int)$_POST['col_lugar'] : null,
                    'zona' => isset($_POST['col_zona']) && $_POST['col_zona'] !== '' ? (int)$_POST['col_zona'] : null
                ];
                
                // Filtrar valores null del mapeo
                $mapeoColumnas = array_filter($mapeoColumnas, function($v) { return $v !== null; });
                
                $opciones = [
                    'delimitador' => $_POST['delimitador'] ?? ',',
                    'saltar_primera_fila' => isset($_POST['saltar_primera_fila']),
                    'actualizar_existentes' => isset($_POST['actualizar_existentes']),
                    'evento_id' => !empty($_POST['evento_id']) ? (int)$_POST['evento_id'] : null
                ];
                
                $importador = new ImportadorVisitantes();
                $resultadoImportacion = $importador->procesarCSV($archivo, $mapeoColumnas, $opciones);
                
                if ($resultadoImportacion['exitosos'] > 0) {
                    $mensaje = "Importación completada: {$resultadoImportacion['exitosos']} visitantes procesados exitosamente";
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = "Importación fallida: No se procesaron visitantes exitosamente";
                    $tipoMensaje = 'warning';
                }
                
                break;
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = 'danger';
    }
}

$pageTitle = 'Importador de Visitantes';
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
        
        .import-step {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .file-drop-zone {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-drop-zone:hover {
            border-color: #0056b3;
            background: #e9ecef;
        }
        
        .preview-table {
            font-size: 0.85rem;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .mapping-row {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
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
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/visitantes.php">
                                <i class="bi bi-people"></i>
                                Visitantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="<?= BASE_URL ?>/admin/importar.php">
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
                                <h1 class="h2 mb-0">
                                    <i class="bi bi-upload text-primary"></i>
                                    Importador de Visitantes
                                </h1>
                                <p class="text-muted mb-0">Importa visitantes masivamente desde archivos CSV</p>
                            </div>
                            <div class="col-auto">
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="accion" value="descargar_template">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bi bi-download"></i> Descargar Template
                                    </button>
                                </form>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="accion" value="descargar_ejemplo_excel">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="bi bi-file-earmark-excel"></i> Descargar ejemplo Excel
                                    </button>
                                </form>
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

                    <?php if ($resultadoImportacion): ?>
                        <!-- Resultado de importación -->
                        <div class="import-step">
                            <div class="d-flex align-items-center mb-3">
                                <div class="step-number">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <h4 class="mb-0">Resultado de la Importación</h4>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-primary"><?= $resultadoImportacion['procesados'] ?></h3>
                                            <p class="mb-0">Procesados</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-success"><?= $resultadoImportacion['importados'] ?></h3>
                                            <p class="mb-0">Nuevos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-warning"><?= $resultadoImportacion['actualizados'] ?></h3>
                                            <p class="mb-0">Actualizados</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h3 class="text-danger"><?= $resultadoImportacion['errores_count'] ?></h3>
                                            <p class="mb-0">Errores</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($resultadoImportacion['errores'])): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                            Errores Encontrados
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div style="max-height: 300px; overflow-y: auto;">
                                            <?php foreach ($resultadoImportacion['errores'] as $error): ?>
                                                <div class="alert alert-warning py-2 mb-2">
                                                    <small><?= htmlspecialchars($error) ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mt-4">
                                <a href="<?= BASE_URL ?>/admin/visitantes.php" class="btn btn-primary">
                                    <i class="bi bi-people"></i> Ver Visitantes
                                </a>
                                <a href="<?= BASE_URL ?>/admin/importar.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Nueva Importación
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Formulario de importación -->
                        <form method="POST" action="" enctype="multipart/form-data" id="importForm">
                            <input type="hidden" name="accion" value="procesar_archivo">
                            
                            <!-- Paso 1: Subir archivo -->
                            <div class="import-step">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">1</div>
                                    <h4 class="mb-0">Seleccionar Archivo CSV</h4>
                                </div>
                                
                                <div class="file-drop-zone" id="dropZone">
                                    <i class="bi bi-cloud-upload display-4 text-primary mb-3"></i>
                                    <h5>Arrastra tu archivo CSV aquí</h5>
                                    <p class="text-muted">o haz clic para seleccionar</p>
                                    <input type="file" id="archivo" name="archivo" accept=".csv,.txt" required style="display: none;">
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('archivo').click()">
                                        <i class="bi bi-folder2-open"></i> Seleccionar Archivo
                                    </button>
                                </div>
                                
                                <div id="fileInfo" style="display: none;" class="mt-3">
                                    <div class="alert alert-info">
                                        <strong>Archivo seleccionado:</strong> <span id="fileName"></span><br>
                                        <strong>Tamaño:</strong> <span id="fileSize"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Configuración -->
                            <div class="import-step">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">2</div>
                                    <h4 class="mb-0">Configuración de Importación</h4>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="delimitador" class="form-label">Separador de columnas</label>
                                        <select class="form-select" id="delimitador" name="delimitador">
                                            <option value=",">Coma (,)</option>
                                            <option value=";">Punto y coma (;)</option>
                                            <option value="\t">Tabulación</option>
                                            <option value="|">Pipe (|)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="evento_id" class="form-label">Inscribir automáticamente a evento (opcional)</label>
                                        <select class="form-select" id="evento_id" name="evento_id">
                                            <option value="">-- Sin inscripción automática --</option>
                                            <?php foreach ($eventos as $ev): ?>
                                                <option value="<?= $ev['id'] ?>"><?= htmlspecialchars($ev['nombre']) ?> - <?= htmlspecialchars($ev['empresa']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="saltar_primera_fila" name="saltar_primera_fila" checked>
                                            <label class="form-check-label" for="saltar_primera_fila">
                                                La primera fila contiene los nombres de las columnas (cabecera)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="actualizar_existentes" name="actualizar_existentes">
                                            <label class="form-check-label" for="actualizar_existentes">
                                                Actualizar visitantes existentes (por email)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 3: Mapeo de columnas -->
                            <div class="import-step">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="step-number">3</div>
                                    <h4 class="mb-0">Mapeo de Columnas</h4>
                                </div>
                                
                                <p class="text-muted">Selecciona qué columna de tu archivo corresponde a cada campo:</p>
                                
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label fw-bold">Nombre *</label>
                                            <select class="form-select form-select-sm" name="col_nombre" required>
                                                <option value="">-- Seleccionar columna --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label fw-bold">Apellido *</label>
                                            <select class="form-select form-select-sm" name="col_apellido" required>
                                                <option value="">-- Seleccionar columna --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1" selected>Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label fw-bold">Email *</label>
                                            <select class="form-select form-select-sm" name="col_email" required>
                                                <option value="">-- Seleccionar columna --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2" selected>Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Teléfono</label>
                                            <select class="form-select form-select-sm" name="col_telefono">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3" selected>Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Empresa</label>
                                            <select class="form-select form-select-sm" name="col_empresa">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4" selected>Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Cargo</label>
                                            <select class="form-select form-select-sm" name="col_cargo">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5" selected>Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">RUT</label>
                                            <select class="form-select form-select-sm" name="col_rut">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6" selected>Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Mesa</label>
                                            <select class="form-select form-select-sm" name="col_mesa">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Asiento/Silla</label>
                                            <select class="form-select form-select-sm" name="col_asiento">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Lugar</label>
                                            <select class="form-select form-select-sm" name="col_lugar">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mapping-row">
                                            <label class="form-label">Zona</label>
                                            <select class="form-select form-select-sm" name="col_zona">
                                                <option value="">-- No mapear --</option>
                                                <option value="0">Columna 1</option>
                                                <option value="1">Columna 2</option>
                                                <option value="2">Columna 3</option>
                                                <option value="3">Columna 4</option>
                                                <option value="4">Columna 5</option>
                                                <option value="5">Columna 6</option>
                                                <option value="6">Columna 7</option>
                                                <option value="7">Columna 8</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <small>
                                        <strong>Nota:</strong> Los campos marcados con * son obligatorios. 
                                        Si tu archivo tiene un formato diferente, ajusta el mapeo según corresponda.
                                    </small>
                                </div>
                            </div>

                            <!-- Botón procesar -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-upload"></i>
                                    Procesar Importación
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Manejo de drag & drop
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('archivo');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#0056b3';
            dropZone.style.backgroundColor = '#e9ecef';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.borderColor = '#007bff';
            dropZone.style.backgroundColor = '#f8f9fa';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#007bff';
            dropZone.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                mostrarInfoArchivo(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                mostrarInfoArchivo(e.target.files[0]);
            }
        });

        function mostrarInfoArchivo(file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            fileInfo.style.display = 'block';
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        // Validación del formulario
        document.getElementById('importForm').addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Por favor selecciona un archivo CSV');
                return false;
            }

            // Mostrar loading
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
