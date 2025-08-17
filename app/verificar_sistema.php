<?php
require_once '../../conexion/db.php';

echo "<h2>🔍 Verificación del Sistema de Citas</h2>";

// Verificar si las tablas existen
$tablas = ['pacientes', 'medicos', 'citas'];
$tablas_existentes = [];

foreach ($tablas as $tabla) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            $tablas_existentes[] = $tabla;
            echo "<p style='color: green;'>✅ Tabla '$tabla' existe</p>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $total = $stmt->fetch()['total'];
            echo "<p>&nbsp;&nbsp;&nbsp;📊 Total de registros: $total</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabla '$tabla' NO existe</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error verificando tabla '$tabla': " . $e->getMessage() . "</p>";
    }
}

// Si no existen las tablas, mostrar script de creación
if (count($tablas_existentes) < 3) {
    echo "<h3>🛠️ Crear tablas faltantes</h3>";
    echo "<p>Ejecute el siguiente SQL en su base de datos:</p>";
    echo "<pre style='background: #f8f9fa; padding: 1rem; border-radius: 5px;'>";
    
    if (!in_array('pacientes', $tablas_existentes)) {
        echo "
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    fecha_nacimiento DATE
);

INSERT INTO pacientes (nombre, correo, telefono, fecha_nacimiento) VALUES
('Pedro Gómez', 'pedro.gomez@email.com', '0991234567', '1990-05-15'),
('Lucía Ramos', 'lucia.ramos@email.com', '0987654321', '1985-11-30'),
('María González', 'maria.gonzalez@email.com', '0976543210', '1992-03-20');

";
    }
    
    if (!in_array('medicos', $tablas_existentes)) {
        echo "
CREATE TABLE IF NOT EXISTS medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    tarifa_por_hora DECIMAL(10,2) NOT NULL
);

INSERT INTO medicos (nombre, especialidad, tarifa_por_hora) VALUES
('Dr. Juan Pérez', 'Cardiología', 50.00),
('Dra. Ana Torres', 'Pediatría', 40.00),
('Dr. Roberto Silva', 'Neurología', 60.00),
('Dra. Carmen Vásquez', 'Ginecología', 45.00),
('Dr. Miguel Morales', 'Medicina General', 35.00);

";
    }
    
    if (!in_array('citas', $tablas_existentes)) {
        echo "
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    medico_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion INT NOT NULL,
    costo_total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'programada',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE
);

";
    }
    
    echo "</pre>";
}

echo "<h3>🔗 Enlaces de prueba</h3>";
echo "<ul>";
echo "<li><a href='../pacientes/listar.php'>👤 Ver Pacientes</a></li>";
echo "<li><a href='../medicos/listar.php'>👨‍⚕️ Ver Médicos</a></li>";
echo "<li><a href='../citas/listar.php'>📅 Ver Citas</a></li>";
echo "<li><a href='../citas/crear.php'>➕ Crear Cita</a></li>";
echo "<li><a href='../../index.html'>🏠 Inicio</a></li>";
echo "</ul>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 2rem;
    line-height: 1.6;
}
pre {
    font-size: 0.9rem;
    max-height: 400px;
    overflow-y: auto;
}
</style>
