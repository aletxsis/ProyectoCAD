<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Alumno.php';

Auth::requireGestion();

$alumno = new Alumno();
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

// Confirmar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $alumno->eliminar($id);
        header('Location: listar.php?success=deleted');
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
    <title>Eliminar Alumno - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>🗑️ Eliminar Alumno</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-error" style="margin-bottom: 30px;">
                ⚠️ <strong>¿Estás seguro de eliminar este alumno?</strong><br>
                Esta acción dará de baja al alumno del sistema.
            </div>
            
            <div style="background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <h3>Datos del Alumno</h3>
                <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($alumnoData['identificador']); ?></p>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($alumnoData['nombre_completo']); ?></p>
                <p><strong>Edad:</strong> <?php echo $alumnoData['edad']; ?> años</p>
                <p><strong>Estado:</strong> 
                    <span class="badge <?php echo $alumnoData['activo'] ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo $alumnoData['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </p>
            </div>
            
            <form method="POST">
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">🗑️ Sí, Eliminar Alumno</button>
                    <a href="listar.php" class="btn btn-primary">❌ No, Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
