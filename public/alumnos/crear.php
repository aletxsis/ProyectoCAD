<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Alumno.php';

Auth::requireGestion();

$alumno = new Alumno();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = [
            'identificador' => $_POST['identificador'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'edad' => $_POST['edad'] ?? '',
            'password' => $_POST['password'] ?? '',
        ];
        
        // Manejar subida de foto
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $datos['foto_perfil'] = $alumno->subirFotoPerfil($_FILES['foto_perfil']);
        }
        
        $nuevoId = $alumno->crear($datos);
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
    <title>Inscribir Alumno - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>➕ Inscribir Nuevo Alumno</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="identificador">Matrícula *</label>
                    <input type="text" id="identificador" name="identificador" required
                           placeholder="Ej: 2021630001"
                           value="<?php echo htmlspecialchars($_POST['identificador'] ?? ''); ?>">
                    <small>Número de matrícula único del alumno</small>
                </div>
                
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required
                           placeholder="Ej: Juan Carlos Pérez López"
                           value="<?php echo htmlspecialchars($_POST['nombre_completo'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edad">Edad *</label>
                    <input type="number" id="edad" name="edad" required min="15" max="99"
                           placeholder="Ej: 20"
                           value="<?php echo htmlspecialchars($_POST['edad'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small>Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="foto_perfil">Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                    <small>Formatos: JPG, PNG, GIF. Máximo 5MB</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">✅ Inscribir Alumno</button>
                    <a href="listar.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
