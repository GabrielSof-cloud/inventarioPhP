<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: CRUD/dashboard.php');
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = '';
$msg_type = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    require_once __DIR__ . '/DBconn/conexion.php';
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $msg = 'Petición inválida.';
        $msg_type = 'error';
    } else {
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $conf_password = $_POST['conf_password'];
        $ubicacion_fisica = htmlspecialchars(trim($_POST['ubicacion_fisica']));

        if ($password !== $conf_password) {
            $msg = 'Las contraseñas no coinciden.';
            $msg_type = 'error';
        } else {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $msg = 'El email ya está registrado.';
                $msg_type = 'error';
                $stmt->close();
            } else {
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, ubicacion_fisica) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $nombre, $email, $hash_password, $ubicacion_fisica);
                if ($stmt->execute()) {
                    $msg = 'Cuenta creada correctamente. Redirigiendo a inicio de sesión...';
                    $msg_type = 'success';
                    // Redirigir tras 2 segundos
                    echo '<meta http-equiv="refresh" content="2;url=Login.php">';
                } else {
                    $msg = 'Error al crear la cuenta.';
                    $msg_type = 'error';
                }
                $stmt->close();
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <link rel="stylesheet" href="style.css">
<meta charset="utf-8" />
<title>Registrarse</title>
<link rel="stylesheet" href="css/styles.css">
<style>
  body {
    margin: 0;
    background-color: #007BFF;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial, sans-serif;
  }
  .login-container {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    text-align: center;
  }
  .icon {
    font-size: 50px;
    color: #007BFF;
    margin-bottom: 10px;
  }
  h2 {
    margin-bottom: 20px;
  }
  form {
    display: flex;
    flex-direction: column;
  }
  input[type="email"],
  input[type="password"] {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  button {
    background-color: #007BFF;
    color: #fff;
    padding: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
  }
  button:hover {
    background-color: #0056b3;
  }
  .footer {
    margin-top: 15px;
  }
  .footer span {
    display: block;
    margin-bottom: 10px;
  }
  .footer a {
    color: #007BFF;
    text-decoration: none;
    font-weight: bold;
  }
  .error {
    color: red;
    margin-bottom: 10px;
  }
  .success {
    color: green;
    margin-bottom: 10px;
  }
</style>
</head>
<body>

<div class="login-container">
  <div class="icon">🔒</div>
  <h2>Crear cuenta</h2>
  <?php if (!empty($msg)) echo "<p class='$msg_type'>" . htmlspecialchars($msg) . "</p>"; ?>
  <form method="post" action="">
    <input type="text" name="nombre" placeholder="Nombre Completo" required>
    <input type="email" name="email" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <input type="password" name="conf_password" placeholder="Confirmar Contraseña" required>
    <select name="ubicacion_fisica" required style="padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">
      <option value="">Seleccione una sucursal (Promipyme)</option>
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
      <option value="Bani">Baní</option>
    </select>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <button type="submit" name="register">Registrar</button>
  </form>
  <div class="footer">
    <span>¿Tienes cuenta? <a href="Login.php">Iniciar sesión</a></span>
  </div>
</div>

</body>
</html>
