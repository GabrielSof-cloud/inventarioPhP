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
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8" />
    <title>Editar Equipo - VAULT</title>
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
            <h1>Gestión de Inventario</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="/logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card" style="max-width: 800px; margin: 0 auto;">
            <h2 style="color: var(--primary-blue); margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
                Editar Equipo: <?php echo htmlspecialchars($equipo['serie']); ?>
            </h2>

            <?php if ($err): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endif; ?>

            <?php if ($msg): ?>
                <div style="background-color: #d4edda; color: #155724; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <?php echo htmlspecialchars($msg); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="vault-form-group">
                        <label>Serie <span style="color: var(--danger-red);">*</span></label>
                        <input type="text" name="serie" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['serie']); ?>" required>
                    </div>
                    
                    <div class="vault-form-group">
                        <label>Modelo <span style="color: var(--danger-red);">*</span></label>
                        <input type="text" name="modelo" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['modelo']); ?>" required>
                    </div>

                    <div class="vault-form-group">
                        <label>Usuario Asignado</label>
                        <input type="text" name="usuario" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['usuario']); ?>">
                    </div>

                    <div class="vault-form-group">
                        <label>Departamento</label>
                        <input type="text" name="departamento" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['departamento']); ?>">
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Ubicación Física</label>
                        <input type="text" name="ubicacion" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['ubicacion']); ?>">
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="vault-form-control" rows="3" style="resize: vertical;"><?php echo htmlspecialchars($equipo['observaciones']); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white; margin-right: 10px;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 30px;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>