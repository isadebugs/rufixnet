<?php
$page_title = "Clientes - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'ventas']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Procesar eliminación
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    try {
        $sql = "DELETE FROM clientes WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Cliente eliminado correctamente";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar el cliente: " . $e->getMessage();
    }
    
    header("Location: index.php");
    exit();
}

// Obtener clientes con filtros
$where = "1=1";
$params = [];

// Filtro por fecha
if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
    $where .= " AND DATE(c.fecha_creacion) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}

// Filtro por nombre
if (isset($_GET['nombre']) && !empty($_GET['nombre'])) {
    $where .= " AND c.nombre_empresa LIKE :nombre";
    $params[':nombre'] = '%' . $_GET['nombre'] . '%';
}

$sql = "SELECT c.*, u.nombre as usuario_creador 
        FROM clientes c 
        LEFT JOIN usuarios u ON c.usuario_creacion = u.id 
        WHERE $where 
        ORDER BY c.fecha_creacion DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Clientes</h1>
        <div class="header-actions">
            <a href="crear.php" class="btn btn-primary">Nuevo Cliente</a>
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
                <label for="nombre">Filtrar por nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>" placeholder="Nombre del cliente">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Filtrar</button>
                <a href="index.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla de clientes -->
    <div class="table-container">
        <?php if (empty($clientes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No hay clientes registrados</h3>
                <p>Comienza agregando tu primer cliente.</p>
                <a href="crear.php" class="btn btn-primary">Agregar Cliente</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre/Empresa</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo $cliente['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($cliente['nombre_empresa']); ?></strong>
                            <?php if (!empty($cliente['observaciones'])): ?>
                                <br><small><?php echo htmlspecialchars($cliente['observaciones']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                        <td><?php echo formatDate($cliente['fecha_creacion']); ?></td>
                        <td class="actions">
                            <a href="editar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                            <a href="index.php?delete_id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>