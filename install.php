<?php
/**
 * Script de instalación y verificación del sistema EventAccess
 * Ejecutar una sola vez después de configurar la base de datos
 */

// Solo permitir en desarrollo
if (!in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']) && !isset($_GET['force'])) {
    die('Script de instalación solo disponible en desarrollo');
}

$checks = [];
$errors = [];

// Verificar PHP
$checks['PHP Version'] = version_compare(PHP_VERSION, '8.0.0', '>=') ? 'OK' : 'ERROR: Se requiere PHP 8.0+';

// Verificar extensiones
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'session', 'openssl'];
foreach ($requiredExtensions as $ext) {
    $checks["Extensión $ext"] = extension_loaded($ext) ? 'OK' : 'ERROR: Extensión no encontrada';
    if (!extension_loaded($ext)) {
        $errors[] = "Extensión PHP $ext requerida";
    }
}

// Verificar permisos de directorio
$directories = ['uploads', 'qr_codes', 'config'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    $checks["Directorio $dir"] = is_writable($dir) ? 'OK' : 'ERROR: Sin permisos de escritura';
    if (!is_writable($dir)) {
        $errors[] = "Directorio $dir debe tener permisos de escritura";
    }
}

// Verificar configuración de base de datos
try {
    require_once 'config/config.php';
    $db = Database::getInstance();
    $checks['Conexión DB'] = 'OK';
    
    // Verificar tablas
    $tables = ['eventos', 'visitantes', 'inscripciones', 'accesos', 'usuarios_admin'];
    foreach ($tables as $table) {
        try {
            $result = $db->query("SELECT 1 FROM $table LIMIT 1");
            $checks["Tabla $table"] = 'OK';
        } catch (Exception $e) {
            $checks["Tabla $table"] = 'ERROR: Tabla no existe';
            $errors[] = "Tabla $table no encontrada. Ejecutar database.sql";
        }
    }
    
} catch (Exception $e) {
    $checks['Conexión DB'] = 'ERROR: ' . $e->getMessage();
    $errors[] = 'No se puede conectar a la base de datos';
}

// Ejecutar instalación automática si se solicita
if (isset($_POST['install']) && empty($errors)) {
    try {
        // Crear usuario admin por defecto si no existe
        $auth = new Auth();
        $usuarios = $auth->obtenerUsuarios();
        
        if (empty($usuarios)) {
            $db->query("INSERT INTO usuarios_admin (username, password, email, nombre) VALUES (?, ?, ?, ?)", [
                'admin',
                password_hash('password', PASSWORD_DEFAULT),
                'admin@eventaccess.com',
                'Administrador'
            ]);
            $checks['Usuario Admin'] = 'CREADO: admin/password';
        } else {
            $checks['Usuario Admin'] = 'YA EXISTE';
        }
        
        // Crear configuraciones por defecto
        $configs = [
            ['smtp_host', 'localhost', 'Servidor SMTP'],
            ['smtp_port', '587', 'Puerto SMTP'],
            ['smtp_from_email', 'noreply@eventaccess.com', 'Email remitente'],
            ['smtp_from_name', 'EventAccess', 'Nombre remitente'],
            ['site_url', 'http://localhost/claudeson4-qr', 'URL base del sitio']
        ];
        
        foreach ($configs as $config) {
            $existe = $db->fetch("SELECT id FROM configuracion WHERE clave = ?", [$config[0]]);
            if (!$existe) {
                $db->query("INSERT INTO configuracion (clave, valor, descripcion) VALUES (?, ?, ?)", $config);
            }
        }
        
        $installSuccess = true;
        
    } catch (Exception $e) {
        $errors[] = 'Error en instalación: ' . $e->getMessage();
        $installSuccess = false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación EventAccess</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card install-card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><i class="bi bi-gear"></i> Instalación EventAccess</h2>
                        <p class="mb-0">Verificación del sistema y configuración inicial</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($installSuccess) && $installSuccess): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle"></i> ¡Instalación Completada!</h5>
                                <p>El sistema está listo para usar.</p>
                                <hr>
                                <p class="mb-0">
                                    <strong>Próximos pasos:</strong><br>
                                    1. <a href="admin/login.php" class="alert-link">Acceder al panel de administración</a><br>
                                    2. Cambiar la contraseña por defecto<br>
                                    3. Configurar el sistema de correos<br>
                                    4. Eliminar este archivo (install.php)
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-exclamation-triangle"></i> Errores Encontrados</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <h5>Verificación del Sistema</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Componente</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($checks as $check => $status): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($check) ?></td>
                                            <td>
                                                <?php if (strpos($status, 'ERROR') === 0): ?>
                                                    <span class="text-danger"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($status) ?></span>
                                                <?php elseif (strpos($status, 'CREADO') === 0): ?>
                                                    <span class="text-success"><i class="bi bi-plus-circle"></i> <?= htmlspecialchars($status) ?></span>
                                                <?php else: ?>
                                                    <span class="text-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($status) ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (empty($errors) && !isset($installSuccess)): ?>
                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle"></i> Sistema Listo para Instalación</h5>
                                <p>Todas las verificaciones han pasado. Puede proceder con la instalación.</p>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="d-grid">
                                    <button type="submit" name="install" class="btn btn-primary btn-lg">
                                        <i class="bi bi-download"></i> Instalar EventAccess
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if (isset($installSuccess) && $installSuccess): ?>
                            <div class="text-center mt-4">
                                <a href="index.php" class="btn btn-success btn-lg me-2">
                                    <i class="bi bi-house"></i> Ir al Sitio
                                </a>
                                <a href="admin/login.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-shield-lock"></i> Panel Admin
                                </a>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Sistema</h6>
                                <ul class="list-unstyled small">
                                    <li><strong>PHP:</strong> <?= PHP_VERSION ?></li>
                                    <li><strong>Servidor:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?></li>
                                    <li><strong>OS:</strong> <?= PHP_OS ?></li>
                                    <li><strong>Memoria:</strong> <?= ini_get('memory_limit') ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Enlaces Útiles</h6>
                                <ul class="list-unstyled small">
                                    <li><a href="README.md">Documentación</a></li>
                                    <li><a href="scanner.php">Escáner QR</a></li>
                                    <li><a href="buscar.php">Buscar Visitantes</a></li>
                                    <li><a href="admin/">Panel de Admin</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center text-muted">
                        EventAccess &copy; <?= date('Y') ?> - Sistema de Acreditación de Visitantes
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
