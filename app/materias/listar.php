<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

// Consultar todas las materias
$sql = "SELECT * FROM materias ORDER BY nombre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materias - Sistema CRUD</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">📖 Gestión de Materias</h4>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearMateria">
                            ➕ Agregar Materia
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>NRC</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($materias) > 0): ?>
                                        <?php foreach($materias as $materia): ?>
                                            <tr id="fila-<?= $materia['id'] ?>">
                                                <td><?= $materia['id'] ?></td>
                                                <td><?= htmlspecialchars($materia['nombre']) ?></td>
                                                <td><?= htmlspecialchars($materia['nrc']) ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick="editarMateria(<?= $materia['id'] ?>, '<?= addslashes($materia['nombre']) ?>', '<?= $materia['nrc'] ?>')">
                                                        ✏️ Editar
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" onclick="eliminarMateria(<?= $materia['id'] ?>)">
                                                        🗑️ Eliminar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No hay materias registradas
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

    <!-- Modal para Crear Materia -->
    <div class="modal fade" id="modalCrearMateria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Agregar Nueva Materia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCrearMateria">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Materia:</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="nrc" class="form-label">NRC:</label>
                            <input type="text" class="form-control" id="nrc" name="nrc" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">💾 Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Materia -->
    <div class="modal fade" id="modalEditarMateria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Editar Materia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarMateria">
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre de la Materia:</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nrc" class="form-label">NRC:</label>
                            <input type="text" class="form-control" id="edit_nrc" name="nrc" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">💾 Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Crear materia
        document.getElementById('formCrearMateria').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('crear_materia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Materia creada correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
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
        });

        // Editar materia
        function editarMateria(id, nombre, nrc) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_nrc').value = nrc;
            new bootstrap.Modal(document.getElementById('modalEditarMateria')).show();
        }

        document.getElementById('formEditarMateria').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('editar_materia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Materia actualizada correctamente',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        // Eliminar materia
        function eliminarMateria(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('eliminar_materia.php', {
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
                                text: 'La materia ha sido eliminada',
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
                    });
                }
            });
        }
    </script>
</body>
</html>
