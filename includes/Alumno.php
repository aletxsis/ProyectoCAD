<?php
/**
 * Clase para gestión de alumnos
 */

class Alumno {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los alumnos activos
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT * FROM alumnos 
            ORDER BY nombre_completo ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener alumno por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM alumnos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener materias inscritas de un alumno
     */
    public function getMaterias($alumnoId) {
        $stmt = $this->db->prepare("
            SELECT m.*, i.parcial_1, i.parcial_2, i.parcial_3, i.calificacion_final
            FROM inscripciones i
            INNER JOIN materias m ON i.materia_id = m.id
            WHERE i.alumno_id = ?
            ORDER BY m.nombre
        ");
        $stmt->execute([$alumnoId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Crear nuevo alumno
     */
    public function crear($datos) {
        try {
            $this->db->beginTransaction();
            
            // Verificar que la matrícula no exista
            $stmt = $this->db->prepare("SELECT id FROM alumnos WHERE identificador = ?");
            $stmt->execute([$datos['identificador']]);
            if ($stmt->fetch()) {
                throw new Exception("La matrícula ya existe");
            }
            
            // Hash de la contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_BCRYPT);
            
            // Insertar alumno
            $stmt = $this->db->prepare("
                INSERT INTO alumnos (identificador, nombre_completo, edad, password, foto_perfil)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $datos['identificador'],
                $datos['nombre_completo'],
                $datos['edad'],
                $passwordHash,
                $datos['foto_perfil'] ?? null
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
     * Actualizar alumno
     */
    public function actualizar($id, $datos) {
        try {
            $this->db->beginTransaction();
            
            // Obtener datos anteriores
            $datosAnteriores = $this->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Alumno no encontrado");
            }
            
            // Verificar que la matrícula no exista en otro alumno
            if (isset($datos['identificador']) && $datos['identificador'] != $datosAnteriores['identificador']) {
                $stmt = $this->db->prepare("SELECT id FROM alumnos WHERE identificador = ? AND id != ?");
                $stmt->execute([$datos['identificador'], $id]);
                if ($stmt->fetch()) {
                    throw new Exception("La matrícula ya existe");
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
            if (isset($datos['edad'])) {
                $campos[] = "edad = ?";
                $valores[] = $datos['edad'];
            }
            if (isset($datos['password']) && !empty($datos['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($datos['password'], PASSWORD_BCRYPT);
            }
            if (isset($datos['foto_perfil'])) {
                $campos[] = "foto_perfil = ?";
                $valores[] = $datos['foto_perfil'];
            }
            if (isset($datos['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = $datos['activo'];
            }
            
            $valores[] = $id;
            
            $stmt = $this->db->prepare("
                UPDATE alumnos 
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
     * Eliminar alumno (soft delete)
     */
    public function eliminar($id) {
        try {
            $this->db->beginTransaction();
            
            // Obtener datos antes de eliminar
            $datosAnteriores = $this->getById($id);
            if (!$datosAnteriores) {
                throw new Exception("Alumno no encontrado");
            }
            
            // Soft delete
            $stmt = $this->db->prepare("UPDATE alumnos SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            // Registrar en auditoría
            $this->registrarAuditoria($id, 'DELETE', $datosAnteriores, ['activo' => 0]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Inscribir alumno a una materia
     */
    public function inscribirMateria($alumnoId, $materiaId) {
        try {
            // Verificar si ya está inscrito
            $stmt = $this->db->prepare("
                SELECT id FROM inscripciones 
                WHERE alumno_id = ? AND materia_id = ?
            ");
            $stmt->execute([$alumnoId, $materiaId]);
            
            if ($stmt->fetch()) {
                throw new Exception("El alumno ya está inscrito en esta materia");
            }
            
            // Inscribir
            $stmt = $this->db->prepare("
                INSERT INTO inscripciones (alumno_id, materia_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$alumnoId, $materiaId]);
            
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Registrar acción en auditoría
     */
    private function registrarAuditoria($alumnoId, $accion, $datosAnteriores, $datosNuevos) {
        $stmt = $this->db->prepare("
            INSERT INTO auditoria 
            (tabla, registro_id, usuario_id, accion, datos_anteriores, datos_nuevos, ip_address)
            VALUES ('alumnos', ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $alumnoId,
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
        
        if ($file['size'] > 5242880) { // 5MB
            throw new Exception("El archivo es demasiado grande (máximo 5MB)");
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('alumno_') . '.' . $extension;
        $uploadPath = __DIR__ . '/../uploads/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        $rutaDestino = $uploadPath . $nombreArchivo;
        
        if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            throw new Exception("Error al subir el archivo");
        }
        
        return $nombreArchivo;
    }
}
