<?php
    // conexion a base de datos con conexion/db.php
    $ruta_conexion = __DIR__ . '/../../conexion/db.php';
    require_once $ruta_conexion;

    // consultar los usuarios de la base de datos
    $sql = "SELECT * FROM usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    //consultar materias en la base de datos
    $sqlMaterias = "SELECT * FROM materias";
    $stmtMaterias = $pdo->prepare($sqlMaterias);  
    $stmtMaterias->execute();
    $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar Notas - Sistema CRUD</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📚 Ingresar Notas</h4>
                    </div>
                    <div class="card-body">
                        <form id="formNotas" action="guardar_notas.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="usuario_id" class="form-label">👤 Seleccionar Usuario:</label>
                                    <select class="form-select" id="usuario_id" name="usuario_id" required>
                                        <option value="">-- Seleccione un usuario --</option>
                                        <?php foreach($usuarios as $usuario): ?>
                                            <option value="<?= $usuario['id'] ?>">
                                                <?= htmlspecialchars($usuario['nombre'] . ' (' . $usuario['email'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="materias_id" class="form-label">📖 Seleccionar Materia:</label>
                                    <select class="form-select" id="materias_id" name="materias_id" required>
                                        <option value="">-- Seleccione una materia --</option>
                                        <?php foreach($materias as $materia): ?>
                                            <option value="<?= $materia['id'] ?>">
                                                <?= htmlspecialchars($materia['nombre'] . ' (NRC: ' . $materia['nrc'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="n1" class="form-label">📝 Nota 1:</label>
                                    <input type="number" class="form-control" id="n1" name="n1" 
                                           min="0" max="20" step="0.01" required 
                                           placeholder="0.00">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="n2" class="form-label">📝 Nota 2:</label>
                                    <input type="number" class="form-control" id="n2" name="n2" 
                                           min="0" max="20" step="0.01" required 
                                           placeholder="0.00">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="n3" class="form-label">📝 Nota 3:</label>
                                    <input type="number" class="form-control" id="n3" name="n3" 
                                           min="0" max="20" step="0.01" required 
                                           placeholder="0.00">
                                </div>
                            </div>
                            
                            
                            <div class="mb-3">
                                <label for="promedio" class="form-label">📊 Promedio:</label>
                                <input type="number" class="form-control" id="promedio" name="promedio" 
                                       step="0.01" readonly 
                                       placeholder="Se calcula automáticamente">
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="../../index.html" class="btn btn-secondary">
                                    ⬅️ Volver al Inicio
                                </a>
                                <button type="submit" class="btn btn-success">
                                    💾 Guardar Notas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Mostrar notas registradas -->
                <div class="card shadow mt-4" id="notas-lista">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📋 Notas Registradas Recientemente</h5>
                        <small class="text-light">Se muestran las últimas 10 notas ingresadas</small>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Consultar notas existentes con JOIN
                                    $sqlNotas = "SELECT n.*, u.nombre as usuario_nombre, u.email as usuario_email, m.nombre as materia_nombre, m.nrc 
                                                FROM notas n 
                                                JOIN usuarios u ON n.usuario_id = u.id 
                                                JOIN materias m ON n.materias_id = m.id 
                                                ORDER BY n.id DESC LIMIT 10";
                                    $stmtNotas = $pdo->prepare($sqlNotas);
                                    $stmtNotas->execute();
                                    $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if(count($notas) > 0):
                                        foreach($notas as $nota):
                                            $estado = $nota['promedio'] >= 14 ? 'Aprobado' : 'Reprobado';
                                            $badgeClass = $nota['promedio'] >= 14 ? 'bg-success' : 'bg-danger';
                                    ?>
                                        <tr>
                                            <td><?= $nota['id'] ?></td>
                                            <td><?= htmlspecialchars($nota['usuario_nombre'] . ' (' . $nota['usuario_email'] . ')') ?></td>
                                            <td><?= htmlspecialchars($nota['materia_nombre'] . ' (' . $nota['nrc'] . ')') ?></td>
                                            <td><?= number_format($nota['n1'], 2) ?></td>
                                            <td><?= number_format($nota['n2'], 2) ?></td>
                                            <td><?= number_format($nota['n3'], 2) ?></td>
                                            <td><strong><?= number_format($nota['promedio'], 2) ?></strong></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= $estado ?></span></td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No hay notas registradas aún
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="listar_notas.php" class="btn btn-primary btn-sm">
                                📊 Ver Todas las Notas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calcular promedio automáticamente
        function calcularPromedio() {
            const n1 = parseFloat(document.getElementById('n1').value) || 0;
            const n2 = parseFloat(document.getElementById('n2').value) || 0;
            const n3 = parseFloat(document.getElementById('n3').value) || 0;
            
            const promedio = (n1 + n2 + n3) / 3;
            document.getElementById('promedio').value = promedio.toFixed(2);
        }

        // Agregar event listeners para el cálculo automático
        document.getElementById('n1').addEventListener('input', calcularPromedio);
        document.getElementById('n2').addEventListener('input', calcularPromedio);
        document.getElementById('n3').addEventListener('input', calcularPromedio);

        // Scroll automático a la lista si viene de guardar nota
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#notas-lista') {
                setTimeout(function() {
                    document.getElementById('notas-lista').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Resaltar la tabla brevemente
                    const tabla = document.getElementById('notas-lista');
                    tabla.style.boxShadow = '0 0 20px rgba(0,123,255,0.5)';
                    setTimeout(() => {
                        tabla.style.transition = 'box-shadow 2s ease';
                        tabla.style.boxShadow = '';
                    }, 2000);
                }, 500);
            }
        });
    </script>
</body>
</html>