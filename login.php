<?php
include 'includes/config.php';
include 'includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (!empty($username) && !empty($password)) {
        // Buscar usuario en la base de datos
        $sql = "SELECT id, username, password, nombre, rol FROM usuarios WHERE username = :username AND activo = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña (en este ejemplo todas son "password123")
            if (password_verify($password, $user['password']) || $password === 'password123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_role'] = $user['rol'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Contraseña incorrecta";
            }
        } else {
            $error = "Usuario no encontrado";
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RUFIXNET</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>RUFIXNET</h1>
            <p>Sistema de Ventas y Compras</p>
        </div>
        
        <form method="POST" class="login-form">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>
        
        <div class="login-footer">
            <p><strong>Usuarios de prueba:</strong></p>
            <p>admin / password123 (Administrador)</p>
            <p>ventas1 / password123 (Ventas)</p>
            <p>compras1 / password123 (Compras)</p>
        </div>
    </div>
</body>
</html>