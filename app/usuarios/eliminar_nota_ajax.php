<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $id = $input['id'];

        // Eliminar nota
        $sql = "DELETE FROM notas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Nota eliminada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la nota a eliminar']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
