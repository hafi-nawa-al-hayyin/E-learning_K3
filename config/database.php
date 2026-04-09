<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elearning_k3');

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    return $conn;
}

// Session configuration
function initSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function requireLogin() {
    initSession();
    if (!isset($_SESSION['id_user'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Get current user info
function getCurrentUser() {
    initSession();
    return [
        'id' => $_SESSION['id_user'] ?? null,
        'nama' => $_SESSION['nama'] ?? null,
        'role' => $_SESSION['role'] ?? null
    ];
}
?>