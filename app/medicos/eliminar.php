<?php
require_once '../../conexion/db.php';

// Verificar que se haya pasado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = $_GET['id'];

try {
    // Verificar si el médico existe
    $stmt = $pdo->prepare("SELECT nombre FROM medicos WHERE id = ?");
    $stmt->execute([$id]);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$medico) {
        header("Location: listar.php?error=not_found");
        exit();
    }
    
    // Verificar si el médico tiene citas asociadas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_citas FROM citas WHERE medico_id = ?");
    $stmt->execute([$id]);
    $citas_count = $stmt->fetch(PDO::FETCH_ASSOC)['total_citas'];
    
    if ($citas_count > 0) {
        header("Location: listar.php?error=has_appointments");
        exit();
    }
    
    // Eliminar el médico
    $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
    $stmt->execute([$id]);
    
    header("Location: listar.php?success=deleted");
    
} catch (PDOException $e) {
    header("Location: listar.php?error=delete_failed");
}
?>
