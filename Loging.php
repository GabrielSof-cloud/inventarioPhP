
<?php
// Redirige a Login.php si alguien accede a Loging.php por error o por enlaces antiguos
header('Location: Login.php', true, 301);
exit;
