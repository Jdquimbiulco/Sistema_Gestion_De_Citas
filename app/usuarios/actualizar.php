<?php
//conectar a la base de datos
require_once '../../conexion/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $edad = $_POST['edad'] ?? '';

    // Validar que se recibió el ID
    if (empty($id)) {
        echo "<script>
                alert('Error: ID de usuario no válido.');
                window.location.href = 'listar.php';
              </script>";
        exit();
    }

    // Actualizar datos en la base de datos
    $sql = "UPDATE usuarios SET nombre = :nombre, email = :email, edad = :edad WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':edad', $edad);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>
                alert('Usuario actualizado exitosamente');
                window.location.href = 'listar.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error al actualizar el usuario.');
                window.location.href = 'listar.php';
              </script>";
    }
} else {
    // Si no es POST, redirigir a listar
    header("Location: listar.php");
    exit();
}
?>
