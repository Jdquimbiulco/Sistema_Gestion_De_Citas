<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $nrc = trim($_POST['nrc']);

        if (empty($nombre) || empty($nrc)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Verificar si ya existe el NRC
        $sqlCheck = "SELECT id FROM materias WHERE nrc = ?";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$nrc]);
        
        if ($stmtCheck->rowCount() > 0) {
            throw new Exception("Ya existe una materia con ese NRC");
        }

        // Insertar nueva materia
        $sql = "INSERT INTO materias (nombre, nrc) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $nrc]);

        echo json_encode(['success' => true, 'message' => 'Materia creada correctamente']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
