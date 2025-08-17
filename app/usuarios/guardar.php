<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $edad = $_POST['edad'] ?? '';

        // Validaciones básicas
        if (empty($nombre) || empty($email) || empty($edad)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        if ($edad < 15 || $edad > 50) {
            throw new Exception("La edad debe estar entre 15 y 50 años");
        }

        // Verificar si el email ya existe
        $sqlCheck = "SELECT id FROM usuarios WHERE email = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$email]);
        
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Ya existe un estudiante registrado con este correo electrónico");
        }

        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, email, edad) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $edad]);

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Estudiante registrado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'listar.php';
                });
            });
        </script>";

    } catch (Exception $e) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: '" . addslashes($e->getMessage()) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'crear.php';
                });
            });
        </script>";
    }
} else {
    header('Location: crear.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
        <div>Procesando...</div>
    </div>
</body>
</html>