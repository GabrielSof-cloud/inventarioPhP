<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: Loging.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: CRUD/dashboard.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$e = $res->fetch_assoc();
$stmt->close();

if (!$e) { echo "Equipo no encontrado."; exit; }

$qrPath = !empty($e['qrcode_file']) ? 'qrcodes/' . $e['qrcode_file'] : null;
?>
<!doctype html>
<html lang="es">
<head>
  <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <title>Equipo <?=htmlspecialchars($e['serie'])?> - VAULT</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<div class="vault-container">
    <aside class="vault-sidebar">
        <h2>VAULT</h2>
        <ul>
            <li><a href="CRUD/dashboard.php">Dashboard</a></li>
            <li><a href="CRUD/create_form.php">Agregar Equipo</a></li>
            <li><a href="CRUD/Descartado.php">Descartados</a></li>
            <li><a href="CRUD/movidos.php">Trazabilidad</a></li>
        </ul>
    </aside>

    <main class="vault-main">
        <header class="vault-header">
            <h1>Detalles del Equipo</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?> 
                <a href="logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <div class="vault-card">
                <div style="border-bottom: 2px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="color: var(--primary-blue); margin: 0;">Ficha Técnica: <?=htmlspecialchars($e['serie'])?></h2>
                    <span style="background-color: #e8f4fd; color: #004B87; padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 14px;">ID: <?= $e['id'] ?></span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div style="background-color: #f8fbff; padding: 10px 15px; border-radius: 6px; border: 1px solid #e1e8ed;">
                        <strong style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Modelo</strong><br>
                        <span style="font-size: 16px; color: var(--text-dark); font-weight: 500;"><?=htmlspecialchars($e['modelo'])?></span>
                    </div>
                    
                    <div style="background-color: #f8fbff; padding: 10px 15px; border-radius: 6px; border: 1px solid #e1e8ed;">
                        <strong style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Usuario Asignado</strong><br>
                        <span style="font-size: 16px; color: var(--text-dark); font-weight: 500;"><?=htmlspecialchars($e['usuario'])?></span>
                    </div>

                    <div style="background-color: #f8fbff; padding: 10px 15px; border-radius: 6px; border: 1px solid #e1e8ed;">
                        <strong style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Departamento</strong><br>
                        <span style="font-size: 16px; color: var(--text-dark); font-weight: 500;"><?=htmlspecialchars($e['departamento'])?></span>
                    </div>

                    <div style="background-color: #f8fbff; padding: 10px 15px; border-radius: 6px; border: 1px solid #e1e8ed;">
                        <strong style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Ubicación</strong><br>
                        <span style="font-size: 16px; color: var(--text-dark); font-weight: 500;"><?=htmlspecialchars($e['ubicacion'])?></span>
                    </div>

                    <div style="grid-column: span 2; background-color: #f8fbff; padding: 10px 15px; border-radius: 6px; border: 1px solid #e1e8ed;">
                        <strong style="color: var(--text-muted); font-size: 12px; text-transform: uppercase;">Observaciones</strong><br>
                        <span style="font-size: 15px; color: var(--text-dark);"><?=nl2br(htmlspecialchars($e['observaciones']))?></span>
                    </div>
                </div>

                <?php if (!empty($e['archivo'])): ?>
                    <div style="margin-bottom: 20px; padding: 15px; background-color: #e8f4fd; border-radius: 8px; border-left: 4px solid var(--accent-blue);">
                        <strong style="color: var(--primary-blue);">📎 Documento adjunto:</strong> 
                        <a href="uploads/<?=urlencode($e['archivo'])?>" style="color: var(--accent-blue); text-decoration: none; font-weight: bold; margin-left: 10px;" target="_blank"><?=htmlspecialchars($e['archivo'])?></a>
                    </div>
                <?php endif; ?>

                <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="CRUD/dashboard.php" class="btn" style="background-color: var(--text-muted); color: white;">Volver</a>
                    <a href="equipo_form.php?id=<?= $e['id'] ?>" class="btn btn-primary" style="background-color: #17a2b8;">✏️ Editar</a>
                    
                    <form method="post" action="delete_equipo.php?id=<?= $e['id'] ?>" style="display:inline; margin: 0;" onsubmit="return confirm('¿Está seguro de que desea eliminar este equipo del sistema?')">
                        <button type="submit" class="btn btn-danger">🗑️ Eliminar</button>
                    </form>
                </div>
            </div>

            <div class="vault-card" style="text-align: center; height: fit-content;">
                <h3 style="color: var(--primary-blue); margin-bottom: 20px; font-size: 18px;">Código QR</h3>
                
                <?php if ($qrPath && file_exists(__DIR__ . '/' . $qrPath)): ?>
                    <div style="padding: 15px; border: 2px dashed var(--border-color); border-radius: 12px; display: inline-block; background-color: white; margin-bottom: 20px;">
                        <img src="<?= htmlspecialchars($qrPath) ?>" alt="QR" width="180" style="display: block;">
                    </div>
                <?php else: ?>
                    <div style="padding: 30px 15px; border: 2px dashed var(--border-color); border-radius: 12px; background-color: #f8fbff; margin-bottom: 20px; color: var(--text-muted);">
                        <p style="margin: 0;">🚫 No hay QR generado</p>
                    </div>
                <?php endif; ?>

                <form method="post" action="generar_qr.php?id=<?= $e['id'] ?>" style="margin: 0;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 8px;">
                        🔄 Generar / Actualizar QR
                    </button>
                </form>
            </div>

        </div>
    </main>
</div>

</body>
</html>