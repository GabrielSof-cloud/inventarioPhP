<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: CRUD/dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <link rel="stylesheet" href="style.css">
<meta charset="utf-8" />
<title>Regisrarse</title>
<link rel="stylesheet" href="css/styles.css">
<style>
  body {
    margin: 0;
    background-color: #007BFF; /* fondo azul */
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    font-family: Arial, sans-serif;
  }
  .login-container {
    background: #fff; /* cuadro blanco */
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
</style>
</head>
<body>

<div class="login-container">
  <div class="icon">🔒</div>
  <h2>Crear cuenta</h2>
  <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
      // Procesar registro
      require_once $_SERVER['DOCUMENT_ROOT'].'/DBconn/conexion.php';
      $email = trim($_POST['email']);
      $password = $_POST['password'];
      $conf_password = $_POST['conf_password'];

      if ($password !== $conf_password) {
          echo '<p class="error">Las contraseñas no coinciden.</p>';
      } else {
          // Verificar si el email ya existe
          $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
          $stmt->bind_param('s', $email);
          $stmt->execute();
          if ($stmt->get_result()->num_rows > 0) {
              echo '<p class="error">El email ya está registrado.</p>';
              $stmt->close();
          } else {
              // Insertar nuevo usuario
              $hash_password = password_hash($password, PASSWORD_DEFAULT);
              $stmt = $conn->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
              $stmt->bind_param('ss', $email, $hash_password);
              if ($stmt->execute()) {
                  echo '<p class="success">Cuenta creada. Ahora puedes iniciar sesión.</p>';
              } else {
                  echo '<p class="error">Error al crear la cuenta.</p>';
              }
              $stmt->close();
          }
      }
  }
  ?>
  <form method="post" action="">
    <input type="email" name="email" placeholder="Correo" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <input type="password" name="conf_password" placeholder="Confirmar Contraseña" required>
    <button type="submit" name="register">Registrar</button>
  </form>
  <div class="footer">
    <span>¿Tienes cuenta? <a href="Loging.php">Iniciar sesión</a></span>
  </div>
</div>

</body>
</html>
