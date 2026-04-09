<?php
// Simulasi session untuk testing
session_start();
$_SESSION['id_user'] = 2; // ID mahasiswa test
$_SESSION['nama'] = 'Mahasiswa Test';
$_SESSION['nim'] = '12345678';
$_SESSION['role'] = 'mahasiswa';

include 'koneksi.php';

echo "Testing riwayat simulasi...\n";

// Test query simulasi
$query_simulasi = "SELECT users.nama_lengkap, simulasi.* FROM simulasi 
                   JOIN users ON simulasi.id_user = users.id_user 
                   WHERE simulasi.id_user = '2'
                   ORDER BY id_simulasi DESC";

$result_simulasi = mysqli_query($conn, $query_simulasi);
if ($result_simulasi) {
    echo "Query simulasi berhasil! Records: " . mysqli_num_rows($result_simulasi) . "\n";
} else {
    echo "Error query simulasi: " . mysqli_error($conn) . "\n";
}

// Test query decision_logs
$query_logs = "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs 
               JOIN users ON decision_logs.id_user = users.id_user 
               WHERE decision_logs.id_user = '2'
               ORDER BY decision_logs.created_at DESC";

$result_logs = mysqli_query($conn, $query_logs);
if ($result_logs) {
    echo "Query decision_logs berhasil! Records: " . mysqli_num_rows($result_logs) . "\n";
} else {
    echo "Error query decision_logs: " . mysqli_error($conn) . "\n";
}

echo "Test selesai!\n";
?>