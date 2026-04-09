<?php
include 'koneksi.php';

echo "Testing database connection...\n";

// Test query sederhana
$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Connection successful! Total users: " . $row['total'] . "\n";
} else {
    echo "Query failed: " . mysqli_error($conn) . "\n";
}

// Test query login simulation
$test_nim = 'ADM001';
$test_role = 'admin';
$query_login = "SELECT * FROM users WHERE nim_nidn='$test_nim' AND role='$test_role'";
$result_login = mysqli_query($conn, $query_login);

if ($result_login && mysqli_num_rows($result_login) === 1) {
    echo "Login query successful! User found.\n";
} else {
    echo "Login query failed: " . mysqli_error($conn) . "\n";
}

echo "Test completed.\n";
?>