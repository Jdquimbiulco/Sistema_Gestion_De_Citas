<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $usuario_id = $_POST['usuario_id'];
        $materias_id = $_POST['materias_id'];
        $n1 = $_POST['n1'];
        $n2 = $_POST['n2'];
        $n3 = $_POST['n3'];
        $promedio = $_POST['promedio'];

        // Validar que las notas estén en el rango correcto (0-20)
        if ($n1 < 0 || $n1 > 20 || $n2 < 0 || $n2 > 20 || $n3 < 0 || $n3 > 20) {
            throw new Exception("Las notas deben estar entre 0 y 20");
        }

        // Verificar si ya existe una nota para este usuario y materia
        $sqlCheck = "SELECT id FROM notas WHERE usuario_id = ? AND materias_id = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$usuario_id, $materias_id]);
        
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Ya existe una nota registrada para este usuario en esta materia");
        }

        // Insertar la nueva nota
        $sql = "INSERT INTO notas (usuario_id, materias_id, n1, n2, n3, promedio) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $materias_id, $n1, $n2, $n3, $promedio]);

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Las notas han sido guardadas correctamente. La lista se actualizará automáticamente.',
                    icon: 'success',
                    confirmButtonText: 'Ver Lista Actualizada'
                }).then(function() {
                    window.location.href = 'ingresar_notas.php#notas-lista';
                });
            });
        </script>";

    } catch (Exception $e) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error',
                    text: '" . addslashes($e->getMessage()) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'ingresar_notas.php';
                });
            });
        </script>";
    }
} else {
    header('Location: ingresar_notas.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardando Notas...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
        <div>Procesando...</div>
    </div>
</body>
</html>
