<?php
$page_title = "Nuevo Proveedor - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'compras']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre_proveedor = trim($_POST['nombre_proveedor']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $fuente = trim($_POST['fuente']);
        
        // Validaciones básicas
        if (empty($nombre_proveedor)) {
            throw new Exception("El nombre del proveedor es obligatorio");
        }
        
        $sql = "INSERT INTO proveedores (nombre_proveedor, telefono, email, fuente, usuario_creacion) 
                VALUES (:nombre_proveedor, :telefono, :email, :fuente, :usuario_creacion)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre_proveedor', $nombre_proveedor);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fuente', $fuente);
        $stmt->bindParam(':usuario_creacion', $_SESSION['user_id']);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Proveedor creado correctamente";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Nuevo Proveedor</h1>
        <a href="index.php" class="btn btn-secondary">Volver a Proveedores</a>
    </div>

    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_proveedor">Nombre del Proveedor *</label>
                    <input type="text" id="nombre_proveedor" name="nombre_proveedor" required 
                           value="<?php echo isset($_POST['nombre_proveedor']) ? htmlspecialchars($_POST['nombre_proveedor']) : ''; ?>">
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
                <label for="fuente">Fuente (Dirección física o enlace web)</label>
                <textarea id="fuente" name="fuente" rows="3" placeholder="Dirección física o URL del proveedor"><?php echo isset($_POST['fuente']) ? htmlspecialchars($_POST['fuente']) : ''; ?></textarea>
                <small>Puede ser una dirección física o un enlace web donde se encuentra el proveedor.</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>