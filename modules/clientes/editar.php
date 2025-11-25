<?php
$page_title = "Editar Cliente - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'ventas']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Obtener cliente
$id = $_GET['id'] ?? 0;
$cliente = null;

if ($id) {
    $sql = "SELECT * FROM clientes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch();
}

if (!$cliente) {
    $_SESSION['error_message'] = "Cliente no encontrado";
    header("Location: index.php");
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre_empresa = trim($_POST['nombre_empresa']);
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $observaciones = trim($_POST['observaciones']);
        
        if (empty($nombre_empresa)) {
            throw new Exception("El nombre de la empresa es obligatorio");
        }
        
        $sql = "UPDATE clientes SET 
                nombre_empresa = :nombre_empresa,
                direccion = :direccion,
                telefono = :telefono,
                email = :email,
                observaciones = :observaciones
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nombre_empresa', $nombre_empresa);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':id', $id);
        
        $stmt->execute();
        
        $_SESSION['success_message'] = "Cliente actualizado correctamente";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Editar Cliente</h1>
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
                           value="<?php echo htmlspecialchars($cliente['nombre_empresa']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($cliente['email']); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <textarea id="direccion" name="direccion" rows="3"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($cliente['observaciones']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>