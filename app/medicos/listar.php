<?php
require_once '../../conexion/db.php';

// Manejar mensajes de estado
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $mensaje = "Médico eliminado exitosamente";
            $tipo_mensaje = "success";
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'not_found':
            $mensaje = "El médico no fue encontrado";
            $tipo_mensaje = "danger";
            break;
        case 'has_appointments':
            $mensaje = "No se puede eliminar el médico porque tiene citas asociadas";
            $tipo_mensaje = "warning";
            break;
        case 'delete_failed':
            $mensaje = "Error al eliminar el médico";
            $tipo_mensaje = "danger";
            break;
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM medicos ORDER BY nombre");
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener médicos: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Médicos - Sistema de Citas Médicas</title>
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
        .badge-specialty {
            font-size: 0.8em;
            padding: 0.4em 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white text-center">
                        <h3>👨‍⚕️ Lista de Médicos</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <a href="../../index.html" class="btn btn-secondary btn-custom">
                                🏠 Inicio
                            </a>
                            <a href="crear.php" class="btn btn-info btn-custom">
                                ➕ Registrar Nuevo Médico
                            </a>
                        </div>

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
                                <strong><?= $tipo_mensaje == 'success' ? '✅ Éxito:' : '❌ Error:' ?></strong> <?= $mensaje ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($medicos)): ?>
                            <div class="alert alert-info text-center">
                                <h5>📋 No hay médicos registrados</h5>
                                <p>Parece que aún no hay médicos en el sistema.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-info">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Especialidad</th>
                                            <th>Tarifa por Hora</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($medicos as $medico): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($medico['id']) ?></td>
                                                <td><?= htmlspecialchars($medico['nombre']) ?></td>
                                                <td>
                                                    <span class="badge bg-success badge-specialty">
                                                        <?= htmlspecialchars($medico['especialidad']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>$<?= number_format($medico['tarifa_por_hora'], 2) ?></strong>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="editar.php?id=<?= $medico['id'] ?>" 
                                                           class="btn btn-warning btn-sm" 
                                                           title="Editar médico"
                                                           data-bs-toggle="tooltip">
                                                            ✏️
                                                        </a>
                                                        <a href="../citas/listar.php?medico_id=<?= $medico['id'] ?>" 
                                                           class="btn btn-info btn-sm" 
                                                           title="Ver citas del médico"
                                                           data-bs-toggle="tooltip">
                                                            📅
                                                        </a>
                                                        <a href="../citas/crear.php?medico_id=<?= $medico['id'] ?>" 
                                                           class="btn btn-primary btn-sm" 
                                                           title="Crear nueva cita"
                                                           data-bs-toggle="tooltip">
                                                            ➕
                                                        </a>
                                                        <a href="eliminar.php?id=<?= $medico['id'] ?>" 
                                                           class="btn btn-danger btn-sm" 
                                                           title="Eliminar médico"
                                                           data-bs-toggle="tooltip"
                                                           onclick="return confirm('¿Está seguro de eliminar este médico?')">
                                                            🗑️
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <p class="text-muted">
                                    <strong>Total de médicos:</strong> <?= count($medicos) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
