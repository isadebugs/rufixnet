<?php
// Usar require_once para incluir solo una vez
require_once 'includes/config.php';

// Verificar si hay una sesión activa antes de destruirla
if (session_status() === PHP_SESSION_ACTIVE) {
    // Limpiar todas las variables de sesión
    $_SESSION = array();

    // Si se desea destruir la sesión completamente, borrar también la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();
}

// Redirigir al login
header("Location: index.php?url=login");
exit();
?>