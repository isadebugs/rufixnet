<?php
include 'includes/config.php';
include 'includes/auth.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Obtener estadísticas según el rol
if ($_SESSION['user_role'] == 'ventas') {
    // Estadísticas para usuario de ventas
    $sql_clientes = "SELECT COUNT(*) as total FROM clientes";
    $sql_ventas = "SELECT COUNT(*) as total FROM ventas WHERE usuario_creacion = :user_id";
    $sql_ventas_hoy = "SELECT COUNT(*) as total FROM ventas WHERE usuario_creacion = :user_id AND DATE(fecha_venta) = CURDATE()";
    
    $stmt = $pdo->prepare($sql_clientes);
    $stmt->execute();
    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_ventas);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_ventas_hoy);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} elseif ($_SESSION['user_role'] == 'compras') {
    // Estadísticas para usuario de compras
    $sql_proveedores = "SELECT COUNT(*) as total FROM proveedores";
    $sql_compras = "SELECT COUNT(*) as total FROM compras WHERE usuario_creacion = :user_id";
    $sql_compras_hoy = "SELECT COUNT(*) as total FROM compras WHERE usuario_creacion = :user_id AND DATE(fecha_compra) = CURDATE()";
    
    $stmt = $pdo->prepare($sql_proveedores);
    $stmt->execute();
    $total_proveedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_compras);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $total_compras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_compras_hoy);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $compras_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} else {
    // Estadísticas para administrador
    $sql_clientes = "SELECT COUNT(*) as total FROM clientes";
    $sql_ventas = "SELECT COUNT(*) as total FROM ventas";
    $sql_proveedores = "SELECT COUNT(*) as total FROM proveedores";
    $sql_compras = "SELECT COUNT(*) as total FROM compras";
    $sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
    $sql_ventas_hoy = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_venta) = CURDATE()";
    $sql_compras_hoy = "SELECT COUNT(*) as total FROM compras WHERE DATE(fecha_compra) = CURDATE()";
    
    $stmt = $pdo->prepare($sql_clientes);
    $stmt->execute();
    $total_clientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_ventas);
    $stmt->execute();
    $total_ventas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_proveedores);
    $stmt->execute();
    $total_proveedores = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_compras);
    $stmt->execute();
    $total_compras = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_usuarios);
    $stmt->execute();
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_ventas_hoy);
    $stmt->execute();
    $ventas_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->prepare($sql_compras_hoy);
    $stmt->execute();
    $compras_hoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

$page_title = "Dashboard - RUFIXNET";
?>

<main class="main-content">
    <div class="content-header">
        <h1>Dashboard</h1>
        <p>Bienvenido, <?php echo $_SESSION['user_name']; ?></p>
    </div>
    
    <div class="stats-container">
        <?php if ($_SESSION['user_role'] == 'ventas'): ?>
            <div class="stat-card">
                <h3>Total Clientes</h3>
                <p class="stat-number"><?php echo $total_clientes; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Ventas</h3>
                <p class="stat-number"><?php echo $total_ventas; ?></p>
            </div>
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <p class="stat-number"><?php echo $ventas_hoy; ?></p>
            </div>
            
        <?php elseif ($_SESSION['user_role'] == 'compras'): ?>
            <div class="stat-card">
                <h3>Total Proveedores</h3>
                <p class="stat-number"><?php echo $total_proveedores; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Compras</h3>
                <p class="stat-number"><?php echo $total_compras; ?></p>
            </div>
            <div class="stat-card">
                <h3>Compras Hoy</h3>
                <p class="stat-number"><?php echo $compras_hoy; ?></p>
            </div>
            
        <?php else: ?>
            <div class="stat-card">
                <h3>Total Clientes</h3>
                <p class="stat-number"><?php echo $total_clientes; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Ventas</h3>
                <p class="stat-number"><?php echo $total_ventas; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Proveedores</h3>
                <p class="stat-number"><?php echo $total_proveedores; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Compras</h3>
                <p class="stat-number"><?php echo $total_compras; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Usuarios</h3>
                <p class="stat-number"><?php echo $total_usuarios; ?></p>
            </div>
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <p class="stat-number"><?php echo $ventas_hoy; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="recent-activity">
        <h2>Actividad Reciente</h2>
        <div class="activity-list">
            <?php
            // Obtener actividad reciente según el rol
            if ($_SESSION['user_role'] == 'ventas') {
                $sql = "SELECT v.id, c.nombre_empresa, v.precio_final, v.fecha_venta 
                        FROM ventas v 
                        JOIN clientes c ON v.cliente_id = c.id 
                        WHERE v.usuario_creacion = :user_id 
                        ORDER BY v.fecha_venta DESC 
                        LIMIT 5";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $actividad = $stmt->fetchAll();
                
                foreach ($actividad as $item): ?>
                    <div class="activity-item">
                        <div class="activity-icon">💰</div>
                        <div class="activity-content">
                            <p><strong>Venta a <?php echo htmlspecialchars($item['nombre_empresa']); ?></strong></p>
                            <small><?php echo formatDate($item['fecha_venta']); ?> - <?php echo formatMoney($item['precio_final']); ?></small>
                        </div>
                    </div>
                <?php endforeach;
                
            } elseif ($_SESSION['user_role'] == 'compras') {
                $sql = "SELECT c.id, p.nombre_proveedor, c.precio_final, c.fecha_compra 
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        WHERE c.usuario_creacion = :user_id 
                        ORDER BY c.fecha_compra DESC 
                        LIMIT 5";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $actividad = $stmt->fetchAll();
                
                foreach ($actividad as $item): ?>
                    <div class="activity-item">
                        <div class="activity-icon">🛒</div>
                        <div class="activity-content">
                            <p><strong>Compra a <?php echo htmlspecialchars($item['nombre_proveedor']); ?></strong></p>
                            <small><?php echo formatDate($item['fecha_compra']); ?> - <?php echo formatMoney($item['precio_final']); ?></small>
                        </div>
                    </div>
                <?php endforeach;
                
            } else {
                // Actividad para admin - mezcla de ventas y compras
                $sql = "(SELECT 'venta' as tipo, v.id, c.nombre_empresa as nombre, v.precio_final, v.fecha_venta as fecha
                        FROM ventas v 
                        JOIN clientes c ON v.cliente_id = c.id 
                        ORDER BY v.fecha_venta DESC 
                        LIMIT 3)
                        UNION ALL
                        (SELECT 'compra' as tipo, c.id, p.nombre_proveedor as nombre, c.precio_final, c.fecha_compra as fecha
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        ORDER BY c.fecha_compra DESC 
                        LIMIT 3)
                        ORDER BY fecha DESC 
                        LIMIT 6";
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $actividad = $stmt->fetchAll();
                
                foreach ($actividad as $item): ?>
                    <div class="activity-item">
                        <div class="activity-icon"><?php echo $item['tipo'] == 'venta' ? '💰' : '🛒'; ?></div>
                        <div class="activity-content">
                            <p><strong><?php echo $item['tipo'] == 'venta' ? 'Venta' : 'Compra'; ?> a <?php echo htmlspecialchars($item['nombre']); ?></strong></p>
                            <small><?php echo formatDate($item['fecha']); ?> - <?php echo formatMoney($item['precio_final']); ?></small>
                        </div>
                    </div>
                <?php endforeach;
            }
            
            if (empty($actividad)): ?>
                <div class="activity-item">
                    <div class="activity-content">
                        <p>No hay actividad reciente</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>