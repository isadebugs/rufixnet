<?php
$page_title = "Usuarios - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar eliminación (solo desactivar)
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    try {
        // No permitir eliminar el propio usuario
        if ($id == $_SESSION['user_id']) {
            throw new Exception("No puedes desactivar tu propio usuario");
        }
        
        $sql = "UPDATE usuarios SET activo = 0 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Usuario desactivado correctamente";
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Procesar activación
if (isset($_GET['activate_id'])) {
    $id = $_GET['activate_id'];
    
    try {
        $sql = "UPDATE usuarios SET activo = 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Usuario activado correctamente";
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Obtener usuarios
$sql = "SELECT id, username, nombre, rol, activo, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC";
$usuarios = $pdo->query($sql)->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Usuarios</h1>
        <div class="header-actions">
            <a href="crear.php" class="btn btn-primary">Nuevo Usuario</a>
        </div>
    </div>

    <!-- Mostrar mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Tabla de usuarios -->
    <div class="table-container">
        <?php if (empty($usuarios)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No hay usuarios registrados</h3>
                <p>Comienza agregando el primer usuario.</p>
                <a href="crear.php" class="btn btn-primary">Agregar Usuario</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo getRoleName($usuario['rol']); ?></span>
                        </td>
                        <td>
                            <?php if ($usuario['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-error">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($usuario['fecha_creacion']); ?></td>
                        <td class="actions">
                            <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                            <?php if ($usuario['activo'] && $usuario['id'] != $_SESSION['user_id']): ?>
                                <a href="index.php?delete_id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de desactivar este usuario?')">Desactivar</a>
                            <?php elseif (!$usuario['activo']): ?>
                                <a href="index.php?activate_id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('¿Estás seguro de activar este usuario?')">Activar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>