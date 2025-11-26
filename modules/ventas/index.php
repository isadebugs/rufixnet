<?php
// ELIMINAR TODOS LOS INCLUDES - YA SE CARGAN DESDE index.php
// Solo el contenido del módulo

// Procesar eliminación
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    try {
        // Eliminar detalles de venta primero
        $sql = "DELETE FROM detalle_ventas WHERE venta_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Eliminar venta
        $sql = "DELETE FROM ventas WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Venta eliminada correctamente";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error al eliminar la venta: " . $e->getMessage();
    }
    
    header("Location: index.php?url=ventas");
    exit();
}

// Obtener ventas con filtros
$where = "1=1";
$params = [];

// Filtro por fecha
if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
    $where .= " AND DATE(v.fecha_venta) = :fecha";
    $params[':fecha'] = $_GET['fecha'];
}

// Filtro por cliente
if (isset($_GET['cliente_id']) && !empty($_GET['cliente_id'])) {
    $where .= " AND v.cliente_id = :cliente_id";
    $params[':cliente_id'] = $_GET['cliente_id'];
}

// Para usuarios de ventas, solo mostrar sus propias ventas
if ($_SESSION['user_role'] == 'ventas') {
    $where .= " AND v.usuario_creacion = :user_id";
    $params[':user_id'] = $_SESSION['user_id'];
}

$sql = "SELECT v.*, c.nombre_empresa, u.nombre as usuario_creador,
               (SELECT COUNT(*) FROM detalle_ventas dv WHERE dv.venta_id = v.id) as total_productos
        FROM ventas v 
        JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN usuarios u ON v.usuario_creacion = u.id 
        WHERE $where 
        ORDER BY v.fecha_venta DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll();

// Obtener clientes para el filtro
$sql_clientes = "SELECT id, nombre_empresa FROM clientes ORDER BY nombre_empresa";
$clientes = $pdo->query($sql_clientes)->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Ventas</h1>
        <div class="header-actions">
            <a href="index.php?url=ventas/crear" class="btn btn-primary">Nueva Venta</a>
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
            <input type="hidden" name="url" value="ventas">
            <div class="form-group">
                <label for="fecha">Filtrar por fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="cliente_id">Filtrar por cliente:</label>
                <select id="cliente_id" name="cliente_id">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($_GET['cliente_id']) && $_GET['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cliente['nombre_empresa']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Filtrar</button>
                <a href="index.php?url=ventas" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla de ventas -->
    <div class="table-container">
        <?php if (empty($ventas)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">💰</div>
                <h3>No hay ventas registradas</h3>
                <p>Comienza registrando tu primera venta.</p>
                <a href="index.php?url=ventas/crear" class="btn btn-primary">Registrar Venta</a>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Método Pago</th>
                        <th>Precio Final</th>
                        <th>Fecha Venta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo $venta['id']; ?></td>
                        <td><?php echo htmlspecialchars($venta['nombre_empresa']); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $venta['total_productos']; ?> productos</span>
                        </td>
                        <td>
                            <?php 
                            $metodos_pago = [
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                                'tarjeta_debito' => 'Tarjeta Débito',
                                'tarjeta_credito' => 'Tarjeta Crédito'
                            ];
                            echo $metodos_pago[$venta['metodo_pago']] ?? $venta['metodo_pago'];
                            ?>
                        </td>
                        <td><?php echo formatMoney($venta['precio_final']); ?></td>
                        <td><?php echo formatDate($venta['fecha_venta']); ?></td>
                        <td class="actions">
                            <a href="index.php?url=ventas/ver&id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                            <a href="index.php?url=ventas&delete_id=<?php echo $venta['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta venta?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>