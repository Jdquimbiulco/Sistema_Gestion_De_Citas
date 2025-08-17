<?php
require_once '../../conexion/db.php';

// Verificar si se proporciona un ID de cita
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$cita_id = $_GET['id'];

try {
    // Obtener información de la cita antes de eliminarla
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre as paciente_nombre,
               m.nombre as medico_nombre
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN medicos m ON c.medico_id = m.id
        WHERE c.id = ?
    ");
    $stmt->execute([$cita_id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cita) {
        header('Location: listar.php?mensaje=Cita no encontrada&tipo=danger');
        exit();
    }
    
    // Eliminar la cita
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$cita_id]);
    
    $mensaje = "Cita eliminada exitosamente: " . $cita['paciente_nombre'] . 
               " con " . $cita['medico_nombre'] . 
               " el " . date('d/m/Y', strtotime($cita['fecha']));
    
    header('Location: listar.php?mensaje=' . urlencode($mensaje) . '&tipo=success');
    exit();
    
} catch (PDOException $e) {
    $mensaje = "Error al eliminar la cita: " . $e->getMessage();
    header('Location: listar.php?mensaje=' . urlencode($mensaje) . '&tipo=danger');
    exit();
}
?>
