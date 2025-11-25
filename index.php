<?php
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