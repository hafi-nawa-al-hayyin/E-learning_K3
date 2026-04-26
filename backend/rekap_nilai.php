<?php 
session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Proteksi Halaman: Hanya Dosen dan Admin yang boleh masuk
if ($_SESSION['role'] !== 'dosen' && $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini hanya untuk Dosen atau Admin.'); window.location.href='index.php';</script>";
    exit();
}

include 'koneksi.php'; 
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K3-VirtuAI - Rekap Nilai</title>
    
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; margin: 0; background: #04070d; color: #e5f0ff; }
        
        .nav { position: sticky; top: 0; z-index: 999; background: #000000; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333333; }
        .main-container { padding: 20px; max-width: 1200px; margin: 0 auto; }

        .card { background: #0f172a; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #3b82f6; box-shadow: 0 4px 8px rgba(59,130,246,0.1); }
        .section-title { margin-bottom: 15px; font-size: 1.1em; color: #007bff; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e40af; color: #e5f0ff; text-align: left; padding: 12px; font-size: 0.9em; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; font-size: 0.85em; }
        tr:hover { background: rgba(59, 130, 246, 0.1); }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .badge-lulus { background: rgba(40, 167, 69, 0.2); color: #28a745; }
        .badge-gagal { background: rgba(220, 53, 69, 0.2); color: #dc3545; }
        
        .btn-back { display: inline-block; padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background: #0056b3; }
    </style>
</head>
<body>

<nav class="nav">
    <div style="font-size: 1.4em; font-weight: bold; color: #ffffff;">K3-VirtuAI 🛡️</div>
    
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="index.php" style="color: #ffffff; text-decoration: none;">Dashboard</a>
        <a href="rekap_nilai.php" style="color: #ffffff; text-decoration: none; font-weight: bold;">Rekap Nilai</a>
        <a href="logout.php" style="color: #ffffff; text-decoration: none; border: 1px solid #ffffff; padding: 5px 10px; border-radius: 4px;">Keluar (<?php echo $_SESSION['nama']; ?>)</a>
    </div>
</nav>

<div class="main-container">
    
    <div style="margin-bottom: 15px;">
        <a href="index.php" class="btn-back">⬅ Kembali ke Dashboard</a>
    </div>

    <div class="card">
        <div class="section-title">📊 Rekap Nilai Akhir & Rata-rata Mahasiswa</div>
        
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th style="text-align: center;">Total Percobaan</th>
                    <th style="text-align: center;">Skor Tertinggi</th>
                    <th style="text-align: center;">Rata-rata Skor</th>
                    <th style="text-align: center;">Status Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Query sakti untuk mengelompokkan data per mahasiswa
                $query_rekap = "SELECT 
                                    u.nim_nidn, 
                                    u.nama_lengkap, 
                                    COUNT(s.id_simulasi) as total_percobaan,
                                    MAX(s.skor) as skor_tertinggi,
                                    AVG(s.skor) as rata_skor
                                FROM users u
                                LEFT JOIN simulasi s ON u.id_user = s.id_user
                                WHERE u.role = 'mahasiswa'
                                GROUP BY u.id_user
                                ORDER BY u.nim_nidn ASC";

                $eksekusi = mysqli_query($conn, $query_rekap);

                if(mysqli_num_rows($eksekusi) == 0){
                    echo "<tr><td colspan='6' style='text-align:center; color:#95a5a6;'>Belum ada data mahasiswa yang terdaftar.</td></tr>";
                }

                while($data = mysqli_fetch_assoc($eksekusi)) {
                    $percobaan = $data['total_percobaan'];
                    $tertinggi = $data['skor_tertinggi'] ?? 0;
                    $rata_rata = $data['rata_skor'] !== null ? round($data['rata_skor'], 1) : 0;

                    // Penentuan status akhir (Lulus jika skor tertinggi >= 70)
                    if ($percobaan == 0) {
                        $status_badge = "<span style='color: #7f8c8d;'>Belum Mencoba</span>";
                    } elseif ($tertinggi >= 70) {
                        $status_badge = "<span class='badge badge-lulus'>LULUS</span>";
                    } else {
                        $status_badge = "<span class='badge badge-gagal'>GAGAL</span>";
                    }

                    echo "<tr>
                            <td>".$data['nim_nidn']."</td>
                            <td>".$data['nama_lengkap']."</td>
                            <td style='text-align: center;'>".$percobaan."x</td>
                            <td style='text-align: center; color: #f1c40f; font-weight: bold;'>".$tertinggi."</td>
                            <td style='text-align: center;'>".$rata_rata."</td>
                            <td style='text-align: center;'>".$status_badge."</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>