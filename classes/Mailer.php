<?php
/**
 * Clase para envío de correos electrónicos
 */
class Mailer {
    private $db;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cargarConfiguracion();
    }

    private function cargarConfiguracion(): void {
        $configs = $this->db->fetchAll("SELECT clave, valor FROM configuracion WHERE clave LIKE 'smtp_%'");
        
        $this->config = [];
        foreach ($configs as $config) {
            $this->config[$config['clave']] = $config['valor'];
        }
    }

    public function enviarConfirmacionInscripcion(array $inscripcion): bool {
        $visitante = $inscripcion;
        $evento = $inscripcion;

        $asunto = "Confirmación de inscripción - {$evento['evento_nombre']}";
        
        // Obtener enlace QR personal del evento
        $eventoModel = new Evento();
        $enlaceQR = null;
        
        if (!empty($evento['evento_id'])) {
            $eventoData = $eventoModel->obtenerPorId($evento['evento_id']);
            if ($eventoData && !empty($eventoData['hash_acceso'])) {
                $enlaceQR = $eventoModel->obtenerEnlaceQRPersonal($eventoData['hash_acceso']);
            }
        }
        
        $cuerpo = $this->generarTemplateConfirmacion($visitante, $evento, $inscripcion, $enlaceQR);
        
        return $this->enviarEmail($visitante['email'], $asunto, $cuerpo);
    }

    public function enviarRecordatorio(array $inscripcion): bool {
        $visitante = $inscripcion;
        $evento = $inscripcion;

        $asunto = "Recordatorio de evento - {$evento['evento_nombre']}";
        
        $cuerpo = $this->generarTemplateRecordatorio($visitante, $evento, $inscripcion);
        
        return $this->enviarEmail($visitante['email'], $asunto, $cuerpo);
    }

    private function enviarEmail(string $destinatario, string $asunto, string $cuerpo): bool {
        // Headers para el email
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['smtp_from_name'] . ' <' . $this->config['smtp_from_email'] . '>',
            'Reply-To: ' . $this->config['smtp_from_email'],
            'X-Mailer: PHP/' . phpversion()
        ];

        // Usando la función mail() de PHP (para desarrollo local)
        // En producción se debería usar una librería como PHPMailer o SwiftMailer
        try {
            $enviado = mail($destinatario, $asunto, $cuerpo, implode("\r\n", $headers));
            
            // Log del envío
            $this->registrarEnvio($destinatario, $asunto, $enviado);
            
            return $enviado;
        } catch (Exception $e) {
            error_log("Error enviando email: " . $e->getMessage());
            return false;
        }
    }

    private function generarTemplateConfirmacion(array $visitante, array $evento, array $inscripcion, ?string $enlaceQR = null): string {
        $qrUrl = $this->generarUrlQR($inscripcion['codigo_qr']);
        
        // Sección del QR personal
        $seccionQRPersonal = '';
        if ($enlaceQR) {
            $seccionQRPersonal = "
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>
                    <h3 style='color: white; margin-top: 0;'>📱 Página QR del Evento</h3>
                    <p style='font-size: 16px; margin-bottom: 20px;'>Acceda a la página con todos los visitantes del evento y sus QR dinámicos:</p>
                    <a href='{$enlaceQR}' style='display: inline-block; background: white; color: #667eea; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px;'>
                        🔗 Ver Visitantes del Evento
                    </a>
                    <p style='font-size: 14px; margin-top: 15px; opacity: 0.9;'>
                        💡 <strong>Nuevas características:</strong><br>
                        • Lista completa de visitantes confirmados<br>
                        • QR individual que se actualiza cada 7 segundos<br>
                        • Optimizado para dispositivos móviles<br>
                        • Búsqueda rápida de participantes<br>
                        • Máxima seguridad anti-fraude
                    </p>
                </div>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmación de Inscripción</title>
        </head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #28a745; text-align: center;'>¡Inscripción Confirmada!</h2>
                
                <p>Estimado/a <strong>{$visitante['nombre']} {$visitante['apellido']}</strong>,</p>
                
                <p>Su inscripción al evento <strong>{$evento['evento_nombre']}</strong> ha sido confirmada exitosamente.</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalles del evento:</h3>
                    <ul>
                        <li><strong>Evento:</strong> {$evento['evento_nombre']}</li>
                        <li><strong>Empresa:</strong> {$evento['evento_empresa']}</li>
                        <li><strong>Fecha inicio:</strong> {$evento['fecha_inicio']}</li>
                        <li><strong>Fecha fin:</strong> {$evento['fecha_fin']}</li>
                    </ul>
                </div>
                
                {$seccionQRPersonal}
                
                <div style='background-color: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;'>
                    <h3>Código de respaldo:</h3>
                    <p>Código: <strong>{$inscripcion['codigo_qr']}</strong></p>
                    <p style='font-size: 12px; color: #666;'>Use este código como respaldo si no puede acceder al QR personal</p>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h4 style='color: #856404; margin-top: 0;'>📲 Instrucciones para el día del evento:</h4>
                    <ol style='color: #856404;'>
                        <li>Abra el enlace 'Ver Visitantes del Evento' en su celular</li>
                        <li>Busque su nombre en la lista y toque 'Ver QR'</li>
                        <li>Mantenga la pantalla encendida al llegar al evento</li>
                        <li>Presente el QR dinámico al personal de acreditación</li>
                        <li>¡Listo! Ingreso confirmado de forma segura</li>
                    </ol>
                </div>
                
                <p>Le recordamos que debe presentar su código QR el día del evento para su acreditación.</p>
                
                <p>Saludos cordiales,<br>
                Equipo de Eventos</p>
            </div>
        </body>
        </html>";
    }

    private function generarTemplateRecordatorio(array $visitante, array $evento, array $inscripcion): string {
        $qrUrl = $this->generarUrlQR($inscripcion['codigo_qr']);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recordatorio de Evento</title>
        </head>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #007bff; text-align: center;'>Recordatorio de Evento</h2>
                
                <p>Estimado/a <strong>{$visitante['nombre']} {$visitante['apellido']}</strong>,</p>
                
                <p>Le recordamos que está inscrito/a en el evento <strong>{$evento['evento_nombre']}</strong>.</p>
                
                <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalles del evento:</h3>
                    <ul>
                        <li><strong>Evento:</strong> {$evento['evento_nombre']}</li>
                        <li><strong>Empresa:</strong> {$evento['evento_empresa']}</li>
                        <li><strong>Fecha inicio:</strong> {$evento['fecha_inicio']}</li>
                        <li><strong>Fecha fin:</strong> {$evento['fecha_fin']}</li>
                    </ul>
                </div>
                
                <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;'>
                    <h3>Su código QR de acceso:</h3>
                    <p>Código: <strong>{$inscripcion['codigo_qr']}</strong></p>
                    <p style='font-size: 12px; color: #666;'>Presente este código en el evento para su acreditación</p>
                </div>
                
                <p>No olvide presentar su código QR para la acreditación en el evento.</p>
                
                <p>¡Esperamos verle pronto!</p>
                
                <p>Saludos cordiales,<br>
                Equipo de Eventos</p>
            </div>
        </body>
        </html>";
    }

    private function generarUrlQR(string $codigoQR): string {
        // En una implementación real, aquí se generaría el código QR como imagen
        return BASE_URL . "/qr.php?code=" . $codigoQR;
    }

    private function registrarEnvio(string $destinatario, string $asunto, bool $exitoso): void {
        try {
            $this->db->query(
                "INSERT INTO log_emails (destinatario, asunto, exitoso, fecha_envio) VALUES (?, ?, ?, NOW())",
                [$destinatario, $asunto, $exitoso ? 1 : 0]
            );
        } catch (Exception $e) {
            // No hacer nada si falla el log
        }
    }

    public function enviarEmailsRecordatorio(int $eventoId): int {
        // Obtener inscripciones confirmadas que no han recibido recordatorio
        $sql = "
            SELECT i.*, 
                   v.nombre, v.apellido, v.email,
                   e.nombre as evento_nombre, e.empresa as evento_empresa, e.fecha_inicio, e.fecha_fin
            FROM inscripciones i
            JOIN visitantes v ON i.visitante_id = v.id
            JOIN eventos e ON i.evento_id = e.id
            WHERE i.evento_id = ? 
              AND i.estado = 'confirmado' 
              AND i.recordatorio_enviado = 0
              AND e.fecha_inicio >= CURDATE()
        ";
        
        $inscripciones = $this->db->fetchAll($sql, [$eventoId]);
        $enviados = 0;
        
        foreach ($inscripciones as $inscripcion) {
            if ($this->enviarRecordatorio($inscripcion)) {
                // Marcar como recordatorio enviado
                $inscripcionModel = new Inscripcion();
                $inscripcionModel->marcarRecordatorioEnviado($inscripcion['id']);
                $enviados++;
            }
        }
        
        return $enviados;
    }
}
