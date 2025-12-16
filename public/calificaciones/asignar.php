<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Alumno.php';
require_once __DIR__ . '/../../includes/Materia.php';
require_once __DIR__ . '/../../includes/Calificacion.php';

Auth::requireGestion();

$alumnoObj = new Alumno();
$materiaObj = new Materia();
$calificacionObj = new Calificacion();

$error = '';
$success = false;

// Si vienen por parámetros (desde ver_alumnos.php)
$alumnoId = $_GET['alumno_id'] ?? null;
$materiaId = $_GET['materia_id'] ?? null;

// Cargar datos si se especificaron
$alumnoSeleccionado = null;
$materiaSeleccionada = null;
$inscripcion = null;

if ($alumnoId && $materiaId) {
    $alumnoSeleccionado = $alumnoObj->getById($alumnoId);
    $materiaSeleccionada = $materiaObj->getById($materiaId);
    $inscripcion = $calificacionObj->getInscripcion($alumnoId, $materiaId);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $alumnoId = $_POST['alumno_id'];
        $materiaId = $_POST['materia_id'];
        $parcial = $_POST['parcial'];
        $calificacion = $_POST['calificacion'];
        
        $calificacionObj->asignar($alumnoId, $materiaId, $parcial, $calificacion);
        $success = true;
        
        // Recargar inscripción
        $inscripcion = $calificacionObj->getInscripcion($alumnoId, $materiaId);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener listas para los selects
$alumnos = $alumnoObj->getAll();
$materias = $materiaObj->getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Calificaciones - SAES 2.0</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        function cargarInscripcion() {
            const alumnoId = document.getElementById('alumno_id').value;
            const materiaId = document.getElementById('materia_id').value;
            
            if (alumnoId && materiaId) {
                window.location.href = `asignar.php?alumno_id=${alumnoId}&materia_id=${materiaId}`;
            }
        }
    </script>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h1>📝 Asignar Calificaciones</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    ✅ Calificación asignada exitosamente
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Selector de Alumno y Materia -->
            <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3>1. Seleccionar Alumno y Materia</h3>
                
                <div class="form-group">
                    <label for="alumno_id">Alumno</label>
                    <select id="alumno_id" onchange="cargarInscripcion()">
                        <option value="">-- Seleccionar alumno --</option>
                        <?php foreach ($alumnos as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($alumnoId == $a['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['identificador'] . ' - ' . $a['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="materia_id">Materia</label>
                    <select id="materia_id" onchange="cargarInscripcion()">
                        <option value="">-- Seleccionar materia --</option>
                        <?php foreach ($materias as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo ($materiaId == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['identificador'] . ' - ' . $m['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <?php if ($inscripcion): ?>
                <!-- Mostrar calificaciones actuales -->
                <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <h3>📊 Calificaciones Actuales</h3>
                    <p><strong>Alumno:</strong> <?php echo htmlspecialchars($inscripcion['alumno_nombre']); ?></p>
                    <p><strong>Materia:</strong> <?php echo htmlspecialchars($inscripcion['materia_nombre']); ?></p>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 15px;">
                        <div style="background: white; padding: 15px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 12px; color: #666;">Parcial 1</div>
                            <div style="font-size: 24px; font-weight: bold; color: #2196F3;">
                                <?php echo $inscripcion['parcial_1'] !== null ? number_format($inscripcion['parcial_1'], 2) : '-'; ?>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 12px; color: #666;">Parcial 2</div>
                            <div style="font-size: 24px; font-weight: bold; color: #2196F3;">
                                <?php echo $inscripcion['parcial_2'] !== null ? number_format($inscripcion['parcial_2'], 2) : '-'; ?>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 12px; color: #666;">Parcial 3</div>
                            <div style="font-size: 24px; font-weight: bold; color: #2196F3;">
                                <?php echo $inscripcion['parcial_3'] !== null ? number_format($inscripcion['parcial_3'], 2) : '-'; ?>
                            </div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 5px; text-align: center;">
                            <div style="font-size: 12px; color: #666;">Final</div>
                            <div style="font-size: 24px; font-weight: bold; color: <?php echo ($inscripcion['calificacion_final'] >= 70) ? '#4CAF50' : '#f44336'; ?>;">
                                <?php echo $inscripcion['calificacion_final'] !== null ? number_format($inscripcion['calificacion_final'], 2) : '-'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Formulario de asignación -->
                <div style="background: #fff3e0; padding: 20px; border-radius: 8px;">
                    <h3>2. Capturar Calificación</h3>
                    
                    <form method="POST">
                        <input type="hidden" name="alumno_id" value="<?php echo $alumnoId; ?>">
                        <input type="hidden" name="materia_id" value="<?php echo $materiaId; ?>">
                        
                        <div class="form-group">
                            <label for="parcial">Parcial *</label>
                            <select id="parcial" name="parcial" required>
                                <option value="">-- Seleccionar parcial --</option>
                                <option value="1">Parcial 1</option>
                                <option value="2">Parcial 2</option>
                                <option value="3">Parcial 3</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="calificacion">Calificación *</label>
                            <input type="number" id="calificacion" name="calificacion" required 
                                   min="0" max="100" step="0.01" placeholder="0.00">
                            <small>Valor entre 0 y 100. Calificación mínima aprobatoria: 70</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">✅ Guardar Calificación</button>
                            <a href="/materias/ver_alumnos.php?materia_id=<?php echo $materiaId; ?>" class="btn btn-secondary">
                                ← Volver a la Materia
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    Selecciona un alumno y una materia para comenzar
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="/gestion/dashboard.php" class="btn btn-primary">← Volver al Dashboard</a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
