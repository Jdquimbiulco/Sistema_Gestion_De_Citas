<?php
header('Content-Type: application/json');
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

// Verificar si se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario no proporcionado.'
    ]);
    exit();
}

$id = intval($_GET['id']); // Convertir a entero para seguridad

try {
    // Primero verificar si el usuario existe
    $checkSql = "SELECT nombre FROM usuarios WHERE id = :id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    $usuario = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode([
            'success' => false,
            'message' => 'El usuario que intentas eliminar no existe.'
        ]);
        exit();
    }
    
    // Proceder con la eliminación
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'El usuario "' . $usuario['nombre'] . '" ha sido eliminado exitosamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo eliminar el usuario. Puede que ya haya sido eliminado.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al ejecutar la eliminación en la base de datos.'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos.'
    ]);
}
?>
