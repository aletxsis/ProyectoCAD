<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::requireAlumno();
$currentUser = Auth::getCurrentUser();

// Obtener materias inscritas con calificaciones
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT m.nombre, i.parcial_1, i.parcial_2, i.parcial_3, i.calificacion_final
    FROM inscripciones i
    INNER JOIN materias m ON i.materia_id = m.id
    WHERE i.alumno_id = ?
    ORDER BY m.nombre
");
$stmt->execute([$currentUser['id']]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular promedio general
$promedioGeneral = 0;
$materiasConFinal = 0;
foreach ($materias as $materia) {
    if ($materia['calificacion_final'] !== null) {
        $promedioGeneral += $materia['calificacion_final'];
        $materiasConFinal++;
    }
}
if ($materiasConFinal > 0) {
    $promedioGeneral = $promedioGeneral / $materiasConFinal;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Alumno - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>Mi Panel - Alumno</h1>
            <p class="welcome">Bienvenido, <strong><?php echo htmlspecialchars($currentUser['nombre_completo']); ?></strong></p>
            <p class="user-info">Matrícula: <?php echo htmlspecialchars($currentUser['identificador']); ?> | Edad: <?php echo $currentUser['edad']; ?> años</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo count($materias); ?></h3>
                    <p>Materias Inscritas</p>
                </div>
                <div class="stat-card <?php echo $promedioGeneral >= 70 ? 'success' : 'danger'; ?>">
                    <h3><?php echo number_format($promedioGeneral, 2); ?></h3>
                    <p>Promedio General</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $materiasConFinal; ?></h3>
                    <p>Materias Calificadas</p>
                </div>
            </div>
            
            <div class="boleta">
                <h2>📋 Boleta de Calificaciones</h2>
                
                <?php if (empty($materias)): ?>
                    <p class="no-data">No tienes materias inscritas aún.</p>
                <?php else: ?>
                    <table class="tabla-calificaciones">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Parcial 1</th>
                                <th>Parcial 2</th>
                                <th>Parcial 3</th>
                                <th>Calificación Final</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias as $materia): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($materia['nombre']); ?></td>
                                    <td><?php echo $materia['parcial_1'] !== null ? number_format($materia['parcial_1'], 2) : '-'; ?></td>
                                    <td><?php echo $materia['parcial_2'] !== null ? number_format($materia['parcial_2'], 2) : '-'; ?></td>
                                    <td><?php echo $materia['parcial_3'] !== null ? number_format($materia['parcial_3'], 2) : '-'; ?></td>
                                    <td class="final-grade">
                                        <?php 
                                        if ($materia['calificacion_final'] !== null) {
                                            echo '<strong>' . number_format($materia['calificacion_final'], 2) . '</strong>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($materia['calificacion_final'] !== null) {
                                            if ($materia['calificacion_final'] >= 70) {
                                                echo '<span class="badge badge-success">Aprobado</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Reprobado</span>';
                                            }
                                        } else {
                                            echo '<span class="badge badge-secondary">Pendiente</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
