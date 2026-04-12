<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    $like = "%{$q}%";
    // Se agregaron los campos de depreciación al SELECT
    $sql = "SELECT id, serie, registro_bn, modelo, usuario, departamento, ubicacion, costo_inicial, fecha_adquisicion, tasa_depreciacion_mensual FROM equipos WHERE id LIKE ? OR serie LIKE ? OR registro_bn LIKE ? OR modelo LIKE ? OR usuario LIKE ? OR departamento LIKE ? OR ubicacion LIKE ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssss', $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $results = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Se agregaron los campos de depreciación al SELECT
    $res = $conn->query("SELECT id, serie, registro_bn, modelo, usuario, departamento, ubicacion, costo_inicial, fecha_adquisicion, tasa_depreciacion_mensual FROM equipos ORDER BY id DESC");
    if ($res) {
        $results = $res->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - VAULT Inventario</title>
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
            <h1>Panel de Control</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Desconocido'); ?> 
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            <form method="get" action="dashboard.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="q" class="vault-form-control" placeholder="Buscar por serie, modelo, departamento..." value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>

        <div class="vault-card" style="padding: 0; overflow-x: auto;">
            <table class="vault-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serie</th>
                        <th>Bienes Nacionales</th>
                        <th>Modelo</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Ubicación</th>
                        <th>Costo</th> <th>Valor Actual</th> <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: var(--text-muted);">No se encontraron registros.</td>
                    </tr>
                    <?php else: ?>
                    <?php 
                        $fechaHoy = new DateTime(); // Tomamos la fecha actual una sola vez fuera del ciclo
                        foreach ($results as $r): 
                            
                            // LÓGICA DE DEPRECIACIÓN
                            $costoInicial = isset($r['costo_inicial']) ? (float)$r['costo_inicial'] : 0;
                            $tasa = isset($r['tasa_depreciacion_mensual']) ? (float)$r['tasa_depreciacion_mensual'] : 0.05;
                            
                            // Prevenir errores si hay equipos antiguos sin fecha
                            $fechaAdquisicion = !empty($r['fecha_adquisicion']) ? new DateTime($r['fecha_adquisicion']) : $fechaHoy;
                            
                            $diferencia = $fechaHoy->diff($fechaAdquisicion);
                            $mesesTranscurridos = ($diferencia->y * 12) + $diferencia->m;

                            $depreciacionTotal = $costoInicial * $tasa * $mesesTranscurridos;
                            $valorActual = $costoInicial - $depreciacionTotal;
                            
                            // Evitar que el valor quede en negativo
                            if ($valorActual < 0) {
                                $valorActual = 0;
                            }
                    ?>
                    <tr>
                        <td><?php echo (int)$r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['serie']); ?></td>
                        <td>
                            <?php if(!empty($r['registro_bn'])): ?>
                                <span style="background-color: #0d47a1; color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold;">BN-<?php echo htmlspecialchars($r['registro_bn']); ?></span>
                            <?php else: ?>
                                <span style="color: #ccc;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($r['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($r['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($r['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
                        
                        <td style="color: #27ae60; font-weight: 600;">$<?php echo number_format($costoInicial, 2); ?></td>
                        <td style="color: #e74c3c; font-weight: 600;">$<?php echo number_format($valorActual, 2); ?></td>

                        <td style="text-align: center; white-space: nowrap;">
                            <a href="edit_equipo.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; margin-right: 5px;">Editar</a>
                            <a href="mover.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; margin-right: 5px; background-color: #17a2b8;">Mover</a>
                            <a href="confirmar_descarto.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Descartar</a>
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