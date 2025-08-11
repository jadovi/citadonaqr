<?php
/**
 * Script para arreglar credenciales de admin
 * Ejecutar una sola vez: http://localhost/claudeson4-qr/fix_admin.php
 */

try {
    require_once 'config/config.php';
    
    echo "<h2>🔧 Reparando credenciales de administrador</h2>";
    echo "<hr>";
    
    $db = Database::getInstance();
    
    // Generar nueva contraseña hasheada
    $password = 'password';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "✅ Nueva contraseña generada<br>";
    echo "Hash: " . substr($hashedPassword, 0, 30) . "...<br><br>";
    
    // Eliminar usuario existente si hay problemas
    $db->query("DELETE FROM usuarios_admin WHERE username = ?", ['admin']);
    echo "🗑️ Usuario anterior eliminado<br>";
    
    // Insertar usuario nuevo
    $db->query("INSERT INTO usuarios_admin (username, password, email, nombre, activo) VALUES (?, ?, ?, ?, ?)", [
        'admin',
        $hashedPassword,
        'admin@eventaccess.com',
        'Administrador del Sistema',
        1
    ]);
    
    echo "✅ <strong>Usuario admin creado exitosamente</strong><br><br>";
    
    // Verificar
    $user = $db->fetch("SELECT id, username, email, nombre, activo FROM usuarios_admin WHERE username = ?", ['admin']);
    
    if ($user) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ ¡Credenciales reparadas exitosamente!</h3>";
        echo "<strong>Usuario:</strong> admin<br>";
        echo "<strong>Contraseña:</strong> password<br>";
        echo "<strong>Email:</strong> " . $user['email'] . "<br>";
        echo "<strong>Nombre:</strong> " . $user['nombre'] . "<br>";
        echo "<strong>Estado:</strong> " . ($user['activo'] ? 'Activo' : 'Inactivo') . "<br>";
        echo "</div>";
        
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<a href='admin/login.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🔐 Probar Login Ahora</a>";
        echo "</div>";
        
        // Prueba de verificación de contraseña
        $testLogin = password_verify('password', $hashedPassword);
        echo "<p>🧪 <strong>Test de verificación:</strong> " . ($testLogin ? '✅ Exitoso' : '❌ Falló') . "</p>";
        
    } else {
        echo "❌ Error: No se pudo crear el usuario<br>";
    }
    
    echo "<hr>";
    echo "<p><strong>Importante:</strong> Elimina este archivo después de usarlo por seguridad.</p>";
    echo "<p><em>Archivo a eliminar: fix_admin.php</em></p>";
    
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
h2 { color: #333; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
</style>
