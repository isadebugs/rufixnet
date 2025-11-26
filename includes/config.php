<?php
// VERIFICAR SI LAS CONSTANTES YA EXISTEN ANTES DE DEFINIRLAS
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'rufixser_isapro');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', ';hD3)#jTsCkN');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'rufixser_rufixnet');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'RUFIXNET');
}

if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', __DIR__ . '/../uploads/');
}

if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VERIFICAR SI LA CONEXIÓN YA EXISTE ANTES DE CREARLA
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("ERROR: No se pudo conectar a la base de datos. " . $e->getMessage());
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('uploadFile')) {
    // Función para subir archivos
    function uploadFile($file, $directory) {
        $uploadDir = UPLOAD_PATH . $directory . '/';
        
        // Crear directorio si no existe
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validar archivo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error en la subida del archivo');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('El archivo es demasiado grande');
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filename;
        } else {
            throw new Exception('Error al guardar el archivo');
        }
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('formatDate')) {
    // Función para formatear fecha
    function formatDate($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
}

// VERIFICAR SI LA FUNCIÓN YA EXISTE ANTES DE DECLARARLA
if (!function_exists('formatMoney')) {
    // Función para formatear dinero
    function formatMoney($amount) {
        return '$' . number_format($amount, 2);
    }
}
?>