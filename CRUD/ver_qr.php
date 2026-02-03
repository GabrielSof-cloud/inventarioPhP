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
    <meta charset="UTF-8">
    <title>QR del equipo</title>
</head>
<body>

<h2>QR del equipo</h2>

<img src="<?php echo htmlspecialchars($rutaQR); ?>" alt="QR del equipo">

<p>Serial: <?php echo htmlspecialchars($serial); ?></p>

<a href="dashboard.php">Volver</a>

</body>
</html>