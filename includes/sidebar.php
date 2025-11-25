<?php
// Determinar la página activa
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>RUFIXNET</h2>
        <div class="user-info">
            <p><?php echo $_SESSION['user_name']; ?></p>
            <small><?php echo getRoleName($_SESSION['user_role']); ?></small>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li><a href="../dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
            
            <?php if ($_SESSION['user_role'] == 'ventas' || $_SESSION['user_role'] == 'admin'): ?>
            <li><a href="../modules/clientes/index.php" class="<?php echo strpos($current_page, 'clientes') !== false ? 'active' : ''; ?>">Clientes</a></li>
            <li><a href="../modules/ventas/index.php" class="<?php echo strpos($current_page, 'ventas') !== false ? 'active' : ''; ?>">Ventas</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] == 'compras' || $_SESSION['user_role'] == 'admin'): ?>
            <li><a href="../modules/proveedores/index.php" class="<?php echo strpos($current_page, 'proveedores') !== false ? 'active' : ''; ?>">Proveedores</a></li>
            <li><a href="../modules/compras/index.php" class="<?php echo strpos($current_page, 'compras') !== false ? 'active' : ''; ?>">Compras</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <li><a href="../modules/usuarios/index.php" class="<?php echo strpos($current_page, 'usuarios') !== false ? 'active' : ''; ?>">Usuarios</a></li>
            <li><a href="../modules/estadisticas/index.php" class="<?php echo strpos($current_page, 'estadisticas') !== false ? 'active' : ''; ?>">Estadísticas</a></li>
            <?php endif; ?>
            
            <li><a href="../logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
</aside>

<button class="menu-toggle" id="menuToggle">☰</button>