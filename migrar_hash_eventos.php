<?php
/**
 * Script de migración para agregar hash_acceso a eventos existentes
 * Ejecutar una vez: http://localhost/claudeson4-qr/migrar_hash_eventos.php
 */

require_once 'config/config.php';

try {
    echo "<h2>🔄 Migración de Hash de Acceso para Eventos</h2>";
    echo "<hr>";
    
    $db = Database::getInstance();
    
    // Verificar si la columna hash_acceso existe
    $columnExists = $db->fetch("SHOW COLUMNS FROM eventos LIKE 'hash_acceso'");
    
    if (!$columnExists) {
        echo "⚙️ Agregando columna hash_acceso a tabla eventos...<br>";
        $db->query("ALTER TABLE eventos ADD COLUMN hash_acceso VARCHAR(64) UNIQUE DEFAULT NULL AFTER link_codigo");
        echo "✅ Columna hash_acceso agregada<br><br>";
    } else {
        echo "✅ Columna hash_acceso ya existe<br><br>";
    }
    
    // Obtener eventos sin hash_acceso
    $eventosSinHash = $db->fetchAll("SELECT id, nombre, empresa, link_codigo FROM eventos WHERE hash_acceso IS NULL");
    
    echo "📊 <strong>Eventos encontrados sin hash_acceso:</strong> " . count($eventosSinHash) . "<br><br>";
    
    if (empty($eventosSinHash)) {
        echo "✅ Todos los eventos ya tienen hash_acceso asignado<br>";
    } else {
        echo "🔄 Generando hash_acceso para eventos existentes...<br><br>";
        
        $procesados = 0;
        $errores = 0;
        
        foreach ($eventosSinHash as $evento) {
            try {
                // Generar hash único
                do {
                    $hash = hash('sha256', uniqid() . random_bytes(32) . microtime(true));
                    $existe = $db->fetch("SELECT id FROM eventos WHERE hash_acceso = ?", [$hash]);
                } while ($existe);
                
                // Actualizar evento
                $actualizado = $db->update('eventos', ['hash_acceso' => $hash], 'id = ?', [$evento['id']]);
                
                if ($actualizado) {
                    $procesados++;
                    echo "✅ <strong>{$evento['nombre']}</strong> ({$evento['empresa']}): Hash generado<br>";
                    echo "&nbsp;&nbsp;&nbsp;<small>Hash: " . substr($hash, 0, 16) . "...</small><br>";
                    echo "&nbsp;&nbsp;&nbsp;<small>📝 Inscripción: " . BASE_URL . "/inscripcion.php?event={$hash}</small><br>";
                    echo "&nbsp;&nbsp;&nbsp;<small>📱 QR Visitantes: " . BASE_URL . "/qr_display.php?access={$hash}</small><br><br>";
                } else {
                    $errores++;
                    echo "❌ Evento ID {$evento['id']}: Error actualizando<br>";
                }
                
            } catch (Exception $e) {
                $errores++;
                echo "❌ Evento ID {$evento['id']}: Error - " . $e->getMessage() . "<br>";
            }
        }
        
        echo "<br>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>📊 Resultado de la Migración</h3>";
        echo "<strong>Procesados exitosamente:</strong> $procesados<br>";
        echo "<strong>Errores:</strong> $errores<br>";
        echo "<strong>Total:</strong> " . count($eventosSinHash) . "<br>";
        echo "</div>";
    }
    
    // Verificar resultado final
    $totalConHash = $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE hash_acceso IS NOT NULL");
    $totalSinHash = $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE hash_acceso IS NULL");
    
    echo "<h3>🎯 Estado Final</h3>";
    echo "✅ <strong>Eventos con hash_acceso:</strong> " . $totalConHash['total'] . "<br>";
    echo "⚠️ <strong>Eventos sin hash_acceso:</strong> " . $totalSinHash['total'] . "<br>";
    
    if ($totalSinHash['total'] == 0) {
        echo "<br><div style='background: #d1ecf1; padding: 15px; border-radius: 5px; color: #0c5460;'>";
        echo "<h4>🎉 ¡Migración de Eventos Completada Exitosamente!</h4>";
        echo "<p>Todos los eventos ahora tienen su hash_acceso único para:</p>";
        echo "<ul>";
        echo "<li><strong>Formulario de inscripción:</strong> inscripcion.php?event={hash}</li>";
        echo "<li><strong>Página QR visitantes:</strong> qr_display.php?access={hash}</li>";
        echo "<li><strong>Gestión centralizada</strong> por evento</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    // Mostrar enlaces de todos los eventos
    if ($totalConHash['total'] > 0) {
        echo "<h3>📋 Enlaces de Eventos Actualizados</h3>";
        $eventosConHash = $db->fetchAll("
            SELECT id, nombre, empresa, hash_acceso, link_codigo, 
                   DATE_FORMAT(fecha_inicio, '%d/%m/%Y') as fecha_inicio_fmt,
                   DATE_FORMAT(fecha_fin, '%d/%m/%Y') as fecha_fin_fmt
            FROM eventos 
            WHERE hash_acceso IS NOT NULL 
            ORDER BY created_at DESC
        ");
        
        foreach ($eventosConHash as $evento) {
            $hashCorto = substr($evento['hash_acceso'], 0, 8);
            $enlaceInscripcion = BASE_URL . "/inscripcion.php?event=" . $evento['hash_acceso'];
            $enlaceQR = BASE_URL . "/qr_display.php?access=" . $evento['hash_acceso'];
            
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 15px; margin: 10px 0;'>";
            echo "<h5><strong>{$evento['nombre']}</strong></h5>";
            echo "<p class='mb-1'><i class='bi bi-building'></i> {$evento['empresa']}</p>";
            echo "<p class='mb-2'><small>📅 {$evento['fecha_inicio_fmt']} - {$evento['fecha_fin_fmt']}</small></p>";
            echo "<p class='mb-2'><small>🔑 Hash: {$hashCorto}...</small></p>";
            
            echo "<div class='row'>";
            echo "<div class='col-md-6'>";
            echo "<strong>📝 Formulario de Inscripción:</strong><br>";
            echo "<a href='$enlaceInscripcion' target='_blank' class='btn btn-sm btn-primary mb-2'>";
            echo "<i class='bi bi-person-plus'></i> Abrir Formulario</a><br>";
            echo "<small class='text-muted'>$enlaceInscripcion</small>";
            echo "</div>";
            
            echo "<div class='col-md-6'>";
            echo "<strong>📱 Página QR Visitantes:</strong><br>";
            echo "<a href='$enlaceQR' target='_blank' class='btn btn-sm btn-success mb-2'>";
            echo "<i class='bi bi-qr-code'></i> Ver Visitantes QR</a><br>";
            echo "<small class='text-muted'>$enlaceQR</small>";
            echo "</div>";
            echo "</div>";
            
            echo "</div>";
        }
    }
    
    echo "<h3>🔗 Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li><strong>Eventos nuevos</strong> automáticamente tendrán hash_acceso</li>";
    echo "<li><strong>Formularios de inscripción</strong> ahora usan hash por evento</li>";
    echo "<li><strong>Página QR</strong> muestra todos los visitantes del evento</li>";
    echo "<li><strong>Logos de empresa</strong> se muestran automáticamente</li>";
    echo "<li><strong>Compatibilidad</strong> con enlaces legacy mantenida</li>";
    echo "</ol>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='admin/' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>🔐 Panel Admin</a>";
    echo "<a href='admin/eventos.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 10px;'>📅 Gestionar Eventos</a>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>⚡ Nuevas Funcionalidades</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
    echo "<h4>🎯 QR Personal por Evento</h4>";
    echo "<ul>";
    echo "<li><strong>Hash único por evento</strong> - Más lógico y fácil de gestionar</li>";
    echo "<li><strong>Página de visitantes</strong> - Muestra todos los participantes del evento</li>";
    echo "<li><strong>QR dinámico individual</strong> - Cada visitante tiene QR que se renueva cada 7s</li>";
    echo "<li><strong>Logo automático</strong> - Se genera basado en el nombre de la empresa</li>";
    echo "<li><strong>Enlaces directos</strong> - Fácil compartir formulario y página QR</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p><strong>Importante:</strong> Elimina este archivo después de ejecutar la migración por seguridad.</p>";
    echo "<p><em>Archivo a eliminar: migrar_hash_eventos.php</em></p>";
    
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
    max-width: 1200px; 
    margin: 50px auto; 
    padding: 20px; 
    background-color: #f8f9fa;
}
h2, h3 { color: #333; }
.row { display: flex; flex-wrap: wrap; margin: -5px; }
.col-md-6 { flex: 0 0 50%; padding: 5px; }
@media (max-width: 768px) {
    .col-md-6 { flex: 0 0 100%; }
}
.btn { 
    display: inline-block; 
    padding: 6px 12px; 
    margin: 2px; 
    text-decoration: none; 
    border-radius: 4px; 
    color: white;
    font-size: 12px;
}
.btn-primary { background-color: #007bff; }
.btn-success { background-color: #28a745; }
.btn-sm { padding: 4px 8px; font-size: 11px; }
</style>
