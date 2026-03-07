<?php
// Asegurar que la sesión esté iniciada para la barra superior
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// CONEXIÓN
// ===============================
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

// ===============================
// VALIDAR ID
// ===============================
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: Descartado.php");
    exit;
}

// Si viene por GET (primera vez)
$id = $_GET['id'] ?? $_POST['id'];

// ===============================
// PROCESAR ELIMINACIÓN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {

    $sqlDelete = "DELETE FROM descarto WHERE id = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        header("Location: Descartado.php");
        exit;
    } else {
        $error = "❌ Error al eliminar el registro";
    }
}

// ===============================
// OBTENER REGISTRO PARA MOSTRAR
// ===============================
$sql = "SELECT * FROM descarto WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: Descartado.php");
    exit;
}

$equipo = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8">
    <title>Eliminar Equipo Definitivamente - VAULT</title>
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
            <h1>Eliminación Definitiva</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?> 
                <a href="/logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            
            <?php if (isset($error)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 25px; border: 1px solid #f5c6cb; text-align: center; font-weight: bold;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: var(--danger-red); font-size: 26px; margin-bottom: 10px;">⚠️ ¿Seguro que quieres borrar este registro de forma permanente?</h2>
                <p style="color: var(--text-muted); font-size: 16px;">Esta acción eliminará el equipo de la base de datos por completo y no se podrá deshacer.</p>
            </div>

            <div style="overflow-x: auto; margin-bottom: 40px; border-radius: 8px; border: 1px solid var(--border-color);">
                <table class="vault-table" style="margin-bottom: 0;">
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $equipo['id']; ?></td>
                            <td><?php echo htmlspecialchars($equipo['serie']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['departamento']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['ubicacion']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['observaciones']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['fecha_descarto']); ?></td>
                            <td><?php echo htmlspecialchars($equipo['motivo']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; border-top: 1px solid var(--border-color); padding-top: 25px;">
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">
                    <a href="Descartado.php" class="btn" style="background-color: var(--text-muted); color: white; margin-right: 15px; padding: 12px 30px;">Cancelar</a>
                    <button type="submit" name="confirmar" class="btn btn-danger" style="padding: 12px 30px;">Sí, Eliminar Definitivamente</button>
                </form>
            </div>
            
        </div>
    </main>
</div>

</body>
</html>