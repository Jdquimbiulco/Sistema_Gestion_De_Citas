<?php
require_once '../../conexion/db.php';

// Manejar mensajes de estado
$mensaje = '';
$tipo_mensaje = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'deleted':
            $mensaje = "Paciente eliminado exitosamente";
            $tipo_mensaje = "success";
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'not_found':
            $mensaje = "El paciente no fue encontrado";
            $tipo_mensaje = "danger";
            break;
        case 'has_appointments':
            $mensaje = "No se puede eliminar el paciente porque tiene citas asociadas";
            $tipo_mensaje = "warning";
            break;
        case 'delete_failed':
            $mensaje = "Error al eliminar el paciente";
            $tipo_mensaje = "danger";
            break;
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM pacientes ORDER BY nombre");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener pacientes: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Pacientes - Sistema de Citas Médicas</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/sistema-styles.css">
    <style>
        .patient-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--success-color);
        }
        
        .patient-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .stats-card {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .age-badge {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .search-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    <!-- Header Card -->
                    <div class="card fade-in">
                        <div class="card-header bg-success text-white">
                            <h3 class="mb-0">
                                <i class="fas fa-users"></i> 👤 Gestión de Pacientes
                            </h3>
                        </div>
                        
                        <div class="card-body">
                            <!-- Stats and Actions -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="stats-card">
                                        <div class="row text-center">
                                            <div class="col-md-4">
                                                <h3 class="mb-1"><?= count($pacientes) ?></h3>
                                                <p class="mb-0">👤 Total Pacientes</p>
                                            </div>
                                            <div class="col-md-4">
                                                <h3 class="mb-1">
                                                    <?= count(array_filter($pacientes, function($p) {
                                                        $edad = (new DateTime())->diff(new DateTime($p['fecha_nacimiento']))->y;
                                                        return $edad >= 18;
                                                    })) ?>
                                                </h3>
                                                <p class="mb-0">🧑 Adultos</p>
                                            </div>
                                            <div class="col-md-4">
                                                <h3 class="mb-1">
                                                    <?= count(array_filter($pacientes, function($p) {
                                                        $edad = (new DateTime())->diff(new DateTime($p['fecha_nacimiento']))->y;
                                                        return $edad < 18;
                                                    })) ?>
                                                </h3>
                                                <p class="mb-0">👶 Menores</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="d-grid gap-2">
                                        <a href="crear.php" class="btn btn-success btn-custom btn-lg">
                                            ➕ Registrar Nuevo Paciente
                                        </a>
                                        <a href="../../index.html" class="btn btn-outline-secondary btn-custom">
                                            🏠 Volver al Inicio
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search Container -->
                            <div class="search-container">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="🔍 Buscar por nombre, correo o teléfono..." 
                                               onkeyup="filtrarPacientes()">
                                    </div>
                                    <div class="col-md-3">
                                        <select id="ageFilter" class="form-select" onchange="filtrarPacientes()">
                                            <option value="">👶 Todas las edades</option>
                                            <option value="menor">Menores de 18</option>
                                            <option value="adulto">18 años o más</option>
                                            <option value="senior">60 años o más</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button onclick="limpiarFiltros()" class="btn btn-outline-light btn-custom w-100">
                                            🗑️ Limpiar Filtros
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Messages -->
                            <?php if (!empty($mensaje)): ?>
                                <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
                                    <strong><?= $tipo_mensaje == 'success' ? '✅ Éxito:' : '❌ Error:' ?></strong> <?= $mensaje ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Patients Content -->
                            <?php if (empty($pacientes)): ?>
                                <div class="text-center py-5">
                                    <div class="card patient-card">
                                        <div class="card-body">
                                            <h1 class="display-1 text-muted">👤</h1>
                                            <h4 class="text-muted">No hay pacientes registrados</h4>
                                            <p class="text-muted mb-4">
                                                Comience registrando el primer paciente del sistema para empezar a gestionar citas médicas.
                                            </p>
                                            <a href="crear.php" class="btn btn-success btn-custom btn-lg">
                                                ➕ Registrar Primer Paciente
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="patientsTable">
                                        <thead>
                                            <tr>
                                                <th width="8%">ID</th>
                                                <th width="25%">👤 Paciente</th>
                                                <th width="20%">📧 Contacto</th>
                                                <th width="15%">🎂 Nacimiento</th>
                                                <th width="12%">👶 Edad</th>
                                                <th width="20%">⚙️ Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pacientes as $paciente): ?>
                                                <?php
                                                // Calcular edad
                                                $fechaNac = new DateTime($paciente['fecha_nacimiento']);
                                                $hoy = new DateTime();
                                                $edad = $hoy->diff($fechaNac)->y;
                                                ?>
                                                <tr class="patient-row" data-edad="<?= $edad ?>">
                                                    <td>
                                                        <span class="badge bg-primary"><?= $paciente['id'] ?></span>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong class="d-block"><?= htmlspecialchars($paciente['nombre']) ?></strong>
                                                            <small class="text-muted">ID: <?= $paciente['id'] ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="mb-1">
                                                                <small class="text-muted">📧</small>
                                                                <span><?= htmlspecialchars($paciente['correo']) ?></span>
                                                            </div>
                                                            <div>
                                                                <small class="text-muted">📞</small>
                                                                <span><?= htmlspecialchars($paciente['telefono']) ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">
                                                            <?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="age-badge">
                                                            <?= $edad ?> años
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="editar.php?id=<?= $paciente['id'] ?>" 
                                                               class="btn btn-warning btn-sm" 
                                                               title="Editar paciente"
                                                               data-bs-toggle="tooltip">
                                                                ✏️
                                                            </a>
                                                            <a href="../citas/listar.php?paciente_id=<?= $paciente['id'] ?>" 
                                                               class="btn btn-info btn-sm" 
                                                               title="Ver citas del paciente"
                                                               data-bs-toggle="tooltip">
                                                                📅
                                                            </a>
                                                            <a href="../citas/crear.php?paciente_id=<?= $paciente['id'] ?>" 
                                                               class="btn btn-primary btn-sm" 
                                                               title="Crear nueva cita"
                                                               data-bs-toggle="tooltip">
                                                                ➕
                                                            </a>
                                                            <a href="eliminar.php?id=<?= $paciente['id'] ?>" 
                                                               class="btn btn-danger btn-sm" 
                                                               title="Eliminar paciente"
                                                               data-bs-toggle="tooltip"
                                                               onclick="return confirm('¿Está seguro de eliminar este paciente?')">
                                                                🗑️
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Results Summary -->
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <strong>📊 Total mostrado:</strong> 
                                            <span id="totalMostrado"><?= count($pacientes) ?></span> pacientes
                                        </div>
                                        <div class="col-md-4">
                                            <strong>👤 Registros totales:</strong> 
                                            <?= count($pacientes) ?> pacientes
                                        </div>
                                        <div class="col-md-4">
                                            <strong>📅 Última actualización:</strong> 
                                            <?= date('d/m/Y H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Filtrar pacientes
        function filtrarPacientes() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const ageFilter = document.getElementById('ageFilter').value;
            const rows = document.querySelectorAll('.patient-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const nombre = row.cells[1].textContent.toLowerCase();
                const correo = row.cells[2].textContent.toLowerCase();
                const telefono = row.cells[2].textContent.toLowerCase();
                const edad = parseInt(row.dataset.edad);
                
                let matchesSearch = nombre.includes(searchTerm) || 
                                  correo.includes(searchTerm) || 
                                  telefono.includes(searchTerm);
                
                let matchesAge = true;
                if (ageFilter === 'menor') {
                    matchesAge = edad < 18;
                } else if (ageFilter === 'adulto') {
                    matchesAge = edad >= 18 && edad < 60;
                } else if (ageFilter === 'senior') {
                    matchesAge = edad >= 60;
                }
                
                if (matchesSearch && matchesAge) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('totalMostrado').textContent = visibleCount;
        }

        // Limpiar filtros
        function limpiarFiltros() {
            document.getElementById('searchInput').value = '';
            document.getElementById('ageFilter').value = '';
            filtrarPacientes();
        }

        // Efecto de carga
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.patient-card, .card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });
    </script>
</body>
</html>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>👤 Lista de Pacientes</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-0">Total de pacientes: <?= count($pacientes) ?></h5>
                            </div>
                            <div>
                                <a href="crear.php" class="btn btn-success btn-custom">
                                    ➕ Nuevo Paciente
                                </a>
                                <a href="../../index.html" class="btn btn-secondary btn-custom">
                                    🏠 Inicio
                                </a>
                            </div>
                        </div>
                        
                        <!-- Mostrar mensajes -->
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                ✅ <?= htmlspecialchars($_GET['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                ❌ <?= htmlspecialchars($_GET['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($pacientes)): ?>
                            <div class="alert alert-info text-center">
                                <h5>📋 No hay pacientes registrados</h5>
                                <p>Comience registrando el primer paciente del sistema.</p>
                                <a href="crear.php" class="btn btn-success btn-custom">
                                    ➕ Registrar Primer Paciente
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>👤 Nombre</th>
                                            <th>📧 Correo</th>
                                            <th>📞 Teléfono</th>
                                            <th>🎂 Fecha Nacimiento</th>
                                            <th>👶 Edad</th>
                                            <th>⚙️ Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pacientes as $paciente): ?>
                                            <?php
                                            // Calcular edad
                                            $fechaNac = new DateTime($paciente['fecha_nacimiento']);
                                            $hoy = new DateTime();
                                            $edad = $hoy->diff($fechaNac)->y;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($paciente['id']) ?></td>
                                                <td><strong><?= htmlspecialchars($paciente['nombre']) ?></strong></td>
                                                <td><?= htmlspecialchars($paciente['correo']) ?></td>
                                                <td><?= htmlspecialchars($paciente['telefono']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $edad ?> años</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="editar.php?id=<?= $paciente['id'] ?>" 
                                                           class="btn btn-warning btn-sm" title="Editar">
                                                            ✏️
                                                        </a>
                                                        <a href="../citas/listar.php?paciente_id=<?= $paciente['id'] ?>" 
                                                           class="btn btn-info btn-sm" title="Ver Citas">
                                                            📅
                                                        </a>
                                                        <a href="eliminar.php?id=<?= $paciente['id'] ?>" 
                                                           class="btn btn-danger btn-sm" title="Eliminar"
                                                           onclick="return confirm('¿Está seguro de eliminar este paciente?')">
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
                                    <strong>Total de pacientes:</strong> <?= count($pacientes) ?>
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
