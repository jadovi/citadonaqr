<?php
/**
 * Modelo para visitantes
 */
class Visitante {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear(array $data): string {
        return $this->db->insert('visitantes', $data);
    }

    public function obtenerTodos(): array {
        return $this->db->fetchAll("SELECT * FROM visitantes ORDER BY apellido, nombre");
    }

    public function obtenerPorId(int $id): ?array {
        return $this->db->fetch("SELECT * FROM visitantes WHERE id = ?", [$id]);
    }

    public function obtenerPorEmail(string $email): ?array {
        return $this->db->fetch("SELECT * FROM visitantes WHERE email = ?", [$email]);
    }

    public function obtenerPorRut(string $rut): ?array {
        return $this->db->fetch("SELECT * FROM visitantes WHERE rut = ?", [$rut]);
    }

    public function actualizar(int $id, array $data): int {
        unset($data['id']); // Evitar actualizar el ID
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('visitantes', $data, 'id = ?', [$id]);
    }

    public function eliminar(int $id): int {
        return $this->db->delete('visitantes', 'id = ?', [$id]);
    }

    public function buscar(string $termino): array {
        $termino = "%{$termino}%";
        $sql = "
            SELECT DISTINCT v.*, 
                   GROUP_CONCAT(DISTINCT e.nombre SEPARATOR ', ') as eventos_inscritos,
                   COUNT(DISTINCT i.id) as total_inscripciones
            FROM visitantes v
            LEFT JOIN inscripciones i ON v.id = i.visitante_id
            LEFT JOIN eventos e ON i.evento_id = e.id
            WHERE v.nombre LIKE ? 
               OR v.apellido LIKE ? 
               OR v.email LIKE ? 
               OR v.empresa LIKE ? 
               OR v.rut LIKE ?
            GROUP BY v.id
            ORDER BY v.apellido, v.nombre
        ";
        
        return $this->db->fetchAll($sql, [$termino, $termino, $termino, $termino, $termino]);
    }

    public function obtenerConInscripciones(): array {
        $sql = "
            SELECT v.*,
                   COUNT(i.id) as total_inscripciones,
                   COUNT(CASE WHEN i.estado = 'confirmado' THEN 1 END) as inscripciones_confirmadas,
                   COUNT(a.id) as total_accesos
            FROM visitantes v
            LEFT JOIN inscripciones i ON v.id = i.visitante_id
            LEFT JOIN accesos a ON i.id = a.inscripcion_id
            GROUP BY v.id
            ORDER BY v.apellido, v.nombre
        ";
        
        return $this->db->fetchAll($sql);
    }

    public function verificarInscripcion(int $visitanteId, int $eventoId): ?array {
        $sql = "
            SELECT i.*, e.nombre as evento_nombre, v.nombre, v.apellido
            FROM inscripciones i
            JOIN eventos e ON i.evento_id = e.id
            JOIN visitantes v ON i.visitante_id = v.id
            WHERE i.visitante_id = ? AND i.evento_id = ?
        ";
        
        return $this->db->fetch($sql, [$visitanteId, $eventoId]);
    }

    public function obtenerInscripcionesPorVisitante(int $visitanteId): array {
        $sql = "
            SELECT i.*, e.nombre as evento_nombre, e.empresa, e.fecha_inicio, e.fecha_fin,
                   COUNT(a.id) as veces_ingresado,
                   MAX(a.fecha_ingreso) as ultimo_ingreso
            FROM inscripciones i
            JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN accesos a ON i.id = a.inscripcion_id
            WHERE i.visitante_id = ?
            GROUP BY i.id
            ORDER BY e.fecha_inicio DESC
        ";
        
        return $this->db->fetchAll($sql, [$visitanteId]);
    }
}
