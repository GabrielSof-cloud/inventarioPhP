<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';
require_once __DIR__ . '/../qrcodes/qr.php';

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

    // NUEVOS CAMPOS DE DEPRECIACIÓN
    $costo_inicial = trim($_POST['costo_inicial'] ?? 0);
    $fecha_adquisicion = trim($_POST['fecha_adquisicion'] ?? date('Y-m-d'));
    $tasa_depreciacion = trim($_POST['tasa_depreciacion'] ?? 0.05);

    if (!$serie || !$modelo) {
        $err = 'Serie y Modelo son obligatorios.';
    } else {
        // Insertar en la base de datos (Actualizado con las nuevas columnas)
        $stmt = $conn->prepare("INSERT INTO equipos (serie, modelo, usuario, departamento, ubicacion, observaciones, costo_inicial, fecha_adquisicion, tasa_depreciacion_mensual) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // 9 strings/decimales ("s")
        $stmt->bind_param("sssssssss", $serie, $modelo, $usuario, $departamento, $ubicacion, $observaciones, $costo_inicial, $fecha_adquisicion, $tasa_depreciacion);
        
        try {
            $stmt->execute();
            
            $serial = $serie;
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $urlqr = "$protocol://$host/proyectoFinal/inventarioPhP/public_view.php?serie=" . urlencode($serial);
            
            $serialSeguro = preg_replace('/[^A-Za-z0-9_\-]/', '_', $serial);
            $ruta = 'qrs/qr_' . $serialSeguro . '.png';
            generalQR($urlqr, $ruta, 4);
            header("Location: ver_qr.php?serie=" . urlencode($serial));
            exit;
            
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $err = 'Error: Ya existe un equipo registrado con el número de serie ' . htmlspecialchars($serie) . '.';
            } else {
                $err = 'Error al guardar el equipo: ' . $e->getMessage();
            }
        } finally {
            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8" />
    <title>Agregar Equipo - VAULT</title>
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
            <h2 style="color: var(--primary-blue); margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">Registrar Nuevo Equipo</h2>
            
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
                        <input type="text" name="serie" class="vault-form-control" required placeholder="Ej: SN-12345678">
                    </div>
                    
                    <div class="vault-form-group">
                        <label>Modelo <span style="color: var(--danger-red);">*</span></label>
                        <input type="text" name="modelo" class="vault-form-control" required placeholder="Ej: Dell Optiplex 3080">
                    </div>

                    <div class="vault-form-group">
                        <label>Usuario Asignado</label>
                        <input type="text" name="usuario" class="vault-form-control" placeholder="Nombre del empleado">
                    </div>

                    <div class="vault-form-group">
                        <label>Departamento</label>
                        <select name="departamento" class="vault-form-control">
                            <option value="">Seleccione un departamento...</option>
                            <option value="Dirección General">Dirección General</option>
                            <option value="Administración y Finanzas">Administración y Finanzas</option>
                            <option value="Recursos Humanos">Recursos Humanos</option>
                            <option value="Tecnología de la Información (TI)">Tecnología de la Información (TI)</option>
                            <option value="Operaciones">Operaciones</option>
                            <option value="Ventas">Ventas</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Logística / Almacén">Logística / Almacén</option>
                            <option value="Soporte Técnico">Soporte Técnico</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="vault-form-group">
                        <label>Costo Inicial ($) <span style="color: var(--danger-red);">*</span></label>
                        <input type="number" step="0.01" name="costo_inicial" class="vault-form-control" required placeholder="Ej: 1500.00">
                    </div>

                    <div class="vault-form-group">
                        <label>Fecha de Adquisición <span style="color: var(--danger-red);">*</span></label>
                        <input type="date" name="fecha_adquisicion" class="vault-form-control" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Tasa de Depreciación Mensual</label>
                        <input type="number" step="0.01" name="tasa_depreciacion" class="vault-form-control" required value="0.05">
                        <small style="color: #6c757d; font-size: 12px; margin-top: 5px; display: block;">El valor 0.05 equivale al 5% mensual. Solo modifícalo si este equipo se deprecia a un ritmo distinto.</small>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Ubicación Física</label>
                        <select name="ubicacion" class="vault-form-control">
                            <option value="">Seleccione una sucursal (Promipyme)...</option>
                            <option value="Sede Principal (Santo Domingo)">Sede Principal (Santo Domingo)</option>
                            <option value="Manoguayabo (Santo Domingo)">Manoguayabo (Santo Domingo)</option>
                            <option value="Santo Domingo Este">Santo Domingo Este</option>
                            <option value="Santo Domingo Norte">Santo Domingo Norte</option>
                            <option value="Santiago">Santiago</option>
                            <option value="La Vega">La Vega</option>
                            <option value="San Francisco de Macorís">San Francisco de Macorís</option>
                            <option value="Puerto Plata">Puerto Plata</option>
                            <option value="Azua">Azua</option>
                            <option value="San Juan de la Maguana">San Juan de la Maguana</option>
                            <option value="Barahona">Barahona</option>
                            <option value="San Pedro de Macorís">San Pedro de Macorís</option>
                            <option value="La Romana">La Romana</option>
                            <option value="Higüey">Higüey</option>
                            <option value="San Cristóbal">San Cristóbal</option>
                            <option value="Baní">Baní</option>
                        </select>
                    </div>

                    <div class="vault-form-group" style="grid-column: span 2;">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="vault-form-control" rows="3" placeholder="Detalles adicionales del equipo, estado, accesorios incluidos..." style="resize: vertical;"></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white; margin-right: 10px; padding: 10px 15px; text-decoration: none; border-radius: 4px;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 30px;">Guardar Equipo</button>
                </div>
            </form>
        </div>
    </main>
</div>

</body>
</html>

