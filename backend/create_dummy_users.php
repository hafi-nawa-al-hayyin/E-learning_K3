<?php
include 'koneksi.php';

// Membuat user admin
$password_admin = password_hash('admin123', PASSWORD_DEFAULT);
$query_admin = "INSERT INTO users (nim_nidn, nama_lengkap, password, role, status_akun) 
                VALUES ('ADM001', 'Administrator', '$password_admin', 'admin', 'aktif')";

if (mysqli_query($conn, $query_admin)) {
    echo "User admin berhasil dibuat.\n";
} else {
    echo "Error membuat admin: " . mysqli_error($conn) . "\n";
}

// Membuat user mahasiswa
$password_mhs = password_hash('123456', PASSWORD_DEFAULT);
$query_mhs = "INSERT INTO users (nim_nidn, nama_lengkap, password, role, status_akun) 
              VALUES ('12345678', 'Mahasiswa Test', '$password_mhs', 'mahasiswa', 'aktif')";

if (mysqli_query($conn, $query_mhs)) {
    echo "User mahasiswa berhasil dibuat.\n";
} else {
    echo "Error membuat mahasiswa: " . mysqli_error($conn) . "\n";
}

// Membuat user dosen
$password_dosen = password_hash('dosen123', PASSWORD_DEFAULT);
$query_dosen = "INSERT INTO users (nim_nidn, nama_lengkap, password, role, status_akun) 
                VALUES ('DSN001', 'Dosen Test', '$password_dosen', 'dosen', 'aktif')";

if (mysqli_query($conn, $query_dosen)) {
    echo "User dosen berhasil dibuat.\n";
} else {
    echo "Error membuat dosen: " . mysqli_error($conn) . "\n";
}

echo "User dummy berhasil dibuat!\n";
echo "Admin: ADM001 / admin123\n";
echo "Mahasiswa: 12345678 / 123456\n";
echo "Dosen: DSN001 / dosen123\n";
?>