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
    $sql = "SELECT id, serie, registro_bn, modelo, categoria, usuario, departamento, ubicacion, costo_inicial, fecha_adquisicion, vida_util_meses 
            FROM equipos 
            WHERE id LIKE ? OR serie LIKE ? OR registro_bn LIKE ? OR modelo LIKE ? OR categoria LIKE ? OR usuario LIKE ? OR departamento LIKE ? OR ubicacion LIKE ? 
            ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssss', $like, $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();
    $results = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $res = $conn->query("SELECT id, serie, registro_bn, modelo, categoria, usuario, departamento, ubicacion, costo_inicial, fecha_adquisicion, vida_util_meses FROM equipos ORDER BY id DESC");
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
    <style>
        .progress-bar-bg { background-color: #e0e0e0; border-radius: 10px; width: 100%; height: 8px; margin-top: 5px; overflow: hidden; }
        .progress-bar-fill { background-color: #e74c3c; height: 100%; border-radius: 10px; transition: width 0.3s ease; }
        .progress-text { font-size: 11px; font-weight: bold; color: #555; }
        /* Ajustes para evitar que la tabla colapse con tantas columnas */
        .vault-table th, .vault-table td { padding: 8px 6px; font-size: 12px; white-space: nowrap; } 
        /* Hacemos la columna de progreso un poco más ancha */
        .col-progreso { min-width: 120px; }
    </style>
</head>
<body>

<div class="vault-container">
    <aside class="vault-sidebar">
        <h2>VAULT</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="por_departamento.php">Inventario por Depto</a></li>
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
                    <input type="text" name="q" class="vault-form-control" placeholder="Buscar por serie, categoría, departamento, modelo..." value="<?php echo htmlspecialchars($q); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Buscar</button>
            </form>
        </div>

        <div class="vault-card" style="padding: 0; overflow-x: auto;">
            <table class="vault-table" style="min-width: 1600px;"> <thead>
                    <tr>
                        <th>ID</th>
                        <th>Serie</th>
                        <th>Reg. BN</th>
                        <th>Categoría</th>
                        <th>Modelo</th>
                        <th>Usuario</th> <th>Departamento</th>
                        <th>Ubicación</th> <th>Costo</th> 
                        <th>Vida Útil</th> 
                        <th>Depr. Acumulada</th>
                        <th class="col-progreso">Progreso (%)</th>
                        <th>Valor Actual</th> 
                        <th style="text-align: center; position: sticky; right: 0; background: #fff; box-shadow: -2px 0 5px rgba(0,0,0,0.05);">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="14" style="text-align: center; padding: 30px; color: var(--text-muted);">No se encontraron registros.</td>
                    </tr>
                    <?php else: ?>
                    <?php 
                        $fechaHoy = new DateTime();
                        foreach ($results as $r): 
                            
                            $costoInicial = isset($r['costo_inicial']) ? (float)$r['costo_inicial'] : 0;
                            $vidaUtil = !empty($r['vida_util_meses']) ? (int)$r['vida_util_meses'] : 60; 
                            
                            $fechaAdquisicion = !empty($r['fecha_adquisicion']) ? new DateTime($r['fecha_adquisicion']) : $fechaHoy;
                            
                            $diferencia = $fechaHoy->diff($fechaAdquisicion);
                            $mesesTranscurridos = ($diferencia->y * 12) + $diferencia->m;

                            if ($mesesTranscurridos > $vidaUtil) {
                                $mesesTranscurridos = $vidaUtil; 
                            }

                            $depreciacionAcumulada = 0;
                            if ($vidaUtil > 0) {
                                $depreciacionMensual = $costoInicial / $vidaUtil;
                                $depreciacionAcumulada = $depreciacionMensual * $mesesTranscurridos;
                            }

                            $valorActual = $costoInicial - $depreciacionAcumulada;
                            if ($valorActual < 0) $valorActual = 0;

                            $porcentaje = ($costoInicial > 0) ? ($depreciacionAcumulada / $costoInicial) * 100 : 0;
                            
                            // Formatear el nombre del QR exactamente igual que en create_form
                            $serialSeguro = preg_replace('/[^A-Za-z0-9_\-]/', '_', $r['serie']);
                            $rutaQR = 'qrs/qr_' . $serialSeguro . '.png';
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
                        <td>
                            <span style="background-color: #eef2f5; padding: 3px 6px; border-radius: 4px; font-size: 11px; border: 1px solid #d5dbdb;">
                                <?php echo htmlspecialchars($r['categoria'] ?? 'Sin Categoría'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($r['modelo']); ?></td>
                        <td><?php echo htmlspecialchars($r['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($r['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($r['ubicacion']); ?></td>
                        
                        <td style="color: #27ae60; font-weight: 600;">$<?php echo number_format($costoInicial, 2); ?></td>
                        <td style="text-align: center; color: #7f8c8d;"><?php echo $vidaUtil; ?> m</td>
                        <td style="color: #e67e22; font-weight: 600;">$<?php echo number_format($depreciacionAcumulada, 2); ?></td>
                        
                        <td class="col-progreso">
                            <div class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo $porcentaje; ?>%; background-color: <?php echo ($porcentaje >= 100) ? '#7f8c8d' : '#e74c3c'; ?>;"></div>
                            </div>
                        </td>

                        <td style="color: #c0392b; font-weight: bold; font-size: 13px;">$<?php echo number_format($valorActual, 2); ?></td>

                        <td style="text-align: center; white-space: nowrap; position: sticky; right: 0; background: #fff; box-shadow: -2px 0 5px rgba(0,0,0,0.05);">
                            <a href="<?php echo $rutaQR; ?>" download="QR_<?php echo htmlspecialchars($r['serie']); ?>.png" class="btn" style="background-color: #2ecc71; color: white; padding: 4px 8px; font-size: 11px; margin-right: 3px; text-decoration: none; border-radius: 4px;" title="Descargar Código QR">⬇️ QR</a>
                            
                            <a href="edit_equipo.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px; margin-right: 3px;">Editar</a>
                            <a href="mover.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px; margin-right: 3px; background-color: #17a2b8;">Mover</a>
                            <a href="confirmar_descarto.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Descartar</a>
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