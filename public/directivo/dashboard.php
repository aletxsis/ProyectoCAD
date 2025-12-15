<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requireDirectivo();
$currentUser = Auth::getCurrentUser();

// Obtener estadísticas
$db = Database::getInstance()->getConnection();

// Contar usuarios de gestión
$stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE tipo_usuario_id = 2 AND activo = 1");
$totalGestion = $stmt->fetchColumn();

// Contar alumnos
$stmt = $db->query("SELECT COUNT(*) FROM alumnos WHERE activo = 1");
$totalAlumnos = $stmt->fetchColumn();

// Contar materias
$stmt = $db->query("SELECT COUNT(*) FROM materias WHERE activa = 1");
$totalMaterias = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Directivo - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>Panel de Control - Directivo</h1>
            <p class="welcome">Bienvenido, <strong><?php echo htmlspecialchars($currentUser['nombre_completo']); ?></strong></p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $totalGestion; ?></h3>
                    <p>Usuarios de Gestión</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $totalAlumnos; ?></h3>
                    <p>Alumnos Registrados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $totalMaterias; ?></h3>
                    <p>Materias Activas</p>
                </div>
            </div>
            
            <div class="actions">
                <h2>Gestión de Usuarios</h2>
                <div class="action-buttons">
                    <a href="/usuarios/listar.php" class="btn btn-primary">
                        📋 Ver Usuarios de Gestión
                    </a>
                    <a href="/usuarios/crear.php" class="btn btn-success">
                        ➕ Crear Usuario de Gestión
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
