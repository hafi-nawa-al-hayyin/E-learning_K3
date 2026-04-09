<?php
include 'koneksi.php';

$result = mysqli_query($conn, "DESCRIBE decision_logs");
echo "Struktur tabel decision_logs:\n";
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>