<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID no válido");
}

# 🔹 1️⃣ OBTENER ESTADO ACTUAL (AHORA)
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$equipo_actual = $result->fetch_assoc();
$stmt->close();

if (!$equipo_actual) {
    die("Equipo no encontrado.");
}

# 🔹 2️⃣ OBTENER ÚLTIMO MOVIMIENTO (ANTES)
$stmt = $conn->prepare("
    SELECT * FROM movimientos 
    WHERE id_equipo = ? 
    ORDER BY fecha_movimiento DESC 
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$equipo_anterior = $result->fetch_assoc();
$stmt->close();

# 🔹 3️⃣ OBTENER HISTORIAL COMPLETO
$stmt = $conn->prepare("
    SELECT * FROM movimientos 
    WHERE id_equipo = ? 
    ORDER BY fecha_movimiento DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$historial = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8">
    <title>Detalle de Movimiento - VAULT</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="vault-container">
    <aside class="vault-sidebar">
        <h2>VAULT</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="create_form.php">Agregar Equipo</a></li>
            <li><a href="Descartado.php">Descarto</a></li>
            <li><a href="movidos.php">Trazado</a></li>
        </ul>
    </aside>

    <main class="vault-main">
        <header class="vault-header">
            <h1>Detalle de Movimiento</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card" style="border-top: 4px solid var(--success-green);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 10px;">
                    🟢 Estado Actual (AHORA)
                </h2>
                <span style="background-color: #d4edda; color: #155724; padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    Vigente
                </span>
            </div>
            
            <div style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color);">
                <table class="vault-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Serie</th>
                            <th>Modelo</th>
                            <th>Usuario</th>
                            <th>Departamento</th>
                            <th>Ubicación</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $equipo_actual['id']; ?></td>
                            <td><?php echo htmlspecialchars($equipo_actual['serie']); ?></td>
                            <td><?php echo htmlspecialchars($equipo_actual['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($equipo_actual['usuario']); ?></td>