<?php
/**
 * Modelo para inscripciones
 */
class Inscripcion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear(int $eventoId, int $visitanteId): string {
        // Verificar si ya existe inscripción
        $existe = $this->db->fetch(
            "SELECT id FROM inscripciones WHERE evento_id = ? AND visitante_id = ?", 
            [$eventoId, $visitanteId]
        );
        
        if ($existe) {
            throw new Exception("El visitante ya está inscrito en este evento");
        }

        $data = [
            'evento_id' => $eventoId,
            'visitante_id' => $visitanteId,
            'codigo_qr' => $this->generarCodigoQR(),
            'estado' => 'pendiente'
        ];

        return $this->db->insert('inscripciones', $data);
    }

    public function obtenerPorCodigoQR(string $codigoQR): ?array {
        $sql = "
            SELECT i.*, 
                   v.nombre, v.apellido, v.email, v.empresa, v.cargo,
                   e.nombre as evento_nombre, e.empresa as evento_empresa, e.fecha_inicio, e.fecha_fin
            FROM inscripciones i
            JOIN visitantes v ON i.visitante_id = v.id
            JOIN eventos e ON i.evento_id = e.id
            WHERE i.codigo_qr = ?
        ";
        
        return $this->db->fetch($sql, [$codigoQR]);
    }

    public function marcarIngreso(string $codigoQR, string $ipAddress = null, string $userAgent = null): array {
        // Obtener inscripción
        $inscripcion = $this->obtenerPorCodigoQR($codigoQR);
        
        if (!$inscripcion) {
            throw new Exception("Código QR no válido");
        }

        // Aceptar tanto "pendiente" como "confirmado" (o todos si está en debug)
        if (ALLOW_ALL_STATES) {
            // En modo debug, aceptar cualquier estado excepto "cancelado"
            if ($inscripcion['estado'] === 'cancelado') {
                throw new Exception("Inscripción cancelada");
            }
        } else {
            // En producción, solo aceptar estados válidos
            if (!in_array($inscripcion['estado'], ['pendiente', 'confirmado'])) {
                throw new Exception("La inscripción no está en un estado válido para ingreso (estado: " . $inscripcion['estado'] . ")");
            }
        }

        // Verificar si el evento está activo (saltar en modo debug)
        if (!SKIP_DATE_VALIDATION) {
            $fechaActual = date('Y-m-d');
            
            // Debug: agregar información de fechas al mensaje de error
            if ($fechaActual < $inscripcion['fecha_inicio'] || $fechaActual > $inscripcion['fecha_fin']) {
                $mensaje = "El evento no está disponible en esta fecha. ";
                $mensaje .= "Fecha actual: {$fechaActual}, ";
                $mensaje .= "Evento del {$inscripcion['fecha_inicio']} al {$inscripcion['fecha_fin']}";
                throw new Exception($mensaje);
            }
        } else {
            // En modo debug, mostrar que se saltó la validación
            error_log("DEBUG: Saltando validación de fechas en marcarIngreso");
        }

        // Iniciar transacción
        $this->db->beginTransaction();
        
        try {
            // Registrar acceso
            $dataAcceso = [
                'inscripcion_id' => $inscripcion['id'],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ];

            $accesoId = $this->db->insert('accesos', $dataAcceso);
            
            // Cambiar estado de la inscripción a "ingresado"
            $filasAfectadas = $this->db->update('inscripciones', 
                ['estado' => 'ingresado'], 
                'id = ?', 
                [$inscripcion['id']]
            );
            
            $this->db->commit();
            
            return [
                'exito' => true,
                'mensaje' => 'Acceso confirmado exitosamente',
                'acceso_id' => $accesoId,
                'inscripcion_id' => $inscripcion['id']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'exito' => false,
                'mensaje' => 'Error al confirmar acceso: ' . $e->getMessage()
            ];
        }
    }

    public function confirmar(int $inscripcionId): bool {
        return $this->db->update('inscripciones', ['estado' => 'confirmado'], 'id = ?', [$inscripcionId]) > 0;
    }

    public function cancelar(int $inscripcionId): bool {
        return $this->db->update('inscripciones', ['estado' => 'cancelado'], 'id = ?', [$inscripcionId]) > 0;
    }

    public function obtenerPorEvento(int $eventoId): array {
        $sql = "
            SELECT i.*, 
                   v.nombre, v.apellido, v.email, v.empresa, v.cargo, v.telefono, v.rut,
                   COUNT(a.id) as total_accesos,
                   MAX(a.fecha_ingreso) as ultimo_acceso
            FROM inscripciones i
            JOIN visitantes v ON i.visitante_id = v.id
            LEFT JOIN accesos a ON i.id = a.inscripcion_id
            WHERE i.evento_id = ?
            GROUP BY i.id
            ORDER BY i.fecha_inscripcion DESC
        ";
        
        return $this->db->fetchAll($sql, [$eventoId]);
    }

    public function obtenerPorVisitante(int $visitanteId): array {
        $sql = "
            SELECT i.*, 
                   e.nombre as evento_nombre, e.empresa as evento_empresa, e.fecha_inicio, e.fecha_fin,
                   COUNT(a.id) as total_accesos,
                   MAX(a.fecha_ingreso) as ultimo_acceso
            FROM inscripciones i
            JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN accesos a ON i.id = a.inscripcion_id
            WHERE i.visitante_id = ?
            GROUP BY i.id
            ORDER BY i.fecha_inscripcion DESC
        ";
        
        return $this->db->fetchAll($sql, [$visitanteId]);
    }

    public function verificarAcceso(string $codigoQR): array {
        $inscripcion = $this->obtenerPorCodigoQR($codigoQR);
        
        if (!$inscripcion) {
            return ['valido' => false, 'mensaje' => 'Código QR no válido'];
        }

        // Aceptar tanto "pendiente" como "confirmado" (o todos si está en debug)
        if (ALLOW_ALL_STATES) {
            // En modo debug, aceptar cualquier estado excepto "cancelado"
            if ($inscripcion['estado'] === 'cancelado') {
                return ['valido' => false, 'mensaje' => 'Inscripción cancelada'];
            }
        } else {
            // En producción, solo aceptar estados válidos
            if (!in_array($inscripcion['estado'], ['pendiente', 'confirmado'])) {
                return ['valido' => false, 'mensaje' => 'Inscripción no válida para ingreso (estado: ' . $inscripcion['estado'] . ')'];
            }
        }

        // Validación de fechas (saltar en modo debug)
        if (!SKIP_DATE_VALIDATION) {
            $fechaActual = date('Y-m-d');
            
            // Debug: agregar información de fechas al mensaje de error
            if ($fechaActual < $inscripcion['fecha_inicio'] || $fechaActual > $inscripcion['fecha_fin']) {
                $mensaje = "Evento no disponible en esta fecha. ";
                $mensaje .= "Fecha actual: {$fechaActual}, ";
                $mensaje .= "Evento del {$inscripcion['fecha_inicio']} al {$inscripcion['fecha_fin']}";
                return ['valido' => false, 'mensaje' => $mensaje];
            }
        } else {
            // En modo debug, mostrar que se saltó la validación
            error_log("DEBUG: Saltando validación de fechas para desarrollo");
        }

        // Verificar si ya ingresó hoy
        $sql = "
            SELECT COUNT(*) as ingresos_hoy
            FROM accesos a
            WHERE a.inscripcion_id = ? AND DATE(a.fecha_ingreso) = CURDATE()
        ";
        
        $accesos = $this->db->fetch($sql, [$inscripcion['id']]);
        
        return [
            'valido' => true,
            'visitante' => $inscripcion,
            'ya_ingreso_hoy' => $accesos['ingresos_hoy'] > 0,
            'total_ingresos_hoy' => $accesos['ingresos_hoy'],
            'puede_ingresar' => true,
            'estado_original' => $inscripcion['estado']
        ];
    }

    public function marcarEmailConfirmado(int $inscripcionId): bool {
        return $this->db->update('inscripciones', ['confirmado_email' => 1], 'id = ?', [$inscripcionId]) > 0;
    }

    public function marcarRecordatorioEnviado(int $inscripcionId): bool {
        return $this->db->update('inscripciones', ['recordatorio_enviado' => 1], 'id = ?', [$inscripcionId]) > 0;
    }

    private function generarCodigoQR(): string {
        do {
            $codigo = bin2hex(random_bytes(20));
            $existe = $this->db->fetch("SELECT id FROM inscripciones WHERE codigo_qr = ?", [$codigo]);
        } while ($existe);
        
        return $codigo;
    }

    public function eliminar(int $inscripcionId): int {
        return $this->db->delete('inscripciones', 'id = ?', [$inscripcionId]);
    }
}
