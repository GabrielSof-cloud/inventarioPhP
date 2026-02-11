<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$err = '';
$msg = '';

// 1️⃣ Obtener ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('ID inválido');
}

// 2️⃣ Si viene POST → actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $serie = trim($_POST['serie'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (!$serie || !$modelo) {
        $err = 'Serie y Modelo son obligatorios.';
    } else {
        $sql = "UPDATE equipos 
                SET serie=?, modelo=?, usuario=?, departamento=?, ubicacion=?, observaciones=? 
                WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $serie,
            $modelo,
            $usuario,
            $departamento,
            $ubicacion,
            $observaciones,
            $id
        );

        if ($stmt->execute()) {
            $msg = 'Equipo actualizado correctamente.';
        } else {
            $err = 'Error al actualizar el equipo.';
        }

        $stmt->close();
    }
}

// 3️⃣ Obtener datos actuales del equipo
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$equipo = $res->fetch_assoc();
$stmt->close();

if (!$equipo) {
    die('Equipo no encontrado');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Editar equipo</title>
<link rel="stylesheet" href="css/styles.css" />
</head>
<body>

<header>
<h1>Inventario</h1>
<div class="user">
Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> —
<a href="/logout.php">Salir</a>
</div>
</header>

<div class="form-box">
<h2>Editar equipo</h2>

<?php if ($err): ?>
<p class="error"><?php echo htmlspecialchars($err); ?></p>
<?php endif; ?>

<?php if ($msg): ?>
<p class="success"><?php echo htmlspecialchars($msg); ?></p>
<?php endif; ?>

<form method="post" action="">
<label>Serie</label>
<input type="text" name="serie" value="<?php echo htmlspecialchars($equipo['serie']); ?>" required>

<label>Modelo</label>
<input type="text" name="modelo" value="<?php echo htmlspecialchars($equipo['modelo']); ?>" required>

<label>Usuario</label>
<input type="text" name="usuario" value="<?php echo htmlspecialchars($equipo['usuario']); ?>">

<label>Departamento</label>
<input type="text" name="departamento" value="<?php echo htmlspecialchars($equipo['departamento']); ?>">

<label>Ubicación</label>
<input type="text" name="ubicacion" value="<?php echo htmlspecialchars($equipo['ubicacion']); ?>">

<label>Observaciones</label>
<textarea name="observaciones"><?php echo htmlspecialchars($equipo['observaciones']); ?></textarea>

<button type="submit">Guardar cambios</button>
<a href="dashboard.php" class="btn">Cancelar</a>
</form>
</div>

</body>
</html>