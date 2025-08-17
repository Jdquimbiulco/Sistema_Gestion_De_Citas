<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $id = $_POST['id'] ?? '';
        $usuario_id = $_POST['usuario_id'] ?? '';
        $materias_id = $_POST['materias_id'] ?? '';
        $n1 = $_POST['n1'] ?? '';
        $n2 = $_POST['n2'] ?? '';
        $n3 = $_POST['n3'] ?? '';
        $promedio = $_POST['promedio'] ?? '';
        
        // Validaciones
        if (empty($id) || empty($usuario_id) || empty($materias_id) || 
            empty($n1) || empty($n2) || empty($n3)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        // Validar que las notas estén en el rango correcto
        if ($n1 < 0 || $n1 > 20 || $n2 < 0 || $n2 > 20 || $n3 < 0 || $n3 > 20) {
            throw new Exception("Las notas deben estar entre 0 y 20");
        }
        
        // Recalcular promedio para asegurar consistencia
        $promedio_calculado = ($n1 + $n2 + $n3) / 3;
        
        // Actualizar nota
        $sql = "UPDATE notas SET n1 = ?, n2 = ?, n3 = ?, promedio = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$n1, $n2, $n3, $promedio_calculado, $id]);
        
        if ($stmt->rowCount() > 0) {
            // Determinar el estado basado en el promedio - CAMBIADO A 14
            $estado = $promedio_calculado >= 14 ? 'Aprobado' : 'Reprobado';
            
            echo json_encode([
                'success' => true,
                'message' => 'Nota actualizada exitosamente',
                'data' => [
                    'id' => $id,
                    'usuario_id' => $usuario_id,
                    'materias_id' => $materias_id,
                    'n1' => (float)$n1,
                    'n2' => (float)$n2,
                    'n3' => (float)$n3,
                    'promedio' => (float)$promedio_calculado,
                    'estado' => $estado
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
