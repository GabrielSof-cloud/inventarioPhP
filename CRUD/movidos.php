<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
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
    <title>Trazado de Equipos - VAULT</title>
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
            <h1>Historial Global de Movimientos</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="buscar" class="vault-form-control" placeholder="Buscar movimientos por serie, usuario o departamento..." value="<?php echo htmlspecialchars($buscar); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white;">Volver al Dashboard</a>
            </form>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="vault-card" style="text-align: center; padding: 40px; color: var(--text-muted);">
                <h3>No hay movimientos registrados.</h3>
                <p>Aún no se ha reasignado ni modificado ningún equipo.</p>
            </div>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="vault-card" style="margin-bottom: 30px; border-left: 5px solid var(--accent-blue);">
                
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">
                    <h3 style="color: var(--primary-blue); margin: 0;">Movimiento #<?php echo $row['id_movimiento']; ?></h3>
                    <span style="background-color: #e8f4fd; color: #004B87; padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                        📅 <?php echo $row['fecha_movimiento']; ?>
                    </span>
                </div>

                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-size: 13px;">🔴 Datos Anteriores</h4>
                    <div style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color);">
                        <table class="vault-table" style="margin: 0; background-color: #fcfcfc;">
                            <thead>
                                <tr>
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
                                    <td><?php echo htmlspecialchars($row['serie']); ?></td>
                                    <td><?php echo htmlspecialchars($row['modelo']); ?></td>
                                    <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($row['departamento']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($row['observaciones']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h4 style="color: var(--text-dark); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; font-size: 13px;">🟢 Estado Actual</h4>
                    
                    <?php if ($row['serie_actual'] === null): ?>
                        <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb; font-weight: 600; display: inline-block;">
                            ⚠️ Este equipo ha sido descartado o eliminado del sistema.
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--border-color);">
                            <table class="vault-table" style="margin: 0;">
                                <thead>
                                    <tr>
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
                                        <td><?php echo htmlspecialchars($row['serie_actual']); ?></td>
                                        <td><?php echo htmlspecialchars($row['modelo_actual']); ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario_actual']); ?></td>
                                        <td><?php echo htmlspecialchars($row['departamento_actual']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ubicacion_actual']); ?></td>
                                        <td><?php echo htmlspecialchars($row['observaciones_actual']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        <?php endwhile; ?>

    </main>
</div>

</body>
</html>