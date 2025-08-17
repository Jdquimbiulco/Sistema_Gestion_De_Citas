<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

$sql = "SELECT * FROM usuarios";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
    <title>Lista de Estudiantes - Sistema de Notas</title>
    <style>
        /* Forzar recarga del CSS */
        .swal2-popup { font-family: inherit; }
        
        /* Animación para eliminar fila */
        .eliminando {
            opacity: 0.5;
            transition: all 0.3s ease;
        }
        
        .fade-out {
            opacity: 0;
            transform: translateX(-100%);
            transition: all 0.5s ease;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h1>Listar usuarios</h1>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Edad</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['edad']); ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarModal" 
                                onclick="cargarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>', '<?php echo htmlspecialchars($usuario['email']); ?>', <?php echo $usuario['edad']; ?>)">
                            Editar
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" 
                                onclick="confirmarEliminar(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="../../index.html" class="btn btn-secondary">Volver al inicio</a>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarModalLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarUsuario" onsubmit="return actualizarUsuarioAjax(event);">
                <div class="modal-body">
                    <input type="hidden" id="editarId" name="id">
                    <div class="mb-3">
                        <label for="editarNombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="editarEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editarEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editarEdad" class="form-label">Edad</label>
                        <input type="number" class="form-control" id="editarEdad" name="edad" required min="15" max="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnActualizarModal">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>

<script>
function cargarUsuario(id, nombre, email, edad) {
    document.getElementById('editarId').value = id;
    document.getElementById('editarNombre').value = nombre;
    document.getElementById('editarEmail').value = email;
    document.getElementById('editarEdad').value = edad;
}

function actualizarUsuarioAjax(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const btnActualizar = document.getElementById('btnActualizarModal');
    const modal = bootstrap.Modal.getInstance(document.getElementById('editarModal'));
    
    // Cambiar botón a estado de carga
    btnActualizar.disabled = true;
    btnActualizar.innerHTML = '⏳ Actualizando...';
    
    fetch('actualizar_modal_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar notificación de éxito
            Swal.fire({
                title: '¡Actualizado!',
                text: data.message,
                icon: 'success',
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Aceptar'
            });
            
            // Actualizar la fila en la tabla sin recargar
            actualizarFilaTabla(data.data);
            
            // Cerrar modal
            modal.hide();
            
        } else {
            // Mostrar error
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Aceptar'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Error de conexión. Inténtalo nuevamente.',
            icon: 'error',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Aceptar'
        });
    })
    .finally(() => {
        // Restaurar botón
        btnActualizar.disabled = false;
        btnActualizar.innerHTML = 'Actualizar Usuario';
    });
    
    return false;
}

function actualizarFilaTabla(userData) {
    // Encontrar la fila correspondiente y actualizar sus datos
    const tabla = document.querySelector('table tbody');
    const filas = tabla.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const celdaId = fila.querySelector('td:first-child');
        if (celdaId && celdaId.textContent.trim() == userData.id) {
            // Actualizar las celdas
            fila.children[1].textContent = userData.nombre; // Nombre
            fila.children[2].textContent = userData.email;  // Email
            fila.children[3].textContent = userData.edad;   // Edad
            
            // Actualizar el botón editar con los nuevos datos
            const btnEditar = fila.querySelector('.btn-warning');
            btnEditar.setAttribute('onclick', 
                `cargarUsuario(${userData.id}, '${userData.nombre}', '${userData.email}', ${userData.edad})`
            );
            
            // Efecto visual de actualización
            fila.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                fila.style.backgroundColor = '';
            }, 2000);
        }
    });
}

function confirmarEliminar(id, nombre) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al usuario "${nombre}"? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading()
                }
            });
            
            // Hacer petición AJAX para eliminar
            fetch(`eliminar_ajax.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Eliminar la fila de la tabla sin recargar la página
                            eliminarFilaTabla(id);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error de conexión. Inténtalo nuevamente.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }
    });
}

function eliminarFilaTabla(id) {
    // Encontrar la fila correspondiente
    const tabla = document.querySelector('table tbody');
    const filas = tabla.querySelectorAll('tr');
    
    filas.forEach(fila => {
        const celdaId = fila.querySelector('td:first-child');
        if (celdaId && celdaId.textContent.trim() == id) {
            // Aplicar animación de fade-out
            fila.classList.add('fade-out');
            
            // Eliminar la fila después de la animación
            setTimeout(() => {
                fila.remove();
                
                // Verificar si quedan usuarios en la tabla
                const filasRestantes = tabla.querySelectorAll('tr');
                if (filasRestantes.length === 0) {
                    // Si no quedan usuarios, mostrar un mensaje
                    tabla.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay usuarios registrados</td></tr>';
                }
            }, 500);
        }
    });
}
</script>

</body>
</html>
