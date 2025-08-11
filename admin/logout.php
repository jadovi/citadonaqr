<?php
require_once '../config/config.php';

$auth = new Auth();
$auth->logout();

// Redirigir al login con mensaje
header('Location: ' . BASE_URL . '/admin/login.php?logout=1');
exit;
