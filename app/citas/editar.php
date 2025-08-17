<?php
require_once '../../conexion/db.php';

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];
$mensaje = '';
$tipo_mensaje = '';

// Obtener datos de la cita
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre as paciente_nombre, m.nombre as medico_nombre, m.especialidad, m.tarifa_por_hora
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN medicos m ON c.medico_id = m.id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cita) {
        header("Location: listar.php");
        exit();
    }
    
    // Obtener pacientes y médicos para los select
    $stmt_pacientes = $pdo->query("SELECT id, nombre FROM pacientes ORDER BY nombre");
    $pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_medicos = $pdo->query("SELECT id, nombre, especialidad, tarifa_por_hora FROM medicos ORDER BY nombre");
    $medicos = $stmt_medicos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Error al obtener la cita: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $medico_id = $_POST['medico_id'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    
    // Validaciones
    $errores = [];
    
    if (empty($paciente_id)) {
        $errores[] = "Debe seleccionar un paciente";
    }
    
    if (empty($medico_id)) {
        $errores[] = "Debe seleccionar un médico";
    }
    
    if (empty($fecha)) {
        $errores[] = "La fecha es obligatoria";
    } else {
        // Validar que la fecha no sea pasada
        $fecha_cita = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        if ($fecha_cita < $hoy) {
            $errores[] = "No se pueden crear citas con fecha pasada";
        }
    }
    
    if (empty($hora_inicio)) {
        $errores[] = "La hora de inicio es obligatoria";
    }
    
    if (empty($hora_fin)) {
        $errores[] = "La hora de fin es obligatoria";
    }
    
    if (!empty($hora_inicio) && !empty($hora_fin)) {
        $inicio = new DateTime($hora_inicio);
        $fin = new DateTime($hora_fin);
        
        if ($fin <= $inicio) {
            $errores[] = "La hora de fin debe ser posterior a la hora de inicio";
        }
        
        // Validar que si es hoy, la hora no sea pasada
        if (!empty($fecha) && $fecha == date('Y-m-d')) {
            $ahora = new DateTime();
            if ($inicio <= $ahora) {
                $errores[] = "No se pueden crear citas con hora pasada";
            }
        }
    }
    
    if (empty($errores)) {
        try {
            // Verificar disponibilidad del médico (excluyendo la cita actual)
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as conflictos 
                FROM citas 
                WHERE medico_id = ? AND fecha = ? AND id != ?
                AND ((hora_inicio < ? AND hora_fin > ?) OR (hora_inicio < ? AND hora_fin > ?))
                AND estado != 'cancelada'
            ");
            $stmt->execute([$medico_id, $fecha, $id, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin]);
            $conflictos = $stmt->fetch(PDO::FETCH_ASSOC)['conflictos'];
            
            if ($conflictos > 0) {
                $errores[] = "El médico ya tiene una cita programada en ese horario";
            } else {
                // Obtener la tarifa del médico
                $stmt = $pdo->prepare("SELECT tarifa_por_hora FROM medicos WHERE id = ?");
                $stmt->execute([$medico_id]);
                $medico = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$medico) {
                    throw new Exception("Médico no encontrado");
                }
                
                // Calcular duración en minutos
                $inicio = new DateTime($hora_inicio);
                $fin = new DateTime($hora_fin);
                $duracion = $fin->diff($inicio);
                $duracion_minutos = ($duracion->h * 60) + $duracion->i;
                
                // Calcular costo total
                $costo_total = ($duracion_minutos / 60) * $medico['tarifa_por_hora'];
                
                // Actualizar la cita
                $stmt = $pdo->prepare("
                    UPDATE citas 
                    SET paciente_id = ?, medico_id = ?, fecha = ?, hora_inicio = ?, hora_fin = ?, duracion = ?, costo_total = ? 
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $paciente_id,
                    $medico_id,
                    $fecha,
                    $hora_inicio,
                    $hora_fin,
                    $duracion_minutos,
                    $costo_total,
                    $id
                ]);
                
                $mensaje = "Cita actualizada exitosamente. Duración: {$duracion_minutos} minutos. Costo: $" . number_format($costo_total, 2);
                $tipo_mensaje = 'success';
                
                // Actualizar los datos de la cita para mostrar en el formulario
                $cita = array_merge($cita, [
                    'paciente_id' => $paciente_id,
                    'medico_id' => $medico_id,
                    'fecha' => $fecha,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'duracion' => $duracion_minutos,
                    'costo_total' => $costo_total
                ]);
            }
        } catch (Exception $e) {
            $errores[] = "Error al actualizar la cita: " . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = 'danger';
    }
}
    
    try {
        // Obtener la tarifa del médico
        $stmt = $pdo->prepare("SELECT tarifa_por_hora FROM medicos WHERE id = ?");
        $stmt->execute([$medico_id]);
        $medico = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$medico) {
            throw new Exception("Médico no encontrado");
        }
        
        // Calcular duración en minutos
        $inicio = new DateTime($hora_inicio);
        $fin = new DateTime($hora_fin);
        $duracion = $fin->diff($inicio);
        $duracion_minutos = ($duracion->h * 60) + $duracion->i;
        
        // Calcular costo total
        $costo_total = ($duracion_minutos / 60) * $medico['tarifa_por_hora'];
        
        // Actualizar la cita
        $stmt = $pdo->prepare("
            UPDATE citas 
            SET paciente_id = ?, medico_id = ?, fecha = ?, hora_inicio = ?, 
                hora_fin = ?, duracion = ?, costo_total = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $paciente_id,
            $medico_id,
            $fecha,
            $hora_inicio,
            $hora_fin,
            $duracion_minutos,
            $costo_total,
            $cita_id
        ]);
        
        $mensaje = "Cita actualizada exitosamente. Duración: {$duracion_minutos} minutos. Costo: $" . number_format($costo_total, 2);
        $tipo_mensaje = 'success';
        
        // Recargar datos de la cita actualizada
        $stmt = $pdo->prepare("
            SELECT c.*, p.nombre as paciente_nombre,
                   m.nombre as medico_nombre
            FROM citas c
            JOIN pacientes p ON c.paciente_id = p.id
            JOIN medicos m ON c.medico_id = m.id
            WHERE c.id = ?
        ");
        $stmt->execute([$cita_id]);
        $cita = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $mensaje = "Error al actualizar la cita: " . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cita - Sistema de Citas Médicas</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-custom {
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .form-control, .form-select {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark text-center">
                        <h3>✏️ Editar Cita Médica #<?= htmlspecialchars($cita['id']) ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <a href="../../index.html" class="btn btn-secondary btn-custom">
                                🏠 Inicio
                            </a>
                            <a href="listar.php" class="btn btn-info btn-custom">
                                📋 Ver Citas
                            </a>
                        </div>

                        <?php if ($mensaje): ?>
                            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
                                <?= htmlspecialchars($mensaje) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="citaForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paciente_id" class="form-label">👤 Paciente</label>
                                    <select class="form-select" id="paciente_id" name="paciente_id" required>
                                        <option value="">Seleccionar paciente...</option>
                                        <?php foreach ($pacientes as $paciente): ?>
                                            <option value="<?= $paciente['id'] ?>" 
                                                    <?= $paciente['id'] == $cita['paciente_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($paciente['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="medico_id" class="form-label">👨‍⚕️ Médico</label>
                                    <select class="form-select" id="medico_id" name="medico_id" required>
                                        <option value="">Seleccionar médico...</option>
                                        <?php foreach ($medicos as $medico): ?>
                                            <option value="<?= $medico['id'] ?>" 
                                                    data-tarifa="<?= $medico['tarifa_por_hora'] ?>"
                                                    <?= $medico['id'] == $cita['medico_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($medico['nombre']) ?> 
                                                - <?= htmlspecialchars($medico['especialidad']) ?>
                                                ($<?= number_format($medico['tarifa_por_hora'], 2) ?>/hora)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="fecha" class="form-label">📅 Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" 
                                           value="<?= htmlspecialchars($cita['fecha']) ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="hora_inicio" class="form-label">🕐 Hora de Inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" 
                                           value="<?= htmlspecialchars($cita['hora_inicio']) ?>" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="hora_fin" class="form-label">🕑 Hora de Fin</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" 
                                           value="<?= htmlspecialchars($cita['hora_fin']) ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">📊 Cálculo Automático</h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <small class="text-muted">Duración:</small>
                                                    <div id="duracion_display" class="fw-bold">
                                                        <?= htmlspecialchars($cita['duracion']) ?> minutos
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Tarifa/hora:</small>
                                                    <div id="tarifa_display" class="fw-bold">
                                                        $<?= number_format($cita['costo_total'] / ($cita['duracion'] / 60), 2) ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <small class="text-muted">Costo Total:</small>
                                                    <div id="costo_display" class="fw-bold text-success">
                                                        $<?= number_format($cita['costo_total'], 2) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning btn-lg btn-custom">
                                    💾 Actualizar Cita
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function calcularCosto() {
            const medicoSelect = document.getElementById('medico_id');
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = document.getElementById('hora_fin').value;
            
            const duracionDisplay = document.getElementById('duracion_display');
            const tarifaDisplay = document.getElementById('tarifa_display');
            const costoDisplay = document.getElementById('costo_display');
            
            if (medicoSelect.selectedIndex > 0 && horaInicio && horaFin) {
                const tarifa = parseFloat(medicoSelect.options[medicoSelect.selectedIndex].dataset.tarifa);
                
                const inicio = new Date('2000-01-01 ' + horaInicio);
                const fin = new Date('2000-01-01 ' + horaFin);
                
                if (fin > inicio) {
                    const diffMs = fin - inicio;
                    const diffMinutos = Math.floor(diffMs / (1000 * 60));
                    const costo = (diffMinutos / 60) * tarifa;
                    
                    duracionDisplay.textContent = diffMinutos + ' minutos';
                    tarifaDisplay.textContent = '$' + tarifa.toFixed(2);
                    costoDisplay.textContent = '$' + costo.toFixed(2);
                } else {
                    duracionDisplay.textContent = 'Hora inválida';
                    costoDisplay.textContent = '$0.00';
                }
            }
        }
        
        document.getElementById('medico_id').addEventListener('change', calcularCosto);
        document.getElementById('hora_inicio').addEventListener('change', calcularCosto);
        document.getElementById('hora_fin').addEventListener('change', calcularCosto);
        
        // Calcular al cargar la página
        window.addEventListener('load', calcularCosto);
        
        // Validación del formulario
        document.getElementById('citaForm').addEventListener('submit', function(e) {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = document.getElementById('hora_fin').value;
            
            if (horaInicio && horaFin) {
                const inicio = new Date('2000-01-01 ' + horaInicio);
                const fin = new Date('2000-01-01 ' + horaFin);
                
                if (fin <= inicio) {
                    e.preventDefault();
                    alert('La hora de fin debe ser posterior a la hora de inicio');
                }
            }
        });
    </script>
</body>
</html>
