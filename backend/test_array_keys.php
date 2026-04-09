<?php
include 'koneksi.php';

echo "Testing array keys untuk riwayat simulasi...\n";

// Test query simulasi
$query_simulasi = "SELECT users.nama_lengkap, simulasi.* FROM simulasi
                   JOIN users ON simulasi.id_user = users.id_user
                   WHERE simulasi.id_user = '2'
                   ORDER BY id_simulasi DESC";

$result_simulasi = mysqli_query($conn, $query_simulasi);
if ($result_simulasi && mysqli_num_rows($result_simulasi) > 0) {
    $row = mysqli_fetch_assoc($result_simulasi);
    echo "Available keys in simulasi result:\n";
    foreach($row as $key => $value) {
        echo "- $key\n";
    }
    echo "\nSample data:\n";
    echo "- nama_lengkap: " . $row['nama_lengkap'] . "\n";
    echo "- jenis_risiko: " . $row['jenis_risiko'] . "\n";
    echo "- skor: " . $row['skor'] . "\n";
} else {
    echo "No data found in simulasi\n";
}

echo "\nTesting array keys untuk decision logs...\n";

// Test query decision_logs
$query_logs = "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs
               JOIN users ON decision_logs.id_user = users.id_user
               WHERE decision_logs.id_user = '2'
               ORDER BY decision_logs.created_at DESC";

$result_logs = mysqli_query($conn, $query_logs);
if ($result_logs && mysqli_num_rows($result_logs) > 0) {
    $row = mysqli_fetch_assoc($result_logs);
    echo "Available keys in decision_logs result:\n";
    foreach($row as $key => $value) {
        echo "- $key\n";
    }
    echo "\nSample data:\n";
    echo "- nama_lengkap: " . $row['nama_lengkap'] . "\n";
    echo "- jenis_risiko: " . $row['jenis_risiko'] . "\n";
    echo "- created_at: " . $row['created_at'] . "\n";
} else {
    echo "No data found in decision_logs\n";
}

echo "\nTest selesai!\n";
?>