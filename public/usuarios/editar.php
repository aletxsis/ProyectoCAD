<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Usuario.php';

Auth::requireDirectivo();

$id = $_GET['id'] ?? 0;
$usuarioObj = new Usuario();
$error = '';

// Obtener datos del usuario
$usuarioData = $usuarioObj->getById($id);

if (!$usuarioData || $usuarioData['tipo_usuario_id'] != 2) {
    header('Location: listar.php?error=not_found');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = [
            'identificador' => $_POST['identificador'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'cargo' => $_POST['cargo'] ?? '',
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($_POST['password'])) {
            $datos['password'] = $_POST['password'];
        }
        
        // Manejar subida de foto
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $datos['foto_perfil'] = $usuarioObj->subirFotoPerfil($_FILES['foto_perfil']);
        }
        
        $usuarioObj->actualizar($id, $datos);
        header('Location: listar.php?success=updated');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Proyecto CAD</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>Editar Usuario de Gestión</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="identificador">Identificador *</label>
                    <input type="text" id="identificador" name="identificador" required
                           value="<?php echo htmlspecialchars($usuarioData['identificador']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required
                           value="<?php echo htmlspecialchars($usuarioData['nombre_completo']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" minlength="6">
                    <small>Dejar en blanco para mantener la contraseña actual</small>
                </div>
                
                <div class="form-group">
                    <label for="cargo">Cargo *</label>
                    <input type="text" id="cargo" name="cargo" required
                           value="<?php echo htmlspecialchars($usuarioData['cargo']); ?>">
                </div>
                
                <div class="form-group">
                    <?php if ($usuarioData['foto_perfil']): ?>
                        <div style="margin-bottom: 10px;">
                            <label>Foto actual:</label><br>
                            <img src="/uploads/<?php echo htmlspecialchars($usuarioData['foto_perfil']); ?>" 
                                 alt="Foto actual" style="max-width: 150px; border-radius: 10px;">
                        </div>
                    <?php endif; ?>
                    
                    <label for="foto_perfil">Nueva Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                    <small>Dejar en blanco para mantener la foto actual</small>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="activo" <?php echo $usuarioData['activo'] ? 'checked' : ''; ?>>
                        Usuario Activo
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-warning">Actualizar Usuario</button>
                    <a href="listar.php" class="btn btn-primary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
