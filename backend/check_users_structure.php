<?php
include 'koneksi.php';

$result = mysqli_query($conn, "DESCRIBE users");
echo "Struktur tabel users:\n";
while($row = mysqli_fetch_assoc($result)) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>