<?php
// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "crud_usuarios"; // Cambia esto al nombre de tu base de datos
$username = "crud_usuarios";
$password = "12345";
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Configurar el modo de error de PDO para que lance excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Solo mostrar este mensaje si se accede directamente al archivo
    if (basename($_SERVER['PHP_SELF']) == 'db.php') {
        echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";
        echo "<p>Base de datos: $dbname</p>";
        echo "<p>Usuario: $username</p>";
    }
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit();
}

?>