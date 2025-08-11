<?php
/**
 * Ejemplo de uso programático del ImportadorVisitantes
 * Este script muestra cómo usar la clase para automatizar importaciones
 */

require_once 'config/config.php';

try {
    echo "🔄 Iniciando importación de ejemplo...\n";
    
    // Crear instancia del importador
    $importador = new ImportadorVisitantes();
    
    // Configurar mapeo de columnas
    // El archivo template_visitantes_ejemplo.csv tiene el formato:
    // nombre,apellido,email,telefono,empresa,cargo,rut
    $mapeoColumnas = [
        'nombre' => 0,    // Columna 1
        'apellido' => 1,  // Columna 2  
        'email' => 2,     // Columna 3
        'telefono' => 3,  // Columna 4
        'empresa' => 4,   // Columna 5
        'cargo' => 5,     // Columna 6
        'rut' => 6        // Columna 7
    ];
    
    // Configurar opciones
    $opciones = [
        'delimitador' => ',',
        'saltar_primera_fila' => true,
        'actualizar_existentes' => true,
        'evento_id' => null  // Sin inscripción automática
    ];
    
    // Procesar archivo
    $archivo = 'template_visitantes_ejemplo.csv';
    
    if (!file_exists($archivo)) {
        throw new Exception("Archivo no encontrado: $archivo");
    }
    
    echo "📁 Procesando archivo: $archivo\n";
    
    $resultado = $importador->procesarCSV($archivo, $mapeoColumnas, $opciones);
    
    // Mostrar resultados
    echo "\n📊 RESULTADOS DE LA IMPORTACIÓN:\n";
    echo "═══════════════════════════════════\n";
    echo "📈 Procesados: " . $resultado['procesados'] . "\n";
    echo "✅ Importados: " . $resultado['importados'] . "\n";
    echo "🔄 Actualizados: " . $resultado['actualizados'] . "\n";
    echo "❌ Errores: " . $resultado['errores_count'] . "\n";
    echo "🎯 Exitosos: " . $resultado['exitosos'] . "\n";
    
    // Mostrar errores si los hay
    if (!empty($resultado['errores'])) {
        echo "\n🚨 ERRORES ENCONTRADOS:\n";
        echo "═════════════════════\n";
        foreach ($resultado['errores'] as $error) {
            echo "• $error\n";
        }
    }
    
    // Ejemplo de inscripción automática a evento
    echo "\n🎟️ EJEMPLO CON INSCRIPCIÓN A EVENTO:\n";
    echo "═══════════════════════════════════\n";
    
    // Obtener primer evento activo
    $evento = new Evento();
    $eventos = $evento->obtenerActivos();
    
    if (!empty($eventos)) {
        $eventoEjemplo = $eventos[0];
        echo "📅 Evento encontrado: " . $eventoEjemplo['nombre'] . "\n";
        
        // Configurar inscripción automática
        $opcionesConEvento = $opciones;
        $opcionesConEvento['evento_id'] = $eventoEjemplo['id'];
        
        echo "🔄 Procesando con inscripción automática...\n";
        
        // Procesar nuevamente (los existentes se actualizarán, pero también se inscribirán)
        $resultadoConEvento = $importador->procesarCSV($archivo, $mapeoColumnas, $opcionesConEvento);
        
        echo "📊 Resultados con inscripción:\n";
        echo "• Exitosos: " . $resultadoConEvento['exitosos'] . "\n";
        echo "• Errores: " . $resultadoConEvento['errores_count'] . "\n";
        
    } else {
        echo "⚠️ No hay eventos activos para inscripción automática\n";
    }
    
    echo "\n✅ Importación de ejemplo completada exitosamente!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

// Función helper para uso en CLI
function mostrarAyuda() {
    echo "\n📖 USO DEL IMPORTADOR PROGRAMÁTICO:\n";
    echo "═══════════════════════════════════\n";
    echo "php ejemplo_importacion.php\n\n";
    
    echo "🔧 PERSONALIZACIÓN:\n";
    echo "• Modifica \$mapeoColumnas para diferentes formatos CSV\n";
    echo "• Ajusta \$opciones según necesidades\n";
    echo "• Cambia la ruta del archivo a procesar\n\n";
    
    echo "📊 AUTOMATIZACIÓN:\n";
    echo "• Usa en cron jobs para importaciones programadas\n";
    echo "• Integra en APIs para procesamiento remoto\n";
    echo "• Combina con scripts de descarga automática\n\n";
}

// Si se ejecuta desde CLI, mostrar ayuda adicional
if (php_sapi_name() === 'cli') {
    mostrarAyuda();
}
?>
