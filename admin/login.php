<?php
require_once '../config/config.php';

$auth = new Auth();

// Si ya está logueado, redirigir al dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/');
    exit;
}

$error = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        if ($auth->login($username, $password)) {
            header('Location: ' . BASE_URL . '/admin/');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}

$pageTitle = 'Iniciar Sesión - Administración';
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
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }
        
        .form-floating > label {
            color: #6c757d;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.2s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .floating-shapes {
            position: absolute;
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
    </style>
</head>
<body>
    <div class="floating-shapes"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card login-card border-0">
                    <div class="card-header login-header text-center py-4 border-0">
                        <h3 class="mb-0">
                            <i class="bi bi-shield-lock"></i>
                            Administración
                        </h3>
                        <p class="mb-0 mt-2 opacity-75">Sistema de Acreditación EventAccess</p>
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Nombre de usuario" required autofocus
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                <label for="username">
                                    <i class="bi bi-person"></i> Nombre de usuario
                                </label>
                            </div>
                            
                            <div class="form-floating mb-4">
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Contraseña" required>
                                <label for="password">
                                    <i class="bi bi-lock"></i> Contraseña
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-login">
                                    <i class="bi bi-box-arrow-in-right"></i>
                                    Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Si no tiene cuenta, contacte al administrador del sistema
                            </small>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i>
                                Volver al sitio público
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent border-0 text-center py-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i>
                            Conexión segura - EventAccess &copy; <?= date('Y') ?>
                        </small>
                    </div>
                </div>
                
                <!-- Demo Credentials Alert -->
                <div class="alert alert-info mt-3 text-center" role="alert">
                    <i class="bi bi-info-circle"></i>
                    <strong>Credenciales de demostración:</strong><br>
                    Usuario: <code>admin</code> | Contraseña: <code>password</code>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 5000);
            });
        });
        
        // Focus handling
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        // Si hay error, enfocar el campo de contraseña
        <?php if ($error && !empty($_POST['username'])): ?>
            passwordInput.focus();
            passwordInput.select();
        <?php endif; ?>
    </script>
</body>
</html>
