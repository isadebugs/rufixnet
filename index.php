<?php
// Manejo de rutas amigables
$request_url = isset($_GET['url']) ? $_GET['url'] : '';

// Definir rutas
$routes = [
    '' => 'dashboard.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'dashboard' => 'dashboard.php',
    'clientes' => 'modules/clientes/index.php',
    'clientes/crear' => 'modules/clientes/crear.php',
    'clientes/editar' => 'modules/clientes/editar.php',
    'ventas' => 'modules/ventas/index.php',
    'ventas/crear' => 'modules/ventas/crear.php',
    'ventas/ver' => 'modules/ventas/ver.php',
    'proveedores' => 'modules/proveedores/index.php',
    'proveedores/crear' => 'modules/proveedores/crear.php',
    'compras' => 'modules/compras/index.php',
    'compras/crear' => 'modules/compras/crear.php',
    'usuarios' => 'modules/usuarios/index.php',
    'usuarios/crear' => 'modules/usuarios/crear.php',
    'estadisticas' => 'modules/estadisticas/index.php'
];

// Encontrar la ruta correspondiente
if (array_key_exists($request_url, $routes)) {
    include $routes[$request_url];
} else {
    // Si no encuentra la ruta, mostrar 404
    http_response_code(404);
    include '404.php'; // Puedes crear esta página
}
include 'includes/config.php';
include 'includes/auth.php';

// Redirigir al dashboard si está logueado, sino al login
if (isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>