<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $nrc = trim($_POST['nrc']);

        if (empty($nombre) || empty($nrc)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Verificar si ya existe el NRC en otra materia
        $sqlCheck = "SELECT id FROM materias WHERE nrc = ? AND id != ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$nrc, $id]);
        
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Ya existe otra materia con ese NRC");
        }

        // Actualizar materia
        $sql = "UPDATE materias SET nombre = ?, nrc = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $nrc, $id]);

        echo json_encode(['success' => true, 'message' => 'Materia actualizada correctamente']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
