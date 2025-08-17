<?php
require_once '../../conexion/db.php';

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    
    if (empty($correo)) {
        $errores[] = "El correo electrónico es obligatorio";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    }
    
    if (empty($fecha_nacimiento)) {
        $errores[] = "La fecha de nacimiento es obligatoria";
    } else {
        // Validar que la fecha no sea futura
        $fecha_nac = new DateTime($fecha_nacimiento);
        $hoy = new DateTime();
        if ($fecha_nac > $hoy) {
            $errores[] = "La fecha de nacimiento no puede ser futura";
        }
    }
    
    if (empty($errores)) {
        try {
            // Verificar que el correo no exista
            $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE correo = ?");
            $stmt->execute([$correo]);
            if ($stmt->fetch()) {
                $errores[] = "Ya existe un paciente con ese correo electrónico";
            } else {
                // Insertar el paciente
                $stmt = $pdo->prepare("INSERT INTO pacientes (nombre, correo, telefono, fecha_nacimiento) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nombre, $correo, $telefono, $fecha_nacimiento]);
                
                $mensaje = "Paciente registrado exitosamente";
                $tipo_mensaje = "success";
                
                // Limpiar campos
                $nombre = $correo = $telefono = $fecha_nacimiento = '';
            }
        } catch (PDOException $e) {
            $errores[] = "Error al registrar el paciente: " . $e->getMessage();
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
    <title>Registrar Paciente - Sistema de Citas Médicas</title>
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
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="text-center mb-4">
                        <h2 class="text-success fw-bold">👤 Registrar Nuevo Paciente</h2>
                        <p class="text-muted">Complete la información del paciente</p>
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
                                    <i class="text-success">👤</i> Nombre Completo *
                                </label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?= htmlspecialchars($nombre ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="correo" class="form-label fw-bold">
                                    <i class="text-info">📧</i> Correo Electrónico *
                                </label>
                                <input type="email" class="form-control" id="correo" name="correo" 
                                       value="<?= htmlspecialchars($correo ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label fw-bold">
                                    <i class="text-warning">📞</i> Teléfono *
                                </label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?= htmlspecialchars($telefono ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-12 mb-4">
                                <label for="fecha_nacimiento" class="form-label fw-bold">
                                    <i class="text-primary">🎂</i> Fecha de Nacimiento *
                                </label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?= htmlspecialchars($fecha_nacimiento ?? '') ?>" 
                                       max="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-modern btn-lg me-3">
                                💾 Registrar Paciente
                            </button>
                            <a href="listar.php" class="btn btn-secondary btn-modern btn-lg">
                                📋 Ver Lista de Pacientes
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
        // Validación en tiempo real
        document.getElementById('correo').addEventListener('blur', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Formatear teléfono
        document.getElementById('telefono').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            this.value = value;
        });
    </script>
</body>
</html>
