<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Alumno.php';

Auth::requireGestion();

$alumno = new Alumno();
$error = '';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: listar.php');
    exit;
}

$alumnoData = $alumno->getById($id);
if (!$alumnoData) {
    header('Location: listar.php?error=Alumno no encontrado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = [
            'identificador' => $_POST['identificador'] ?? '',
            'nombre_completo' => $_POST['nombre_completo'] ?? '',
            'edad' => $_POST['edad'] ?? '',
        ];
        
        // Solo actualizar contraseña si se proporciona
        if (!empty($_POST['password'])) {
            $datos['password'] = $_POST['password'];
        }
        
        // Manejar subida de foto
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $datos['foto_perfil'] = $alumno->subirFotoPerfil($_FILES['foto_perfil']);
        }
        
        $alumno->actualizar($id, $datos);
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
    <title>Editar Alumno - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>✏️ Editar Alumno</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="identificador">Matrícula (9 dígitos) *</label>
                    <input type="text" id="identificador" name="identificador" required
                           pattern="[0-9]{9}" maxlength="9"
                           placeholder="Ej: 202163001"
                           value="<?php echo htmlspecialchars($alumnoData['identificador']); ?>">
                    <small>Número de matrícula de 9 dígitos</small>
                </div>
                
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required
                           placeholder="Ej: Juan Carlos Pérez López"
                           value="<?php echo htmlspecialchars($alumnoData['nombre_completo']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edad">Edad *</label>
                    <input type="number" id="edad" name="edad" required min="15" max="99"
                           placeholder="Ej: 20"
                           value="<?php echo htmlspecialchars($alumnoData['edad']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" 
                           pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                           minlength="8">
                    <small>Dejar en blanco para mantener la actual. Mínimo 8 caracteres con: 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial (@$!%*?&)</small>
                </div>
                
                <div class="form-group">
                    <label>Foto de Perfil Actual</label>
                    <?php if ($alumnoData['foto_perfil']): ?>
                        <div style="margin: 10px 0;">
                            <img src="/uploads/<?php echo htmlspecialchars($alumnoData['foto_perfil']); ?>" 
                                 alt="Foto actual" style="max-width: 150px; border-radius: 10px;">
                        </div>
                    <?php else: ?>
                        <p style="color: #666;">Sin foto de perfil</p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="foto_perfil">Cambiar Foto de Perfil</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                    <small>Formatos: JPG, PNG, GIF. Máximo 5MB</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
                    <a href="listar.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
