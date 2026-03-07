<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Loging.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    die("ID no válido");
}

# 🔹 SI ES POST → HACER UPDATE CON TRAZABILIDAD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $serie = trim($_POST['serie']);
    $modelo = trim($_POST['modelo']);
    $usuario = trim($_POST['usuario']);
    $departamento = trim($_POST['departamento']);
    $ubicacion = trim($_POST['ubicacion']);
    $observaciones = trim($_POST['observaciones']);

    if ($serie === '' || $modelo === '') {
        die("Campos obligatorios vacíos.");
    }

    # 🔥 INICIAR TRANSACCIÓN
    $conn->begin_transaction();

    try {

        # 1️⃣ COPIAR ESTADO ACTUAL A movimientos
        $stmt = $conn->prepare("
            INSERT INTO movimientos 
            (id_equipo, serie, modelo, usuario, departamento, ubicacion, observaciones, tipo_movimiento)
            SELECT 
                id, serie, modelo, usuario, departamento, ubicacion, observaciones, 'ACTUALIZADO'
            FROM equipos
            WHERE id = ?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        # 2️⃣ HACER UPDATE
        $stmt = $conn->prepare("
            UPDATE equipos 
            SET serie=?, modelo=?, usuario=?, departamento=?, ubicacion=?, observaciones=?
            WHERE id=?
        ");

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

        $stmt->execute();
        $stmt->close();

        # 🔥 CONFIRMAR
        $conn->commit();

        header("Location: movimiento.php?id=".$id);
        exit;

    } catch (Exception $e) {

        $conn->rollback();
        die("Error en la actualización.");
    }
}

# 🔹 SI ES GET → MOSTRAR FORMULARIO
$stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$equipo = $result->fetch_assoc();
$stmt->close();

if (!$equipo) {
    die("Equipo no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
<meta charset="UTF-8">
<title>mover Equipo</title>
</head>
<body>

<h2>Trazacion de Equipo</h2>

<form method="post">

<input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">

Serie:<br>
<input type="text" name="serie" value="<?php echo htmlspecialchars($equipo['serie']); ?>"><br><br>

Modelo:<br>
<input type="text" name="modelo" value="<?php echo htmlspecialchars($equipo['modelo']); ?>"><br><br>

Usuario:<br>
<input type="text" name="usuario" value="<?php echo htmlspecialchars($equipo['usuario']); ?>"><br><br>

Departamento:<br>
<input type="text" name="departamento" value="<?php echo htmlspecialchars($equipo['departamento']); ?>"><br><br>

Ubicación:<br>
<input type="text" name="ubicacion" value="<?php echo htmlspecialchars($equipo['ubicacion']); ?>"><br><br>

Observaciones:<br>
<textarea name="observaciones"><?php echo htmlspecialchars($equipo['observaciones']); ?></textarea><br><br>

<button type="submit">Actualizar</button>
<a href="dashboard.php">Cancelar</a>

</form>

<h1>Importante</h1>
<p>modifique los campos donde ocurrio el cambio de este equipo ejemplo: si se movio de un departamento a otro, cambie el valor de departamento al nuevo departamento. Lo mismo si fue de ubicacion o de usuario</p>
</body>
</html>