<?php
include 'koneksi.php';

echo "Data di tabel simulasi:\n";
$result_simulasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM simulasi");
$row_simulasi = mysqli_fetch_assoc($result_simulasi);
echo "Total records: " . $row_simulasi['total'] . "\n\n";

echo "Data di tabel decision_logs:\n";
$result_logs = mysqli_query($conn, "SELECT COUNT(*) as total FROM decision_logs");
$row_logs = mysqli_fetch_assoc($result_logs);
echo "Total records: " . $row_logs['total'] . "\n\n";

if ($row_logs['total'] > 0) {
    echo "Sample data dari decision_logs:\n";
    $sample = mysqli_query($conn, "SELECT dl.*, u.nama_lengkap FROM decision_logs dl JOIN users u ON dl.id_user = u.id_user LIMIT 3");
    while($row = mysqli_fetch_assoc($sample)) {
        echo "- ID: " . $row['id_log'] . ", User: " . $row['nama_lengkap'] . ", Risiko: " . $row['jenis_risiko'] . ", Tindakan: " . substr($row['tindakan_dipilih'], 0, 50) . "...\n";
    }
}
?>