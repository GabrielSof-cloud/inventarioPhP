<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

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
    <title>Mover / Reasignar Equipo - VAULT</title>
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
            <h1>Trazabilidad de Equipos</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre']); ?> 
                <a href="/logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card" style="max-width: 800px; margin: 0 auto;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">
                <h2 style="color: var(--primary-blue); margin: 0;">Reasignar / Mover Equipo</h2>
            </div>

            <div style="background-color: #e8f4fd; color: #004B87; padding: 15px 20px; border-left: 5px solid var(--accent-blue); border-radius: 4px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 5px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 1.2em;">ℹ️</span> Importante
                </h4>
                <p style="margin: 0; font-size: 0.95em;">Modifique únicamente los campos donde ocurrió el cambio. Por ejemplo: si se movió de un departamento a otro, cambie el valor de <strong>Departamento</strong> al nuevo destino. Lo mismo aplica si hubo un cambio de <strong>Ubicación</strong> o de <strong>Usuario responsable</strong>.</p>
            </div>

            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo $equipo['id']; ?>">

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
                        <label>Nuevo Usuario Asignado</label>
                        <input type="text" name="usuario" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['usuario']); ?>">
                    </div>

                    <div class="vault-form-group">
                        <label>Nuevo Departamento</label>
                        <select name="departamento" class="vault-form-control">
                            <option value="">Seleccione un departamento...</option>
                            <?php 
                            $departamentos_std = [
                                'Dirección General',
                                'Administración y Finanzas',
                                'Recursos Humanos',
                                'Tecnología de la Información (TI)',
                                'Operaciones',
                                'Ventas',
                                'Marketing',
                                'Logística / Almacén',
                                'Soporte Técnico',
                                'Mantenimiento',
                                'Otro'
                            ];
                            $depto_actual = $equipo['departamento'];
                            $found = false;
                            foreach ($departamentos_std as $depto) {
                                $selected = ($depto_actual === $depto) ? 'selected' : '';
                                if ($depto_actual === $depto) $found = true;
                                echo "<option value=\"" . htmlspecialchars($depto) . "\" $selected>" . htmlspecialchars($depto) . "</option>\n";
                            }
                            if ($depto_actual && !$found) {
                                echo "<option value=\"" . htmlspecialchars($depto_actual) . "\" selected>" . htmlspecialchars($depto_actual) . " (Actual)</option>\n";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Nueva Ubicación Física</label>
                        <select name="ubicacion" class="vault-form-control">
                            <option value="">Seleccione una sucursal (Promipyme)...</option>
                            <?php 
                            $sucursales_std = [
                                'Sede Principal (Santo Domingo)', 'Manoguayabo (Santo Domingo)', 'Santo Domingo Este', 'Santo Domingo Norte',
                                'Santiago', 'La Vega', 'San Francisco de Macorís', 'Puerto Plata', 'Azua',
                                'San Juan de la Maguana', 'Barahona', 'San Pedro de Macorís', 'La Romana',
                                'Higüey', 'San Cristóbal', 'Baní'
                            ];
                            $ubicacion_actual = $equipo['ubicacion'];
                            $found_ub = false;
                            foreach ($sucursales_std as $suc) {
                                $selected_ub = ($ubicacion_actual === $suc) ? 'selected' : '';
                                if ($ubicacion_actual === $suc) $found_ub = true;
                                echo "<option value=\"" . htmlspecialchars($suc) . "\" $selected_ub>" . htmlspecialchars($suc) . "</option>\n";
                            }
                            if ($ubicacion_actual && !$found_ub) {
                                echo "<option value=\"" . htmlspecialchars($ubicacion_actual) . "\" selected>" . htmlspecialchars($ubicacion_actual) . " (Actual)</option>\n";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Observaciones del Movimiento</label>
                        <textarea name="observaciones" class="vault-form-control" rows="3" style="resize: vertical;"><?php echo htmlspecialchars($equipo['observaciones']); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white; margin-right: 10px;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 30px; background-color: #17a2b8;">Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>