-- Base de datos para Sistema de Citas Médicas
-- Ejecutar estas consultas en tu base de datos MySQL

-- Tabla de Pacientes
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    fecha_nacimiento DATE
);

-- Tabla de Médicos
CREATE TABLE IF NOT EXISTS medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    tarifa_por_hora DECIMAL(10,2) NOT NULL
);

-- Tabla de Citas (con cálculos en PHP)
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    medico_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion INT NOT NULL, -- en minutos, calculado en PHP
    costo_total DECIMAL(10,2) NOT NULL, -- calculado en PHP
    estado VARCHAR(20) DEFAULT 'programada',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (medico_id) REFERENCES medicos(id) ON DELETE CASCADE
);

-- Insertar pacientes de ejemplo
INSERT INTO pacientes (nombre, correo, telefono, fecha_nacimiento) VALUES
('Pedro Gómez', 'pedro.gomez@email.com', '0991234567', '1990-05-15'),
('Lucía Ramos', 'lucia.ramos@email.com', '0987654321', '1985-11-30'),
('María González', 'maria.gonzalez@email.com', '0976543210', '1992-03-20'),
('Carlos López', 'carlos.lopez@email.com', '0965432109', '1988-07-12'),
('Ana Rodríguez', 'ana.rodriguez@email.com', '0954321098', '1995-09-08');

-- Insertar médicos de ejemplo
INSERT INTO medicos (nombre, especialidad, tarifa_por_hora) VALUES
('Dr. Juan Pérez', 'Cardiología', 50.00),
('Dra. Ana Torres', 'Pediatría', 40.00),
('Dr. Roberto Silva', 'Neurología', 60.00),
('Dra. Carmen Vásquez', 'Ginecología', 45.00),
('Dr. Miguel Morales', 'Medicina General', 35.00);

-- Insertar citas de ejemplo (duración y costo calculados en PHP)
INSERT INTO citas (paciente_id, medico_id, fecha, hora_inicio, hora_fin, duracion, costo_total, estado) VALUES
(1, 1, '2025-08-15', '09:00:00', '10:00:00', 60, 50.00, 'programada'),
(2, 2, '2025-08-16', '14:00:00', '14:30:00', 30, 20.00, 'programada'),
(3, 3, '2025-08-17', '10:00:00', '11:30:00', 90, 90.00, 'programada'),
(4, 4, '2025-08-18', '15:00:00', '16:00:00', 60, 45.00, 'programada'),
(5, 5, '2025-08-19', '08:00:00', '08:45:00', 45, 26.25, 'programada');

-- Consultas útiles para verificar los datos

-- Ver todos los pacientes
-- SELECT * FROM pacientes;

-- Ver todos los médicos
-- SELECT * FROM medicos;

-- Ver todas las citas con información completa
-- SELECT 
--     c.id,
--     c.fecha,
--     c.hora_inicio,
--     c.hora_fin,
--     c.duracion,
--     c.costo_total,
--     c.estado,
--     p.nombre AS paciente,
--     m.nombre AS medico,
--     m.especialidad
-- FROM citas c
-- JOIN pacientes p ON c.paciente_id = p.id
-- JOIN medicos m ON c.medico_id = m.id
-- ORDER BY c.fecha DESC, c.hora_inicio DESC;

-- Estadísticas del sistema
-- SELECT 
--     (SELECT COUNT(*) FROM pacientes) as total_pacientes,
--     (SELECT COUNT(*) FROM medicos) as total_medicos,
--     (SELECT COUNT(*) FROM citas) as total_citas,
--     (SELECT SUM(costo_total) FROM citas) as ingresos_totales;
