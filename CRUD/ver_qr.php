<?php
session_start();

$serial = $_GET['serie'] ?? null;

if (!$serial) {
    die('Serial no válido');
}

// Sanitizar para archivo
$serialSeguro = preg_replace('/[^A-Za-z0-9_\-]/', '_', $serial);

// Ruta del QR
$rutaQR = "qrs/qr_" . $serialSeguro . ".png";

// Verificar que exista
if (!file_exists($rutaQR)) {
    die('QR no encontrado');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="../style.css">
    <meta charset="UTF-8">
    <title>Código QR del Equipo - VAULT</title>
    <link rel="stylesheet" href="../style.css">
    
    <style>
        @media print {
            .vault-sidebar, .vault-header, .no-print {
                display: none !important;
            }
            .vault-main {
                padding: 0 !important;
            }
            .vault-card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
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
            <h1>Etiquetado de Equipo</h1>
            <div class="user" style="font-weight: 600;">
                Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?> 
                <a href="../logout.php" class="btn btn-danger no-print" style="margin-left: 15px; padding: 5px 15px; font-size: 14px;">Salir</a>
            </div>
        </header>

        <div class="vault-card" style="max-width: 500px; margin: 40px auto; text-align: center;">
            <h2 style="color: var(--primary-blue); margin-bottom: 10px;">Código QR Generado Exitosamente</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Pegue este código en un lugar visible del equipo.</p>
            
            <div style="margin: 20px auto; display: inline-block; padding: 20px; border: 2px dashed var(--border-color); border-radius: 12px; background-color: white;">
                <img src="<?php echo htmlspecialchars($rutaQR); ?>" alt="QR del equipo" style="width: 200px; height: 200px; display: block;">
                <div style="margin-top: 15px; font-size: 18px; font-weight: bold; letter-spacing: 1px; color: var(--text-dark);">
                    <?php echo htmlspecialchars($serial); ?>
                </div>
            </div>

            <div class="no-print" style="margin-top: 30px; display: flex; justify-content: center; gap: 15px;">
                <a href="dashboard.php" class="btn" style="background-color: var(--text-muted); color: white;">Volver al Dashboard</a>
                <button onclick="window.print()" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    🖨️ Imprimir Etiqueta
                </button>
            </div>
        </div>

    </main>
</div>

</body>
</html>