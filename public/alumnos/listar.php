<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Alumno.php';

// Solo usuarios de Gestión pueden acceder
Auth::requireGestion();

$alumno = new Alumno();
$alumnos = $alumno->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumnos - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1>📋 Gestión de Alumnos</h1>
                <a href="crear.php" class="btn btn-success">➕ Inscribir Nuevo Alumno</a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch($_GET['success']) {
                        case 'created':
                            echo '✅ Alumno inscrito exitosamente';
                            break;
                        case 'updated':
                            echo '✅ Alumno actualizado exitosamente';
                            break;
                        case 'deleted':
                            echo '✅ Alumno dado de baja exitosamente';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    ❌ Error: <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Matrícula</th>
                            <th>Nombre Completo</th>
                            <th>Edad</th>
                            <th>Estado</th>
                            <th>Fecha Inscripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($alumnos)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No hay alumnos registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($alumnos as $a): ?>
                                <tr>
                                    <td>
                                        <?php if ($a['foto_perfil']): ?>
                                            <img src="/uploads/<?php echo htmlspecialchars($a['foto_perfil']); ?>" 
                                                 alt="Foto" class="user-photo">
                                        <?php else: ?>
                                            <div class="user-photo" style="background: #ddd; display: flex; align-items: center; justify-content: center;">
                                                🎓
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($a['identificador']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($a['nombre_completo']); ?></td>
                                    <td><?php echo $a['edad']; ?> años</td>
                                    <td>
                                        <span class="badge <?php echo $a['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $a['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($a['fecha_creacion'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="editar.php?id=<?php echo $a['id']; ?>" class="btn btn-warning btn-sm">
                                                ✏️ Editar
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $a['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de dar de baja a este alumno?')">
                                                🗑️ Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="/gestion/dashboard.php" class="btn btn-primary">← Volver al Dashboard</a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
