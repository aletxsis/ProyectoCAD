<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';

Auth::requireLogin();
$currentUser = Auth::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Proyecto CAD</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1>Bienvenido, <?php echo htmlspecialchars($currentUser['nombre_completo']); ?></h1>
            
            <div class="user-info">
                <?php if ($currentUser['foto_perfil']): ?>
                    <img src="/uploads/<?php echo htmlspecialchars($currentUser['foto_perfil']); ?>" 
                         alt="Foto de perfil" class="profile-pic">
                <?php endif; ?>
                
                <div class="info-details">
                    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($currentUser['cargo']); ?></p>
                    <p><strong>Tipo de usuario:</strong> <?php echo htmlspecialchars($currentUser['tipo_usuario_nombre']); ?></p>
                </div>
            </div>
            
            <?php if (Auth::isDirectivo()): ?>
                <div class="actions">
                    <h2>Panel de Gestión</h2>
                    <div class="action-buttons">
                        <a href="usuarios/listar.php" class="btn btn-primary">
                            Ver Usuarios de Gestión
                        </a>
                        <a href="usuarios/crear.php" class="btn btn-success">
                            Crear Nuevo Usuario
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
