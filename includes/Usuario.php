<?php
/**
 * Clase para gestión de usuarios
 */

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los usuarios de tipo Gestión
     */
    public function getUsuariosGestion() {
        $stmt = $this->db->prepare("
            SELECT u.*, t.nombre as tipo_usuario_nombre 
            FROM usuarios u
            INNER JOIN tipo_usuario t ON u.tipo_usuario_id = t.id
            WHERE u.tipo_usuario_id = 2
            ORDER BY u.nombre_completo ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, t.nombre as tipo_usuario_nombre 
            FROM usuarios u
            INNER JOIN tipo_usuario t ON u.tipo_usuario_id = t.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo usuario de gestión
     */
    public function crear($datos) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que el identificador no exista
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE identificador = ?");
            $stmt->execute([$datos['identificador']]);
            if ($stmt->fetch()) {
                throw new Exception("El identificador ya existe");
            }
            
            // Hash de la contraseña
            $passwordHash = password_hash($datos['password'], HASH_ALGO);
            
            // Insertar usuario
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (identificador, nombre_completo, password, foto_perfil, cargo, tipo_usuario_id)
                VALUES (?, ?, ?, ?, ?, 2)
            ");
            
            $stmt->execute([
                $datos['identificador'],
                $datos['nombre_completo'],
                $passwordHash,
                $datos['foto_perfil'] ?? null,
                $datos['cargo']
            ]);
            
            $nuevoId = $this->db->lastInsertId();
            
            // Registrar en auditoría
            $this->registrarAuditoria($nuevoId, 'CREATE', null, $datos);
            
            $this->db->commit();
            return $nuevoId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Actualizar usuario de gestión
     */
    public function actualizar($id, $datos) {
        try {
            $this->db->beginTransaction();
            
            // Obtener datos anteriores
            $datosAnteriores = $this->getById($id);
            if (!$datosAnteriores || $datosAnteriores['tipo_usuario_id'] != 2) {
                throw new Exception("Usuario no encontrado o no es de tipo Gestión");
            }
            
            // Verificar que el identificador no exista en otro usuario
            if (isset($datos['identificador']) && $datos['identificador'] != $datosAnteriores['identificador']) {
                $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE identificador = ? AND id != ?");
                $stmt->execute([$datos['identificador'], $id]);
                if ($stmt->fetch()) {
                    throw new Exception("El identificador ya existe");
                }
            }
            
            // Construir query de actualización
            $campos = [];
            $valores = [];
            
            if (isset($datos['identificador'])) {
                $campos[] = "identificador = ?";
                $valores[] = $datos['identificador'];
            }
            if (isset($datos['nombre_completo'])) {
                $campos[] = "nombre_completo = ?";
                $valores[] = $datos['nombre_completo'];
            }
            if (isset($datos['password']) && !empty($datos['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($datos['password'], HASH_ALGO);
            }
            if (isset($datos['foto_perfil'])) {
                $campos[] = "foto_perfil = ?";
                $valores[] = $datos['foto_perfil'];
            }
            if (isset($datos['cargo'])) {
                $campos[] = "cargo = ?";
                $valores[] = $datos['cargo'];
            }
            if (isset($datos['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = $datos['activo'];
            }
            
            $valores[] = $id;
            
            $stmt = $this->db->prepare("
                UPDATE usuarios 
                SET " . implode(", ", $campos) . "
                WHERE id = ?
            ");
            
            $stmt->execute($valores);
            
            // Registrar en auditoría
            $this->registrarAuditoria($id, 'UPDATE', $datosAnteriores, $datos);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Eliminar usuario de gestión
     */
    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            
            // Obtener datos antes de eliminar
            $datosAnteriores = $this->getById($id);
            if (!$datosAnteriores || $datosAnteriores['tipo_usuario_id'] != 2) {
                throw new Exception("Usuario no encontrado o no es de tipo Gestión");
            }
            
            // Eliminar usuario
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            // Registrar en auditoría
            $this->registrarAuditoria($id, 'DELETE', $datosAnteriores, null);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Registrar acción en auditoría
     */
    private function registrarAuditoria($usuarioId, $accion, $datosAnteriores, $datosNuevos) {
        $stmt = $this->db->prepare("
            INSERT INTO auditoria_usuarios 
            (usuario_id, usuario_modificador_id, accion, datos_anteriores, datos_nuevos, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $usuarioId,
            $_SESSION['usuario_id'] ?? null,
            $accion,
            $datosAnteriores ? json_encode($datosAnteriores) : null,
            $datosNuevos ? json_encode($datosNuevos) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    /**
     * Manejar subida de foto de perfil
     */
    public function subirFotoPerfil($file) {
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        if (!in_array($file['type'], $permitidos)) {
            throw new Exception("Tipo de archivo no permitido");
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception("El archivo es demasiado grande");
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('perfil_') . '.' . $extension;
        $rutaDestino = UPLOAD_PATH . $nombreArchivo;
        
        if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            throw new Exception("Error al subir el archivo");
        }
        
        return $nombreArchivo;
    }
}
