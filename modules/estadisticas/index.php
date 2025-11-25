<?php
$page_title = "Estadísticas - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Obtener estadísticas generales
$sql_ventas_mes = "SELECT COUNT(*) as total, SUM(precio_final) as total_ventas 
                   FROM ventas 
                   WHERE MONTH(fecha_venta) = MONTH(CURRENT_DATE()) 
                   AND YEAR(fecha_venta) = YEAR(CURRENT_DATE())";
$ventas_mes = $pdo->query($sql_ventas_mes)->fetch();

$sql_compras_mes = "SELECT COUNT(*) as total, SUM(precio_final) as total_compras 
                    FROM compras 
                    WHERE MONTH(fecha_compra) = MONTH(CURRENT_DATE()) 
                    AND YEAR(fecha_compra) = YEAR(CURRENT_DATE())";
$compras_mes = $pdo->query($sql_compras_mes)->fetch();

$sql_clientes_total = "SELECT COUNT(*) as total FROM clientes";
$clientes_total = $pdo->query($sql_clientes_total)->fetch();

$sql_proveedores_total = "SELECT COUNT(*) as total FROM proveedores";
$proveedores_total = $pdo->query($sql_proveedores_total)->fetch();

// Ventas por mes (últimos 6 meses)
$sql_ventas_por_mes = "SELECT 
    YEAR(fecha_venta) as año,
    MONTH(fecha_venta) as mes,
    COUNT(*) as total_ventas,
    SUM(precio_final) as total_monto
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(fecha_venta), MONTH(fecha_venta)
    ORDER BY año, mes";
$ventas_por_mes = $pdo->query($sql_ventas_por_mes)->fetchAll();

// Productos más vendidos
$sql_productos_vendidos = "SELECT 
    p.nombre,
    SUM(dv.cantidad) as total_vendido,
    SUM(dv.cantidad * dv.precio_unitario) as total_ventas
    FROM detalle_ventas dv
    JOIN productos p ON dv.producto_id = p.id
    GROUP BY p.id, p.nombre
    ORDER BY total_vendido DESC
    LIMIT 10";
$productos_vendidos = $pdo->query($sql_productos_vendidos)->fetchAll();

// Métodos de pago más utilizados
$sql_metodos_pago = "SELECT 
    metodo_pago,
    COUNT(*) as total_ventas,
    SUM(precio_final) as total_monto
    FROM ventas
    GROUP BY metodo_pago
    ORDER BY total_ventas DESC";
$metodos_pago = $pdo->query($sql_metodos_pago)->fetchAll();
?>

<main class="main-content">
    <div class="content-header">
        <h1>Estadísticas Generales</h1>
        <p>Resumen y análisis de la actividad del sistema</p>
    </div>

    <!-- Estadísticas principales -->
    <div class="stats-container">
        <div class="stat-card">
            <h3>Ventas del Mes</h3>
            <p class="stat-number"><?php echo $ventas_mes['total']; ?></p>
            <p class="stat-amount"><?php echo formatMoney($ventas_mes['total_ventas'] ?? 0); ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Compras del Mes</h3>
            <p class="stat-number"><?php echo $compras_mes['total']; ?></p>
            <p class="stat-amount"><?php echo formatMoney($compras_mes['total_compras'] ?? 0); ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Total Clientes</h3>
            <p class="stat-number"><?php echo $clientes_total['total']; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Total Proveedores</h3>
            <p class="stat-number"><?php echo $proveedores_total['total']; ?></p>
        </div>
    </div>

    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
        <!-- Ventas por mes -->
        <div class="table-container">
            <h3>Ventas Últimos 6 Meses</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Total Ventas</th>
                        <th>Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas_por_mes as $venta_mes): ?>
                    <tr>
                        <td>
                            <?php 
                            $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            echo $meses[$venta_mes['mes']] . ' ' . $venta_mes['año'];
                            ?>
                        </td>
                        <td><?php echo $venta_mes['total_ventas']; ?></td>
                        <td><?php echo formatMoney($venta_mes['total_monto']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Métodos de pago -->
        <div class="table-container">
            <h3>Métodos de Pago Más Utilizados</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Total Ventas</th>
                        <th>Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($metodos_pago as $metodo): ?>
                    <tr>
                        <td>
                            <?php 
                            $nombres_metodos = [
                                'efectivo' => 'Efectivo',
                                'transferencia' => 'Transferencia',
                                'tarjeta_debito' => 'Tarjeta Débito',
                                'tarjeta_credito' => 'Tarjeta Crédito'
                            ];
                            echo $nombres_metodos[$metodo['metodo_pago']] ?? $metodo['metodo_pago'];
                            ?>
                        </td>
                        <td><?php echo $metodo['total_ventas']; ?></td>
                        <td><?php echo formatMoney($metodo['total_monto']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="table-container" style="margin-top: 2rem;">
        <h3>Productos Más Vendidos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidades Vendidas</th>
                    <th>Total Ventas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos_vendidos as $producto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo $producto['total_vendido']; ?></td>
                    <td><?php echo formatMoney($producto['total_ventas']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<style>
.stat-amount {
    color: var(--success);
    font-weight: bold;
    margin-top: 0.5rem;
}
</style>

<?php include '../../includes/footer.php'; ?>