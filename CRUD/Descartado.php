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
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8" />
    <title>Equipos Descartados - VAULT</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="vault-container">
    <aside class="vault-sidebar">
        <h2>VAULT</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="create_form.php">Agregar Equipo</a></li>
            <li><a href="Descartado.php">Descartados</a></li>
            <li><a href="movidos.php">Trazabilidad</a></li>
        </ul>
    </aside>

    <main class="vault-main">
        <header class="vault-header">
            <h1>Equipos Descartados</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="/logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            <form method="get" action="Descartado.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="q" class="vault-form-control" placeholder="Buscar descartados por serie, modelo, motivo..." value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white;">Volver al Dashboard</a>
            </form>
        </div>

        <div class="vault-card" style="padding: 0; overflow: hidden; overflow-x: auto;">
            <table class="vault-table">
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
                        <th style="text-align: center;">Acciones</th>
                        </tr>
                </thead>
                <tbody>
                    <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">No hay equipos descartados.</td>
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
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="delete_equipo.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Eliminar</a>
                        </td>

                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>