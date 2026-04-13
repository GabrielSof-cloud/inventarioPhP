<?php
session_start();
require_once __DIR__ . '/../DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../Login.php');
    exit;
}

$depto_sel = $_GET['depto'] ?? '';
$cat_sel = $_GET['cat'] ?? '';
$ubicacion_sel = $_GET['ubicacion'] ?? '';

$where = "WHERE 1=1";
$params = []; $types = "";

if ($depto_sel) { $where .= " AND departamento = ?"; $params[] = $depto_sel; $types .= "s"; }
if ($cat_sel) { $where .= " AND categoria = ?"; $params[] = $cat_sel; $types .= "s"; }
if ($ubicacion_sel) { $where .= " AND ubicacion = ?"; $params[] = $ubicacion_sel; $types .= "s"; }

$sql = "SELECT * FROM equipos $where ORDER BY departamento ASC, id DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Inventario y Auditoría - VAULT</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        /* Estilos compactos idénticos al Dashboard */
        .vault-table th, .vault-table td { padding: 8px; font-size: 11px; white-space: nowrap; }
        .progress-bar-bg { background:#eee; border-radius:5px; height:6px; width:100%; margin-top: 2px;}
        .progress-bar-fill { background:#e74c3c; height:100%; border-radius:5px; }
        
        /* Modal de Auditoría */
        #audit-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; padding: 20px; overflow-y: auto;}
        .audit-content { background: white; max-width: 800px; margin: auto; padding: 20px; border-radius: 8px; position: relative;}
    </style>
</head>
<body>
<div class="vault-container">
    <aside class="vault-sidebar">
        <h2>VAULT</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="por_departamento.php">Inventario por Depto</a></li>
            <li><a href="create_form.php">Agregar Equipo</a></li>
            <li><a href="Descartado.php">Descarto</a></li>
            <li><a href="movidos.php">Trazado</a></li>
        </ul>
    </aside>

    <main class="vault-main">
        <header class="vault-header">
            <h1>Inventario por Departamento</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Desconocido'); ?> 
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card">
            <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                <select name="depto" class="vault-form-control" style="flex: 1; min-width: 180px;">
                    <option value="">Todos los Deptos</option>
                    <option value="Dirección General" <?php if($depto_sel == 'Dirección General') echo 'selected'; ?>>Dirección General</option>
                    <option value="Administración y Finanzas" <?php if($depto_sel == 'Administración y Finanzas') echo 'selected'; ?>>Administración y Finanzas</option>
                    <option value="Recursos Humanos" <?php if($depto_sel == 'Recursos Humanos') echo 'selected'; ?>>Recursos Humanos</option>
                    <option value="Tecnología de la Información (TI)" <?php if($depto_sel == 'Tecnología de la Información (TI)') echo 'selected'; ?>>TI</option>
                    <option value="Operaciones" <?php if($depto_sel == 'Operaciones') echo 'selected'; ?>>Operaciones</option>
                    <option value="Ventas" <?php if($depto_sel == 'Ventas') echo 'selected'; ?>>Ventas</option>
                    <option value="Marketing" <?php if($depto_sel == 'Marketing') echo 'selected'; ?>>Marketing</option>
                    <option value="Logística / Almacén" <?php if($depto_sel == 'Logística / Almacén') echo 'selected'; ?>>Logística / Almacén</option>
                    <option value="Soporte Técnico" <?php if($depto_sel == 'Soporte Técnico') echo 'selected'; ?>>Soporte Técnico</option>
                    <option value="Mantenimiento" <?php if($depto_sel == 'Mantenimiento') echo 'selected'; ?>>Mantenimiento</option>
                </select>

                <select name="cat" class="vault-form-control" style="flex: 1; min-width: 180px;">
                    <option value="">Todas las Categorías</option>
                    <option value="Mobiliarios de Oficina" <?php if($cat_sel == 'Mobiliarios de Oficina') echo 'selected'; ?>>Mobiliarios de Oficina</option>
                    <option value="Equipos Informáticos" <?php if($cat_sel == 'Equipos Informáticos') echo 'selected'; ?>>Equipos Informáticos</option>
                    <option value="Maquinaria o Equipos Pesados" <?php if($cat_sel == 'Maquinaria o Equipos Pesados') echo 'selected'; ?>>Maquinaria o Equipos Pesados</option>
                    <option value="Vehiculos" <?php if($cat_sel == 'Vehiculos') echo 'selected'; ?>>Vehículos</option>
                    <option value="Edificaciones" <?php if($cat_sel == 'Edificaciones') echo 'selected'; ?>>Edificaciones</option>
                </select>

                <select name="ubicacion" class="vault-form-control" style="flex: 1; min-width: 180px;">
                    <option value="">Todas las Sedes</option>
                    <option value="Sede Principal (Santo Domingo)" <?php if($ubicacion_sel == 'Sede Principal (Santo Domingo)') echo 'selected'; ?>>Sede Principal (SD)</option>
                    <option value="Manoguayabo (Santo Domingo)" <?php if($ubicacion_sel == 'Manoguayabo (Santo Domingo)') echo 'selected'; ?>>Manoguayabo (SD)</option>
                    <option value="Santo Domingo Este" <?php if($ubicacion_sel == 'Santo Domingo Este') echo 'selected'; ?>>Santo Domingo Este</option>
                    <option value="Santo Domingo Norte" <?php if($ubicacion_sel == 'Santo Domingo Norte') echo 'selected'; ?>>Santo Domingo Norte</option>
                    <option value="Santiago" <?php if($ubicacion_sel == 'Santiago') echo 'selected'; ?>>Santiago</option>
                    <option value="La Vega" <?php if($ubicacion_sel == 'La Vega') echo 'selected'; ?>>La Vega</option>
                    <option value="San Francisco de Macorís" <?php if($ubicacion_sel == 'San Francisco de Macorís') echo 'selected'; ?>>San Francisco de Macorís</option>
                    <option value="Puerto Plata" <?php if($ubicacion_sel == 'Puerto Plata') echo 'selected'; ?>>Puerto Plata</option>
                    <option value="Azua" <?php if($ubicacion_sel == 'Azua') echo 'selected'; ?>>Azua</option>
                    <option value="San Juan de la Maguana" <?php if($ubicacion_sel == 'San Juan de la Maguana') echo 'selected'; ?>>San Juan de la Maguana</option>
                    <option value="Barahona" <?php if($ubicacion_sel == 'Barahona') echo 'selected'; ?>>Barahona</option>
                    <option value="San Pedro de Macorís" <?php if($ubicacion_sel == 'San Pedro de Macorís') echo 'selected'; ?>>San Pedro de Macorís</option>
                    <option value="La Romana" <?php if($ubicacion_sel == 'La Romana') echo 'selected'; ?>>La Romana</option>
                    <option value="Higüey" <?php if($ubicacion_sel == 'Higüey') echo 'selected'; ?>>Higüey</option>
                    <option value="San Cristóbal" <?php if($ubicacion_sel == 'San Cristóbal') echo 'selected'; ?>>San Cristóbal</option>
                    <option value="Baní" <?php if($ubicacion_sel == 'Baní') echo 'selected'; ?>>Baní</option>
                </select>

                <button type="submit" class="btn btn-primary" style="padding: 10px 15px;">Filtrar</button>
                <button type="button" class="btn" style="background: #8e44ad; color: white; padding: 10px 15px;" onclick="abrirAuditoria()">📸 Auditar</button>
            </form>
        </div>

        <div class="vault-card" style="padding:0; overflow-x: auto;">
            <table class="vault-table" style="min-width: 1600px;">
                <thead>
                    <tr>
                        <th>ID</th><th>Serie</th><th>BN</th><th>Categoría</th><th>Modelo</th><th>Usuario</th><th>Depto</th><th>Sede</th><th>Costo</th><th>Vida</th><th>Acumulado</th><th>%</th><th>Actual</th>
                        <th style="text-align:center; position:sticky; right:0; background:#fff; box-shadow:-2px 0 5px rgba(0,0,0,0.05);">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) === 0): ?>
                    <tr><td colspan="14" style="text-align: center; padding: 30px; color: #888;">No hay equipos con estos filtros.</td></tr>
                    <?php else: ?>
                    <?php 
                    $hoy = new DateTime();
                    foreach ($results as $r): 
                        // Cálculos idénticos al Dashboard
                        $costo = (float)$r['costo_inicial']; 
                        $vida = (int)$r['vida_util_meses'];
                        $fechaA = !empty($r['fecha_adquisicion']) ? new DateTime($r['fecha_adquisicion']) : $hoy;
                        $meses = min($vida, ($hoy->diff($fechaA)->y * 12) + $hoy->diff($fechaA)->m);
                        $acum = ($vida > 0) ? ($costo / $vida) * $meses : 0;
                        $valor = max(0, $costo - $acum);
                        $perc = ($costo > 0) ? ($acum / $costo) * 100 : 0;
                        $serieS = preg_replace('/[^A-Za-z0-9_\-]/', '_', $r['serie']);
                    ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo $r['serie']; ?></td>
                        <td>
                            <?php if(!empty($r['registro_bn'])): ?>
                                <span style="background:#0d47a1; color:#fff; padding:2px 4px; border-radius:3px; font-size:10px;">BN-<?php echo $r['registro_bn']; ?></span>
                            <?php else: ?>
                                <span style="color: #ccc;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $r['categoria']; ?></td>
                        <td><?php echo $r['modelo']; ?></td>
                        <td><?php echo $r['usuario']; ?></td>
                        <td><?php echo $r['departamento']; ?></td>
                        <td><?php echo $r['ubicacion']; ?></td>
                        <td style="color:green; font-weight:bold;">$<?php echo number_format($costo, 2); ?></td>
                        <td><?php echo $vida; ?>m</td>
                        <td style="color:orange;">$<?php echo number_format($acum, 2); ?></td>
                        <td style="width:80px;">
                            <div style="font-size:10px; color:#555;"><?php echo number_format($perc, 1); ?>%</div>
                            <div class="progress-bar-bg"><div class="progress-bar-fill" style="width:<?php echo $perc; ?>%;"></div></div>
                        </td>
                        <td style="color:red; font-weight:bold;">$<?php echo number_format($valor, 2); ?></td>
                        
                        <td style="text-align:center; position:sticky; right:0; background:#fff; box-shadow:-2px 0 5px rgba(0,0,0,0.1);">
                            <a href="qrs/qr_<?php echo $serieS; ?>.png" download class="btn" style="background:#2ecc71; color:#fff; padding:3px 6px; font-size:10px;" title="Descargar QR">⬇️ QR</a>
                            <a href="edit_equipo.php?id=<?php echo $r['id']; ?>" class="btn btn-primary" style="padding:3px 6px; font-size:10px;">Ed.</a>
                            <a href="mover.php?id=<?php echo $r['id']; ?>" class="btn btn-primary" style="background:#17a2b8; padding:3px 6px; font-size:10px;">Mov.</a>
                            <a href="confirmar_descarto.php?id=<?php echo $r['id']; ?>" class="btn btn-danger" style="padding:3px 6px; font-size:10px;">Desc.</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="audit-modal">
    <div class="audit-content">
        <button onclick="cerrarAuditoria()" style="float:right; background:none; border:none; font-size:20px; cursor:pointer;">❌</button>
        <h2 style="color: #8e44ad; border-bottom: 2px solid #eee; padding-bottom: 10px;">Auditoría de Inventario</h2>
        <p style="color: #555;">Escanea los códigos QR físicos para compararlos con esta lista filtrada.</p>
        
        <div style="display:flex; gap:10px; margin:20px 0;">
            <input type="text" id="lector_usb" class="vault-form-control" placeholder="Escanee con pistola USB..." autofocus autocomplete="off">
            <button class="btn btn-primary" id="btn-camara">📷 Cámara del Celular</button>
        </div>
        
        <div id="reader" style="max-width: 500px; margin: 0 auto;"></div>
        
        <div style="margin-top: 20px; text-align: center; font-size: 18px; font-weight: bold;">
            Escaneados: <span id="contador">0</span> / <?php echo count($results); ?>
        </div>
        
        <div id="audit-results" style="margin-top:20px; background: #f9f9f9; padding: 15px; border-radius: 5px; display: none;"></div>
        
        <button onclick="finalizarAuditoria()" class="btn btn-primary" style="width:100%; margin-top:20px; background:#27ae60; padding: 12px; font-size: 16px;">✅ Finalizar y Comparar</button>
    </div>
</div>

<script>
    const esperados = <?php echo json_encode($results); ?>;
    let escaneados = new Set();
    let scanner = null;

    function abrirAuditoria() { 
        if(esperados.length === 0) { alert("No hay equipos en la lista para auditar."); return; }
        document.getElementById('audit-modal').style.display = 'block'; 
        document.getElementById('lector_usb').focus();
    }
    
    function cerrarAuditoria() { 
        document.getElementById('audit-modal').style.display = 'none'; 
        if(scanner) { scanner.clear(); scanner = null; }
        escaneados.clear();
        document.getElementById('contador').innerText = '0';
        document.getElementById('audit-results').style.display = 'none';
        document.getElementById('lector_usb').value = '';
    }

    function procesar(texto) {
        let s = texto.trim();
        try { let u = new URL(texto); if(u.searchParams.has('serie')) s = u.searchParams.get('serie'); } catch(e){}
        if(s && !escaneados.has(s)) {
            escaneados.add(s); 
            document.getElementById('contador').innerText = escaneados.size;
            document.getElementById('lector_usb').style.background = '#d4edda';
            setTimeout(() => document.getElementById('lector_usb').style.background = '#fff', 300);
        }
        document.getElementById('lector_usb').value = '';
    }

    document.getElementById('lector_usb').addEventListener('keypress', (e) => { if(e.key==='Enter') procesar(e.target.value); });
    document.getElementById('btn-camara').addEventListener('click', () => {
        if(!scanner) {
            scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
            scanner.render(procesar);
        }
    });

    function finalizarAuditoria() {
        if(scanner) { scanner.clear(); scanner = null; }
        let faltantes = esperados.filter(e => !escaneados.has(e.serie));
        let inesperados = [...escaneados].filter(s => !esperados.some(e => e.serie === s));
        
        let html = `<h3 style="margin-top:0;">Resumen Final</h3>`;
        html += `<p><strong>Esperados:</strong> ${esperados.length} | <strong>Escaneados:</strong> ${escaneados.size}</p>`;
        
        if(faltantes.length > 0) {
            html += `<h4 style="color:#c0392b; margin-bottom:5px;">❌ Faltantes (${faltantes.length}):</h4><ul style="color:#c0392b; margin-top:0;">`;
            faltantes.forEach(f => html += `<li>${f.serie} - ${f.modelo}</li>`);
            html += `</ul>`;
        }
        
        if(inesperados.length > 0) {
            html += `<h4 style="color:#d35400; margin-bottom:5px;">⚠️ Inesperados (${inesperados.length}):</h4><ul style="color:#d35400; margin-top:0;">`;
            inesperados.forEach(i => html += `<li>Serie: ${i}</li>`);
            html += `</ul>`;
        }
        
        if(faltantes.length === 0 && inesperados.length === 0) {
            html += `<div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb;">🎉 ¡Auditoría Perfecta! Todo coincide.</div>`;
        }
        
        const resDiv = document.getElementById('audit-results');
        resDiv.innerHTML = html;
        resDiv.style.display = 'block';
    }
</script>
</body>
</html>