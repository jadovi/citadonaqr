<?php
/**
 * Script de ejemplo para demostrar QR Personal Dinámico
 * http://localhost/claudeson4-qr/ejemplo_qr_personal.php
 */

require_once 'config/config.php';

try {
    echo "<h1>🧪 Demo QR Personal Dinámico - EventAccess</h1>";
    echo "<hr>";
    
    // Verificar que el sistema esté configurado
    $db = Database::getInstance();
    echo "✅ <strong>Base de datos conectada</strong><br>";
    
    // Verificar columna hash_acceso
    $columnExists = $db->fetch("SHOW COLUMNS FROM inscripciones LIKE 'hash_acceso'");
    if (!$columnExists) {
        echo "❌ <strong>Error:</strong> Columna hash_acceso no existe. Ejecute migrar_hash_acceso.php primero<br>";
        exit;
    }
    echo "✅ <strong>Columna hash_acceso configurada</strong><br>";
    
    // Obtener ejemplos de inscripciones con hash_acceso
    $ejemplosInscripciones = $db->fetchAll("
        SELECT i.*, v.nombre, v.apellido, v.email, e.nombre as evento_nombre, e.empresa as evento_empresa
        FROM inscripciones i
        JOIN visitantes v ON i.visitante_id = v.id
        JOIN eventos e ON i.evento_id = e.id
        WHERE i.hash_acceso IS NOT NULL AND i.estado = 'confirmado'
        LIMIT 5
    ");
    
    echo "<br><h2>📱 Ejemplos de QR Personal Disponibles</h2>";
    
    if (empty($ejemplosInscripciones)) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
        echo "<h3>⚠️ No hay inscripciones confirmadas con QR Personal</h3>";
        echo "<p>Para ver ejemplos de QR Personal, necesita:</p>";
        echo "<ol>";
        echo "<li>Crear un evento</li>";
        echo "<li>Inscribir visitantes</li>";
        echo "<li>Confirmar las inscripciones</li>";
        echo "<li>Ejecutar la migración de hash_acceso</li>";
        echo "</ol>";
        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<a href='admin/eventos.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📅 Crear Evento</a>";
        echo "<a href='migrar_hash_acceso.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔄 Migrar Hash</a>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<p>✅ <strong>Encontradas " . count($ejemplosInscripciones) . " inscripciones con QR Personal</strong></p>";
        
        foreach ($ejemplosInscripciones as $inscripcion) {
            $enlaceQR = BASE_URL . "/qr_display.php?access=" . $inscripcion['hash_acceso'];
            
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
            echo "<div class='row'>";
            echo "<div class='col-md-8'>";
            echo "<h4>👤 {$inscripcion['nombre']} {$inscripcion['apellido']}</h4>";
            echo "<p><strong>📧 Email:</strong> {$inscripcion['email']}</p>";
            echo "<p><strong>🎪 Evento:</strong> {$inscripcion['evento_nombre']} - {$inscripcion['evento_empresa']}</p>";
            echo "<p><strong>🔑 Hash:</strong> <code>" . substr($inscripcion['hash_acceso'], 0, 16) . "...</code></p>";
            echo "</div>";
            echo "<div class='col-md-4 text-center'>";
            echo "<a href='$enlaceQR' target='_blank' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 25px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block; margin: 10px;'>";
            echo "📱 Ver QR Personal";
            echo "</a>";
            echo "<br><small style='color: #6c757d;'>Optimizado para móvil</small>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        
        echo "<br><h2>🔍 Probar Funcionalidades</h2>";
        
        $primeraInscripcion = $ejemplosInscripciones[0];
        $hashEjemplo = $primeraInscripcion['hash_acceso'];
        
        echo "<div style='background: #e7f3ff; border: 1px solid #b8daff; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
        echo "<h3>🧪 Pruebas de API</h3>";
        
        // Test API generar_qr
        echo "<h4>1. 🔄 API Generar QR Dinámico</h4>";
        $urlGenerarQR = BASE_URL . "/api/generar_qr.php?access=" . $hashEjemplo;
        echo "<p><strong>URL:</strong> <a href='$urlGenerarQR' target='_blank'>$urlGenerarQR</a></p>";
        
        // Test API verificar_qr (necesita POST)
        echo "<h4>2. ✅ API Verificar QR</h4>";
        echo "<p><strong>URL:</strong> " . BASE_URL . "/api/verificar_qr.php (método POST)</p>";
        echo "<p><strong>Parámetro:</strong> <code>codigo_qr</code> con JSON del QR dinámico</p>";
        
        // Test escáner
        echo "<h4>3. 📷 Escáner QR</h4>";
        $urlScanner = BASE_URL . "/scanner.php";
        echo "<p><strong>URL:</strong> <a href='$urlScanner' target='_blank'>$urlScanner</a></p>";
        echo "<p>Use el escáner para probar tanto QR legacy como QR dinámicos</p>";
        
        echo "</div>";
        
        echo "<br><h2>📋 Instrucciones de Prueba</h2>";
        echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
        echo "<h3>🎯 Flujo Completo de Prueba</h3>";
        echo "<ol>";
        echo "<li><strong>Abrir QR Personal</strong> - Haga clic en 'Ver QR Personal' desde móvil</li>";
        echo "<li><strong>Observar renovación</strong> - El QR se actualiza cada 7 segundos automáticamente</li>";
        echo "<li><strong>Copiar código QR</strong> - Use herramientas de developer para ver el JSON</li>";
        echo "<li><strong>Probar en escáner</strong> - Use el escáner del sistema para validar</li>";
        echo "<li><strong>Verificar seguridad</strong> - Códigos antiguos son rechazados</li>";
        echo "</ol>";
        echo "</div>";
        
        echo "<br><h2>🔧 Características Técnicas</h2>";
        echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
        echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        echo "<h4>🛡️ Seguridad</h4>";
        echo "<ul>";
        echo "<li>Hash SHA-256 único por visitante</li>";
        echo "<li>Timestamp renovado cada 7 segundos</li>";
        echo "<li>Validación anti-fraude con salt</li>";
        echo "<li>Ventana de validez de 30 segundos</li>";
        echo "<li>JSON con datos encriptados</li>";
        echo "</ul>";
        echo "</div>";
        echo "<div class='col-md-6'>";
        echo "<h4>📱 Móvil</h4>";
        echo "<ul>";
        echo "<li>Diseño responsive completo</li>";
        echo "<li>Wake Lock para pantalla activa</li>";
        echo "<li>Sin necesidad de apps adicionales</li>";
        echo "<li>Optimizado para eventos</li>";
        echo "<li>Cache inteligente offline</li>";
        echo "</ul>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        echo "<br><h2>📊 Datos del JSON QR</h2>";
        
        // Obtener ejemplo de datos QR
        try {
            $inscripcionModel = new Inscripcion();
            $datosQR = $inscripcionModel->generarDatosQR($hashEjemplo);
            
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
            echo "<h4>🔍 Ejemplo de JSON QR en tiempo real:</h4>";
            echo "<pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
            echo htmlspecialchars(json_encode($datosQR['datos_qr'], JSON_PRETTY_PRINT));
            echo "</pre>";
            echo "<p><strong>🕐 Timestamp actual:</strong> " . date('Y-m-d H:i:s', $datosQR['timestamp']) . "</p>";
            echo "<p><strong>⏱️ Próxima renovación en:</strong> " . $datosQR['next_refresh'] . " segundos</p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 10px; padding: 20px; margin: 15px 0;'>";
            echo "<p><strong>❌ Error generando datos QR:</strong> " . $e->getMessage() . "</p>";
            echo "</div>";
        }
    }
    
    echo "<br><h2>🚀 Enlaces Útiles</h2>";
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='admin/' style='background: #007bff; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 10px;'>🔐 Panel Admin</a>";
    echo "<a href='scanner.php' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 10px;'>📷 Escáner QR</a>";
    echo "<a href='buscar.php' style='background: #17a2b8; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; margin: 10px;'>🔍 Buscar Visitantes</a>";
    echo "</div>";
    
    echo "<hr>";
    echo "<p><strong>💡 Tip:</strong> Para obtener mejores resultados, abra los enlaces QR Personal desde un dispositivo móvil.</p>";
    echo "<p><strong>📚 Documentación:</strong> Consulte QR_PERSONAL.md para información técnica detallada.</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Error en Demo</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1200px; 
    margin: 30px auto; 
    padding: 20px; 
    background-color: #f8f9fa;
}
h1, h2, h3, h4 { color: #333; }
.row { display: flex; flex-wrap: wrap; margin: -15px; }
.col-md-6 { flex: 0 0 50%; padding: 15px; }
.col-md-8 { flex: 0 0 66.66%; padding: 15px; }
.col-md-4 { flex: 0 0 33.33%; padding: 15px; }
@media (max-width: 768px) {
    .col-md-6, .col-md-8, .col-md-4 { flex: 0 0 100%; }
}
</style>
