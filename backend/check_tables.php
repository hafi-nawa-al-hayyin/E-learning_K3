<?php
include 'koneksi.php';

$result = mysqli_query($conn, 'SHOW TABLES');
echo "Tabel yang ada di database elearning_k3:\n";
while($row = mysqli_fetch_array($result)) {
    echo "- " . $row[0] . "\n";
}
?>