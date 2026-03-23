<?php
require_once __DIR__ . '/conexion.php';


// Crear tabla usuarios
$conn->query("
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ubicacion_fisica VARCHAR(255),
    activo TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Crear tabla equipos
$conn->query("
CREATE TABLE IF NOT EXISTS equipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    serie VARCHAR(150) UNIQUE,
    modelo VARCHAR(150),
    usuario VARCHAR(150),
    departamento VARCHAR(150),
    ubicacion VARCHAR(255),
    observaciones TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Crear tabla descarto
$conn->query("
CREATE TABLE IF NOT EXISTS descarto (
    id INT PRIMARY KEY,
    serie VARCHAR(150) UNIQUE,
    modelo VARCHAR(150),
    usuario VARCHAR(150),
    departamento VARCHAR(150),
    ubicacion VARCHAR(255),
    observaciones TEXT,
    fecha_descarto DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Crear tabla movimientos
$conn->query("
CREATE TABLE IF NOT EXISTS movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT,
    serie VARCHAR(150),
    modelo VARCHAR(150),
    usuario VARCHAR(150),
    departamento VARCHAR(150),
    ubicacion VARCHAR(255),
    observaciones TEXT,
    fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_movimiento VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Crear admin si no existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$email = 'smailinsantos9@gmail.com';
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $pwd = password_hash('123456789', PASSWORD_DEFAULT);
    $stmt2 = $conn->prepare("INSERT INTO usuarios (nombre, email, password, activo) VALUES (?, ?, ?, 1)");
    $name = 'Administrador';
    $stmt2->bind_param('sss', $name, $email, $pwd);
    $stmt2->execute();
    $stmt2->close();
    echo "Admin creado: smailinsantos9@gmail.com / 123456789";
} else {
    echo "Admin ya existe";
    $stmt->close();
}

echo "Inicialización completada.";
?>