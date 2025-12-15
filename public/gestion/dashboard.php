<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requireGestion();
$currentUser = Auth::getCurrentUser();

// Obtener estadísticas
$db = Database::getInstance()->getConnection();

// Contar alumnos activos
$stmt = $db->query("SELECT COUNT(*) FROM alumnos WHERE activo = 1");
$totalAlumnos = $stmt->fetchColumn();

// Contar materias activas
$stmt = $db->query("SELECT COUNT(*) FROM materias WHERE activa = 1");
$totalMaterias = $stmt->fetchColumn();

// Contar inscripciones totales
$stmt = $db->query("SELECT COUNT(*) FROM inscripciones");
$totalInscripciones = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Gestión - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>Panel de Control - Gestión</h1>
            <p class="welcome">Bienvenido, <strong><?php echo htmlspecialchars($currentUser['nombre_completo']); ?></strong></p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $totalAlumnos; ?></h3>
                    <p>Alumnos Registrados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $totalMaterias; ?></h3>
                    <p>Materias Activas</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $totalInscripciones; ?></h3>
                    <p>Inscripciones Totales</p>
                </div>
            </div>
            
            <div class="actions">
                <h2>Gestión de Alumnos</h2>
                <div class="action-buttons">
                    <a href="/alumnos/listar.php" class="btn btn-primary">
                        📋 Ver Alumnos
                    </a>
                    <a href="/alumnos/crear.php" class="btn btn-success">
                        ➕ Inscribir Nuevo Alumno
                    </a>
                </div>
                
                <h2>Gestión de Materias y Calificaciones</h2>
                <div class="action-buttons">
                    <a href="/materias/listar.php" class="btn btn-info">
                        📚 Ver Materias
                    </a>
                    <a href="/calificaciones/asignar.php" class="btn btn-warning">
                        📝 Asignar Calificaciones
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
