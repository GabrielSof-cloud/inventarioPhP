<?php
session_start();
session_destroy();
header('Location: Loging.php');
exit;
?>
