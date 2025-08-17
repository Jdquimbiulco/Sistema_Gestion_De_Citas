<?php
    // conexion a base de datos con conexion/db.php
    $ruta_conexion = __DIR__ . '/../../conexion/db.php';
    require_once $ruta_conexion;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas Registradas - Sistema CRUD</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">📋 Notas Registradas</h4>
                        <a href="ingresar_notas.php" class="btn btn-light btn-sm">
                            ➕ Ingresar Nueva Nota
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Materia</th>
                                        <th>Nota 1</th>
                                        <th>Nota 2</th>
                                        <th>Nota 3</th>
                                        <th>Promedio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Consultar notas existentes con JOIN
                                    $sqlNotas = "SELECT n.*, u.nombre as usuario_nombre, u.email as usuario_email, m.nombre as materia_nombre, m.nrc 
                                                FROM notas n 
                                                JOIN usuarios u ON n.usuario_id = u.id 
                                                JOIN materias m ON n.materias_id = m.id 
                                                ORDER BY n.id DESC";
                                    $stmtNotas = $pdo->prepare($sqlNotas);
                                    $stmtNotas->execute();
                                    $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if(count($notas) > 0):
                                        foreach($notas as $nota):
                                            $estado = $nota['promedio'] >= 14 ? 'Aprobado' : 'Reprobado';
                                            $badgeClass = $nota['promedio'] >= 14 ? 'bg-success' : 'bg-danger';
                                    ?>
                                        <tr id="fila-<?= $nota['id'] ?>">
                                            <td><?= $nota['id'] ?></td>
                                            <td><?= htmlspecialchars($nota['usuario_nombre'] . ' (' . $nota['usuario_email'] . ')') ?></td>
                                            <td><?= htmlspecialchars($nota['materia_nombre'] . ' (' . $nota['nrc'] . ')') ?></td>
                                            <td><?= number_format($nota['n1'], 2) ?></td>
                                            <td><?= number_format($nota['n2'], 2) ?></td>
                                            <td><?= number_format($nota['n3'], 2) ?></td>
                                            <td><strong><?= number_format($nota['promedio'], 2) ?></strong></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= $estado ?></span></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editarNotaModal"
                                                        onclick="cargarNota(<?= $nota['id'] ?>, <?= $nota['usuario_id'] ?>, <?= $nota['materias_id'] ?>, <?= $nota['n1'] ?>, <?= $nota['n2'] ?>, <?= $nota['n3'] ?>)">
                                                    ✏️ Editar
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="eliminarNota(<?= $nota['id'] ?>)">
                                                    🗑️ Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">
                                                No hay notas registradas aún
                                                <br><br>
                                                <a href="ingresar_notas.php" class="btn btn-primary">
                                                    Ingresar Primera Nota
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="../../index.html" class="btn btn-secondary">
                        ⬅️ Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Nota -->
    <div class="modal fade" id="editarNotaModal" tabindex="-1" aria-labelledby="editarNotaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editarNotaModalLabel">✏️ Editar Nota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarNota" onsubmit="return actualizarNotaAjax(event);">
                    <div class="modal-body">
                        <input type="hidden" id="editarNotaId" name="id">
                        <input type="hidden" id="editarUsuarioId" name="usuario_id">
                        <input type="hidden" id="editarMateriaId" name="materias_id">
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editarNota1" class="form-label">Nota 1</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="editarNota1" 
                                           name="n1" 
                                           required 
                                           min="0" 
                                           max="20" 
                                           step="0.1"
                                           onchange="calcularPromedioEditar()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editarNota2" class="form-label">Nota 2</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="editarNota2" 
                                           name="n2" 
                                           required 
                                           min="0" 
                                           max="20" 
                                           step="0.1"
                                           onchange="calcularPromedioEditar()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="editarNota3" class="form-label">Nota 3</label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="editarNota3" 
                                           name="n3" 
                                           required 
                                           min="0" 
                                           max="20" 
                                           step="0.1"
                                           onchange="calcularPromedioEditar()">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editarPromedio" class="form-label">Promedio</label>
                            <input type="number" 
                                   class="form-control bg-light" 
                                   id="editarPromedio" 
                                   readonly 
                                   step="0.01">
                        </div>
                        
                        <div class="alert alert-info">
                            <small><strong>Nota:</strong> El promedio se calcula automáticamente. Las notas deben estar entre 0 y 20.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" id="btnActualizarNota">
                            💾 Actualizar Nota
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para cargar datos de la nota en el modal
        function cargarNota(id, usuarioId, materiaId, n1, n2, n3) {
            document.getElementById('editarNotaId').value = id;
            document.getElementById('editarUsuarioId').value = usuarioId;
            document.getElementById('editarMateriaId').value = materiaId;
            document.getElementById('editarNota1').value = n1;
            document.getElementById('editarNota2').value = n2;
            document.getElementById('editarNota3').value = n3;
            calcularPromedioEditar();
        }
        
        // Función para calcular promedio en tiempo real
        function calcularPromedioEditar() {
            const n1 = parseFloat(document.getElementById('editarNota1').value) || 0;
            const n2 = parseFloat(document.getElementById('editarNota2').value) || 0;
            const n3 = parseFloat(document.getElementById('editarNota3').value) || 0;
            const promedio = (n1 + n2 + n3) / 3;
            document.getElementById('editarPromedio').value = promedio.toFixed(2);
        }
        
        // Función para actualizar nota vía AJAX
        function actualizarNotaAjax(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const btnActualizar = document.getElementById('btnActualizarNota');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editarNotaModal'));
            
            // Calcular promedio antes de enviar
            const n1 = parseFloat(formData.get('n1'));
            const n2 = parseFloat(formData.get('n2'));
            const n3 = parseFloat(formData.get('n3'));
            const promedio = (n1 + n2 + n3) / 3;
            formData.append('promedio', promedio.toFixed(2));
            
            // Cambiar botón a estado de carga
            btnActualizar.disabled = true;
            btnActualizar.innerHTML = '⏳ Actualizando...';
            
            fetch('actualizar_nota_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos del servidor:', data); // Debug
                if (data.success) {
                    // Mostrar notificación de éxito
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'Aceptar'
                    });
                    
                    // Actualizar la fila en la tabla
                    actualizarFilaNota(data.data);
                    
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
                btnActualizar.innerHTML = '💾 Actualizar Nota';
            });
            
            return false;
        }
        
        // Función para actualizar la fila en la tabla
        function actualizarFilaNota(notaData) {
            const fila = document.getElementById('fila-' + notaData.id);
            if (fila) {
                // Actualizar las celdas de notas
                fila.children[3].textContent = parseFloat(notaData.n1).toFixed(2); // Nota 1
                fila.children[4].textContent = parseFloat(notaData.n2).toFixed(2); // Nota 2
                fila.children[5].textContent = parseFloat(notaData.n3).toFixed(2); // Nota 3
                fila.children[6].innerHTML = '<strong>' + parseFloat(notaData.promedio).toFixed(2) + '</strong>'; // Promedio
                
                // Actualizar estado (Aprobado/Reprobado) - Corregido para nota mínima 14
                const promedio = parseFloat(notaData.promedio);
                const estado = promedio >= 14 ? 'Aprobado' : 'Reprobado';
                const badgeClass = promedio >= 14 ? 'bg-success' : 'bg-danger';
                
                // Encontrar la celda del estado (columna 8, índice 7)
                const celdaEstado = fila.children[7];
                celdaEstado.innerHTML = '<span class="badge ' + badgeClass + '">' + estado + '</span>';
                
                console.log('Estado actualizado:', estado, 'Promedio:', promedio, 'Criterio: >= 14'); // Debug
                
                // Actualizar botón editar con nuevos datos
                const btnEditar = fila.querySelector('.btn-warning');
                if (btnEditar) {
                    btnEditar.setAttribute('onclick', 
                        `cargarNota(${notaData.id}, ${notaData.usuario_id}, ${notaData.materias_id}, ${notaData.n1}, ${notaData.n2}, ${notaData.n3})`
                    );
                }
                
                // Efecto visual de actualización
                fila.style.backgroundColor = '#d4edda';
                fila.style.transition = 'background-color 0.5s ease';
                setTimeout(() => {
                    fila.style.backgroundColor = '';
                }, 2000);
            }
        }

        // Función para eliminar nota
        function eliminarNota(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará la nota permanentemente',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('eliminar_nota_ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({id: id})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const fila = document.getElementById('fila-' + id);
                            fila.style.transition = 'opacity 0.3s';
                            fila.style.opacity = '0';
                            setTimeout(() => fila.remove(), 300);
                            
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: 'La nota ha sido eliminada',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error de conexión',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>
