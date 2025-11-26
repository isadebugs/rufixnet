<?php
// Determinar la página activa
$current_page = isset($_GET['url']) ? $_GET['url'] : 'dashboard';
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
            <li><a href="index.php?url=dashboard" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
            
            <?php if ($_SESSION['user_role'] == 'ventas' || $_SESSION['user_role'] == 'admin'): ?>
            <li><a href="index.php?url=clientes" class="<?php echo $current_page == 'clientes' ? 'active' : ''; ?>">Clientes</a></li>
            <li><a href="index.php?url=ventas" class="<?php echo $current_page == 'ventas' ? 'active' : ''; ?>">Ventas</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] == 'compras' || $_SESSION['user_role'] == 'admin'): ?>
            <li><a href="index.php?url=proveedores" class="<?php echo $current_page == 'proveedores' ? 'active' : ''; ?>">Proveedores</a></li>
            <li><a href="index.php?url=compras" class="<?php echo $current_page == 'compras' ? 'active' : ''; ?>">Compras</a></li>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
            <li><a href="index.php?url=usuarios" class="<?php echo $current_page == 'usuarios' ? 'active' : ''; ?>">Usuarios</a></li>
            <li><a href="index.php?url=estadisticas" class="<?php echo $current_page == 'estadisticas' ? 'active' : ''; ?>">Estadísticas</a></li>
            <?php endif; ?>
            
            <li><a href="index.php?url=logout">Cerrar Sesión</a></li>
        </ul>
    </nav>
</aside>

<button class="menu-toggle" id="menuToggle">☰</button>