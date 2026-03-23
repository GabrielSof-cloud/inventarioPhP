<?php
// Asegurar que la sesión esté iniciada y el usuario autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
    exit;
}

// ===============================
// CONEXIÓN
// ===============================
require_once __DIR__ . '/../DBconn/conexion.php';

// ===============================
// VALIDAR ID
// ===============================
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header("Location: Descartado.php");
    exit;
}

// Si viene por GET (primera vez)
$id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);

// ===============================
// PROCESAR ELIMINACIÓN
// ===============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Petición inválida.';
    } else {
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

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
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
                            }