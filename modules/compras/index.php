<?php
$page_title = "Compras - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'compras']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar eliminación
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    try {
        // Eliminar detalles de compra primero
        $sql = "DELETE FROM detalle_compras WHERE compra_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Eliminar compra
        $sql = "DELETE FROM compras WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Compra eliminada correctamente";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar la compra: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Obtener compras con filtros
$where = "1=1";
$params = [];

// Filtro por fecha
if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
    $where .= " AND DATE(c.fecha_compra) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}

// Filtro por proveedor
if (isset($_GET['proveedor_id']) && !empty($_GET['proveedor_id'])) {
    $where .= " AND c.proveedor_id = :proveedor_id";
    $params[':proveedor_id'] = $_GET['proveedor_id'];
}

// Filtro por producto
if (isset($_GET['producto']) && !empty($_GET['producto'])) {
    $where .= " AND EXISTS (SELECT 1 FROM detalle_compras dc WHERE dc.compra_id = c.id AND dc.nombre_producto LIKE :producto)";
    $params[':producto'] = '%' . $_GET['producto'] . '%';
}

// Para usuarios de compras, solo mostrar sus propias compras
if ($_SESSION['user_role'] == 'compras') {
    $where .= " AND c.usuario_creacion = :user_id";
    $params[':user_id'] = $_SESSION['user_id'];
}

$sql = "SELECT c.*, p.nombre_proveedor, u.nombre as usuario_creador,
               (SELECT COUNT(*) FROM detalle_compras dc WHERE dc.compra_id = c.id) as total_productos
        FROM compras c 
        JOIN proveedores p ON c.proveedor_id = p.id 
        LEFT JOIN usuarios u ON c.usuario_creacion = u.id 
        WHERE $where 
        ORDER BY c.fecha_compra DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$compras = $stmt->fetchAll();

// Obtener proveedores para el filtro
$sql_proveedores = "SELECT id, nombre_proveedor FROM proveedores ORDER BY nombre_proveedor";
$proveedores = $pdo->query($sql_proveedores)->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Compras</h1>
        <div class="header-actions">
            <a href="crear.php" class="btn btn-primary">Nueva Compra</a>
        </div>
    </div>

    <!-- Mostrar mensajes -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filters">
        <form method="GET" class="filter-grid">
            <div class="form-group">
                <label for="fecha">Filtrar por fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="proveedor_id">Filtrar por proveedor:</label>
                <select id="proveedor_id" name="proveedor_id">
                    <option value="">Todos los proveedores</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>" <?php echo (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] == $proveedor['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="producto">Filtrar por producto:</label>
                <input type="text" id="producto" name="producto" value="<?php echo isset($_GET['producto']) ? htmlspecialchars($_GET['producto']) : ''; ?>" placeholder="Nombre del producto">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla de compras -->
    <div class="table-container">
        <?php if (empty($compras)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🛒</div>
                <h3>No hay compras registradas</h3>
                <p>Comienza registrando tu primera compra.</p>
                <a href="crear.php" class="btn btn-primary">Registrar Compra</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Proveedor</th>
                        <th>Productos</th>
                        <th>Precio Final</th>
                        <th>Fecha Compra</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?php echo $compra['id']; ?></td>
                        <td><?php echo htmlspecialchars($compra['nombre_proveedor']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $compra['total_productos']; ?> productos</span>
                        </td>
                        <td><?php echo formatMoney($compra['precio_final']); ?></td>
                        <td><?php echo formatDate($compra['fecha_compra']); ?></td>
                        <td class="actions">
                            <a href="ver.php?id=<?php echo $compra['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                            <a href="index.php?delete_id=<?php echo $compra['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta compra?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>