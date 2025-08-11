<?php
/**
 * Modelo para eventos
 */
class Evento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear(array $data): string {
        // Generar código único para el enlace
        $data['link_codigo'] = $this->generarCodigoUnico();
        
        // Generar hash único para acceso QR y páginas
        $data['hash_acceso'] = $this->generarHashAcceso();
        
        return $this->db->insert('eventos', $data);
    }

    public function obtenerTodos(): array {
        return $this->db->fetchAll("SELECT * FROM eventos ORDER BY created_at DESC");
    }

    public function obtenerPorId(int $id): ?array {
        return $this->db->fetch("SELECT * FROM eventos WHERE id = ?", [$id]);
    }

    public function obtenerPorCodigo(string $codigo): ?array {
        return $this->db->fetch("SELECT * FROM eventos WHERE link_codigo = ?", [$codigo]);
    }

    public function obtenerPorHashAcceso(string $hashAcceso): ?array {
        return $this->db->fetch("SELECT * FROM eventos WHERE hash_acceso = ? AND activo = 1", [$hashAcceso]);
    }

    public function actualizar(int $id, array $data): int {
        unset($data['id']); // Evitar actualizar el ID
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('eventos', $data, 'id = ?', [$id]);
    }

    public function eliminar(int $id): int {
        return $this->db->delete('eventos', 'id = ?', [$id]);
    }

    public function obtenerActivos(): array {
        return $this->db->fetchAll("SELECT * FROM eventos WHERE activo = 1 ORDER BY fecha_inicio ASC");
    }

    public function obtenerEstadisticas(int $eventoId): array {
        $sql = "
            SELECT 
                e.nombre as evento_nombre,
                e.empresa,
                e.fecha_inicio,
                e.fecha_fin,
                COUNT(i.id) as total_inscritos,
                COUNT(CASE WHEN i.estado = 'confirmado' THEN 1 END) as confirmados,
                COUNT(a.id) as total_accesos,
                COUNT(DISTINCT a.inscripcion_id) as visitantes_ingresados
            FROM eventos e
            LEFT JOIN inscripciones i ON e.id = i.evento_id
            LEFT JOIN accesos a ON i.id = a.inscripcion_id
            WHERE e.id = ?
            GROUP BY e.id
        ";
        
        return $this->db->fetch($sql, [$eventoId]) ?: [];
    }

    private function generarCodigoUnico(): string {
        do {
            $codigo = bin2hex(random_bytes(16));
            $existe = $this->db->fetch("SELECT id FROM eventos WHERE link_codigo = ?", [$codigo]);
        } while ($existe);
        
        return $codigo;
    }

    private function generarHashAcceso(): string {
        do {
            $hash = hash('sha256', uniqid() . random_bytes(32) . microtime(true));
            $existe = $this->db->fetch("SELECT id FROM eventos WHERE hash_acceso = ?", [$hash]);
        } while ($existe);
        
        return $hash;
    }

    public function obtenerEnlaceInscripcion(string $codigo): string {
        return BASE_URL . "/inscripcion.php?codigo=" . $codigo;
    }

    public function obtenerEnlaceInscripcionPorHash(string $hashAcceso): string {
        return BASE_URL . "/inscripcion.php?event=" . $hashAcceso;
    }

    public function obtenerEnlaceQRPersonal(string $hashAcceso): string {
        return BASE_URL . "/qr_display.php?access=" . $hashAcceso;
    }

    public function obtenerVisitantesDelEvento(string $hashAcceso): array {
        $sql = "
            SELECT i.*, v.nombre, v.apellido, v.email, v.empresa, v.cargo, v.rut, v.telefono,
                   e.nombre as evento_nombre, e.empresa as evento_empresa
            FROM inscripciones i
            JOIN visitantes v ON i.visitante_id = v.id
            JOIN eventos e ON i.evento_id = e.id
            WHERE e.hash_acceso = ? AND i.estado = 'confirmado' AND e.activo = 1
            ORDER BY v.nombre ASC
        ";
        
        return $this->db->fetchAll($sql, [$hashAcceso]);
    }
}
