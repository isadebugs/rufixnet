<?php
$page_title = "Nuevo Cliente - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'ventas']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre_empresa = trim($_POST['nombre_empresa']);
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $observaciones = trim($_POST['observaciones']);
        
        // Validaciones básicas
        if (empty($nombre_empresa)) {
            throw new Exception("El nombre de la empresa es obligatorio");
        }
        
        $sql = "INSERT INTO clientes (nombre_empresa, direccion, telefono, email, observaciones, usuario_creacion) 
                VALUES (:nombre_empresa, :direccion, :telefono, :email, :observaciones, :usuario_creacion)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre_empresa', $nombre_empresa);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':usuario_creacion', $_SESSION['user_id']);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Cliente creado correctamente";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Nuevo Cliente</h1>
        <a href="index.php" class="btn btn-secondary">Volver a Clientes</a>
    </div>

    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_empresa">Nombre de la Empresa *</label>
                    <input type="text" id="nombre_empresa" name="nombre_empresa" required 
                           value="<?php echo isset($_POST['nombre_empresa']) ? htmlspecialchars($_POST['nombre_empresa']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="3"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>