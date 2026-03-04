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
<meta charset="utf-8"><title>Equipo <?=htmlspecialchars($e['serie'])?></title></head>
<body>
<h2>Equipo <?=htmlspecialchars($e['serie'])?></h2>
<ul>
  <li>Modelo: <?=htmlspecialchars($e['modelo'])?></li>
  <li>Usuario: <?=htmlspecialchars($e['usuario'])?></li>
  <li>Departamento: <?=htmlspecialchars($e['departamento'])?></li>
  <li>Ubicación: <?=htmlspecialchars($e['ubicacion'])?></li>
  <li>Observaciones: <?=nl2br(htmlspecialchars($e['observaciones']))?></li>
</ul>

<?php if (!empty($e['archivo'])): ?>
  <p>Archivo: <a href="uploads/<?=urlencode($e['archivo'])?>"><?=htmlspecialchars($e['archivo'])?></a></p>
<?php endif; ?>

<form method="post" action="generar_qr.php?id=<?= $e['id'] ?>" style="display:inline">
  <button type="submit">Generar/Actualizar QR</button>
</form>
<a href="equipo_form.php?id=<?= $e['id'] ?>">Editar</a>
<form method="post" action="delete_equipo.php?id=<?= $e['id'] ?>" style="display:inline" onsubmit="return confirm('Eliminar equipo?')">
  <button type="submit">Eliminar</button>
</form>

<?php if ($qrPath && file_exists(__DIR__ . '/' . $qrPath)): ?>
  <div><h3>QR</h3><img src="<?= htmlspecialchars($qrPath) ?>" alt="QR" width="200"></div>
<?php else: ?>
  <p>No hay QR generado.</p>
<?php endif; ?>

<p><a href="CRUD/dashboard.php">Volver</a></p>
</body>
</html>