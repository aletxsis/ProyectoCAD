<?php
/**
 * Clase para gestión de materias
 */

class Materia {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todas las materias activas
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT * FROM materias 
            WHERE activa = 1
            ORDER BY nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener materia por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM materias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener alumnos inscritos en una materia
     */
    public function getAlumnosInscritos($materiaId) {
        $stmt = $this->db->prepare("
            SELECT a.*, i.parcial_1, i.parcial_2, i.parcial_3, i.calificacion_final, i.id as inscripcion_id
            FROM inscripciones i
            INNER JOIN alumnos a ON i.alumno_id = a.id
            WHERE i.materia_id = ? AND a.activo = 1
            ORDER BY a.nombre_completo
        ");
        $stmt->execute([$materiaId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener estadísticas de una materia
     */
    public function getEstadisticas($materiaId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_inscritos,
                AVG(calificacion_final) as promedio,
                SUM(CASE WHEN calificacion_final >= 70 THEN 1 ELSE 0 END) as aprobados,
                SUM(CASE WHEN calificacion_final < 70 AND calificacion_final IS NOT NULL THEN 1 ELSE 0 END) as reprobados
            FROM inscripciones
            WHERE materia_id = ?
        ");
        $stmt->execute([$materiaId]);
        return $stmt->fetch();
    }
}
