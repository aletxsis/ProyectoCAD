<?php
/**
 * Clase para manejo de sesiones y autenticación
 */

class Auth {
    
    public static function login($identificador, $password) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT u.*, t.nombre as tipo_usuario_nombre 
            FROM usuarios u
            INNER JOIN tipo_usuario t ON u.tipo_usuario_id = t.id
            WHERE u.identificador = ? AND u.activo = 1
        ");
        
        $stmt->execute([$identificador]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['identificador'] = $usuario['identificador'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['tipo_usuario_id'] = $usuario['tipo_usuario_id'];
            $_SESSION['tipo_usuario_nombre'] = $usuario['tipo_usuario_nombre'];
            $_SESSION['cargo'] = $usuario['cargo'];
            $_SESSION['foto_perfil'] = $usuario['foto_perfil'];
            $_SESSION['last_activity'] = time();
            
            return true;
        }
        
        return false;
    }
    
    public static function logout() {
        session_unset();
        session_destroy();
    }
    
    public static function isLoggedIn() {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }
        
        // Verificar timeout de sesión
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            self::logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public static function isDirectivo() {
        return self::isLoggedIn() && $_SESSION['tipo_usuario_id'] == 1;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    public static function requireDirectivo() {
        self::requireLogin();
        if (!self::isDirectivo()) {
            header('Location: /index.php?error=no_permission');
            exit;
        }
    }
    
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'identificador' => $_SESSION['identificador'],
            'nombre_completo' => $_SESSION['nombre_completo'],
            'tipo_usuario_id' => $_SESSION['tipo_usuario_id'],
            'tipo_usuario_nombre' => $_SESSION['tipo_usuario_nombre'],
            'cargo' => $_SESSION['cargo'],
            'foto_perfil' => $_SESSION['foto_perfil']
        ];
    }
}
