<?php
/**
 * Clase para autenticación de usuarios
 */
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login(string $username, string $password): bool {
        $usuario = $this->db->fetch(
            "SELECT * FROM usuarios_admin WHERE username = ? AND activo = 1", 
            [$username]
        );

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['admin_user_id'] = $usuario['id'];
            $_SESSION['admin_username'] = $usuario['username'];
            $_SESSION['admin_nombre'] = $usuario['nombre'];
            $_SESSION['admin_email'] = $usuario['email'];
            return true;
        }

        return false;
    }

    public function logout(): void {
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_nombre']);
        unset($_SESSION['admin_email']);
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['admin_user_id']);
    }

    public function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/admin/login.php');
            exit;
        }
    }

    public function getUser(): ?array {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['admin_user_id'],
            'username' => $_SESSION['admin_username'],
            'nombre' => $_SESSION['admin_nombre'],
            'email' => $_SESSION['admin_email']
        ];
    }

    public function crearUsuario(array $data): string {
        // Verificar que el username no existe
        $existe = $this->db->fetch(
            "SELECT id FROM usuarios_admin WHERE username = ?", 
            [$data['username']]
        );

        if ($existe) {
            throw new Exception("El nombre de usuario ya existe");
        }

        // Hash de la contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        return $this->db->insert('usuarios_admin', $data);
    }

    public function cambiarPassword(int $userId, string $newPassword): bool {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->db->update(
            'usuarios_admin', 
            ['password' => $hashedPassword], 
            'id = ?', 
            [$userId]
        ) > 0;
    }

    public function obtenerUsuarios(): array {
        return $this->db->fetchAll(
            "SELECT id, username, email, nombre, activo, created_at FROM usuarios_admin ORDER BY username"
        );
    }

    public function activarDesactivarUsuario(int $userId, bool $activo): bool {
        return $this->db->update(
            'usuarios_admin', 
            ['activo' => $activo ? 1 : 0], 
            'id = ?', 
            [$userId]
        ) > 0;
    }
}
