<?php
/**
 * Script de migración para agregar hash_acceso a inscripciones existentes
 * Ejecutar una vez: http://localhost/claudeson4-qr/migrar_hash_acceso.php
 */

require_once 'config/config.php';

try {
    echo "<h2>🔄 Migración de Hash de Acceso para QR Personal</h2>";
    echo "<hr>";
    
    $db = Database::getInstance();
    
    // Verificar si la columna hash_acceso existe
    $columnExists = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'hash_acceso'");
    
    if (!$columnExists) {
        echo "⚙️ Agregando columna hash_acceso...<br>";
        $db->query("ALTER TABLE inscripciones ADD COLUMN hash_acceso VARCHAR(64) UNIQUE DEFAULT NULL AFTER codigo_qr");
        echo "✅ Columna hash_acceso agregada<br><br>";
    } else {
        echo "✅ Columna hash_acceso ya existe<br><br>";
    }
    
    // Obtener inscripciones sin hash_acceso
    $inscripcionesSinHash = $db->fetchAll("SELECT id, codigo_qr FROM inscripciones WHERE hash_acceso IS NULL");
    
    echo "📊 <strong>Inscripciones encontradas sin hash_acceso:</strong> " . count($inscripcionesSinHash) . "<br><br>";
    
    if (empty($inscripcionesSinHash)) {
        echo "✅ Todas las inscripciones ya tienen hash_acceso asignado<br>";
    } else {
        echo "🔄 Generando hash_acceso para inscripciones existentes...<br>";
        
        $procesadas = 0;
        $errores = 0;
        
        foreach ($inscripcionesSinHash as $inscripcion) {
            try {
                // Generar hash único
                do {
                    $hash = hash('sha256', uniqid() . random_bytes(32) . microtime(true));
                    $existe = $db->fetch("SELECT id FROM inscripciones WHERE hash_acceso = ?", [$hash]);
                } while ($existe);
                
                // Actualizar inscripción
                $actualizado = $db->update('inscripciones', ['hash_acceso' => $hash], 'id = ?', [$inscripcion['id']]);
                
                if ($actualizado) {
                    $procesadas++;
                    echo "✅ Inscripción ID {$inscripcion['id']}: Hash generado<br>";
                } else {
                    $errores++;
                    echo "❌ Inscripción ID {$inscripcion['id']}: Error actualizando<br>";
                }
                
            } catch (Exception $e) {
                $errores++;
                echo "❌ Inscripción ID {$inscripcion['id']}: Error - " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>📊 Resultado de la Migración</h3>";
        echo "<strong>Procesadas exitosamente:</strong> $procesadas<br>";
        echo "<strong>Errores:</strong> $errores<br>";
        echo "<strong>Total:</strong> " . count($inscripcionesSinHash) . "<br>";
        echo "</div>";
    }
    
    // Verificar resultado final
    $totalConHash = $db->fetch("SELECT COUNT(*) as total FROM inscripciones WHERE hash_acceso IS NOT NULL");
    $totalSinHash = $db->fetch("SELECT COUNT(*) as total FROM inscripciones WHERE hash_acceso IS NULL");
    
    echo "<h3>🎯 Estado Final</h3>";
    echo "✅ <strong>Inscripciones con hash_acceso:</strong> " . $totalConHash['total'] . "<br>";
    echo "⚠️ <strong>Inscripciones sin hash_acceso:</strong> " . $totalSinHash['total'] . "<br>";
    
    if ($totalSinHash['total'] == 0) {
        echo "<br><div style='background: #d1ecf1; padding: 15px; border-radius: 5px; color: #0c5460;'>";
        echo "<h4>🎉 ¡Migración Completada Exitosamente!</h4>";
        echo "<p>Todas las inscripciones ahora tienen su hash_acceso único para QR personal.</p>";
        echo "</div>";
        
        echo "<h3>🔗 Próximos pasos:</h3>";
        echo "<ol>";
        echo "<li>Las nuevas inscripciones automáticamente tendrán QR personal</li>";
        echo "<li>Los emails de confirmación incluirán el enlace al QR personal</li>";
        echo "<li>Los visitantes pueden acceder a su QR desde cualquier dispositivo</li>";
        echo "<li>El sistema de escáner reconoce tanto QR legacy como QR dinámicos</li>";
        echo "</ol>";
        
        echo "<div style='text-align: center; margin: 30px 0;'>";
        echo "<a href='admin/' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>🔐 Panel Admin</a>";
        echo "<a href='scanner.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>📱 Probar Escáner</a>";
        echo "</div>";
    }
    
    // Ejemplo de uso
    if ($totalConHash['total'] > 0) {
        echo "<h3>📋 Ejemplo de Enlaces QR Personal</h3>";
        $ejemplos = $db->fetchAll("
            SELECT i.hash_acceso, v.nombre, v.apellido, e.nombre as evento_nombre 
            FROM inscripciones i 
            JOIN visitantes v ON i.visitante_id = v.id 
            JOIN eventos e ON i.evento_id = e.id 
            WHERE i.hash_acceso IS NOT NULL 
            LIMIT 3
        ");
        
        foreach ($ejemplos as $ejemplo) {
            $enlace = BASE_URL . "/qr_display.php?access=" . $ejemplo['hash_acceso'];
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
            echo "<strong>{$ejemplo['nombre']} {$ejemplo['apellido']}</strong> - {$ejemplo['evento_nombre']}<br>";
            echo "<small><a href='$enlace' target='_blank'>$enlace</a></small>";
            echo "</div>";
        }
    }
    
    echo "<hr>";
    echo "<p><strong>Importante:</strong> Elimina este archivo después de ejecutar la migración por seguridad.</p>";
    echo "<p><em>Archivo a eliminar: migrar_hash_acceso.php</em></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ Error en la Migración</h3>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "</div>";
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1000px; 
    margin: 50px auto; 
    padding: 20px; 
    background-color: #f8f9fa;
}
h2, h3 { color: #333; }
</style>
