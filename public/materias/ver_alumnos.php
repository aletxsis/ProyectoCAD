<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Materia.php';

Auth::requireGestion();

$materiaObj = new Materia();
$materiaId = $_GET['materia_id'] ?? null;

if (!$materiaId) {
    header('Location: listar.php');
    exit;
}

$materia = $materiaObj->getById($materiaId);
if (!$materia) {
    header('Location: listar.php?error=Materia no encontrada');
    exit;
}

$alumnos = $materiaObj->getAlumnosInscritos($materiaId);
$estadisticas = $materiaObj->getEstadisticas($materiaId);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos Inscritos - <?php echo htmlspecialchars($materia['nombre']); ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>👥 Alumnos Inscritos</h1>
            <h2><?php echo htmlspecialchars($materia['nombre']); ?> (<?php echo htmlspecialchars($materia['identificador']); ?>)</h2>
            
            <div class="stats-grid" style="margin: 20px 0;">
                <div class="stat-card">
                    <h3><?php echo $estadisticas['total_inscritos']; ?></h3>
                    <p>Total Inscritos</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $estadisticas['promedio'] ? number_format($estadisticas['promedio'], 2) : '-'; ?></h3>
                    <p>Promedio General</p>
                </div>
                <div class="stat-card success">
                    <h3><?php echo $estadisticas['aprobados']; ?></h3>
                    <p>Aprobados</p>
                </div>
                <div class="stat-card danger">
                    <h3><?php echo $estadisticas['reprobados']; ?></h3>
                    <p>Reprobados</p>
                </div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Nombre del Alumno</th>
                            <th>Parcial 1</th>
                            <th>Parcial 2</th>
                            <th>Parcial 3</th>
                            <th>Calificación Final</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alumnos)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    No hay alumnos inscritos en esta materia
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alumnos as $a): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($a['identificador']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($a['nombre_completo']); ?></td>
                                    <td><?php echo $a['parcial_1'] !== null ? number_format($a['parcial_1'], 2) : '-'; ?></td>
                                    <td><?php echo $a['parcial_2'] !== null ? number_format($a['parcial_2'], 2) : '-'; ?></td>
                                    <td><?php echo $a['parcial_3'] !== null ? number_format($a['parcial_3'], 2) : '-'; ?></td>
                                    <td>
                                        <?php if ($a['calificacion_final'] !== null): ?>
                                            <strong><?php echo number_format($a['calificacion_final'], 2); ?></strong>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($a['calificacion_final'] !== null) {
                                            if ($a['calificacion_final'] >= 70) {
                                                echo '<span class="badge badge-success">Aprobado</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Reprobado</span>';
                                            }
                                        } else {
                                            echo '<span class="badge badge-secondary">Pendiente</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="/calificaciones/asignar.php?alumno_id=<?php echo $a['id']; ?>&materia_id=<?php echo $materiaId; ?>" 
                                           class="btn btn-warning btn-sm">
                                            📝 Calificar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="listar.php" class="btn btn-primary">← Volver a Materias</a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
