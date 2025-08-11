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

    public function marcarIngreso(string $codigoQR, string $ipAddress = null, string $userAgent = null): bool {
        // Obtener inscripción
        $inscripcion = $this->obtenerPorCodigoQR($codigoQR);
        
        if (!$inscripcion) {
            throw new Exception("Código QR no válido");
        }

        if ($inscripcion['estado'] !== 'confirmado') {
            throw new Exception("La inscripción no está confirmada");
        }

        // Verificar si el evento está activo
        $fechaActual = date('Y-m-d');
        if ($fechaActual < $inscripcion['fecha_inicio'] || $fechaActual > $inscripcion['fecha_fin']) {
            throw new Exception("El evento no está disponible en esta fecha");
        }

        // Registrar acceso
        $dataAcceso = [
            'inscripcion_id' => $inscripcion['id'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ];

        $this->db->insert('accesos', $dataAcceso);
        
        return true;
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

        if ($inscripcion['estado'] !== 'confirmado') {
            return ['valido' => false, 'mensaje' => 'Inscripción no confirmada'];
        }

        $fechaActual = date('Y-m-d');
        if ($fechaActual < $inscripcion['fecha_inicio'] || $fechaActual > $inscripcion['fecha_fin']) {
            return ['valido' => false, 'mensaje' => 'Evento no disponible en esta fecha'];
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
            'total_ingresos_hoy' => $accesos['ingresos_hoy']
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
