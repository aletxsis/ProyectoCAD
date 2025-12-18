<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';

// Permitir acceso a Directivo y Gestión
Auth::requireLogin();
if (!Auth::isDirectivo() && !Auth::isGestion()) {
    header('Location: /index.php?error=no_permission');
    exit;
}

$db = Database::getInstance()->getConnection();

// Estadísticas Generales
$stmt = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario_id = 2 AND activo = 1) as total_gestion,
        (SELECT COUNT(*) FROM alumnos WHERE activo = 1) as total_alumnos,
        (SELECT COUNT(*) FROM materias WHERE activa = 1) as total_materias,
        (SELECT COUNT(*) FROM inscripciones) as total_inscripciones
");
$estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);

// Promedio General del Sistema
$stmt = $db->query("
    SELECT AVG(calificacion_final) as promedio_sistema
    FROM inscripciones
    WHERE calificacion_final IS NOT NULL
");
$promedioSistema = $stmt->fetch(PDO::FETCH_ASSOC);

// Estadísticas por Materia
$stmt = $db->query("
    SELECT 
        m.identificador,
        m.nombre,
        COUNT(i.id) as total_inscritos,
        AVG(i.calificacion_final) as promedio,
        SUM(CASE WHEN i.calificacion_final >= 70 THEN 1 ELSE 0 END) as aprobados,
        SUM(CASE WHEN i.calificacion_final < 70 AND i.calificacion_final IS NOT NULL THEN 1 ELSE 0 END) as reprobados,
        SUM(CASE WHEN i.calificacion_final IS NULL THEN 1 ELSE 0 END) as sin_calificar
    FROM materias m
    LEFT JOIN inscripciones i ON m.id = i.materia_id
    WHERE m.activa = 1
    GROUP BY m.id, m.identificador, m.nombre
    ORDER BY promedio DESC
");
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top 10 Mejores Alumnos
$stmt = $db->query("
    SELECT 
        a.identificador,
        a.nombre_completo,
        AVG(i.calificacion_final) as promedio,
        COUNT(i.id) as materias_cursadas
    FROM alumnos a
    INNER JOIN inscripciones i ON a.id = i.alumno_id
    WHERE i.calificacion_final IS NOT NULL AND a.activo = 1
    GROUP BY a.id, a.identificador, a.nombre_completo
    HAVING COUNT(i.id) >= 2
    ORDER BY promedio DESC
    LIMIT 10
");
$mejoresAlumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alumnos en Riesgo (promedio < 70)
$stmt = $db->query("
    SELECT 
        a.identificador,
        a.nombre_completo,
        AVG(i.calificacion_final) as promedio,
        COUNT(CASE WHEN i.calificacion_final < 70 THEN 1 END) as materias_reprobadas
    FROM alumnos a
    INNER JOIN inscripciones i ON a.id = i.alumno_id
    WHERE i.calificacion_final IS NOT NULL AND a.activo = 1
    GROUP BY a.id, a.identificador, a.nombre_completo
    HAVING AVG(i.calificacion_final) < 70
    ORDER BY promedio ASC
    LIMIT 10
");
$alumnosRiesgo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Índices de Aprobación/Reprobación
$stmt = $db->query("
    SELECT 
        COUNT(CASE WHEN calificacion_final >= 70 THEN 1 END) as total_aprobados,
        COUNT(CASE WHEN calificacion_final < 70 AND calificacion_final IS NOT NULL THEN 1 END) as total_reprobados,
        COUNT(CASE WHEN calificacion_final IS NULL THEN 1 END) as sin_calificar,
        COUNT(*) as total
    FROM inscripciones
");
$indices = $stmt->fetch(PDO::FETCH_ASSOC);

$porcentajeAprobacion = $indices['total'] > 0 ? ($indices['total_aprobados'] / $indices['total']) * 100 : 0;
$porcentajeReprobacion = $indices['total'] > 0 ? ($indices['total_reprobados'] / $indices['total']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .progress-success {
            background: linear-gradient(90deg, #28a745, #20c997);
        }
        .progress-danger {
            background: linear-gradient(90deg, #dc3545, #fd7e14);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>📊 Reportes y Estadísticas</h1>
            <p style="color: #666; margin-bottom: 30px;">Análisis general del sistema académico</p>
            
            <!-- Estadísticas Generales -->
            <h2>📈 Estadísticas Generales</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $estadisticas['total_gestion']; ?></h3>
                    <p>Usuarios de Gestión</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $estadisticas['total_alumnos']; ?></h3>
                    <p>Alumnos Activos</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $estadisticas['total_materias']; ?></h3>
                    <p>Materias Activas</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $estadisticas['total_inscripciones']; ?></h3>
                    <p>Inscripciones Totales</p>
                </div>
            </div>
            
            <!-- Promedio del Sistema -->
            <div class="chart-container">
                <h3>🎯 Promedio General del Sistema</h3>
                <div style="text-align: center; margin: 20px 0;">
                    <div style="font-size: 72px; font-weight: bold; color: <?php echo ($promedioSistema['promedio_sistema'] >= 70) ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo number_format($promedioSistema['promedio_sistema'] ?? 0, 2); ?>
                    </div>
                    <p style="color: #666;">Promedio de todas las calificaciones finales</p>
                </div>
            </div>
            
            <!-- Índices de Aprobación -->
            <div class="chart-container">
                <h3>✅ Índices de Aprobación y Reprobación</h3>
                
                <div style="margin: 20px 0;">
                    <p><strong>Aprobados:</strong> <?php echo $indices['total_aprobados']; ?> estudiantes (<?php echo number_format($porcentajeAprobacion, 2); ?>%)</p>
                    <div class="progress-bar">
                        <div class="progress-fill progress-success" style="width: <?php echo $porcentajeAprobacion; ?>%;">
                            <?php echo number_format($porcentajeAprobacion, 1); ?>%
                        </div>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <p><strong>Reprobados:</strong> <?php echo $indices['total_reprobados']; ?> estudiantes (<?php echo number_format($porcentajeReprobacion, 2); ?>%)</p>
                    <div class="progress-bar">
                        <div class="progress-fill progress-danger" style="width: <?php echo $porcentajeReprobacion; ?>%;">
                            <?php echo number_format($porcentajeReprobacion, 1); ?>%
                        </div>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <p><strong>Sin Calificar:</strong> <?php echo $indices['sin_calificar']; ?> inscripciones</p>
                </div>
            </div>
            
            <!-- Estadísticas por Materia -->
            <div class="chart-container">
                <h3>📚 Rendimiento por Materia</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Materia</th>
                                <th>Inscritos</th>
                                <th>Promedio</th>
                                <th>Aprobados</th>
                                <th>Reprobados</th>
                                <th>Sin Calificar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias as $m): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['identificador']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                                    <td><?php echo $m['total_inscritos']; ?></td>
                                    <td>
                                        <span style="color: <?php echo ($m['promedio'] >= 70) ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                            <?php echo $m['promedio'] ? number_format($m['promedio'], 2) : '-'; ?>
                                        </span>
                                    </td>
                                    <td><span class="badge badge-success"><?php echo $m['aprobados']; ?></span></td>
                                    <td><span class="badge badge-danger"><?php echo $m['reprobados']; ?></span></td>
                                    <td><span class="badge badge-secondary"><?php echo $m['sin_calificar']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Top 10 Mejores Alumnos -->
            <div class="chart-container">
                <h3>🏆 Top 10 Mejores Alumnos</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Matrícula</th>
                                <th>Nombre</th>
                                <th>Promedio</th>
                                <th>Materias Cursadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mejoresAlumnos)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">
                                        No hay suficientes datos para mostrar el ranking
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mejoresAlumnos as $index => $alumno): ?>
                                    <tr>
                                        <td><strong><?php echo $index + 1; ?></strong></td>
                                        <td><?php echo htmlspecialchars($alumno['identificador']); ?></td>
                                        <td><?php echo htmlspecialchars($alumno['nombre_completo']); ?></td>
                                        <td>
                                            <span style="color: #28a745; font-weight: bold; font-size: 16px;">
                                                <?php echo number_format($alumno['promedio'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $alumno['materias_cursadas']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Alumnos en Riesgo -->
            <?php if (!empty($alumnosRiesgo)): ?>
                <div class="chart-container" style="border-left: 4px solid #dc3545;">
                    <h3>⚠️ Alumnos en Riesgo Académico</h3>
                    <p style="color: #666; margin-bottom: 15px;">Alumnos con promedio menor a 70</p>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Nombre</th>
                                    <th>Promedio</th>
                                    <th>Materias Reprobadas</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnosRiesgo as $alumno): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($alumno['identificador']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($alumno['nombre_completo']); ?></td>
                                        <td>
                                            <span style="color: #dc3545; font-weight: bold;">
                                                <?php echo number_format($alumno['promedio'], 2); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $alumno['materias_reprobadas']; ?></td>
                                        <td><span class="badge badge-danger">En Riesgo</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 30px;">
                <a href="<?php echo Auth::isDirectivo() ? '/directivo/dashboard.php' : '/gestion/dashboard.php'; ?>" class="btn btn-primary">
                    ← Volver al Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
