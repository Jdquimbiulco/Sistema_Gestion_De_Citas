<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

// Solo procesar peticiones AJAX POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    try {
        $id = $_POST['id'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $edad = $_POST['edad'] ?? '';
        
        // Validaciones
        if (empty($nombre) || empty($email) || empty($edad)) {
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
                'message' => 'Estudiante actualizado exitosamente',
                'data' => [
                    'nombre' => $nombre,
                    'email' => $email,
                    'edad' => $edad
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se realizaron cambios o el estudiante no existe'
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
