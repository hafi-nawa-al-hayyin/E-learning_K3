<?php
include 'koneksi.php';

// Membuat tabel simulasi
$query_simulasi = "CREATE TABLE IF NOT EXISTS simulasi (
    id_simulasi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    jenis_risiko VARCHAR(255) NOT NULL,
    skor INT DEFAULT 0,
    status_kelulusan ENUM('LULUS', 'GAGAL') NOT NULL,
    konsekuensi TEXT,
    rekomendasi TEXT,
    waktu_respon FLOAT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
)";

if (mysqli_query($conn, $query_simulasi)) {
    echo "Tabel simulasi berhasil dibuat.<br>";
} else {
    echo "Error membuat tabel simulasi: " . mysqli_error($conn) . "<br>";
}

// Membuat tabel decision_logs
$query_logs = "CREATE TABLE IF NOT EXISTS decision_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    jenis_risiko VARCHAR(255) NOT NULL,
    tindakan_dipilih TEXT,
    skor INT DEFAULT 0,
    status_kelulusan ENUM('LULUS', 'GAGAL') NOT NULL,
    konsekuensi TEXT,
    rekomendasi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
)";

if (mysqli_query($conn, $query_logs)) {
    echo "Tabel decision_logs berhasil dibuat.<br>";
} else {
    echo "Error membuat tabel decision_logs: " . mysqli_error($conn) . "<br>";
}

// Membuat tabel pengaturan_skenario
$query_skenario = "CREATE TABLE IF NOT EXISTS pengaturan_skenario (
    id INT PRIMARY KEY DEFAULT 1,
    skenario_aktif VARCHAR(255) NOT NULL DEFAULT 'Kebocoran Pipa Gas',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $query_skenario)) {
    echo "Tabel pengaturan_skenario berhasil dibuat.<br>";
} else {
    echo "Error membuat tabel pengaturan_skenario: " . mysqli_error($conn) . "<br>";
}

// Insert default value untuk pengaturan_skenario jika belum ada
$query_insert = "INSERT IGNORE INTO pengaturan_skenario (id, skenario_aktif) VALUES (1, 'Kebocoran Pipa Gas')";

if (mysqli_query($conn, $query_insert)) {
    echo "Data default untuk pengaturan_skenario berhasil dimasukkan.<br>";
} else {
    echo "Error memasukkan data default: " . mysqli_error($conn) . "<br>";
}

echo "Semua tabel berhasil dibuat!";
?></content>
<parameter name="filePath">c:\xampp\htdocs\k3_project\backend\create_tables.php