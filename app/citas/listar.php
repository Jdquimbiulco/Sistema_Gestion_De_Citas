<?php
require_once '../../conexion/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.fecha,
            c.hora_inicio,
            c.hora_fin,
            c.duracion,
            c.costo_total,
            c.estado,
            p.nombre as paciente_nombre,
            m.nombre as medico_nombre,
            m.especialidad
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN medicos m ON c.medico_id = m.id
        ORDER BY c.fecha DESC, c.hora_inicio DESC
    ");
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener citas: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Citas - Sistema de Citas Médicas</title>
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
        .cita-card {
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        .fecha-badge {
            font-size: 0.9em;
            padding: 0.5em 1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>📅 Lista de Citas Médicas</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <a href="../../index.html" class="btn btn-secondary btn-custom">
                                🏠 Inicio
                            </a>
                            <a href="crear.php" class="btn btn-success btn-custom">
                                ➕ Nueva Cita
                            </a>
                        </div>

                        <?php if (isset($_GET['mensaje'])): ?>
                            <div class="alert alert-<?= htmlspecialchars($_GET['tipo'] ?? 'info') ?> alert-dismissible fade show">
                                <?= htmlspecialchars($_GET['mensaje']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($citas)): ?>
                            <div class="alert alert-info text-center">
                                <h5>📋 No hay citas programadas</h5>
                                <p>No hay citas médicas programadas en el sistema.</p>
                                <a href="crear.php" class="btn btn-primary btn-custom">
                                    📅 Programar Primera Cita
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Paciente</th>
                                            <th>Médico</th>
                                            <th>Especialidad</th>
                                            <th>Duración</th>
                                            <th>Costo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citas as $cita): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($cita['id']) ?></td>
                                                <td>
                                                    <span class="badge bg-info fecha-badge">
                                                        <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($cita['hora_inicio']) ?></strong>
                                                    <small class="text-muted">
                                                        - <?= htmlspecialchars($cita['hora_fin']) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($cita['paciente_nombre']) ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($cita['medico_nombre']) ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <?= htmlspecialchars($cita['especialidad']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($cita['duracion']) ?> min
                                                </td>
                                                <td>
                                                    <strong class="text-success">
                                                        $<?= number_format($cita['costo_total'], 2) ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="editar.php?id=<?= $cita['id'] ?>" 
                                                           class="btn btn-warning btn-sm">
                                                            ✏️
                                                        </a>
                                                        <a href="eliminar.php?id=<?= $cita['id'] ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('¿Está seguro de eliminar esta cita?')">
                                                            🗑️
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="text-muted">
                                            <strong>Total de citas:</strong> <?= count($citas) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <p class="text-muted">
                                            <strong>Ingresos totales:</strong> 
                                            $<?= number_format(array_sum(array_column($citas, 'costo_total')), 2) ?>
                                        </p>
                                    </div>
                                </div>
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
