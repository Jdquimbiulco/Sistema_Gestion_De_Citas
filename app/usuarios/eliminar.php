<?php
$ruta_conexion = __DIR__ . '/../../conexion/db.php';
require_once $ruta_conexion;

// Verificar si se recibió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: "Error",
                text: "ID de usuario no proporcionado.",
                icon: "error",
                confirmButtonColor: "#dc3545",
                confirmButtonText: "Aceptar"
            }).then((result) => {
                window.location.href = "listar.php";
            });
        </script>
    </body>
    </html>';
    exit();
}

$id = intval($_GET['id']); // Convertir a entero para seguridad

try {
    // Primero verificar si el usuario existe
    $checkSql = "SELECT COUNT(*) FROM usuarios WHERE id = :id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    $userExists = $checkStmt->fetchColumn();
    
    if ($userExists == 0) {
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: "Usuario no encontrado",
                    text: "El usuario que intentas eliminar no existe.",
                    icon: "warning",
                    confirmButtonColor: "#ffc107",
                    confirmButtonText: "Aceptar"
                }).then((result) => {
                    window.location.href = "listar.php";
                });
            </script>
        </body>
        </html>';
        exit();
    }
    
    // Proceder con la eliminación
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected > 0) {
            echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: "¡Eliminado!",
                        text: "El usuario ha sido eliminado exitosamente.",
                        icon: "success",
                        confirmButtonColor: "#28a745",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        window.location.href = "listar.php";
                    });
                </script>
            </body>
            </html>';
        } else {
            echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        title: "Sin cambios",
                        text: "No se pudo eliminar el usuario. Puede que ya haya sido eliminado.",
                        icon: "info",
                        confirmButtonColor: "#17a2b8",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        window.location.href = "listar.php";
                    });
                </script>
            </body>
            </html>';
        }
    } else {
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: "Error",
                    text: "Error al ejecutar la eliminación en la base de datos.",
                    icon: "error",
                    confirmButtonColor: "#dc3545",
                    confirmButtonText: "Aceptar"
                }).then((result) => {
                    window.location.href = "listar.php";
                });
            </script>
        </body>
        </html>';
    }
    
} catch (PDOException $e) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.jsdelivr.net/npm/@magicbruno/swalstrap5@1.0.8/dist/js/swalstrap5_all.min.js"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                title: "Error de Base de Datos",
                text: "Error de conexión con la base de datos: ' . addslashes($e->getMessage()) . '",
                icon: "error",
                confirmButtonColor: "#dc3545",
                confirmButtonText: "Aceptar"
            }).then((result) => {
                window.location.href = "listar.php";
            });
        </script>
    </body>
    </html>';
}
?>
