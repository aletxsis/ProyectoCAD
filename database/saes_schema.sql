-- Base de datos para Proyecto CAD (SAES 2.0)
-- Sistema de gestión de calificaciones de estudiantes

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS `proyecto_cad`;
CREATE DATABASE `proyecto_cad` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `proyecto_cad`;

-- ==================================================
-- TABLAS DEL SISTEMA
-- ==================================================

-- Tabla de tipos de usuario
CREATE TABLE `tipo_usuario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de usuario
INSERT INTO `tipo_usuario` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Directivo', 'Administrador que gestiona usuarios de tipo Gestión'),
(2, 'Gestión', 'Puede inscribir alumnos y asignar materias/calificaciones'),
(3, 'Alumno', 'Estudiante que ve sus materias y calificaciones');

-- Tabla de usuarios (Directivo y Gestión)
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(50) NOT NULL,
  `nombre_completo` VARCHAR(200) NOT NULL,
  `correo` VARCHAR(150) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `foto_perfil` TEXT DEFAULT NULL,
  `cargo` VARCHAR(100) DEFAULT NULL,
  `tipo_usuario_id` INT(11) NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`),
  KEY `tipo_usuario_id` (`tipo_usuario_id`),
  CONSTRAINT `fk_usuarios_tipo` FOREIGN KEY (`tipo_usuario_id`) REFERENCES `tipo_usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de alumnos
CREATE TABLE `alumnos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(50) NOT NULL,
  `nombre_completo` VARCHAR(200) NOT NULL,
  `edad` INT(3) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `foto_perfil` TEXT DEFAULT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de materias
CREATE TABLE `materias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(20) NOT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `creditos` INT(2) DEFAULT 6,
  `activa` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de inscripciones (alumnos inscritos a materias)
CREATE TABLE `inscripciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` INT(11) NOT NULL,
  `materia_id` INT(11) NOT NULL,
  `parcial_1` DECIMAL(5,2) DEFAULT NULL,
  `parcial_2` DECIMAL(5,2) DEFAULT NULL,
  `parcial_3` DECIMAL(5,2) DEFAULT NULL,
  `calificacion_final` DECIMAL(5,2) DEFAULT NULL,
  `fecha_inscripcion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_materia` (`alumno_id`, `materia_id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `materia_id` (`materia_id`),
  CONSTRAINT `fk_inscripcion_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inscripcion_materia` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trigger para calcular calificación final automáticamente
DELIMITER //
CREATE TRIGGER `calcular_final_insert` BEFORE INSERT ON `inscripciones`
FOR EACH ROW
BEGIN
  IF NEW.parcial_1 IS NOT NULL AND NEW.parcial_2 IS NOT NULL AND NEW.parcial_3 IS NOT NULL THEN
    SET NEW.calificacion_final = (NEW.parcial_1 + NEW.parcial_2 + NEW.parcial_3) / 3;
  END IF;
END//

CREATE TRIGGER `calcular_final_update` BEFORE UPDATE ON `inscripciones`
FOR EACH ROW
BEGIN
  IF NEW.parcial_1 IS NOT NULL AND NEW.parcial_2 IS NOT NULL AND NEW.parcial_3 IS NOT NULL THEN
    SET NEW.calificacion_final = (NEW.parcial_1 + NEW.parcial_2 + NEW.parcial_3) / 3;
  END IF;
END//
DELIMITER ;

-- Tabla de auditoría
CREATE TABLE `auditoria` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tabla` VARCHAR(50) NOT NULL,
  `registro_id` INT(11) NOT NULL,
  `usuario_id` INT(11) DEFAULT NULL,
  `accion` ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
  `datos_anteriores` JSON,
  `datos_nuevos` JSON,
  `fecha_accion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45),
  PRIMARY KEY (`id`),
  KEY `tabla` (`tabla`),
  KEY `registro_id` (`registro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- DATOS DE PRUEBA
-- ==================================================
-- Contraseña para todos: Admin123!

-- Usuario Directivo
INSERT INTO `usuarios` (`identificador`, `nombre_completo`, `correo`, `password`, `cargo`, `tipo_usuario_id`) VALUES
('admin', 'Carlos Rodríguez Martínez', 'admin@saes.mx', '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi', 'Director General', 1);

-- Usuarios de Gestión
INSERT INTO `usuarios` (`identificador`, `nombre_completo`, `correo`, `password`, `tipo_usuario_id`) VALUES
('gestion1', 'María Elena Torres Ramírez', 'mtorres@saes.mx', '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi', 2),
('gestion2', 'Juan Carlos Mendoza Silva', 'jmendoza@saes.mx', '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi', 2),
('gestion3', 'Patricia Hernández Cruz', 'phernandez@saes.mx', '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi', 2);

-- Alumnos
INSERT INTO `alumnos` (`identificador`, `nombre_completo`, `edad`, `password`) VALUES
('2021630001', 'Diego Alejandro Castro Ruiz', 20, '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi'),
('2021630002', 'Sofía Gabriela Ortiz Medina', 19, '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi'),
('2021630003', 'Miguel Ángel Vargas López', 21, '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi'),
('2021630004', 'Daniela Isabel Ramos Gutiérrez', 20, '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi'),
('2021630005', 'Fernando José Jiménez Navarro', 22, '$2y$10$WULvXWTRRBmLlAvzAw7f1OnN/gtskLBAyBfj.8buyLGMXwx919loi');

-- Materias
INSERT INTO `materias` (`identificador`, `nombre`, `creditos`) VALUES
('MAT001', 'Cálculo Diferencial e Integral', 8),
('MAT002', 'Álgebra Lineal', 6),
('PROG001', 'Programación Orientada a Objetos', 8),
('PROG002', 'Estructuras de Datos', 8),
('CLOUD001', 'Cómputo en la Nube', 6),
('DB001', 'Bases de Datos', 6),
('WEB001', 'Desarrollo Web', 6),
('NET001', 'Redes de Computadoras', 6);

-- Inscripciones con calificaciones de ejemplo
INSERT INTO `inscripciones` (`alumno_id`, `materia_id`, `parcial_1`, `parcial_2`, `parcial_3`) VALUES
(1, 1, 85.00, 90.00, 88.00),
(1, 3, 92.00, 88.00, 95.00),
(1, 5, 78.00, 82.00, 85.00),
(2, 1, 90.00, 92.00, 91.00),
(2, 2, 88.00, 85.00, 87.00),
(2, 4, 95.00, 93.00, 94.00),
(3, 3, 70.00, 75.00, 78.00),
(3, 5, 88.00, 90.00, 92.00),
(3, 6, 82.00, 85.00, 83.00),
(4, 2, 92.00, 90.00, 91.00),
(4, 4, 85.00, 88.00, 86.00),
(4, 7, 90.00, 92.00, 91.00),
(5, 1, 75.00, 78.00, 80.00),
(5, 6, 88.00, 90.00, 89.00),
(5, 8, 92.00, 94.00, 93.00);

COMMIT;
