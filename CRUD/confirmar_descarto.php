<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    die("ID no válido");
}

# 🔹 SI ES POST → HACER DESCARTE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $motivo = trim($_POST['motivo']);

    if ($motivo === '') {
        die("Debe indicar el motivo del descarte.");
    }

    # 1️⃣ Obtener datos del equipo
    $stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipo = $result->fetch_assoc();
    $stmt->close();

    if (!$equipo) {
        die("Equipo no encontrado.");
    }

    # 2️⃣ Insertar en tabla descarto
    $stmt = $conn->prepare("INSERT INTO descarto 
        (id, serie, modelo, usuario, departamento, ubicacion, observaciones, fecha_descarto, motivo)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

    $stmt->bind_param(
        "isssssss",
        $equipo['id'],
        $equipo['serie'],
        $equipo['modelo'],
        $equipo['usuario'],
        $equipo['departamento'],
        $equipo['ubicacion'],
        $equipo['observaciones'],
        $motivo
    );

    $stmt->execute();
    $stmt->close();

    # 3️⃣ Eliminar de equipos
    $stmt = $conn->prepare("DELETE FROM equipos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: Descartado.php");
    exit;
}

# 🔹 SI ES GET → SOLO MOSTRAR EQUIPO
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$equipo = $result->fetch_assoc();
$stmt->close();

if (!$equipo) {
    die("Equipo no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Confirmar Descarte</title>
</head>
<body>

<h2>¿Quieres descartar el siguiente equipo?</h2>

<table>
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
<td><?php echo $equipo['id']; ?></td>
<td><?php echo htmlspecialchars($equipo['serie']); ?></td>
<td><?php echo htmlspecialchars($equipo['modelo']); ?></td>
<td><?php echo htmlspecialchars($equipo['usuario']); ?></td>
<td><?php echo htmlspecialchars($equipo['departamento']); ?></td>
<td><?php echo htmlspecialchars($equipo['ubicacion']); ?></td>
<td><?php echo htmlspecialchars($equipo['observaciones']); ?></td>
</tr>
</tbody>
</table>

<h3>Motivo del descarte</h3>

<form method="post">
    <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">
    <textarea name="motivo" required></textarea><br><br>

    <button type="submit">Continuar</button>
    <a href="dashboard.php">Cancelar</a>
</form>

</body>
</html>