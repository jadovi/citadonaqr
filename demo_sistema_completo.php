<?php
/**
 * Demo del Sistema Completo de QR Personal por Evento
 * http://localhost/claudeson4-qr/demo_sistema_completo.php
 */

require_once 'config/config.php';

try {
    echo "<h1>🎉 Demo Sistema QR Personal por Evento - EventAccess</h1>";
    echo "<hr>";
    
    $db = Database::getInstance();
    echo "✅ <strong>Base de datos conectada</strong><br>";

    // Estadísticas generales
    $totalEventos = $db->fetch("SELECT COUNT(*) as total FROM eventos");
    $eventosConHash = $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE hash_acceso IS NOT NULL");
    $eventosSinHash = $db->fetch("SELECT COUNT(*) as total FROM eventos WHERE hash_acceso IS NULL");
    $totalVisitantes = $db->fetch("SELECT COUNT(*) as total FROM visitantes");
    $totalInscripciones = $db->fetch("SELECT COUNT(*) as total FROM inscripciones");

    echo "<div class='stats-grid'>";
    echo "<div class='stat-card'>";
    echo "<h3>📊 Estadísticas del Sistema</h3>";
    echo "<p><strong>Total eventos:</strong> {$totalEventos['total']}</p>";
    echo "<p><strong>Eventos con hash:</strong> {$eventosConHash['total']}</p>";
    echo "<p><strong>Eventos sin hash:</strong> {$eventosSinHash['total']}</p>";
    echo "<p><strong>Total visitantes:</strong> {$totalVisitantes['total']}</p>";
    echo "<p><strong>Total inscripciones:</strong> {$totalInscripciones['total']}</p>";
    echo "</div>";
    echo "</div>";

    echo "<h2>🆕 Nuevas Funcionalidades Implementadas</h2>";
    echo "<div class='features-grid'>";
    
    echo "<div class='feature-card'>";
    echo "<h4>🔑 Hash Único por Evento</h4>";
    echo "<p>Cada evento tiene un hash SHA-256 único que se usa para:</p>";
    echo "<ul>";
    echo "<li>Formulario de inscripción personalizado</li>";
    echo "<li>Página de visitantes con QR personal</li>";
    echo "<li>Gestión centralizada por evento</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='feature-card'>";
    echo "<h4>📱 QR Personal Dinámico</h4>";
    echo "<p>Cada visitante tiene un QR que:</p>";
    echo "<ul>";
    echo "<li>Se renueva automáticamente cada 7 segundos</li>";
    echo "<li>Contiene datos JSON encriptados</li>";
    echo "<li>Es imposible de falsificar</li>";
    echo "<li>Funciona en cualquier dispositivo móvil</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='feature-card'>";
    echo "<h4>🏢 Logo Automático de Empresa</h4>";
    echo "<p>Los formularios de inscripción muestran:</p>";
    echo "<ul>";
    echo "<li>Logo generado automáticamente</li>";
    echo "<li>Información completa del evento</li>";
    echo "<li>Hash del evento para referencia</li>";
    echo "<li>Diseño responsive optimizado</li>";
    echo "</ul>";
    echo "</div>";

    echo "</div>";

    // Verificar si hay eventos
    if ($eventosConHash['total'] > 0) {
        echo "<h2>🎪 Eventos Disponibles con Nuevas Funcionalidades</h2>";
        
        $eventosDemo = $db->fetchAll("
            SELECT e.*, 
                   COUNT(i.id) as total_inscripciones,
                   COUNT(CASE WHEN i.estado = 'confirmado' THEN 1 END) as confirmados
            FROM eventos e
            LEFT JOIN inscripciones i ON e.id = i.evento_id
            WHERE e.hash_acceso IS NOT NULL
            GROUP BY e.id
            ORDER BY e.created_at DESC
            LIMIT 5
        ");

        foreach ($eventosDemo as $evento) {
            $hashCorto = substr($evento['hash_acceso'], 0, 12);
            $enlaceInscripcion = BASE_URL . "/inscripcion.php?event=" . $evento['hash_acceso'];
            $enlaceQR = BASE_URL . "/qr_display.php?access=" . $evento['hash_acceso'];
            
            echo "<div class='event-demo-card'>";
            echo "<div class='event-header'>";
            echo "<h3>{$evento['nombre']}</h3>";
            echo "<p class='company'><i class='icon'>🏢</i> {$evento['empresa']}</p>";
            echo "<p class='dates'><i class='icon'>📅</i> " . date('d/m/Y', strtotime($evento['fecha_inicio'])) . " - " . date('d/m/Y', strtotime($evento['fecha_fin'])) . "</p>";
            echo "<p class='hash'><i class='icon'>🔑</i> Hash: {$hashCorto}...</p>";
            echo "</div>";

            echo "<div class='stats-row'>";
            echo "<span class='badge badge-info'>📝 {$evento['total_inscripciones']} inscripciones</span>";
            echo "<span class='badge badge-success'>✅ {$evento['confirmados']} confirmados</span>";
            echo "</div>";

            echo "<div class='links-section'>";
            echo "<h5>🔗 Enlaces del Evento:</h5>";
            
            echo "<div class='link-group'>";
            echo "<div class='link-item'>";
            echo "<strong>📝 Formulario de Inscripción:</strong><br>";
            echo "<a href='$enlaceInscripcion' target='_blank' class='btn btn-primary'>";
            echo "<i class='icon'>📝</i> Abrir Formulario</a>";
            echo "<input type='text' value='$enlaceInscripcion' readonly class='url-input'>";
            echo "</div>";

            echo "<div class='link-item'>";
            echo "<strong>📱 Página QR Visitantes:</strong><br>";
            echo "<a href='$enlaceQR' target='_blank' class='btn btn-success'>";
            echo "<i class='icon'>📱</i> Ver Visitantes</a>";
            echo "<input type='text' value='$enlaceQR' readonly class='url-input'>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

            if ($evento['confirmados'] > 0) {
                echo "<div class='demo-actions'>";
                echo "<h5>🧪 Probar Funcionalidades:</h5>";
                echo "<div class='action-buttons'>";
                echo "<button onclick='window.open(\"$enlaceInscripcion\", \"_blank\")' class='btn btn-outline-primary'>";
                echo "<i class='icon'>📝</i> Probar Inscripción</button>";
                echo "<button onclick='window.open(\"$enlaceQR\", \"_blank\")' class='btn btn-outline-success'>";
                echo "<i class='icon'>📱</i> Ver QR Dinámicos</button>";
                echo "<button onclick='window.open(\"scanner.php\", \"_blank\")' class='btn btn-outline-info'>";
                echo "<i class='icon'>📷</i> Probar Escáner</button>";
                echo "</div>";
                echo "</div>";
            }

            echo "</div>";
        }
    } else {
        echo "<div class='no-events'>";
        echo "<h3>⚠️ No hay eventos con hash_acceso</h3>";
        echo "<p>Para probar las nuevas funcionalidades, necesita:</p>";
        echo "<ol>";
        echo "<li>Crear eventos en el panel de administración</li>";
        echo "<li>Ejecutar la migración de hash_acceso</li>";
        echo "<li>Inscribir visitantes para probar QR dinámicos</li>";
        echo "</ol>";
        echo "<div class='action-buttons'>";
        echo "<a href='admin/eventos.php' target='_blank' class='btn btn-primary'>📅 Gestionar Eventos</a>";
        echo "<a href='migrar_hash_eventos.php' target='_blank' class='btn btn-warning'>🔄 Ejecutar Migración</a>";
        echo "</div>";
        echo "</div>";
    }

    echo "<h2>🔧 Funcionalidades del Sistema</h2>";
    echo "<div class='functionality-grid'>";

    echo "<div class='functionality-card'>";
    echo "<h4>👥 Para Visitantes</h4>";
    echo "<ul>";
    echo "<li>✅ Formulario de inscripción con logo de empresa</li>";
    echo "<li>✅ QR personal dinámico renovado cada 7 segundos</li>";
    echo "<li>✅ Página optimizada para móviles</li>";
    echo "<li>✅ Wake Lock para mantener pantalla activa</li>";
    echo "<li>✅ Búsqueda de otros participantes</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='functionality-card'>";
    echo "<h4>🔐 Para Administradores</h4>";
    echo "<ul>";
    echo "<li>✅ Panel de eventos con enlaces hash</li>";
    echo "<li>✅ Gestión centralizada por evento</li>";
    echo "<li>✅ Migración automática de eventos legacy</li>";
    echo "<li>✅ Estadísticas en tiempo real</li>";
    echo "<li>✅ Importación masiva de visitantes</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='functionality-card'>";
    echo "<h4>📱 Para Personal del Evento</h4>";
    echo "<ul>";
    echo "<li>✅ Escáner QR compatible con ambos formatos</li>";
    echo "<li>✅ Validación anti-fraude automática</li>";
    echo "<li>✅ Detección de QR expirados</li>";
    echo "<li>✅ Historial de accesos completo</li>";
    echo "<li>✅ Interfaz de acreditación rápida</li>";
    echo "</ul>";
    echo "</div>";

    echo "</div>";

    echo "<h2>🚀 Acciones Rápidas</h2>";
    echo "<div class='quick-actions'>";
    echo "<a href='admin/' target='_blank' class='action-btn admin'>🔐 Panel Admin</a>";
    echo "<a href='scanner.php' target='_blank' class='action-btn scanner'>📷 Escáner QR</a>";
    echo "<a href='buscar.php' target='_blank' class='action-btn search'>🔍 Buscar Visitantes</a>";
    if ($eventosSinHash['total'] > 0) {
        echo "<a href='migrar_hash_eventos.php' target='_blank' class='action-btn migrate'>🔄 Migrar Eventos</a>";
    }
    echo "</div>";

    echo "<h2>📚 Documentación</h2>";
    echo "<div class='docs-section'>";
    echo "<p>Para más información sobre las funcionalidades implementadas:</p>";
    echo "<ul>";
    echo "<li><strong>README.md</strong> - Documentación general del sistema</li>";
    echo "<li><strong>QR_PERSONAL.md</strong> - Documentación técnica específica del QR personal</li>";
    echo "<li><strong>IMPORTADOR.md</strong> - Guía del importador batch</li>";
    echo "</ul>";
    echo "</div>";

    echo "<hr>";
    echo "<div class='footer'>";
    echo "<h3>✨ Sistema EventAccess Completamente Funcional</h3>";
    echo "<p>Todas las funcionalidades solicitadas han sido implementadas:</p>";
    echo "<div class='check-list'>";
    echo "<p>✅ App HTML5 responsive para acreditación de visitantes</p>";
    echo "<p>✅ Escáner QR con cámara móvil</p>";
    echo "<p>✅ CRUD de visitantes con buscador avanzado</p>";
    echo "<p>✅ Backoffice con gestión de eventos</p>";
    echo "<p>✅ Enlaces codificados para formularios de inscripción</p>";
    echo "<p>✅ Sistema de correos de confirmación y recordatorios</p>";
    echo "<p>✅ <strong>QR personal dinámico por evento</strong> (NUEVO)</p>";
    echo "<p>✅ <strong>Hash único por evento con página dedicada</strong> (NUEVO)</p>";
    echo "<p>✅ <strong>Logo automático de empresa</strong> (NUEVO)</p>";
    echo "<p>✅ <strong>Renovación automática cada 7 segundos</strong> (NUEVO)</p>";
    echo "</div>";
    echo "<p><em>Desarrollado con PHP 8+, Bootstrap 5, JavaScript vanilla y MariaDB</em></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error en Demo</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    max-width: 1400px; 
    margin: 30px auto; 
    padding: 20px; 
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    color: #333;
}

h1, h2, h3 { 
    color: #2c3e50; 
    margin-bottom: 1rem;
}

h1 {
    text-align: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.stats-grid, .features-grid, .functionality-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card, .feature-card, .functionality-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-left: 5px solid #667eea;
}

.event-demo-card {
    background: white;
    margin: 20px 0;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.event-header h3 {
    color: #667eea;
    margin-bottom: 10px;
}

.company { color: #7f8c8d; }
.dates { color: #95a5a6; }
.hash { color: #34495e; font-family: monospace; }

.stats-row {
    margin: 15px 0;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    margin-right: 10px;
}

.badge-info { background: #3498db; color: white; }
.badge-success { background: #27ae60; color: white; }

.links-section {
    margin: 20px 0;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.link-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.link-item {
    margin: 15px 0;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 25px;
    margin: 5px;
    font-weight: bold;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary { background: #667eea; color: white; }
.btn-success { background: #27ae60; color: white; }
.btn-warning { background: #f39c12; color: white; }
.btn-outline-primary { background: white; color: #667eea; border: 2px solid #667eea; }
.btn-outline-success { background: white; color: #27ae60; border: 2px solid #27ae60; }
.btn-outline-info { background: white; color: #3498db; border: 2px solid #3498db; }

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.url-input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: monospace;
    font-size: 0.9rem;
    background: #f8f9fa;
}

.demo-actions, .quick-actions {
    margin: 20px 0;
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 30px 0;
}

.action-btn {
    display: block;
    padding: 20px;
    text-decoration: none;
    border-radius: 15px;
    text-align: center;
    font-weight: bold;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.action-btn.admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.action-btn.scanner { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; }
.action-btn.search { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; }
.action-btn.migrate { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; }

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.2);
}

.no-events {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    margin: 20px 0;
    color: #856404;
}

.docs-section {
    background: #e8f4fd;
    border: 1px solid #bee5eb;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.footer {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    text-align: center;
    margin: 30px 0;
}

.check-list p {
    text-align: left;
    margin: 8px 0;
    font-size: 1.1rem;
}

.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.icon {
    margin-right: 5px;
}

@media (max-width: 768px) {
    body { padding: 10px; }
    .link-group { grid-template-columns: 1fr; }
    .quick-actions { grid-template-columns: 1fr; }
    .action-buttons { flex-direction: column; }
}
</style>
