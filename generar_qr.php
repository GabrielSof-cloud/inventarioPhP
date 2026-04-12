<?php
session_start();
require_once __DIR__ . '/DBconn/conexion.php';
require_once __DIR__ . '/qrcodes/qr.php';

if (empty($_SESSION['user_id'])) {
    header('Location: Login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: CRUD/dashboard.php');
    exit;
}

// Obtener la serie del equipo
$stmt = $conn->prepare("SELECT serie FROM equipos WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$e = $res->fetch_assoc();
$stmt->close();

if (!$e) {
    die("Equipo no encontrado.");
}

$serial = $e['serie'];

// Generamos la URL Dinamica apuntando a public_view.php
// Usamos $_SERVER['HTTP_HOST'] para que tome la IP/Dominio con el que se está accediendo.
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$urlqr = "$protocol://$host/proyectoFinal/inventarioPhP/public_view.php?serie=" . urlencode($serial);

// Guardamos el QR en la ruta habitual
$serialSeguro = preg_replace('/[^A-Za-z0-9_\-]/', '_', $serial);
$ruta = 'CRUD/qrs/qr_' . $serialSeguro . '.png';

// Generar o sobreescribir el QR
generalQR($urlqr, $ruta, 4);

// Redirigir de nuevo a la vista de equipo
header("Location: equipo_view.php?id=" . $id . "&qr_updated=1");
exit;
