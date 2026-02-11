<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}
//Antes de continuar, los bloques de codigo comentados componen todo la funcion de
//restaurar un equipo descartado, pero por ahora se ha decidido eliminar esta función, 
//por lo que se han comentado para evitar confusiones futuras.


$q = trim($_GET['q'] ?? '');
$results = [];

/* =========================
   RESTAURAR EQUIPO
========================= */
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_id'])) {

//     $idRestore = (int)$_POST['restore_id'];

//     // Buscar equipo en descarto
//     $stmt = $conn->prepare("SELECT id, serie, modelo, usuario, departamento, ubicacion, observaciones 
//                             FROM descarto WHERE id = ?");
//     $stmt->bind_param("i", $idRestore);
//     $stmt->execute();
//     $res = $stmt->get_result();
//     $equipo = $res->fetch_assoc();
//     $stmt->close();

//     if ($equipo) {

//         // Insertar nuevamente en equipos
//         $stmtInsert = $conn->prepare("INSERT INTO equipos 
//             (id, serie, modelo, usuario, departamento, ubicacion, observaciones)
//             VALUES (?, ?, ?, ?, ?, ?, ?)");

//         $stmtInsert->bind_param(
//             "issssss",
//             $equipo['id'],
//             $equipo['serie'],
//             $equipo['modelo'],
//             $equipo['usuario'],
//             $equipo['departamento'],
//             $equipo['ubicacion'],
//             $equipo['observaciones']
//         );

//         if ($stmtInsert->execute()) {

//             // Eliminar de descarto
//             $stmtDelete = $conn->prepare("DELETE FROM descarto WHERE id = ?");
//             $stmtDelete->bind_param("i", $idRestore);
//             $stmtDelete->execute();
//             $stmtDelete->close();
//         }

//         $stmtInsert->close();
//     }

//     header("Location: Descartado.php");
//     exit;
// }

/* =========================
   BÚSQUEDA
========================= */

if ($q !== '') {

    $like = "%{$q}%";

    $sql = "SELECT id, serie, modelo, usuario, departamento, ubicacion, observaciones, fecha_descarto, motivo
            FROM descarto
            WHERE id LIKE ?
            OR serie LIKE ?
            OR modelo LIKE ?
            OR usuario LIKE ?
            OR departamento LIKE ?
            OR ubicacion LIKE ?
            OR motivo LIKE ?
            ORDER BY fecha_descarto DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $results = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} else {

    $res = $conn->query("SELECT id, serie, modelo, usuario, departamento, ubicacion, observaciones, fecha_descarto, motivo
                         FROM descarto
                         ORDER BY fecha_descarto DESC");

    if ($res) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Equipos descartados</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>

<header>
<h1>Equipos descartados</h1>
<div class="user">
Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> —
<a href="/logout.php">Salir</a>
</div>
</header>

<div class="container">

<form method="get" action="Descartado.php" class="search-form">
<input type="text" name="q" placeholder="Buscar..." value="<?php echo htmlspecialchars($q); ?>">
<button type="submit">Buscar</button>
<a href="dashboard.php" class="btn">Volver</a>
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
<th>Observaciones</th>
<th>Fecha Descarte</th>
<th>Motivo</th>
<th></th>
<!-- <th>Acción</th> -->
</tr>
</thead>

<tbody>

<?php if (count($results) === 0): ?>
<tr>
<td colspan="10">No hay equipos descartados.</td>
</tr>
<?php else: ?>

<?php foreach ($results as $r): ?>
<tr>
<td><?php echo (int)$r['id']; ?></td>
<td><?php echo htmlspecialchars($r['serie']); ?></td>
<td><?php echo htmlspecialchars($r['modelo']); ?></td>
<td><?php echo htmlspecialchars($r['usuario']); ?></td>
<td><?php echo htmlspecialchars($r['departamento']); ?></td>
<td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
<td><?php echo htmlspecialchars($r['observaciones']); ?></td>
<td><?php echo htmlspecialchars($r['fecha_descarto']); ?></td>
<td><?php echo htmlspecialchars($r['motivo']); ?></td>
<td><a href="delete_equipo.php?id=<?php echo (int)$r['id']; ?>">Eliminar</a></td>

<!-- <td>
<form method="post" style="display:inline;">
<input type="hidden" name="restore_id" value="<?php echo (int)$r['id']; ?>">
<button type="submit" onclick="return confirm('¿Restaurar este equipo?')">
Restaurar
</button>
</form>
</td> -->

</tr>
<?php endforeach; ?>

<?php endif; ?>

</tbody>
</table>

</div>

</body>
</html>