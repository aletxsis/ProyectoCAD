<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Materia.php';

Auth::requireGestion();

$materia = new Materia();
$materias = $materia->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>📚 Catálogo de Materias</h1>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre de la Materia</th>
                            <th>Créditos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($materias)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px;">
                                    No hay materias registradas
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materias as $m): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($m['identificador']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($m['nombre']); ?></td>
                                    <td><?php echo $m['creditos']; ?> créditos</td>
                                    <td>
                                        <span class="badge <?php echo $m['activa'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $m['activa'] ? 'Activa' : 'Inactiva'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="ver_alumnos.php?materia_id=<?php echo $m['id']; ?>" class="btn btn-info btn-sm">
                                            👥 Ver Alumnos Inscritos
                                        </a>
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
