<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Usuario.php';

Auth::requireDirectivo();

$usuario = new Usuario();
$usuarios = $usuario->getUsuariosGestion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios de Gestión - Proyecto CAD</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h1>Usuarios de Gestión</h1>
                <a href="crear.php" class="btn btn-success">+ Crear Usuario</a>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    switch($_GET['success']) {
                        case 'created':
                            echo 'Usuario creado exitosamente';
                            break;
                        case 'updated':
                            echo 'Usuario actualizado exitosamente';
                            break;
                        case 'deleted':
                            echo 'Usuario eliminado exitosamente';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    Error: <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Identificador</th>
                            <th>Nombre Completo</th>
                            <th>Cargo</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px;">
                                    No hay usuarios de gestión registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td>
                                        <?php if ($u['foto_perfil']): ?>
                                            <img src="/uploads/<?php echo htmlspecialchars($u['foto_perfil']); ?>" 
                                                 alt="Foto" class="user-photo">
                                        <?php else: ?>
                                            <div class="user-photo" style="background: #ddd; display: flex; align-items: center; justify-content: center;">
                                                👤
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['identificador']); ?></td>
                                    <td><?php echo htmlspecialchars($u['nombre_completo']); ?></td>
                                    <td><?php echo htmlspecialchars($u['cargo']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $u['activo'] ? 'Activo' : 'Inactivo'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($u['fecha_creacion'])); ?></td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="editar.php?id=<?php echo $u['id']; ?>" class="btn btn-warning btn-sm">
                                                ✏️ Editar
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $u['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
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
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
