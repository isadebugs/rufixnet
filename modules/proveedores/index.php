<?php
// ELIMINA TODOS LOS INCLUDES - YA SE CARGAN DESDE index.php
// Solo el contenido del módulo

// Procesar eliminación
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    try {
        $sql = "DELETE FROM proveedores WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Proveedor eliminado correctamente";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar el proveedor: " . $e->getMessage();
    }
    
    header("Location: index.php?url=proveedores");
    exit();
}

// Obtener proveedores
$sql = "SELECT * FROM proveedores ORDER BY fecha_creacion DESC";
$proveedores = $pdo->query($sql)->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Proveedores</h1>
        <div class="header-actions">
            <a href="index.php?url=proveedores/crear" class="btn btn-primary">Nuevo Proveedor</a>
        </div>
    </div>

    <!-- Mostrar mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Tabla de proveedores -->
    <div class="table-container">
        <?php if (empty($proveedores)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🏢</div>
                <h3>No hay proveedores registrados</h3>
                <p>Comienza registrando tu primer proveedor.</p>
                <a href="index.php?url=proveedores/crear" class="btn btn-primary">Registrar Proveedor</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Proveedor</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Fuente</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                    <tr>
                        <td><?php echo $proveedor['id']; ?></td>
                        <td><?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?></td>
                        <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($proveedor['email']); ?></td>
                        <td>
                            <?php if (filter_var($proveedor['fuente'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo htmlspecialchars($proveedor['fuente']); ?>" target="_blank" class="btn btn-sm btn-secondary">Visitar</a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($proveedor['fuente']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($proveedor['fecha_creacion']); ?></td>
                        <td class="actions">
                            <a href="index.php?url=proveedores/crear&id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                            <a href="index.php?url=proveedores&delete_id=<?php echo $proveedor['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este proveedor?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>