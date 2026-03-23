<?php
session_start();
require_once __DIR__ . '/DBconn/conexion.php';

// Regenerar el ID de sesión al iniciar sesión para mayor seguridad
if (isset($_SESSION['user_id'])) {
    header('Location: CRUD/dashboard.php');
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $err = 'Petición inválida.';
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows == 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                header('Location: CRUD/dashboard.php');
                exit;
            } else {
                $err = "Credenciales incorrectas.";
            }
        } else {
            $err = "Credenciales incorrectas.";
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>Iniciar Sesión</title>
<link rel="stylesheet" href="css/styles.css">
<style>
  body {
    margin: 0;
    background-color: #8988df;
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
  .error {
    color: red;
    margin-bottom: 10px;
  }
  .success {
    color: green;
    margin-bottom: 10px;
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
</style>
</head>
<body>
<div class="login-container">
  <div class="icon">🔑</div>
  <h2>Iniciar Sesión</h2>
  <?php if (!empty($err)) echo "<p class='error'>" . htmlspecialchars($err) . "</p>"; ?>
  <form method="post" action="">
    <input type="email" name="email" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <button type="submit">Ingresar</button>
  </form>
  <div class="footer">
    <span>¿No tienes cuenta? <a href="Registrar.php">Crear cuenta</a></span>
  </div>
</div>
</body>
</html>
