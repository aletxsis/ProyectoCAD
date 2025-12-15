<?php
/**
 * Configuración de Base de Datos
 * Copiar este archivo como database.php y configurar las credenciales
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'proyecto_cad');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Configuración para Azure MySQL
 * Si se usa Azure Database for MySQL, descomentar y configurar:
 */
// define('DB_HOST', getenv('AZURE_MYSQL_HOST'));
// define('DB_USER', getenv('AZURE_MYSQL_USER'));
// define('DB_PASS', getenv('AZURE_MYSQL_PASSWORD'));
// define('DB_NAME', getenv('AZURE_MYSQL_DATABASE'));
// define('DB_SSL_CA', '/path/to/DigiCertGlobalRootCA.crt.pem');
