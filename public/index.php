<?php
// Main application entry point
require_once '../config/database.php';
require_once '../backend/controllers/DashboardController.php';

initSession();
requireLogin();

$controller = new DashboardController();
$controller->index();
?>