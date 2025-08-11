<?php
/**
 * Archivo de debug - Diagnosticar errores del sistema
 */

echo "<h1>🔍 Debug EventAccess</h1>";
echo "<hr>";

try {
    echo "✅ <strong>PHP funcionando</strong><br>";
    echo "PHP Version: " . PHP_VERSION . "<br><br>";
    
    // Test 1: Configuración básica
    echo "<h3>1. Probando configuración básica...</h3>";
    if (file_exists('config/config.php')) {
        echo "✅ config/config.php existe<br>";
        
        // Capturar errores
        ob_start();
        $error = error_get_last();
        
        include_once 'config/config.php';
        
        $output = ob_get_clean();
        $newError = error_get_last();
        
        if ($newError && $newError !== $error) {
            echo "❌ Error en config.php: " . $newError['message'] . "<br>";
            echo "Archivo: " . $newError['file'] . " línea " . $newError['line'] . "<br>";
        } else {
            echo "✅ config/config.php cargado correctamente<br>";
        }
    } else {
        echo "❌ config/config.php NO existe<br>";
    }
    
    echo "<br>";
    
    // Test 2: Base de datos
    echo "<h3>2. Probando conexión a base de datos...</h3>";
    if (class_exists('Database')) {
        echo "✅ Clase Database encontrada<br>";
        try {
            $db = Database::getInstance();
            echo "✅ Conexión a base de datos exitosa<br>";
        } catch (Exception $e) {
            echo "❌ Error de base de datos: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Clase Database NO encontrada<br>";
    }
    
    echo "<br>";
    
    // Test 3: Directorios
    echo "<h3>3. Verificando directorios...</h3>";
    $dirs = ['uploads', 'qr_codes', 'config', 'classes', 'models', 'api', 'admin'];
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            echo "✅ Directorio $dir existe<br>";
        } else {
            echo "❌ Directorio $dir NO existe<br>";
        }
    }
    
    echo "<br>";
    
    // Test 4: Archivos críticos
    echo "<h3>4. Verificando archivos críticos...</h3>";
    $files = [
        'classes/Database.php',
        'classes/Auth.php',
        'models/Evento.php',
        'models/Visitante.php',
        'models/Inscripcion.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "✅ $file existe<br>";
        } else {
            echo "❌ $file NO existe<br>";
        }
    }
    
    echo "<br>";
    
    // Test 5: Constantes
    echo "<h3>5. Verificando constantes...</h3>";
    if (defined('BASE_URL')) {
        echo "✅ BASE_URL definida: " . BASE_URL . "<br>";
    } else {
        echo "❌ BASE_URL NO definida<br>";
    }
    
    echo "<br>";
    echo "<h3>✅ Debug completado</h3>";
    echo "<p><strong>Si ve este mensaje, PHP está funcionando correctamente.</strong></p>";
    echo "<p><a href='index.php'>🏠 Probar página principal</a></p>";
    
} catch (Exception $e) {
    echo "❌ <strong>Error crítico:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "❌ <strong>Error fatal:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
}
?>
