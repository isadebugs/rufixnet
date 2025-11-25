<?php
// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Verificar rol de usuario
function checkRole($allowedRoles) {
    if (!isLoggedIn()) {
        header("Location: /rufixnet/login.php");
        exit();
    }
    
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        header("Location: /rufixnet/dashboard.php");
        exit();
    }
}

// Redirigir si ya está logueado
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: /rufixnet/dashboard.php");
        exit();
    }
}

// Obtener nombre del rol
function getRoleName($role) {
    $roles = [
        'admin' => 'Administrador',
        'ventas' => 'Ventas',
        'compras' => 'Compras'
    ];
    return $roles[$role] ?? $role;
}
?>