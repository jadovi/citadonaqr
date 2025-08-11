<?php
/**
 * Script de prueba de login directo
 * http://localhost/claudeson4-qr/test_login.php
 */

echo "<h1>🔐 Test de Login EventAccess</h1>";
echo "<hr>";

try {
    // Incluir configuración
    require_once 'config/config.php';
    echo "✅ Config cargada<br>";
    
    // Probar conexión
    $db = Database::getInstance();
    echo "✅ Base de datos conectada<br>";
    
    // Verificar tabla usuarios_admin
    $tableExists = $db->fetch("SHOW TABLES LIKE 'usuarios_admin'");
    if ($tableExists) {
        echo "✅ Tabla usuarios_admin existe<br>";
    } else {
        echo "❌ Tabla usuarios_admin NO existe<br>";
        echo "<strong>Creando tabla...</strong><br>";
        
        $db->query("CREATE TABLE usuarios_admin (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✅ Tabla creada<br>";
    }
    
    // Verificar usuario admin
    $admin = $db->fetch("SELECT * FROM usuarios_admin WHERE username = 'admin'");
    
    if (!$admin) {
        echo "❌ Usuario admin NO existe. Creando...<br>";
        
        // Crear usuario admin
        $password = password_hash('password', PASSWORD_DEFAULT);
        $db->query("INSERT INTO usuarios_admin (username, password, email, nombre) VALUES (?, ?, ?, ?)", [
            'admin',
            $password,
            'admin@eventaccess.com',
            'Administrador'
        ]);
        
        echo "✅ Usuario admin creado<br>";
        $admin = $db->fetch("SELECT * FROM usuarios_admin WHERE username = 'admin'");
    } else {
        echo "✅ Usuario admin existe<br>";
    }
    
    echo "<br><strong>Datos del usuario:</strong><br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Nombre: " . $admin['nombre'] . "<br>";
    echo "Activo: " . ($admin['activo'] ? 'Sí' : 'No') . "<br>";
    echo "Password Hash (primeros 30 chars): " . substr($admin['password'], 0, 30) . "...<br>";
    
    echo "<hr>";
    
    // PROBAR LOGIN DIRECTO
    echo "<h2>🧪 Prueba de autenticación</h2>";
    
    $testPassword = 'password';
    $verify = password_verify($testPassword, $admin['password']);
    
    echo "<strong>Contraseña a probar:</strong> '$testPassword'<br>";
    echo "<strong>Resultado verificación:</strong> " . ($verify ? '✅ CORRECTO' : '❌ INCORRECTO') . "<br>";
    
    if ($verify) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>✅ ¡AUTENTICACIÓN FUNCIONA!</h3>";
        echo "<p>Las credenciales están correctas:</p>";
        echo "<strong>Usuario:</strong> admin<br>";
        echo "<strong>Contraseña:</strong> password<br>";
        echo "</div>";
        
        // Probar clase Auth
        echo "<h3>🔧 Probando clase Auth</h3>";
        
        if (class_exists('Auth')) {
            echo "✅ Clase Auth existe<br>";
            
            $auth = new Auth();
            $loginResult = $auth->login('admin', 'password');
            
            echo "<strong>Resultado login():</strong> " . ($loginResult ? '✅ EXITOSO' : '❌ FALLÓ') . "<br>";
            
            if ($loginResult) {
                echo "✅ Login funcionando perfectamente<br>";
                echo "<a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir al Admin Panel</a>";
            } else {
                echo "❌ Hay problema con la clase Auth<br>";
            }
        } else {
            echo "❌ Clase Auth NO encontrada<br>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3>❌ PROBLEMA CON CONTRASEÑA</h3>";
        echo "<p>Regenerando contraseña...</p>";
        echo "</div>";
        
        // Regenerar contraseña
        $newPassword = password_hash('password', PASSWORD_DEFAULT);
        $db->query("UPDATE usuarios_admin SET password = ? WHERE username = 'admin'", [$newPassword]);
        
        echo "✅ Contraseña regenerada. Recarga la página para probar.<br>";
    }
    
    echo "<hr>";
    echo "<h3>📋 Enlaces de prueba</h3>";
    echo "<a href='admin/login.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>🔐 Página Login</a> ";
    echo "<a href='admin/' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>📊 Dashboard</a> ";
    echo "<a href='index.php' style='background: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin: 5px;'>🏠 Inicio</a>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ ERROR CRÍTICO</h3>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre style='background: #fff; padding: 10px; border-radius: 3px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
h1, h2, h3 { color: #333; }
pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>
