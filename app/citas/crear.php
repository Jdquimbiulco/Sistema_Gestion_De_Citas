<?php
require_once '../../conexion/db.php';

$mensaje = '';
$tipo_mensaje = '';

// Obtener pacientes y médicos para los select
try {
    $stmt_pacientes = $pdo->query("SELECT id, nombre FROM pacientes ORDER BY nombre");
    $pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt_medicos = $pdo->query("SELECT id, nombre, especialidad, tarifa_por_hora FROM medicos ORDER BY nombre");
    $medicos = $stmt_medicos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "Error al cargar datos: " . $e->getMessage();
    $tipo_mensaje = "danger";
    $pacientes = [];
    $medicos = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $medico_id = $_POST['medico_id'];
    $fecha = $_POST['fecha_cita'];
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
        $errores[] = "La fecha de la cita es obligatoria";
    } else {
        // Validar que la fecha no sea pasada
        $fecha_obj = new DateTime($fecha);
        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);
        
        if ($fecha_obj < $hoy) {
            $errores[] = "La fecha de la cita no puede ser anterior a hoy";
        }
    }
    
    if (empty($hora_inicio)) {
        $errores[] = "La hora de inicio es obligatoria";
    }
    
    if (empty($hora_fin)) {
        $errores[] = "La hora de finalización es obligatoria";
    }
    
    if (!empty($hora_inicio) && !empty($hora_fin)) {
        if ($hora_fin <= $hora_inicio) {
            $errores[] = "La hora de finalización debe ser posterior a la hora de inicio";
        }
    }
    
    if (!empty($fecha) && !empty($hora_inicio)) {
        // Validar que no sea una fecha/hora pasada
        $fecha_hora_cita = new DateTime($fecha . ' ' . $hora_inicio);
        $ahora = new DateTime();
        
        if ($fecha_hora_cita < $ahora) {
            $errores[] = "La fecha y hora de la cita no puede ser anterior al momento actual";
        }
    }
    
    // Verificar conflictos de horario si no hay errores básicos
    if (empty($errores)) {
        try {
            // Verificar que el médico no tenga otra cita en el mismo horario
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as conflictos 
                FROM citas 
                WHERE medico_id = ? 
                AND fecha = ? 
                AND (
                    (hora_inicio <= ? AND hora_fin > ?) OR
                    (hora_inicio < ? AND hora_fin >= ?) OR
                    (hora_inicio >= ? AND hora_fin <= ?)
                )
            ");
            $stmt->execute([
                $medico_id, $fecha, 
                $hora_inicio, $hora_inicio,
                $hora_fin, $hora_fin,
                $hora_inicio, $hora_fin
            ]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado['conflictos'] > 0) {
                $errores[] = "El médico ya tiene una cita en ese horario";
            }
        } catch (PDOException $e) {
            $errores[] = "Error al verificar conflictos: " . $e->getMessage();
        }
    }
    
    if (empty($errores)) {
        try {
            // Calcular duración en minutos
            $hora_inicio_obj = new DateTime($hora_inicio);
            $hora_fin_obj = new DateTime($hora_fin);
            $duracion_minutos = ($hora_fin_obj->getTimestamp() - $hora_inicio_obj->getTimestamp()) / 60;
            
            // Obtener tarifa del médico
            $stmt = $pdo->prepare("SELECT tarifa_por_hora FROM medicos WHERE id = ?");
            $stmt->execute([$medico_id]);
            $medico = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calcular costo
            $costo_total = ($duracion_minutos / 60) * $medico['tarifa_por_hora'];
            
            // Insertar la cita (sin motivo, según tu estructura de BD)
            $stmt = $pdo->prepare("
                INSERT INTO citas (paciente_id, medico_id, fecha, hora_inicio, hora_fin, duracion, costo_total) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $paciente_id, $medico_id, $fecha, 
                $hora_inicio, $hora_fin, 
                $duracion_minutos, $costo_total
            ]);
            
            $mensaje = "Cita registrada exitosamente. Duración: " . $duracion_minutos . " minutos. Costo: $" . number_format($costo_total, 2);
            $tipo_mensaje = "success";
            
            // Limpiar campos
            $paciente_id = $medico_id = $fecha = $hora_inicio = $hora_fin = '';
        } catch (PDOException $e) {
            $errores[] = "Error al registrar la cita: " . $e->getMessage();
        }
    }
    
    if (!empty($errores)) {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cita Médica - Sistema de Citas</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url("../../public/imgs/fondo.jpg") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(248, 249, 250, 0.7);
            z-index: -1;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .btn-modern {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .info-box {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="form-container">
                    <div class="text-center mb-4">
                        <h2 class="text-primary fw-bold">📅 Crear Nueva Cita Médica</h2>
                        <p class="text-muted">Complete la información de la cita médica</p>
                    </div>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                            <?= $mensaje ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paciente_id" class="form-label fw-bold">
                                    <i class="text-success">👤</i> Paciente *
                                </label>
                                <select class="form-select" id="paciente_id" name="paciente_id" required>
                                    <option value="">Seleccione un paciente</option>
                                    <?php foreach ($pacientes as $paciente): ?>
                                        <option value="<?= $paciente['id'] ?>" 
                                                <?= (isset($paciente_id) && $paciente_id == $paciente['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($paciente['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="medico_id" class="form-label fw-bold">
                                    <i class="text-info">👨‍⚕️</i> Médico *
                                </label>
                                <select class="form-select" id="medico_id" name="medico_id" required>
                                    <option value="">Seleccione un médico</option>
                                    <?php foreach ($medicos as $medico): ?>
                                        <option value="<?= $medico['id'] ?>" 
                                                data-tarifa="<?= $medico['tarifa_por_hora'] ?>"
                                                <?= (isset($medico_id) && $medico_id == $medico['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($medico['nombre']) ?> - <?= htmlspecialchars($medico['especialidad']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="fecha_cita" class="form-label fw-bold">
                                    <i class="text-warning">📅</i> Fecha de la Cita *
                                </label>
                                <input type="date" class="form-control" id="fecha_cita" name="fecha_cita" 
                                       value="<?= htmlspecialchars($fecha ?? '') ?>" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="hora_inicio" class="form-label fw-bold">
                                    <i class="text-primary">🕐</i> Hora Inicio *
                                </label>
                                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" 
                                       value="<?= htmlspecialchars($hora_inicio ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <label for="hora_fin" class="form-label fw-bold">
                                    <i class="text-danger">🕕</i> Hora Fin *
                                </label>
                                <input type="time" class="form-control" id="hora_fin" name="hora_fin" 
                                       value="<?= htmlspecialchars($hora_fin ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <!-- Información calculada -->
                        <div class="info-box" id="calculosInfo" style="display: none;">
                            <h6 class="fw-bold mb-2">📊 Información de la Cita:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Duración:</strong> <span id="duracionTexto">0 minutos</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Tarifa por hora:</strong> $<span id="tarifaTexto">0.00</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Costo total:</strong> $<span id="costoTexto">0.00</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-modern btn-lg me-3">
                                📅 Crear Cita
                            </button>
                            <a href="listar.php" class="btn btn-secondary btn-modern btn-lg">
                                📋 Ver Lista de Citas
                            </a>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="../../index.html" class="btn btn-outline-primary btn-modern">
                            🏠 Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calcular duración y costo en tiempo real
        function calcularCosto() {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = document.getElementById('hora_fin').value;
            const medicoSelect = document.getElementById('medico_id');
            const selectedOption = medicoSelect.options[medicoSelect.selectedIndex];
            
            if (horaInicio && horaFin && selectedOption && selectedOption.dataset.tarifa) {
                const inicio = new Date('2000-01-01 ' + horaInicio);
                const fin = new Date('2000-01-01 ' + horaFin);
                
                if (fin > inicio) {
                    const duracionMs = fin - inicio;
                    const duracionMinutos = duracionMs / (1000 * 60);
                    const tarifaPorHora = parseFloat(selectedOption.dataset.tarifa);
                    const costo = (duracionMinutos / 60) * tarifaPorHora;
                    
                    document.getElementById('duracionTexto').textContent = duracionMinutos + ' minutos';
                    document.getElementById('tarifaTexto').textContent = tarifaPorHora.toFixed(2);
                    document.getElementById('costoTexto').textContent = costo.toFixed(2);
                    document.getElementById('calculosInfo').style.display = 'block';
                } else {
                    document.getElementById('calculosInfo').style.display = 'none';
                }
            } else {
                document.getElementById('calculosInfo').style.display = 'none';
            }
        }
        
        // Event listeners para cálculo automático
        document.getElementById('hora_inicio').addEventListener('change', calcularCosto);
        document.getElementById('hora_fin').addEventListener('change', calcularCosto);
        document.getElementById('medico_id').addEventListener('change', calcularCosto);
        
        // Validación de fecha y hora
        document.getElementById('fecha_cita').addEventListener('change', function() {
            const fechaSeleccionada = new Date(this.value);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (fechaSeleccionada < hoy) {
                alert('La fecha de la cita no puede ser anterior a hoy');
                this.value = '';
            }
        });
        
        // Validación de horarios
        document.getElementById('hora_fin').addEventListener('change', function() {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = this.value;
            
            if (horaInicio && horaFin && horaFin <= horaInicio) {
                alert('La hora de finalización debe ser posterior a la hora de inicio');
                this.value = '';
            }
        });
        
        // Calcular al cargar la página si hay valores
        document.addEventListener('DOMContentLoaded', calcularCosto);
    </script>
</body>
</html>
