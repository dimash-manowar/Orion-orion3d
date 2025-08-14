<?php
session_start();
session_destroy();
session_start();
$_SESSION['alert'] = [
    'icon' => 'info',
    'title' => 'Sesión cerrada',
    'text' => 'Has cerrado sesión correctamente'
];
header("Location: " . BASE_URL . "Auth");
exit;
