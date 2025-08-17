<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estudiante - Sistema de Notas</title>
    <link rel="stylesheet" href="../../public/lib/bootstrap-5.3.7-dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">👨‍🎓 Registrar Nuevo Estudiante</h4>
                    </div>
                    <div class="card-body">
                                                <form action="./guardar.php" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">📝 Nombre Completo del Estudiante</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">📧 Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="estudiante@email.com">
                            </div>
                            <div class="mb-3">
                                <label for="edad" class="form-label">🎂 Edad</label>
                                <input type="number" class="form-control" id="edad" name="edad" required min="15" max="50" placeholder="Ej: 20">
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="../../index.html" class="btn btn-secondary">
                                    ⬅️ Volver al Inicio
                                </a>
                                <button type="submit" class="btn btn-success">
                                    💾 Registrar Estudiante
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../public/lib/bootstrap-5.3.7-dist/js/bootstrap.bundle.min.js"></script>


    </div>
    
</body>
</html>