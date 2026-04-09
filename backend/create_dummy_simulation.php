<?php
include 'koneksi.php';

// Ambil ID user mahasiswa untuk testing
$result = mysqli_query($conn, "SELECT id_user FROM users WHERE role = 'mahasiswa' LIMIT 1");
if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $id_user = $user['id_user'];

    // Insert data simulasi
    $query_simulasi = "INSERT INTO simulasi (id_user, jenis_risiko, skor, status_kelulusan, konsekuensi, rekomendasi, waktu_respon) VALUES
                      ('$id_user', 'Kebocoran Pipa Gas', 85, 'LULUS', 'Berhasil menangani kebocoran dengan cepat', 'Lanjutkan protokol evakuasi', 2.5),
                      ('$id_user', 'Korsleting Listrik', 45, 'GAGAL', 'Terlambat dalam menangani korsleting', 'Perlu latihan lebih intensif', 5.2)";

    if (mysqli_query($conn, $query_simulasi)) {
        echo "Data simulasi berhasil ditambahkan.\n";
    } else {
        echo "Error menambah simulasi: " . mysqli_error($conn) . "\n";
    }

    // Insert data decision_logs
    $query_logs = "INSERT INTO decision_logs (id_user, jenis_risiko, tindakan_dipilih, skor, status_kelulusan, konsekuensi, rekomendasi) VALUES
                  ('$id_user', 'Kebocoran Pipa Gas', 'Menutup katup utama dan mengaktifkan alarm evakuasi', 85, 'LULUS', 'Berhasil menangani kebocoran dengan cepat', 'Lanjutkan protokol evakuasi'),
                  ('$id_user', 'Kebocoran Pipa Gas', 'Mencoba memperbaiki pipa langsung tanpa APD', 20, 'GAGAL', 'Berisiko terpapar gas beracun', 'Selalu gunakan APD lengkap'),
                  ('$id_user', 'Korsleting Listrik', 'Memadamkan api dengan air', 45, 'GAGAL', 'Air dapat memperburuk korsleting listrik', 'Gunakan alat pemadam api CO2 untuk kebakaran listrik')";

    if (mysqli_query($conn, $query_logs)) {
        echo "Data decision logs berhasil ditambahkan.\n";
    } else {
        echo "Error menambah decision logs: " . mysqli_error($conn) . "\n";
    }

} else {
    echo "Tidak ada user mahasiswa untuk testing.\n";
}

echo "Data dummy berhasil dibuat!\n";
?>