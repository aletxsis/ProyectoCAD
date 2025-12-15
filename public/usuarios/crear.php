<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Usuario.php';

Auth::requireDirectivo();

$usuario = new Usuario();
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = [
            'identificador' => $_POST['identificador'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'password' => $_POST['password'] ?? '',
            'cargo' => $_POST['cargo'] ?? '',
        ];
        
        // Manejar subida de foto
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $datos['foto_perfil'] = $usuario->subirFotoPerfil($_FILES['foto_perfil']);
        }
        
        $nuevoId = $usuario->crear($datos);
        header('Location: listar.php?success=created');
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
    <title>Crear Usuario - Proyecto CAD</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>Crear Usuario de Gestión</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="identificador">Identificador *</label>
                    <input type="text" id="identificador" name="identificador" required
                           value="<?php echo htmlspecialchars($_POST['identificador'] ?? ''); ?>">
                    <small>Nombre de usuario único para iniciar sesión</small>
                </div>
                
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required
                           value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="cargo">Cargo *</label>
                    <input type="text" id="cargo" name="cargo" required
                           value="<?php echo htmlspecialchars($_POST['cargo'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="foto_perfil">Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                    <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Crear Usuario</button>
                    <a href="listar.php" class="btn btn-primary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
