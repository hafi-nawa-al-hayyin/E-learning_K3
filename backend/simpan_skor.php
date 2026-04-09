<?php
ini_set('display_errors', 0);
error_reporting(0);
include 'koneksi.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_user          = isset($_POST['id_user']) ? mysqli_real_escape_string($conn, $_POST['id_user']) : '';
        $jenis_risiko     = isset($_POST['jenis_risiko']) ? mysqli_real_escape_string($conn, $_POST['jenis_risiko']) : '';
        $skor             = isset($_POST['skor']) ? intval($_POST['skor']) : 0;
        $status_kelulusan = isset($_POST['status_kelulusan']) ? mysqli_real_escape_string($conn, $_POST['status_kelulusan']) : '';
        $konsekuensi      = isset($_POST['konsekuensi']) ? mysqli_real_escape_string($conn, $_POST['konsekuensi']) : '';
        $rekomendasi      = isset($_POST['rekomendasi']) ? mysqli_real_escape_string($conn, $_POST['rekomendasi']) : '';
        $tindakan_dipilih = isset($_POST['tindakan_dipilih']) ? mysqli_real_escape_string($conn, $_POST['tindakan_dipilih']) : '';

        if (!$conn) { throw new Exception("Koneksi ke database gagal!"); }
        if (empty($id_user)) { throw new Exception("ID User tidak boleh kosong!"); }

        // Query INSERT ke tabel simulasi
        $query_simulasi = "INSERT INTO simulasi (id_user, jenis_risiko, skor, status_kelulusan, konsekuensi, rekomendasi) 
                          VALUES ('$id_user', '$jenis_risiko', '$skor', '$status_kelulusan', '$konsekuensi', '$rekomendasi')";

        // Query INSERT ke tabel decision_logs
        $query_logs = "INSERT INTO decision_logs (id_user, jenis_risiko, tindakan_dipilih, skor, status_kelulusan, konsekuensi, rekomendasi) 
                       VALUES ('$id_user', '$jenis_risiko', '$tindakan_dipilih', '$skor', '$status_kelulusan', '$konsekuensi', '$rekomendasi')";

        if (mysqli_query($conn, $query_simulasi) && mysqli_query($conn, $query_logs)) {
            echo json_encode(['status' => 'sukses', 'data' => ['kelulusan' => $status_kelulusan]]);
        } else {
            throw new Exception("Gagal Query: " . mysqli_error($conn));
        }
    } else {
        throw new Exception("Request tidak valid (Bukan POST)");
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'gagal', 'pesan' => $e->getMessage()]);
}
exit;
?>