<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: Loging.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: dashboard.php');
    exit;
}

// Obtener archivos para borrarlos
$stmt = $conn->prepare("SELECT archivo, qrcode_file FROM equipos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$r = $res->fetch_assoc();
$stmt->close();

// Borrar archivos físicos si existen
if (!empty($r['archivo'])) {
    $f = __DIR__ . '/uploads/' . $r['archivo'];
    if (file_exists($f)) @unlink($f);
}
if (!empty($r['qrcode_file'])) {
    $f = __DIR__ . '/qrcodes/' . $r['qrcode_file'];
    if (file_exists($f)) @unlink($f);
}

// Borrar registro
$stmt = $conn->prepare("DELETE FROM equipos WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: dashboard.php');
exit;
?>
