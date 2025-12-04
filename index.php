<?php
// Definir ruta base absoluta
define('BASE_PATH', dirname(__FILE__) . '/');

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

// Si hay una ruta específica solicitada, cargarla
if (!empty($request_url) && array_key_exists($request_url, $routes)) {
    // INCLUIR ARCHIVOS BASE PRIMERO
    include BASE_PATH . 'includes/config.php';
    include BASE_PATH . 'includes/auth.php';
    
    // VERIFICAR SI EL USUARIO ESTÁ LOGUEADO
    if (!isLoggedIn() && $request_url != 'login') {
        header("Location: index.php?url=login");
        exit();
    }
    
    // SI ES LOGIN Y YA ESTÁ LOGUEADO, REDIRIGIR AL DASHBOARD
    if ($request_url == 'login' && isLoggedIn()) {
        header("Location: index.php?url=dashboard");
        exit();
    }
    
    // PARA PÁGINAS QUE NO SON LOGIN/LOGOUT, INCLUIR DISEÑO COMPLETO
    if ($request_url != 'login' && $request_url != 'logout') {
        include BASE_PATH . 'includes/header.php';
        include BASE_PATH . 'includes/sidebar.php';
    }
    
    $file_to_load = $routes[$request_url];
    
    // Verificar que el archivo existe antes de incluirlo
    if (file_exists(BASE_PATH . $file_to_load)) {
        include BASE_PATH . $file_to_load;
    } else {
        http_response_code(404);
        echo "<h1>Error 404 - Archivo no encontrado: $file_to_load</h1>";
    }
    
    // PARA PÁGINAS QUE NO SON LOGIN/LOGOUT, INCLUIR FOOTER
    if ($request_url != 'login' && $request_url != 'logout') {
        include BASE_PATH . 'includes/footer.php';
    }
    exit();
}

// Si no hay ruta específica, incluir archivos base y redirigir según autenticación
include BASE_PATH . 'includes/config.php';
include BASE_PATH . 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php?url=dashboard");
} else {
    header("Location: index.php?url=login");
}
exit();
?>