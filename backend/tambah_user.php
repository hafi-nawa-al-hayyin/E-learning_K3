<?php
include 'koneksi.php';

if (isset($_POST['nama']) && isset($_POST['jabatan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);

    $query = "INSERT INTO Users (nama, jabatan) VALUES ('$nama', '$jabatan')";
    
    if (mysqli_query($conn, $query)) {
        // Berikan kode 200 (OK) agar JavaScript tahu ini sukses
        http_response_code(200);
        echo "Sukses";
    } else {
        // Berikan kode 500 jika database error
        http_response_code(500);
        echo "Gagal: " . mysqli_error($conn);
    }
} else {
    http_response_code(400);
    echo "Data kurang lengkap";
}
?>