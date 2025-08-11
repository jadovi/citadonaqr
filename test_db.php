<?php
/**
 * Script de prueba de conexión a base de datos
 * Ejecutar desde: http://localhost/claudeson4-qr/test_db.php
 */

try {
    echo "<h2>Prueba de Conexión EventAccess</h2>";
    echo "<hr>";
    
    // Configuración
    $host = 'localhost';
    $dbname = 'evento_acreditacion';
    $username = 'root';
    $password = '123123';
    
    // Conexión
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ <strong>Conexión exitosa a MariaDB</strong><br><br>";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "✅ Base de datos '$dbname' verificada<br>";
    
    // Usar la base de datos
    $pdo->exec("USE $dbname");
    
    // Mostrar bases de datos
    echo "<h3>Bases de datos disponibles:</h3>";
    $stmt = $pdo->query("SHOW DATABASES");
    while ($row = $stmt->fetch()) {
        $current = ($row['Database'] == $dbname) ? ' <strong>(ACTUAL)</strong>' : '';
        echo "- " . $row['Database'] . $current . "<br>";
    }
    
    echo "<br>";
    
    // Verificar si existen las tablas
    echo "<h3>Tablas en '$dbname':</h3>";
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();
        
        if (empty($tables)) {
            echo "⚠️ <strong>No hay tablas</strong> - Necesita ejecutar database.sql<br>";
            echo "<br><strong>Para crear las tablas:</strong><br>";
            echo "1. Abrir phpMyAdmin<br>";
            echo "2. Seleccionar base de datos 'evento_acreditacion'<br>";
            echo "3. Importar archivo 'database.sql'<br>";
        } else {
            echo "✅ <strong>Tablas encontradas:</strong><br>";
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                
                // Contar registros
                try {
                    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM `$tableName`");
                    $count = $countStmt->fetch()['total'];
                    echo "- $tableName ($count registros)<br>";
                } catch (Exception $e) {
                    echo "- $tableName (error al contar)<br>";
                }
            }
        }
    } catch (Exception $e) {
        echo "⚠️ Error al listar tablas: " . $e->getMessage() . "<br>";
        echo "Probablemente necesita importar database.sql<br>";
    }
    
    echo "<br>";
    
    // Información del servidor
    echo "<h3>Información del servidor:</h3>";
    $version = $pdo->query("SELECT VERSION() as version")->fetch();
    echo "- Versión: " . $version['version'] . "<br>";
    echo "- Host: $host<br>";
    echo "- Usuario: $username<br>";
    echo "- Base de datos: $dbname<br>";
    
    echo "<br>";
    echo "<h3>Acciones disponibles:</h3>";
    echo "<a href='install.php' style='padding:10px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Ejecutar Instalación</a> ";
    echo "<a href='index.php' style='padding:10px; background:#28a745; color:white; text-decoration:none; border-radius:5px;'>Ir al Sistema</a> ";
    echo "<a href='admin/login.php' style='padding:10px; background:#17a2b8; color:white; text-decoration:none; border-radius:5px;'>Panel Admin</a>";
    
} catch (PDOException $e) {
    echo "❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "<br><br>";
    echo "<strong>Posibles soluciones:</strong><br>";
    echo "1. Verificar que XAMPP esté iniciado<br>";
    echo "2. Verificar usuario/contraseña (root/123123)<br>";
    echo "3. Verificar que MariaDB esté ejecutándose<br>";
} catch (Exception $e) {
    echo "❌ <strong>Error general:</strong> " . $e->getMessage();
}
?>
