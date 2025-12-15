<?php
/**
 * Script de verificación del sistema
 * Acceder a: http://localhost:8080/verificar.php
 */

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificación del Sistema - Proyecto CAD</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .check { margin: 15px 0; padding: 10px; border-radius: 5px; }
        .ok { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .icon { font-weight: bold; margin-right: 10px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Verificación del Sistema</h1>
        <p><strong>Proyecto CAD - Sistema de Gestión de Usuarios</strong></p>
        <hr>
";

// Verificar versión de PHP
echo "<h2>Entorno PHP</h2>";
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.0.0', '>=');
echo "<div class='check " . ($phpOk ? "ok" : "error") . "'>
    <span class='icon'>" . ($phpOk ? "✅" : "❌") . "</span>
    <strong>Versión PHP:</strong> $phpVersion " . ($phpOk ? "(Compatible)" : "(Se requiere PHP 8.0 o superior)") . "
</div>";

// Verificar extensiones
$extensiones = ['pdo', 'pdo_mysql', 'mysqli', 'gd', 'mbstring', 'json'];
echo "<h2>Extensiones PHP</h2>";
foreach ($extensiones as $ext) {
    $loaded = extension_loaded($ext);
    echo "<div class='check " . ($loaded ? "ok" : "error") . "'>
        <span class='icon'>" . ($loaded ? "✅" : "❌") . "</span>
        <strong>$ext:</strong> " . ($loaded ? "Instalada" : "No instalada") . "
    </div>";
}

// Verificar archivos de configuración
echo "<h2>Archivos de Configuración</h2>";
$archivos = [
    'config/config.php' => 'Configuración general',
    'config/database.php' => 'Configuración de base de datos',
    'includes/Database.php' => 'Clase Database',
    'includes/Auth.php' => 'Clase Auth',
    'includes/Usuario.php' => 'Clase Usuario'
];

foreach ($archivos as $ruta => $desc) {
    $exists = file_exists(__DIR__ . '/' . $ruta);
    echo "<div class='check " . ($exists ? "ok" : "error") . "'>
        <span class='icon'>" . ($exists ? "✅" : "❌") . "</span>
        <strong>$desc:</strong> " . ($exists ? "Encontrado" : "No encontrado") . " ($ruta)
    </div>";
}

// Verificar permisos de carpeta uploads
echo "<h2>Permisos</h2>";
$uploadsPath = __DIR__ . '/uploads';
$uploadsWritable = is_writable($uploadsPath);
echo "<div class='check " . ($uploadsWritable ? "ok" : "warning") . "'>
    <span class='icon'>" . ($uploadsWritable ? "✅" : "⚠️") . "</span>
    <strong>Carpeta uploads:</strong> " . ($uploadsWritable ? "Escribible" : "Sin permisos de escritura") . "
</div>";

// Intentar conexión a base de datos
echo "<h2>Conexión a Base de Datos</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/Database.php';
    
    $db = Database::getInstance()->getConnection();
    echo "<div class='check ok'>
        <span class='icon'>✅</span>
        <strong>Conexión MySQL:</strong> Exitosa
    </div>";
    
    // Verificar tablas
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $tablasRequeridas = ['tipo_usuario', 'usuarios', 'auditoria_usuarios'];
    foreach ($tablasRequeridas as $tabla) {
        $existe = in_array($tabla, $tables);
        echo "<div class='check " . ($existe ? "ok" : "error") . "'>
            <span class='icon'>" . ($existe ? "✅" : "❌") . "</span>
            <strong>Tabla $tabla:</strong> " . ($existe ? "Existe" : "No encontrada") . "
        </div>";
    }
    
    // Verificar usuario admin
    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE identificador = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn() > 0;
    
    echo "<div class='check " . ($adminExists ? "ok" : "warning") . "'>
        <span class='icon'>" . ($adminExists ? "✅" : "⚠️") . "</span>
        <strong>Usuario admin:</strong> " . ($adminExists ? "Encontrado" : "No encontrado") . "
    </div>";
    
} catch (Exception $e) {
    echo "<div class='check error'>
        <span class='icon'>❌</span>
        <strong>Error de conexión:</strong> " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "
        <hr>
        <h2>📋 Resumen</h2>
        <p>Si todos los checks están en verde ✅, el sistema está listo para usar.</p>
        <p><a href='/public/login.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Ir al Login</a></p>
        
        <hr>
        <p style='color: #666; font-size: 12px;'>
            <strong>Nota:</strong> Este archivo debe eliminarse en producción por seguridad.
        </p>
    </div>
</body>
</html>";
