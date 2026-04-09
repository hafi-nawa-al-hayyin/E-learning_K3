<?php
include 'koneksi.php';

$result = mysqli_query($conn, "SELECT id_user, nim_nidn, nama_lengkap, role FROM users LIMIT 5");
echo "Data user yang ada:\n";
while($row = mysqli_fetch_assoc($result)) {
    echo "- ID: " . $row['id_user'] . ", NIM: " . $row['nim_nidn'] . ", Nama: " . $row['nama_lengkap'] . ", Role: " . $row['role'] . "\n";
}

if (mysqli_num_rows($result) == 0) {
    echo "Tidak ada user yang terdaftar.\n";
}
?>