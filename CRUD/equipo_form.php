<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/qrcodes/qr.php';


if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$err = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar inputs
    $serie = trim($_POST['serie'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (!$serie || !$modelo) {
        $err = 'Serie y Modelo son obligatorios.';
    } else {
        // Insertar en la base de datos
        $stmt = $conn->prepare("INSERT INTO equipos (serie, modelo, usuario, departamento, ubicacion, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $serie, $modelo, $usuario, $departamento, $ubicacion, $observaciones);
        if ($stmt->execute()) {
           
            $serial = $serie;
            $ruta = 'qrs/qr_'.$serial.'.png';
            generalQR($serial, $ruta, 4);
        header("Location: ver_qr.php?serie=" . urlencode($serial));
          exit;
           
        } else {
            $err = 'Error al guardar el equipo.';
        }
         
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Agregar equipo</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>
<header>
<h1>Inventario</h1>
<div class="user">Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> — <a href="/logout.php">Salir</a></div>
</header>

<div class="form-box">
<h2>Nuevo equipo</h2>
<?php if ($err): ?><p class="error"><?php echo htmlspecialchars($err); ?></p><?php endif; ?>
<?php if ($msg): ?><p class="success"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>

<form method="post" action="">
<label>Serie</label>
<input type="text" name="serie" required>
<label>Modelo</label>
<input type="text" name="modelo" required>
<label>Usuario</label>
<input type="text" name="usuario">
<label>Departamento</label>
<input type="text" name="departamento">
<label>Ubicación</label>
<input type="text" name="ubicacion">
<label>Observaciones</label>
<textarea name="observaciones"></textarea>
<button type="submit">Guardar</button>
</form>
</div>
</body>
</html>


