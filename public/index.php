<?php
// Main application entry point
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../backend/controllers/DashboardController.php';

initSession();
requireLogin('../backend/login.php');

$controller = new DashboardController();
$controller->index();
?>
