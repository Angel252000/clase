-- =============================================
-- Dashboard Académico Universitario
-- Base de Datos: academia_db
-- =============================================

CREATE DATABASE IF NOT EXISTS academia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE academia_db;

-- Tabla ESTUDIANTES
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    correo VARCHAR(100),
    pais VARCHAR(80),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla MATERIAS
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ncurso VARCHAR(150) NOT NULL,
    cuatrimestre VARCHAR(20) NOT NULL,
    anio YEAR NOT NULL,
    docente VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla CALIFICACIONES
CREATE TABLE IF NOT EXISTS calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    devocionales DECIMAL(5,2) DEFAULT 0,
    cotidianos DECIMAL(5,2) DEFAULT 0,
    complementarios DECIMAL(5,2) DEFAULT 0,
    proyectos DECIMAL(5,2) DEFAULT 0,
    nota_final DECIMAL(5,2) GENERATED ALWAYS AS ((devocionales + cotidianos + complementarios + proyectos) / 4) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
);

-- Datos de ejemplo
INSERT INTO estudiantes (nombre, cedula, telefono, correo, pais) VALUES
('María García', '001-1234567-8', '809-555-0001', 'maria@email.com', 'República Dominicana'),
('Carlos Pérez', '002-7654321-9', '829-555-0002', 'carlos@email.com', 'República Dominicana'),
('Ana Martínez', '003-1122334-5', '849-555-0003', 'ana@email.com', 'México');

INSERT INTO materias (ncurso, cuatrimestre, anio, docente) VALUES
('Lenguajes de Cuarta Generación', '1ro', 2025, 'Prof. López'),
('Base de Datos I', '2do', 2025, 'Prof. Ramírez'),
('Programación Web', '1ro', 2025, 'Prof. Torres');

INSERT INTO calificaciones (estudiante_id, materia_id, devocionales, cotidianos, complementarios, proyectos) VALUES
(1, 1, 90, 85, 88, 92),
(2, 1, 75, 80, 70, 85),
(3, 2, 95, 90, 92, 88);
