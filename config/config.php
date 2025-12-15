<?php
/**
 * Configuración General de la Aplicación
 */

session_start();

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// URLs base
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost:8080');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Seguridad
define('SESSION_TIMEOUT', 3600); // 1 hora
define('HASH_ALGO', PASSWORD_BCRYPT);

// Cargar configuración de base de datos
require_once __DIR__ . '/database.php';
