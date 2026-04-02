<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: /Login.php');
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$err = '';
$msg = '';

// 1️⃣ Obtener ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: dashboard.php?error=ID_invalido');
    exit;
}

// 2️⃣ Si viene POST → actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $err = 'Petición inválida.';
    } else {
        $serie = htmlspecialchars(trim($_POST['serie'] ?? ''));
        $modelo = htmlspecialchars(trim($_POST['modelo'] ?? ''));
        $usuario = htmlspecialchars(trim($_POST['usuario'] ?? ''));
        $departamento = htmlspecialchars(trim($_POST['departamento'] ?? ''));
        $ubicacion = htmlspecialchars(trim($_POST['ubicacion'] ?? ''));
        $observaciones = htmlspecialchars(trim($_POST['observaciones'] ?? ''));

        // NUEVOS CAMPOS DE DEPRECIACIÓN
        $costo_inicial = trim($_POST['costo_inicial'] ?? 0);
        $fecha_adquisicion = trim($_POST['fecha_adquisicion'] ?? date('Y-m-d'));
        $tasa_depreciacion = trim($_POST['tasa_depreciacion'] ?? 0.05);

        if (!$serie || !$modelo) {
            $err = 'Serie y Modelo son obligatorios.';
        } else {
            // Se agregaron los campos de depreciación al UPDATE
            $sql = "UPDATE equipos 
                    SET serie=?, modelo=?, usuario=?, departamento=?, ubicacion=?, observaciones=?, costo_inicial=?, fecha_adquisicion=?, tasa_depreciacion_mensual=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            // 9 strings/decimales ("s") y 1 entero ("i") para el ID
            $stmt->bind_param(
                "sssssssssi",
                $serie,
                $modelo,
                $usuario,
                $departamento,
                $ubicacion,
                $observaciones,
                $costo_inicial,
                $fecha_adquisicion,
                $tasa_depreciacion,
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
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Desconocido'); ?> 
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

            <form method="post" action="" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
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

                    <div class="vault-form-group">
                        <label>Costo Inicial ($) <span style="color: var(--danger-red);">*</span></label>
                        <input type="number" step="0.01" name="costo_inicial" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['costo_inicial'] ?? 0); ?>" required>
                    </div>

                    <div class="vault-form-group">
                        <label>Fecha de Adquisición <span style="color: var(--danger-red);">*</span></label>
                        <input type="date" name="fecha_adquisicion" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['fecha_adquisicion'] ?? date('Y-m-d')); ?>" required>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Tasa de Depreciación Mensual</label>
                        <input type="number" step="0.01" name="tasa_depreciacion" class="vault-form-control" value="<?php echo htmlspecialchars($equipo['tasa_depreciacion_mensual'] ?? 0.05); ?>" required>
                        <small style="color: #6c757d; font-size: 12px; margin-top: 5px; display: block;">El valor 0.05 equivale al 5% mensual. Solo modifícalo si este equipo se deprecia a un ritmo distinto.</small>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Ubicación Física</label>
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
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="vault-form-control" rows="3" style="resize: vertical;"><?php echo htmlspecialchars($equipo['observaciones']); ?></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white; margin-right: 10px; padding: 10px 15px; text-decoration: none; border-radius: 4px;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 30px;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>