<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    die("ID no válido");
}

# 🔹 SI ES POST → HACER DESCARTE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $motivo = trim($_POST['motivo']);

    if ($motivo === '') {
        die("Debe indicar el motivo del descarte.");
    }

    # 1️⃣ Obtener datos del equipo
    $stmt = $conn->prepare("SELECT * FROM equipos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $equipo = $result->fetch_assoc();
    $stmt->close();

    if (!$equipo) {
        die("Equipo no encontrado.");
    }

    # 2️⃣ Insertar en tabla descarto
    $stmt = $conn->prepare("INSERT INTO descarto 
        (id, serie, modelo, usuario, departamento, ubicacion, observaciones, fecha_descarto, motivo)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

    $stmt->bind_param(
        "isssssss",
        $equipo['id'],
        $equipo['serie'],
        $equipo['modelo'],
        $equipo['usuario'],
        $equipo['departamento'],
        $equipo['ubicacion'],
        $equipo['observaciones'],
        $motivo
    );

    $stmt->execute();
    $stmt->close();

    # 3️⃣ Eliminar de equipos
    $stmt = $conn->prepare("DELETE FROM equipos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: Descartado.php");
    exit;
}

# 🔹 SI ES GET → SOLO MOSTRAR EQUIPO
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
    <title>Confirmar Descarte - VAULT</title>
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
            <h1>Confirmar Descarte</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            <h3 style="color: var(--danger-red); margin-bottom: 20px;">¿Estás seguro de que deseas descartar este equipo?</h3>
            
            <div style="overflow-x: auto; margin-bottom: 30px; border-radius: 8px; border: 1px solid var(--border-color);">
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
                        </tr>
                    </tbody>
                </table>
            </div>

            <form method="post">
                <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">
                
                <div class="vault-form-group">
                    <label for="motivo">Motivo del descarte <span style="color: var(--danger-red);">*</span></label>
                    <textarea name="motivo" id="motivo" class="vault-form-control" rows="4" required placeholder="Especifique la razón por la que se descarta este equipo (ej. Daño irreparable, obsolescencia...)" style="resize: vertical;"></textarea>
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn btn-danger">Confirmar Descarte</button>
                    <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white; margin-left: 10px;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>