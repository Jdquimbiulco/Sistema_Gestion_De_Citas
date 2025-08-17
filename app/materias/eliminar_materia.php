<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    try {
        $id = $input['id'];

        // Verificar si la materia tiene notas asociadas
        $sqlCheck = "SELECT COUNT(*) as total FROM notas WHERE materias_id = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$id]);
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar la materia porque tiene notas asociadas");
        }

        // Eliminar materia
        $sql = "DELETE FROM materias WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Materia eliminada correctamente']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
