<?php
// Main application entry point
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../backend/controllers/DashboardController.php';

initSession();

if (!isset($_SESSION['id_user'])) {
    $appBasePath = 'backend';
    $postLoginRedirect = './';
    require __DIR__ . '/../backend/login.php';
    exit();
}

$controller = new DashboardController();
$controller->index();
?>
