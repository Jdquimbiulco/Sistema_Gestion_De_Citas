<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $id = $_POST['id'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $edad = $_POST['edad'] ?? '';
        
        // Validaciones
        if (empty($id) || empty($nombre) || empty($email) || empty($edad)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        if ($edad < 15 || $edad > 50) {
            throw new Exception("La edad debe estar entre 15 y 50 años");
        }
        
        // Verificar si el email ya existe en otro usuario
        $sqlCheck = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$email, $id]);
        
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Ya existe otro estudiante con este correo electrónico");
        }
        
        // Actualizar usuario
        $sql = "UPDATE usuarios SET nombre = ?, email = ?, edad = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $email, $edad, $id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => [
                    'id' => $id,
                    'nombre' => $nombre,
                    'email' => $email,
                    'edad' => $edad
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se realizaron cambios'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
