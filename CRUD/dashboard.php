<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $like = "%{$q}%";
    $sql = "SELECT id, serie, modelo, usuario, departamento, ubicacion FROM equipos WHERE id LIKE ? OR serie LIKE ? OR modelo LIKE ? OR usuario LIKE ? OR departamento LIKE ? OR ubicacion LIKE ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssss', $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $results = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $res = $conn->query("SELECT id, serie, modelo, usuario, departamento, ubicacion FROM equipos ORDER BY id DESC");
    if ($res) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Dashboard - Inventario</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<header>
<h1>Inventario</h1>
<div class="user">Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> — <a href="/logout.php">Salir</a></div>
</header>

<div class="container">
<form method="get" action="dashboard.php" class="search-form">
<input type="text" name="q" placeholder="Buscar..." value="<?php echo htmlspecialchars($q); ?>">
<button type="submit">Buscar</button>
<a href="equipo_form.php" class="btn">Agregar equipo</a>
<a href="delee_equipo.php" class="btn">eliminar equipo</a>
</form>

<table class="table">
<thead>
<tr>
<th>ID</th>
<th>Serie</th>
<th>Modelo</th>
<th>Usuario</th>
<th>Departamento</th>
<th>Ubicación</th>
<th>Descartar</th>
<th>Editar</th>
<th>Eliminar</th>
</tr>
</thead>
<tbody>
<?php if (count($results) === 0): ?>
<tr><td colspan="6">No se encontraron registros.</td></tr>
<?php else: ?>
<?php foreach ($results as $r): ?>
<tr>
<td><?php echo (int)$r['id']; ?></td>
<td><?php echo htmlspecialchars($r['serie']); ?></td>
<td><?php echo htmlspecialchars($r['modelo']); ?></td>
<td><?php echo htmlspecialchars($r['usuario']); ?></td>
<td><?php echo htmlspecialchars($r['departamento']); ?></td>
<td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
<td><a href="Descartado.php?id=<?php echo (int)$r['id']; ?>">Descartar</a></td>
<td><a href="edit_equipo.php?id=<?php echo (int)$r['id']; ?>">Editar</a></td>
<td><a href="delete_equipo.php?id=<?php echo (int)$r['id']; ?>">Eliminar</a></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>
</body>
</html>
    