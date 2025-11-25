<?php
$page_title = "Nuevo Usuario - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $nombre = trim($_POST['nombre']);
        $rol = $_POST['rol'];
        
        // Validaciones
        if (empty($username) || empty($password) || empty($nombre) || empty($rol)) {
            throw new Exception("Todos los campos son obligatorios");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Las contraseñas no coinciden");
        }
        
        if (strlen($password) < 6) {
            throw new Exception("La contraseña debe tener al menos 6 caracteres");
        }
        
        // Verificar si el usuario ya existe
        $sql_check = "SELECT id FROM usuarios WHERE username = :username";
        $stmt = $pdo->prepare($sql_check);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("El nombre de usuario ya está en uso");
        }
        
        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (username, password, nombre, rol) 
                VALUES (:username, :password, :nombre, :rol)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':rol', $rol);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Usuario creado correctamente";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Nuevo Usuario</h1>
        <a href="index.php" class="btn btn-secondary">Volver a Usuarios</a>
    </div>

    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Usuario *</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required 
                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="ventas" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'ventas') ? 'selected' : ''; ?>>Ventas</option>
                        <option value="compras" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'compras') ? 'selected' : ''; ?>>Compras</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>