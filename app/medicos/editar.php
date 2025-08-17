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

// Obtener datos del médico
try {
    $stmt = $pdo->prepare("SELECT * FROM medicos WHERE id = ?");
    $stmt->execute([$id]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$medico) {
        header("Location: listar.php");
        exit();
    }
} catch (PDOException $e) {
    echo "Error al obtener el médico: " . $e->getMessage();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $especialidad = trim($_POST['especialidad']);
    $tarifa_por_hora = $_POST['tarifa_por_hora'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    
    if (empty($especialidad)) {
        $errores[] = "La especialidad es obligatoria";
    }
    
    if (empty($tarifa_por_hora) || !is_numeric($tarifa_por_hora) || $tarifa_por_hora <= 0) {
        $errores[] = "La tarifa por hora debe ser un número mayor a 0";
    }
    
    if (empty($errores)) {
        try {
            // Actualizar el médico
            $stmt = $pdo->prepare("UPDATE medicos SET nombre = ?, especialidad = ?, tarifa_por_hora = ? WHERE id = ?");
            $stmt->execute([$nombre, $especialidad, $tarifa_por_hora, $id]);
            
            $mensaje = "Médico actualizado exitosamente";
            $tipo_mensaje = "success";
            
            // Actualizar los datos del médico para mostrar en el formulario
            $medico = [
                'id' => $id,
                'nombre' => $nombre,
                'especialidad' => $especialidad,
                'tarifa_por_hora' => $tarifa_por_hora
            ];
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar el médico: " . $e->getMessage();
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
    <title>Editar Médico - Sistema de Citas Médicas</title>
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
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="text-center mb-4">
                        <h2 class="text-warning fw-bold">✏️ Editar Médico</h2>
                        <p class="text-muted">Modifique la información del médico</p>
                    </div>
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                            <?= $mensaje ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" novalidate>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nombre" class="form-label fw-bold">
                                    <i class="text-info">👨‍⚕️</i> Nombre Completo *
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($medico['nombre']) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="especialidad" class="form-label fw-bold">
                                    <i class="text-primary">🩺</i> Especialidad *
                                </label>
                                <select class="form-select" id="especialidad" name="especialidad" required>
                                    <option value="">Seleccione una especialidad</option>
                                    <option value="Medicina General" <?= $medico['especialidad'] == 'Medicina General' ? 'selected' : '' ?>>Medicina General</option>
                                    <option value="Cardiología" <?= $medico['especialidad'] == 'Cardiología' ? 'selected' : '' ?>>Cardiología</option>
                                    <option value="Pediatría" <?= $medico['especialidad'] == 'Pediatría' ? 'selected' : '' ?>>Pediatría</option>
                                    <option value="Ginecología" <?= $medico['especialidad'] == 'Ginecología' ? 'selected' : '' ?>>Ginecología</option>
                                    <option value="Neurología" <?= $medico['especialidad'] == 'Neurología' ? 'selected' : '' ?>>Neurología</option>
                                    <option value="Dermatología" <?= $medico['especialidad'] == 'Dermatología' ? 'selected' : '' ?>>Dermatología</option>
                                    <option value="Oftalmología" <?= $medico['especialidad'] == 'Oftalmología' ? 'selected' : '' ?>>Oftalmología</option>
                                    <option value="Traumatología" <?= $medico['especialidad'] == 'Traumatología' ? 'selected' : '' ?>>Traumatología</option>
                                    <option value="Psiquiatría" <?= $medico['especialidad'] == 'Psiquiatría' ? 'selected' : '' ?>>Psiquiatría</option>
                                    <option value="Endocrinología" <?= $medico['especialidad'] == 'Endocrinología' ? 'selected' : '' ?>>Endocrinología</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="tarifa_por_hora" class="form-label fw-bold">
                                    <i class="text-success">💰</i> Tarifa por Hora (USD) *
                                </label>
                                <input type="number" class="form-control" id="tarifa_por_hora" name="tarifa_por_hora" 
                                       value="<?= htmlspecialchars($medico['tarifa_por_hora']) ?>" 
                                       min="1" max="500" step="0.01" required>
                                <div class="form-text">Ingrese la tarifa en dólares (ejemplo: 45.00)</div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning btn-modern btn-lg me-3">
                                💾 Actualizar Médico
                            </button>
                            <a href="listar.php" class="btn btn-secondary btn-modern btn-lg">
                                📋 Ver Lista de Médicos
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
        // Validación en tiempo real para la tarifa
        document.getElementById('tarifa_por_hora').addEventListener('input', function() {
            const value = parseFloat(this.value);
            
            if (value < 1) {
                this.setCustomValidity('La tarifa debe ser mayor a $1');
            } else if (value > 500) {
                this.setCustomValidity('La tarifa no puede exceder $500');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
