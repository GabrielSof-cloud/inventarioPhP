<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$buscar = trim($_GET['buscar'] ?? '');

$sql = "
SELECT 
    m.*,
    e.serie AS serie_actual,
    e.modelo AS modelo_actual,
    e.usuario AS usuario_actual,
    e.departamento AS departamento_actual,
    e.ubicacion AS ubicacion_actual,
    e.observaciones AS observaciones_actual
FROM movimientos m
LEFT JOIN equipos e ON m.id_equipo = e.id
";

if ($buscar !== '') {
    $sql .= " WHERE 
        m.serie LIKE ? OR
        m.usuario LIKE ? OR
        m.departamento LIKE ?
    ";
}

$sql .= " ORDER BY m.fecha_movimiento DESC";

$stmt = $conn->prepare($sql);

if ($buscar !== '') {
    $like = "%".$buscar."%";
    $stmt->bind_param("sss", $like, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
<meta charset="UTF-8">
<title>Historial Global de Movimientos</title>
</head>
<body>

<h2>Historial Global de Movimientos</h2>

<form method="get">
    <input type="text" name="buscar" placeholder="Buscar..." 
        value="<?php echo htmlspecialchars($buscar); ?>">
    <button type="submit">Buscar</button>
    <a href="dashboard.php">Volver</a>
</form>

<hr>

<?php if ($result->num_rows === 0): ?>
    <p>No hay movimientos registrados.</p>
<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>

<h3>
Movimiento #<?php echo $row['id_movimiento']; ?> 
— <?php echo $row['fecha_movimiento']; ?>
</h3>

<h4>ANTES</h4>

<table border="1">
<tr>
<th>Serie</th>
<th>Modelo</th>
<th>Usuario</th>
<th>Departamento</th>
<th>Ubicación</th>
<th>Observaciones</th>
</tr>
<tr>
<td><?php echo htmlspecialchars($row['serie']); ?></td>
<td><?php echo htmlspecialchars($row['modelo']); ?></td>
<td><?php echo htmlspecialchars($row['usuario']); ?></td>
<td><?php echo htmlspecialchars($row['departamento']); ?></td>
<td><?php echo htmlspecialchars($row['ubicacion']); ?></td>
<td><?php echo htmlspecialchars($row['observaciones']); ?></td>
</tr>
</table>

<br>

<h4>AHORA</h4>

<?php if ($row['serie_actual'] === null): ?>

<p style="color:red;font-weight:bold;">
Equipo descartado o eliminado del sistema.
</p>

<?php else: ?>

<table border="1">
<tr>
<th>Serie</th>
<th>Modelo</th>
<th>Usuario</th>
<th>Departamento</th>
<th>Ubicación</th>
<th>Observaciones</th>
</tr>
<tr>
<td><?php echo htmlspecialchars($row['serie_actual']); ?></td>
<td><?php echo htmlspecialchars($row['modelo_actual']); ?></td>
<td><?php echo htmlspecialchars($row['usuario_actual']); ?></td>
<td><?php echo htmlspecialchars($row['departamento_actual']); ?></td>
<td><?php echo htmlspecialchars($row['ubicacion_actual']); ?></td>
<td><?php echo htmlspecialchars($row['observaciones_actual']); ?></td>
</tr>
</table>

<?php endif; ?>

<hr>

<?php endwhile; ?>

<br>
<a href="dashboard.php">Volver</a>

</body>
</html>