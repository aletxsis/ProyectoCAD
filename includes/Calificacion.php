<?php
/**
 * Clase para gestión de calificaciones
 */

class Calificacion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Asignar calificación a un alumno en una materia
     */
    public function asignar($alumnoId, $materiaId, $parcial, $calificacion) {
        try {
            // Validar parcial
            if (!in_array($parcial, [1, 2, 3])) {
                throw new Exception("Parcial inválido");
            }
            
            // Validar calificación
            if ($calificacion < 0 || $calificacion > 100) {
                throw new Exception("La calificación debe estar entre 0 y 100");
            }
            
            // Verificar que existe la inscripción
            $stmt = $this->db->prepare("
                SELECT id FROM inscripciones 
                WHERE alumno_id = ? AND materia_id = ?
            ");
            $stmt->execute([$alumnoId, $materiaId]);
            $inscripcion = $stmt->fetch();
            
            if (!$inscripcion) {
                throw new Exception("El alumno no está inscrito en esta materia");
            }
            
            // Actualizar calificación
            $campo = "parcial_" . $parcial;
            $stmt = $this->db->prepare("
                UPDATE inscripciones 
                SET $campo = ?
                WHERE alumno_id = ? AND materia_id = ?
            ");
            
            $stmt->execute([$calificacion, $alumnoId, $materiaId]);
            
            // Registrar en auditoría
            $this->registrarAuditoria($inscripcion['id'], $parcial, $calificacion);
            
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Obtener inscripción de un alumno en una materia
     */
    public function getInscripcion($alumnoId, $materiaId) {
        $stmt = $this->db->prepare("
            SELECT i.*, a.nombre_completo as alumno_nombre, m.nombre as materia_nombre
            FROM inscripciones i
            INNER JOIN alumnos a ON i.alumno_id = a.id
            INNER JOIN materias m ON i.materia_id = m.id
            WHERE i.alumno_id = ? AND i.materia_id = ?
        ");
        $stmt->execute([$alumnoId, $materiaId]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener todas las inscripciones de un alumno
     */
    public function getInscripcionesPorAlumno($alumnoId) {
        $stmt = $this->db->prepare("
            SELECT i.*, m.nombre as materia_nombre, m.identificador as materia_codigo
            FROM inscripciones i
            INNER JOIN materias m ON i.materia_id = m.id
            WHERE i.alumno_id = ?
            ORDER BY m.nombre
        ");
        $stmt->execute([$alumnoId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Registrar en auditoría
     */
    private function registrarAuditoria($inscripcionId, $parcial, $calificacion) {
        $stmt = $this->db->prepare("
            INSERT INTO auditoria 
            (tabla, registro_id, usuario_id, accion, datos_nuevos, ip_address)
            VALUES ('inscripciones', ?, ?, 'UPDATE', ?, ?)
        ");
        
        $datos = [
            'parcial' => $parcial,
            'calificacion' => $calificacion,
            'fecha' => date('Y-m-d H:i:s')
        ];
        
        $stmt->execute([
            $inscripcionId,
            $_SESSION['usuario_id'] ?? null,
            json_encode($datos),
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
