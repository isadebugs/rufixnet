<?php
// ============================================
// SISTEMA DE AUTENTICACIÓN Y ROLES - RUFIXNET
// ============================================

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('isLoggedIn')) {
    /**
     * Verifica si el usuario está actualmente logueado en el sistema
     * @return bool true si hay sesión activa, false si no
     */
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('checkRole')) {
    /**
     * Verifica si el usuario tiene el rol necesario para acceder a una página
     * @param array $allowedRoles Roles permitidos para acceder (ej: ['admin', 'ventas'])
     * Si el usuario no está logueado, redirige al login
     * Si no tiene el rol necesario, redirige al dashboard
     */
    function checkRole($allowedRoles) {
        // Si no está logueado, ir al login
        if (!isLoggedIn()) {
            header("Location: index.php?url=login");
            exit();
        }
        
        // Si no tiene el rol permitido, ir al dashboard
        if (!in_array($_SESSION['user_role'], $allowedRoles)) {
            header("Location: index.php?url=dashboard");
            exit();
        }
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('redirectIfLoggedIn')) {
    /**
     * Redirige al dashboard si el usuario YA está logueado
     * Útil para la página de login, para evitar que usuarios logueados
     * accedan nuevamente al formulario de login
     */
    function redirectIfLoggedIn() {
        if (isLoggedIn()) {
            header("Location: index.php?url=dashboard");
            exit();
        }
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA  
if (!function_exists('getRoleName')) {
    /**
     * Obtiene el nombre legible de un rol a partir de su código
     * @param string $role Código del rol (admin, ventas, compras)
     * @return string Nombre legible del rol
     */
    function getRoleName($role) {
        $roles = [
            'admin' => 'Administrador',
            'ventas' => 'Ventas',
            'compras' => 'Compras'
        ];
        return $roles[$role] ?? $role;
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('checkLogin')) {
    /**
     * Verifica simplemente si el usuario está logueado
     * Redirige al login si no hay sesión activa
     * Versión simplificada de checkRole sin verificación de roles
     */
    function checkLogin() {
        if (!isLoggedIn()) {
            header("Location: index.php?url=login");
            exit();
        }
    }
}
?>