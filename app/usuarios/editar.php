<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

// Obtener el usuario a editar para mostrar en el formulario
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        header("Location: listar.php");
        exit();
    }
} else {
    header("Location: listar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estudiante - Sistema de Notas</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        console.log('Script cargándose...');
    </script>
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">✏️ Editar Información del Estudiante</h4>
                    </div>
                    <div class="card-body">
                        <form id="formEditar" method="POST" action="actualizar_ajax.php" onsubmit="return enviarFormularioAjax(event);">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">📝 Nombre Completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">📧 Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edad" class="form-label">🎂 Edad</label>
                                <input type="number" class="form-control" id="edad" name="edad" 
                                       value="<?php echo htmlspecialchars($usuario['edad']); ?>" 
                                       required min="15" max="50">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-secondary">
                                    ⬅️ Cancelar
                                </a>
                                <button type="submit" class="btn btn-warning" id="btnActualizar">
                                    💾 Actualizar Estudiante
                                </button>
                            </div>
                        </form>
                        
                        <!-- Mostrar información actualizada -->
                        <div id="infoActualizada" class="mt-4" style="display: none;">
                            <div class="alert alert-success">
                                <h6><strong>✅ Información Actualizada:</strong></h6>
                                <ul class="mb-0">
                                    <li><strong>Nombre:</strong> <span id="nombreActualizado"></span></li>
                                    <li><strong>Email:</strong> <span id="emailActualizado"></span></li>
                                    <li><strong>Edad:</strong> <span id="edadActualizada"></span> años</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        console.log('Script principal cargándose...');
        
        // Función más simple para manejar el envío del formulario
        function enviarFormularioAjax(event) {
            console.log('=== FUNCIÓN AJAX EJECUTADA ===');
            
            // Prevenir envío tradicional
            event.preventDefault();
            event.stopPropagation();
            
            console.log('Prevenido envío tradicional');
            
            const form = event.target;
            const formData = new FormData(form);
            const btnActualizar = document.getElementById('btnActualizar');
            
            console.log('Datos del formulario:');
            for (let [key, value] of formData.entries()) {
                console.log('  ' + key + ': ' + value);
            }
            
            // Cambiar botón
            btnActualizar.disabled = true;
            btnActualizar.innerHTML = '⏳ Actualizando...';
            
            console.log('Enviando petición AJAX...');
            
            // Usar XMLHttpRequest como alternativa a fetch
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'actualizar_ajax.php', true);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log('Respuesta recibida. Status:', xhr.status);
                    
                    // Rehabilitar botón
                    btnActualizar.disabled = false;
                    btnActualizar.innerHTML = '💾 Actualizar Estudiante';
                    
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            console.log('Datos recibidos:', data);
                            
                            if (data.success) {
                                alert('✅ ¡Estudiante actualizado exitosamente!');
                                
                                // Mostrar información actualizada
                                document.getElementById('nombreActualizado').textContent = data.data.nombre;
                                document.getElementById('emailActualizado').textContent = data.data.email;
                                document.getElementById('edadActualizada').textContent = data.data.edad;
                                
                                const infoDiv = document.getElementById('infoActualizada');
                                infoDiv.style.display = 'block';
                                infoDiv.scrollIntoView({ behavior: 'smooth' });
                                
                            } else {
                                alert('❌ Error: ' + data.message);
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            alert('❌ Error procesando respuesta');
                        }
                    } else {
                        console.error('Error HTTP:', xhr.status);
                        alert('❌ Error de conexión: ' + xhr.status);
                    }
                }
            };
            
            console.log('Enviando datos...');
            xhr.send(formData);
            
            // Importante: devolver false para prevenir envío tradicional
            return false;
        }
        
        // Event listener adicional como respaldo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado - configurando respaldo');
            
            const form = document.getElementById('formEditar');
            if (form) {
                form.addEventListener('submit', function(e) {
                    console.log('Event listener de respaldo ejecutado');
                    return enviarFormularioAjax(e);
                });
            }
        });
    </script>
    
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>
</html>
