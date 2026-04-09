<?php
// 1. HEADER & KONEKSI
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'koneksi.php';

// Ambil metode HTTP yang digunakan (GET, POST, atau DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// =========================================================================
// [GET] UNTUK MENGAMBIL DATA (USERS ATAU RIWAYAT)
// =========================================================================
if ($method === 'GET') {
    $target = isset($_GET['target']) ? $_GET['target'] : '';

    if ($target === 'users') {
        // Ambil semua data user untuk dimasukkan ke dropdown
        $query = "SELECT id_user, nama_lengkap, role FROM Users ORDER BY nama_lengkap ASC";
        $result = mysqli_query($conn, $query);
        
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
        echo json_encode($users);
    } 
    elseif ($target === 'riwayat') {
        // Ambil data riwayat gabungan dengan nama user untuk tabel
        $query = "SELECT Users.nama, Simulasi.* FROM Simulasi 
                  JOIN Users ON Simulasi.id_user = Users.id_user 
                  ORDER BY id_simulasi DESC";
        $result = mysqli_query($conn, $query);
        
        $riwayat = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $riwayat[] = $row;
        }
        echo json_encode($riwayat);
    } 
    elseif ($target === 'stats') {
        // Ambil statistik kelulusan dari tabel simulasi
        $query = "SELECT status_kelulusan, COUNT(*) AS total FROM Simulasi GROUP BY status_kelulusan";
        $result = mysqli_query($conn, $query);

        $stats = [
            'LULUS' => 0,
            'GAGAL' => 0
        ];

        while ($row = mysqli_fetch_assoc($result)) {
            $status = strtoupper($row['status_kelulusan']);
            if (isset($stats[$status])) {
                $stats[$status] = (int) $row['total'];
            }
        }

        echo json_encode([
            'lulus' => $stats['LULUS'],
            'gagal' => $stats['GAGAL'],
            'total' => $stats['LULUS'] + $stats['GAGAL']
        ]);
    } 
    else {
        echo json_encode(["error" => "Target tidak valid"]);
    }
}

// =========================================================================
// [POST] UNTUK MENYIMPAN SKOR BARU
// =========================================================================
elseif ($method === 'POST') {
    // Ambil input JSON dari JavaScript
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (isset($input['id_user'], $input['jenis_risiko'], $input['waktu_respon'])) {
        $id_user = mysqli_real_escape_string($conn, $input['id_user']);
        $jenis_risiko = mysqli_real_escape_string($conn, $input['jenis_risiko']);
        $waktu_respon = mysqli_real_escape_string($conn, $input['waktu_respon']);
        
        // Logika kelulusan (Contoh: Dibawah 3 detik dianggap LULUS)
        $status_kelulusan = ($waktu_respon <= 3.0) ? "LULUS" : "GAGAL";

        $query = "INSERT INTO Simulasi (id_user, jenis_risiko, waktu_respon, status_kelulusan) 
                  VALUES ('$id_user', '$jenis_risiko', '$waktu_respon', '$status_kelulusan')";

        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => "Data berhasil disimpan"]);
        } else {
            echo json_encode(["error" => "Gagal menyimpan ke database: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["error" => "Data yang dikirim tidak lengkap"]);
    }
}

// =========================================================================
// [DELETE] UNTUK MENGHAPUS DATA
// =========================================================================
elseif ($method === 'DELETE') {
    // Ambil input JSON dari JavaScript
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // Kasus 1: Hapus Semua Riwayat
    if (isset($input['mode']) && $input['mode'] === 'all') {
        $query = "DELETE FROM Simulasi";
        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => "Semua riwayat berhasil dihapus"]);
        } else {
            echo json_encode(["error" => "Gagal menghapus semua data"]);
        }
    } 
    // Kasus 2: Hapus Riwayat Per Item
    elseif (isset($input['id'])) {
        $id_simulasi = mysqli_real_escape_string($conn, $input['id']);
        $query = "DELETE FROM Simulasi WHERE id_simulasi = '$id_simulasi'";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(["success" => "Data berhasil dihapus"]);
        } else {
            echo json_encode(["error" => "Gagal menghapus data"]);
        }
    } 
    else {
        echo json_encode(["error" => "Data hapus tidak valid"]);
    }
}

// Tutup koneksi
mysqli_close($conn);
?>