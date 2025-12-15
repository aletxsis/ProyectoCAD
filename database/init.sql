-- Base de datos para Proyecto CAD
-- Sistema de gestión de usuarios

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `proyecto_cad` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `proyecto_cad`;

-- Tabla de tipos de usuario
CREATE TABLE IF NOT EXISTS `tipo_usuario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipos de usuario
INSERT INTO `tipo_usuario` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Directivo', 'Usuario con permisos para gestionar usuarios de tipo gestión'),
(2, 'Gestión', 'Usuario gestionado por directivos'),
(3, 'Operativo', 'Usuario operativo del sistema');

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(50) NOT NULL,
  `nombre_completo` VARCHAR(200) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `foto_perfil` VARCHAR(255) DEFAULT NULL,
  `cargo` VARCHAR(100) NOT NULL,
  `tipo_usuario_id` INT(11) NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`),
  KEY `tipo_usuario_id` (`tipo_usuario_id`),
  CONSTRAINT `fk_tipo_usuario` FOREIGN KEY (`tipo_usuario_id`) REFERENCES `tipo_usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de auditoría para cambios en usuarios
CREATE TABLE IF NOT EXISTS `auditoria_usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` INT(11) NOT NULL,
  `usuario_modificador_id` INT(11) DEFAULT NULL,
  `accion` ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
  `datos_anteriores` JSON,
  `datos_nuevos` JSON,
  `fecha_accion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `usuario_modificador_id` (`usuario_modificador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario directivo por defecto
-- Contraseña: admin123
INSERT INTO `usuarios` (`identificador`, `nombre_completo`, `password`, `foto_perfil`, `cargo`, `tipo_usuario_id`) VALUES
('admin', 'Administrador del Sistema', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'Director General', 1);

COMMIT;
