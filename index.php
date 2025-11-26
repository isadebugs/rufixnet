<?php
// Definir ruta base absoluta
define('BASE_PATH', dirname(__FILE__) . '/');

// Manejo de rutas amigables
$request_url = isset($_GET['url']) ? $_GET['url'] : '';

// DEBUG - Mostrar información (puedes eliminar esto después)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Si hay una ruta específica solicitada, cargarla
if (!empty($request_url) && array_key_exists($request_url, $routes)) {
    // Incluir archivos base PRIMERO con rutas absolutas
    include BASE_PATH . 'includes/config.php';
    include BASE_PATH . 'includes/auth.php';
    
    $file_to_load = $routes[$request_url];
    
    // Verificar que el archivo existe antes de incluirlo
    if (file_exists(BASE_PATH . $file_to_load)) {
        include BASE_PATH . $file_to_load;
    } else {
        http_response_code(404);
        echo "<h1>Error 404 - Archivo no encontrado: $file_to_load</h1>";
    }
    exit();
}

// Si no hay ruta específica, incluir archivos base y redirigir según autenticación
include BASE_PATH . 'includes/config.php';
include BASE_PATH . 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>