<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
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
<meta charset="UTF-8">
<title>Historial de Movimiento</title>
</head>
<body>

<h2>Estado Actual (AHORA)</h2>

<table border="1">
<tr>
<th>ID</th>
<th>Serie</th>
<th>Modelo</th>
<th>Usuario</th>
<th>Departamento</th>
<th>Ubicación</th>
<th>Observaciones</th>
</tr>
<tr>
<td><?php echo $equipo_actual['id']; ?></td>
<td><?php echo htmlspecialchars($equipo_actual['serie']); ?></td>
<td><?php echo htmlspecialchars($equipo_actual['modelo']); ?></td>
<td><?php echo htmlspecialchars($equipo_actual['usuario']); ?></td>
<td><?php echo htmlspecialchars($equipo_actual['departamento']); ?></td>
<td><?php echo htmlspecialchars($equipo_actual['ubicacion']); ?></td>
<td><?php echo htmlspecialchars($equipo_actual['observaciones']); ?></td>
</tr>
</table>



<?php if ($equipo_anterior): ?>

<h2>Estado Anterior (ANTES)</h2>

<table border="1">
<tr>
<th>Serie</th>
<th>Modelo</th>
<th>Usuario</th>
<th>Departamento</th>
<th>Ubicación</th>
<th>Observaciones</th>
<th>Fecha</th>
<th>Tipo</th>
</tr>
<tr>
<td><?php echo htmlspecialchars($equipo_anterior['serie']); ?></td>
<td><?php echo htmlspecialchars($equipo_anterior['modelo']); ?></td>
<td><?php echo htmlspecialchars($equipo_anterior['usuario']); ?></td>
<td><?php echo htmlspecialchars($equipo_anterior['departamento']); ?></td>
<td><?php echo htmlspecialchars($equipo_anterior['ubicacion']); ?></td>
<td><?php echo htmlspecialchars($equipo_anterior['observaciones']); ?></td>
<td><?php echo $equipo_anterior['fecha_movimiento']; ?></td>
<td><?php echo $equipo_anterior['tipo_movimiento']; ?></td>
</tr>
</table>

<hr>
<hr>

<?php endif; ?>

<h2>Historial Completo</h2>

<a href="movidos.php">Ver todo el historial</a>

</body>
</html>