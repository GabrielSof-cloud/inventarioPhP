<?php
// ===============================
// CONEXIÓN
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

// ===============================
// VALIDAR ID
// ===============================
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: Descartado.php");
    exit;
}

// Si viene por GET (primera vez)
$id = $_GET['id'] ?? $_POST['id'];

// ===============================
// PROCESAR ELIMINACIÓN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {

    $sqlDelete = "DELETE FROM descarto WHERE id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        header("Location: Descartado.php");
        exit;
    } else {
        $error = "❌ Error al eliminar el registro";
    }
}

// ===============================
// OBTENER REGISTRO PARA MOSTRAR
// ===============================
$sql = "SELECT * FROM descarto WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: Descartado.php");
    exit;
}

$equipo = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8">
    <title>Eliminar Equipo</title>
</head>
<body>

<h2>Eliminar Equipo</h2>

<!-- TABLA CON EL REGISTRO -->
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
            <th>Fecha Descarte</th>
            <th>Motivo</th>
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
            <td><?php echo htmlspecialchars($equipo['fecha_descarto']); ?></td>
            <td><?php echo htmlspecialchars($equipo['motivo']); ?></td>
        </tr>
    </tbody>
</table>


<!-- MENSAJE DE ADVERTENCIA -->
<h1>⚠️ ¿Seguro que quieres borrarlo?</h1>

<!-- FORMULARIO DE CONFIRMACIÓN -->
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">
    <button type="submit" name="confirmar">Continuar</button>
    <a href="Descartado.php">Cancelar</a>
</form>

<?php
if (isset($error)) {
    echo "<p>$error</p>";
}
?>

</body>
</html>
