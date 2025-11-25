<?php
$page_title = "Detalle de Venta - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'ventas']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Obtener venta
$id = $_GET['id'] ?? 0;
$venta = null;

if ($id) {
    // Obtener datos de la venta
    $sql = "SELECT v.*, c.nombre_empresa, c.telefono, c.email, c.direccion, u.nombre as usuario_creador
            FROM ventas v 
            JOIN clientes c ON v.cliente_id = c.id 
            LEFT JOIN usuarios u ON v.usuario_creacion = u.id 
            WHERE v.id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $venta = $stmt->fetch();
    
    // Obtener detalles de la venta
    $sql_detalles = "SELECT dv.*, p.nombre as producto_nombre, p.descripcion
                    FROM detalle_ventas dv 
                    JOIN productos p ON dv.producto_id = p.id 
                    WHERE dv.venta_id = :venta_id";
    $stmt = $pdo->prepare($sql_detalles);
    $stmt->bindParam(':venta_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $detalles = $stmt->fetchAll();
}

if (!$venta) {
    $_SESSION['error_message'] = "Venta no encontrada";
    header("Location: index.php");
    exit();
}

// Verificar permisos (usuarios de ventas solo pueden ver sus propias ventas)
if ($_SESSION['user_role'] == 'ventas' && $venta['usuario_creacion'] != $_SESSION['user_id']) {
    $_SESSION['error_message'] = "No tienes permisos para ver esta venta";
    header("Location: index.php");
    exit();
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Detalle de Venta #<?php echo $venta['id']; ?></h1>
        <div class="header-actions">
            <a href="index.php" class="btn btn-secondary">Volver a Ventas</a>
        </div>
    </div>

    <div class="form-container">
        <div class="form-grid">
            <div class="form-group">
                <label><strong>Cliente:</strong></label>
                <p><?php echo htmlspecialchars($venta['nombre_empresa']); ?></p>
            </div>
            
            <div class="form-group">
                <label><strong>Fecha de Venta:</strong></label>
                <p><?php echo formatDate($venta['fecha_venta']); ?></p>
            </div>
            
            <div class="form-group">
                <label><strong>Método de Pago:</strong></label>
                <p>
                    <?php 
                    $metodos_pago = [
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'tarjeta_debito' => 'Tarjeta Débito',
                        'tarjeta_credito' => 'Tarjeta Crédito'
                    ];
                    echo $metodos_pago[$venta['metodo_pago']] ?? $venta['metodo_pago'];
                    ?>
                </p>
            </div>
            
            <div class="form-group">
                <label><strong>Registrado por:</strong></label>
                <p><?php echo htmlspecialchars($venta['usuario_creador']); ?></p>
            </div>
        </div>
        
        <!-- Información del cliente -->
        <div class="form-group">
            <label><strong>Información del Cliente:</strong></label>
            <div style="background: var(--gray-50); padding: 1rem; border-radius: 5px;">
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($venta['telefono']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($venta['email']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($venta['direccion']); ?></p>
            </div>
        </div>
        
        <!-- Productos de la venta -->
        <div class="form-group">
            <label><strong>Productos Vendidos:</strong></label>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($detalle['producto_nombre']); ?></strong>
                                <?php if (!empty($detalle['descripcion'])): ?>
                                    <br><small><?php echo htmlspecialchars($detalle['descripcion']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td><?php echo formatMoney($detalle['precio_unitario']); ?></td>
                            <td><?php echo formatMoney($detalle['cantidad'] * $detalle['precio_unitario']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Subtotal Productos:</strong></td>
                            <td><strong><?php echo formatMoney($venta['precio_final'] - $venta['gastos_extra']); ?></strong></td>
                        </tr>
                        <?php if ($venta['gastos_extra'] > 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Gastos Extra:</strong></td>
                            <td><strong><?php echo formatMoney($venta['gastos_extra']); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                            <td><strong><?php echo formatMoney($venta['precio_final']); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Comprobante de pago -->
        <?php if (!empty($venta['comprobante_pago'])): ?>
        <div class="form-group">
            <label><strong>Comprobante de Pago:</strong></label>
            <div>
                <img src="../../uploads/comprobantes/<?php echo $venta['comprobante_pago']; ?>" 
                     alt="Comprobante de pago" style="max-width: 300px; border-radius: 5px;">
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Observaciones -->
        <?php if (!empty($venta['observaciones'])): ?>
        <div class="form-group">
            <label><strong>Observaciones:</strong></label>
            <p><?php echo nl2br(htmlspecialchars($venta['observaciones'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary">Volver</a>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>