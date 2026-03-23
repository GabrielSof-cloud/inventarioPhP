<?php
require_once __DIR__ . '/DBconn/conexion.php';

$serie = $_GET['serie'] ?? null;

if (!$serie) {
    die("No se especificó un equipo.");
}

$stmt = $conn->prepare("SELECT * FROM equipos WHERE serie = ? OR id = ? LIMIT 1");
$stmt->bind_param("ss", $serie, $serie);

$stmt->execute();
$res = $stmt->get_result();
$e = $res->fetch_assoc();
$stmt->close();

if (!$e) {
    die("Equipo no encontrado.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Equipo <?= htmlspecialchars($e['serie']) ?></title>
    <!-- Incluir estilos básicos adaptados para móviles -->
    <style>
        :root {
            --primary-blue: #0A2540;
            --secondary-blue: #004B87;
            --accent-blue: #0077D7;
            --background-light: #F6F9FC;
            --text-dark: #32325D;
            --text-muted: #6B7C93;
            --white: #FFFFFF;
            --border-color: #E6EBF1;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .mobile-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .header {
            background: var(--primary-blue);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.8;
        }

        .content {
            padding: 20px;
        }

        .info-group {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-label {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            background-color: #e8f4fd;
            color: var(--secondary-blue);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="mobile-card">
    <div class="header">
        <h1>Detalles del Equipo</h1>
        <p>Consulta Pública</p>
    </div>
    <div class="content">
        <div class="info-group">
            <div class="info-label">Número de Serie</div>
            <div class="info-value" style="font-size: 18px; color: var(--accent-blue);">
                <?= htmlspecialchars($e['serie']) ?>
                <div class="badge">ID: <?= $e['id'] ?></div>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Modelo</div>
            <div class="info-value"><?= htmlspecialchars($e['modelo']) ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Usuario Asignado</div>
            <div class="info-value"><?= htmlspecialchars($e['usuario'] ?: 'No asignado') ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Departamento</div>
            <div class="info-value"><?= htmlspecialchars($e['departamento'] ?: 'N/A') ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Ubicación Física</div>
            <div class="info-value"><?= htmlspecialchars($e['ubicacion'] ?: 'N/A') ?></div>
        </div>

        <?php if (!empty($e['observaciones'])): ?>
        <div class="info-group">
            <div class="info-label">Observaciones</div>
            <div class="info-value" style="font-size: 14px; line-height: 1.5; color: var(--text-muted);">
                <?= nl2br(htmlspecialchars($e['observaciones'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
