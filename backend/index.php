<?php
// 1. Session ditaruh paling atas agar tidak error "headers already sent"
session_start();

// 2. Cek apakah user sudah login, jika belum lempar ke login.php
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// 3. Masukkan koneksi database dan query skenario
$koneksi = mysqli_connect("localhost", "root", "", "elearning_k3");

// Cek koneksi agar aman
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$query_skenario = mysqli_query($koneksi, "SELECT skenario_aktif FROM pengaturan_skenario WHERE id = 1");
$data_skenario = mysqli_fetch_assoc($query_skenario);
$skenario_dari_admin = $data_skenario['skenario_aktif'] ?? 'Kebocoran Pipa Gas'; // Fallback default
?>

<?php
// ================= LOGIKA BACKEND (KHUSUS ADMIN) =================

// 1. LOGIKA TAMBAH USER 
if (isset($_POST['tambah_user']) && $_SESSION['role'] === 'admin') {
    $nama = trim(mysqli_real_escape_string($koneksi, $_POST['nama']));
    $nim = trim(mysqli_real_escape_string($koneksi, $_POST['nim']));
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $password = password_hash('123456', PASSWORD_DEFAULT); // Default password
    
    if ($nama === '' || $nim === '') {
        echo "<script>alert('Nama dan NIM wajib diisi.');</script>";
    } else {
        // Validasi agar tidak ada NIM ganda
        $cekUser = mysqli_query($koneksi, "SELECT * FROM users WHERE nim_nidn = '$nim' LIMIT 1");
        if (mysqli_num_rows($cekUser) > 0) {
            echo "<script>alert('Error: NIM/NIDN tersebut sudah terdaftar!');</script>";
        } else {
            $query = "INSERT INTO users (nama_lengkap, nim_nidn, role, password) VALUES ('$nama', '$nim', '$role', '$password')";
            if (mysqli_query($koneksi, $query)) {
                echo "<script>alert('Peserta berhasil ditambahkan! Password default: 123456'); window.location.href='index.php';</script>";
            } else {
                echo "Error: " . mysqli_error($koneksi);
            }
        }
    }
}

// 2. LOGIKA HAPUS USER
if (isset($_GET['hapus_user']) && $_SESSION['role'] === 'admin') {
    $id_user = mysqli_real_escape_string($koneksi, $_GET['hapus_user']);
    $query = "DELETE FROM users WHERE id_user = '$id_user'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Peserta berhasil dihapus!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// 3. LOGIKA HAPUS SATU RIWAYAT
if (isset($_GET['hapus_riwayat']) && $_SESSION['role'] === 'admin') {
    $id_simulasi = mysqli_real_escape_string($koneksi, $_GET['hapus_riwayat']);
    $query = "DELETE FROM simulasi WHERE id_simulasi = '$id_simulasi'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Satu rekaman riwayat berhasil dihapus!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

// 4. LOGIKA KOSONGKAN SEMUA RIWAYAT
if (isset($_POST['kosongkan_riwayat']) && $_SESSION['role'] === 'admin') {
    $query = "DELETE FROM simulasi";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Semua riwayat berhasil dikosongkan!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K3-VirtuAI - Dashboard Simulation</title>
    
    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; margin: 0; background: #ffffff; color: #000000; }
        
        .nav { position: sticky; top: 0; z-index: 999; background: #000000; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333333; }
        .main-container { padding: 20px; max-width: 1200px; margin: 0 auto; }

        .card { background: #ffffff; padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #007bff; box-shadow: 0 4px 8px rgba(0,123,255,0.1); }
        .section-title { margin-bottom: 15px; font-size: 1.1em; color: #007bff; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #007bff; color: #ffffff; text-align: left; padding: 10px; font-size: 0.9em; }
        td { padding: 10px; border-bottom: 1px solid #dee2e6; font-size: 0.85em; }

        .simulation-window { background: #f8f9fa; height: 450px; border-radius: 12px; position: relative; overflow: hidden; border: 3px solid #007bff; transition: 0.3s; }
        
        .hazard-overlay { position: absolute; top: 15%; left: 50%; transform: translate(-50%, -50%); color: #dc3545; font-weight: bold; font-size: 1.8em; display: none; text-shadow: 0 0 10px rgba(255,255,255,0.8); z-index: 999; text-align: center; pointer-events: none; width: 100%; }

        .btn { padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; color: white; margin-bottom: 10px; }
        .btn-start { background: #dc3545; }
        .btn-start:hover { background: #c82333; }
        .btn-emergency { background: #007bff; }
        .btn-emergency:hover { background: #0056b3; }
        .btn-delete { background: #dc3545; padding: 5px 10px; font-size: 0.8em; border-radius: 4px; text-decoration: none; color: white; }
        .btn-delete:hover { background: #c82333; }
        
        .input-field { padding: 10px; background: #ffffff; border: 2px solid #007bff; color: #000000; border-radius: 5px; margin-bottom: 10px; width: 100%; }
        .input-field:focus { border-color: #0056b3; box-shadow: 0 0 5px rgba(0,123,255,0.3); }
        .ai-status { background: #e9ecef; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 0.85em; color: #007bff; border-left: 4px solid #007bff; }
    </style>
</head>
<body>

<nav class="nav">
    <div style="font-size: 1.4em; font-weight: bold; color: #ffffff;">K3-VirtuAI 🛡️</div>
    
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="index.php" style="color: #ffffff; text-decoration: none; font-weight: bold;">Dashboard</a>
        
        <?php if ($_SESSION['role'] === 'mahasiswa') : ?>
            <a href="profil.php" style="color: #ffffff; text-decoration: none;">Profil Saya</a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'dosen' || $_SESSION['role'] === 'admin') : ?>
            <a href="rekap_nilai.php" style="color: #ffffff; text-decoration: none;">Rekap Nilai</a>
<a href="admin_skenario_fixed.php" style="color: #ffffff; text-decoration: none;">Atur Skenario</a>
        <?php endif; ?>

        <div id="connectionStatus">Status: <span style="color:#28a745">● Terhubung</span></div>
        
        <a href="logout.php" style="color: #ffffff; text-decoration: none; margin-left: 10px; border: 1px solid #ffffff; padding: 5px 10px; border-radius: 4px;">Keluar (<?php echo $_SESSION['nama']; ?>)</a>
    </div>
</nav>

<div class="main-container">
    
    <?php if ($_SESSION['role'] === 'admin') : ?>
    <div class="card">
        <div class="section-title">➕ Manajemen Peserta</div>
        
        <form method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
            <input type="text" name="nama" class="input-field" placeholder="Nama Lengkap" required style="margin-bottom:0;">
            <input type="text" name="nim" class="input-field" placeholder="NIM" required style="margin-bottom:0;">
            <select name="role" class="input-field" required style="margin-bottom:0;">
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="tambah_user" class="btn btn-start" style="width: 150px; margin: 0;">Simpan</button>
        </form>

        <div style="max-height: 150px; overflow-y: auto; border: 1px solid #0f3460; border-radius: 6px;">
            <table style="margin-top:0;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Role</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $listUser = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                    if(mysqli_num_rows($listUser) == 0){
                        echo "<tr><td colspan='4' style='text-align:center; color:#95a5a6;'>Belum ada peserta terdaftar.</td></tr>";
                    }
                    while($lu = mysqli_fetch_assoc($listUser)) {
                        echo "<tr>
                                <td>".$lu['nama_lengkap']."</td>
                                <td>".$lu['nim_nidn']."</td>
                                <td>".$lu['role']."</td>
                                <td style='text-align:center;'>
                                    <a href='index.php?hapus_user=".$lu['id_user']."' style='color:#e74c3c; text-decoration:none;' onclick='return confirm(\"Hapus user ini?\")'>Hapus</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div style="display: flex; gap: 20px; align-items: stretch; margin-bottom: 20px;">
        
        <div class="simulation-window" id="simWindow" style="flex: 2;">
            <div id="hazardText" class="hazard-overlay">⚠️ BAHAYA TERDETEKSI!</div>
            
            <a-scene embedded style="height: 100%; width: 100%;">
                <a-entity id="cameraK3" camera position="0 1.6 0" look-controls>
                    <a-cursor id="kursorK3" color="#ffaa00"></a-cursor>
                </a-entity>
                <a-sky color="#ECECEC"></a-sky>

                <a-plane position="0 0 -3" rotation="-90 0 0" width="10" height="10" color="#7f8c8d" opacity="0.5"></a-plane>
                
                <a-entity id="indukMesinPipa" visible="true">
                    <a-box id="kubusMesin" width="0.8" height="0.8" depth="0.8" color="#2c3e50" position="0 0.4 -3"></a-box>
                    <a-cylinder radius="0.06" height="1.2" color="#7f8c8d" position="0 1.2 -3"></a-cylinder>
                    <a-sphere radius="0.09" color="#e74c3c" position="0 1.8 -3"></a-sphere>
                    <a-cylinder id="pipaHorizontal" radius="0.06" height="3" color="#7f8c8d" position="1.5 1.8 -3" rotation="0 0 90"></a-cylinder>
                    <a-circle id="efekGenanganOli" radius="1.2" color="#f1c40f" position="0 0.01 -3" rotation="-90 0 0" opacity="0.8" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.15 1.15 1.15; dur: 800; loop: true; dir: alternate;"></a-circle>
                    <a-sphere id="efekBocorPipa" radius="0.3" color="#e67e22" position="1.5 1.8 -3" opacity="0.7" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 700; loop: true; dir: alternate;"></a-sphere>
                    <a-sphere id="efekListrik" radius="0.25" color="#ffaa00" position="0 1.2 -2.5" opacity="0.9" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.25 1.25 1.25; dur: 600; loop: true; dir: alternate;"></a-sphere>
                    <a-sphere id="efekHancur" radius="0.6" color="#ff4500" position="0 1.2 -3" opacity="0.9" visible="false" animation="property: scale; from: 1 1 1; to: 1.18 1.18 1.18; dur: 750; loop: true; dir: alternate;"></a-sphere>
                </a-entity>
            </a-scene>
            
            <div id="timerText" style="position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%); color: #fff; font-size: 2em; font-weight: bold; z-index: 999; display: none; text-shadow: 0 0 10px #000;"></div>
        </div>

        <div style="flex: 1.2; display: flex; flex-direction: column; gap: 20px;">
            
            <div class="card" style="flex: 1; margin-bottom: 0; display: flex; flex-direction: column; gap: 10px;">
                <h3 style="margin-top:0; font-size: 1.1em; color: #3498db;">Simulator Control</h3>
                
                <label style="font-size: 0.85em; color: #3498db;">Peserta Aktif:</label>
                <select id="pilihPeserta" class="input-field" style="margin-top: 5px;">
                    <?php if ($_SESSION['role'] === 'mahasiswa') : ?>
                        <option value="<?php echo $_SESSION['id_user']; ?>"><?php echo $_SESSION['nama']; ?> (Pribadi)</option>
                    <?php elseif ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'dosen') : ?>
                        <option value="<?php echo $_SESSION['id_user']; ?>"><?php echo $_SESSION['nama']; ?> (Akun Pribadi)</option>
                    <?php else : ?>
                        <option value="">-- Pilih Nama --</option>
                        <?php 
                        $ambilUser = mysqli_query($koneksi, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                        while($u = mysqli_fetch_assoc($ambilUser)) {
                            echo "<option value='".$u['id_user']."'>".$u['nama_lengkap']." (".$u['nim_nidn'].")</option>";
                        }
                        ?>
                    <?php endif; ?>
                </select>

                <button class="btn btn-start" onclick="startSim()">MULAI</button>
                <button class="btn btn-emergency" onclick="ambilTindakan()" style="height: 60px; font-size: 1.1em;">TINDAKAN DARURAT</button>
                
                <div class="ai-status" id="aiLog" style="margin-top: auto;">> System Online</div>
            </div>

            <div class="card" style="flex: 1; margin-bottom: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div class="section-title" style="margin-bottom: 10px;">📊 Statistik Kelulusan</div>
                
                <?php 
                $qLulus = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'LULUS'");
                $dLulus = mysqli_fetch_assoc($qLulus);
                $jumlahLulus = $dLulus['total'] ?? 0;

                $qGagal = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'GAGAL'");
                $dGagal = mysqli_fetch_assoc($qGagal);
                $jumlahGagal = $dGagal['total'] ?? 0;
                ?>

                <div style="width: 130px; height: 130px;">
                    <canvas id="grafikK3"></canvas>
                </div>
                <div id="chartStatSummary" style="margin-top: 12px; font-size: 0.95em; color: #222; text-align: center;">
                    Memuat statistik kelulusan...
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div class="section-title" style="margin-bottom:0;">🏆 Riwayat Hasil Simulasi</div>
            
            <?php if ($_SESSION['role'] === 'admin') : ?>
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat simulasi?')">
                <button type="submit" name="kosongkan_riwayat" class="btn-delete" style="border:none; cursor:pointer; padding: 8px 12px;">Kosongkan Semua Riwayat</button>
            </form>
            <?php endif; ?>
        </div>
        
        <table>
            <thead>
                <tr>
                   <th>Nama Peserta</th>
                   <th>Risiko</th>
                   <th>Skor</th>
                   <th>Status</th>
                   <th>Rekomendasi AI</th> 
                   <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($_SESSION['role'] === 'mahasiswa') {
                    $id_mhs = $_SESSION['id_user'];
                    $ambilSimulasi = mysqli_query($koneksi, "SELECT users.nama_lengkap, simulasi.* FROM simulasi 
                                                            JOIN users ON simulasi.id_user = users.id_user 
                                                            WHERE simulasi.id_user = '$id_mhs'
                                                            ORDER BY id_simulasi DESC");
                } else {
                    $ambilSimulasi = mysqli_query($koneksi, "SELECT users.nama_lengkap, simulasi.* FROM simulasi 
                                                            JOIN users ON simulasi.id_user = users.id_user 
                                                            ORDER BY id_simulasi DESC");
                }

                if(mysqli_num_rows($ambilSimulasi) == 0){
                    echo "<tr><td colspan='6' style='text-align:center; color:#95a5a6;'>Belum ada riwayat simulasi.</td></tr>";
                }

                while($s = mysqli_fetch_assoc($ambilSimulasi)) {
                    $color = ($s['status_kelulusan'] == "LULUS") ? "#28a745" : "#dc3545";
                    echo "<tr>
                            <td>".$s['nama_lengkap']."</td>
                            <td>".$s['jenis_risiko']."</td>
                            <td>".$s['skor']."</td> 
                            <td style='color: $color; font-weight:bold;'>".$s['status_kelulusan']."</td>
                            <td>".$s['rekomendasi']."</td>
                            <td style='text-align: center;'>";
                                
                                if ($_SESSION['role'] === 'admin') {
                                    echo "<a href='index.php?hapus_riwayat=".$s['id_simulasi']."' class='btn-delete' onclick='return confirm(\"Hapus rekaman riwayat ini?\")'>Hapus</a>";
                                } else {
                                    echo "<span style='color: #7f8c8d;'>No Action</span>";
                                }
                                
                    echo "</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="section-title">📋 Riwayat Keputusan Pengguna (Decision Log)</div>
        
        <table>
            <thead>
                <tr>
                   <th>Nama Peserta</th>
                   <th>Risiko</th>
                   <th>Tindakan Dipilih</th>
                   <th>Skor</th>
                   <th>Status</th>
                   <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($_SESSION['role'] === 'mahasiswa') {
                    $id_mhs = $_SESSION['id_user'];
                    $ambilLogs = mysqli_query($koneksi, "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs 
                                                        JOIN users ON decision_logs.id_user = users.id_user 
                                                        WHERE decision_logs.id_user = '$id_mhs'
                                                        ORDER BY decision_logs.created_at DESC");
                } else {
                    $ambilLogs = mysqli_query($koneksi, "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs 
                                                        JOIN users ON decision_logs.id_user = users.id_user 
                                                        ORDER BY decision_logs.created_at DESC");
                }

                if(mysqli_num_rows($ambilLogs) == 0){
                    echo "<tr><td colspan='6' style='text-align:center; color:#95a5a6;'>Belum ada riwayat keputusan.</td></tr>";
                }

                while($l = mysqli_fetch_assoc($ambilLogs)) {
                    $color = ($l['status_kelulusan'] == "LULUS") ? "#28a745" : "#dc3545";
                    echo "<tr>
                            <td>".$l['nama_lengkap']."</td>
                            <td>".$l['jenis_risiko']."</td>
                            <td>".$l['tindakan_dipilih']."</td>
                            <td>".$l['skor']."</td> 
                            <td style='color: $color; font-weight:bold;'>".$l['status_kelulusan']."</td>
                            <td>".date('d/m/Y H:i', strtotime($l['created_at']))."</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    let waktuBahaya = 0;
    let isRunning = false;
    let jenisRisikoAktif = "";
    let countdownInterval = null;
    const currentRole = "<?php echo $_SESSION['role']; ?>";

    // 1. DAFTAR 5 SKENARIO K3 RESMI
    const skenarioK3 = [
        {
            jenis_risiko: "Kebocoran Pipa Gas",
            soal: "Sensor mendeteksi adanya kebocoran gas beracun di area produksi. Apa tindakan darurat pertama yang harus Anda lakukan?",
            pilihan: [
                { teks: "Segera keluar menuju titik kumpul mengikuti arah evakuasi, dan beri tahu tim K3/rekans kerja.", skor: 100, konsekuensi: "Tepat! Tanggap cepat, evakuasi terkoordinasi, mengurangi risiko paparan gas.", rekomendasi: "Jangan kembali sebelum area dinyatakan aman oleh petugas K3." },
                { teks: "Mencari sumber kebocoran untuk mencoba menutupnya sendiri.", skor: 30, konsekuensi: "Fatal! Anda terpapar gas beracun konsentrasi tinggi dan pingsan.", rekomendasi: "Dilarang keras menangani kebocoran gas tanpa APD khusus (Breathing Apparatus)." },
                { teks: "Berteriak memanggil rekan kerja di dalam ruangan.", skor: 40, konsekuensi: "Berisiko! Anda membuang waktu evakuasi dan menghirup lebih banyak gas.", rekomendasi: "Gunakan tombol alarm evakuasi daripada berteriak untuk meminimalkan hirupan nafas." },
                { teks: "Diam di tempat menunggu instruksi lebih lanjut lewat speaker.", skor: 50, konsekuensi: "Berbahaya! Gas terus menyebar sementara Anda tetap terpapar.", rekomendasi: "Dapatkan jarak aman dulu sebelum mengikuti arahan jika masih memungkinkan." }
            ]
        },
        {
            jenis_risiko: "Korsleting Listrik",
            soal: "Terlihat percikan api dan kepulan asap dari panel listrik mesin utama. Apa tindakan Anda?",
            pilihan: [
                { teks: "Mengambil APAR jenis Foam untuk memadamkannya.", skor: 40, konsekuensi: "Berbahaya! APAR jenis foam berbasis air dan masih bisa menghantarkan listrik.", rekomendasi: "Gunakan APAR khusus kelas C (Kelistrikan) seperti CO2 agar tidak tersetrum." },
                { teks: "Menyiram percikan api tersebut menggunakan air.", skor: 0, konsekuensi: "Fatal! Anda tersengat aliran listrik tegangan tinggi (Electrocuted).", rekomendasi: "Jangan sekali-kali menyiram kebakaran listrik dengan air. Gunakan APAR jenis CO2 atau Powder." },
                { teks: "Mematikan saklar pusat (Main Breaker), kemudian panggil tim listrik/ K3 sambil menjaga keamanan area.", skor: 100, konsekuensi: "Tepat! Menghentikan sumber munculnya percikan dan menunggu teknisi.", rekomendasi: "Gunakan APAR CO2/residu rendah bila diperlukan, serta jangan ada air di area." },
                { teks: "Memanggil teknisi listrik tanpa melakukan tindakan pengamanan.", skor: 30, konsekuensi: "Lambat dan berisiko jika sumber arus belum diputus.", rekomendasi: "Lakukan pemutusan arus mandiri sebelum menunggu teknisi." }
            ]
        },
        {
            jenis_risiko: "Tumpahan Oli",
            soal: "Sebuah jeriken berisi cairan berbahaya (B3) tumpah meluas di lantai laboratorium. Bagaimana tindakan Anda?",
            pilihan: [
                { teks: "Meninggalkan ruangan dan mengunci pintunya agar uap tidak keluar.", skor: 40, konsekuensi: "Kurang tepat karena tidak ada langkah mitigasi kontak awal.", rekomendasi: "Isolasi area dan segera koordinasi dengan respons B3." },
                { teks: "Mengelapnya langsung menggunakan kain pel biasa.", skor: 20, konsekuensi: "Fatal! Kain pel rusak dan uap korosif mengenai wajah Anda.", rekomendasi: "Bahan kimia B3 korosif memerlukan penanganan khusus, bukan alat pembersih rumah tangga." },
                { teks: "Membilas tumpahan dengan air dalam jumlah banyak ke saluran drainase.", skor: 30, konsekuensi: "Buruk! Anda mencemari air tanah dan melanggar hukum lingkungan.", rekomendasi: "Dilarang membuang limbah B3 langsung ke saluran air umum tanpa dinetralkan." },
                { teks: "Membatasi area, pakai APD lengkap, dan laporkan ke tim K3 untuk spill kit/bahan netralisasi.", skor: 100, konsekuensi: "Tepat! Baik keselamatan diri maupun tindakan teknis terkoordinasi.", rekomendasi: "Jangan gunakan pel biasa; gunakan peralatan khusus B3 dan pertahankan pengawasan area." }
            ]
        },
        {
            jenis_risiko: "Kebakaran Area Panel",
            soal: "Muncul api kecil di tumpukan kardus dekat panel evakuasi. Apa yang harus Anda lakukan?",
            pilihan: [
                { teks: "Meniup api tersebut agar padam.", skor: 40, konsekuensi: "Buruk! Tiupan Anda justru memberikan oksigen tambahan dan membesarkan api.", rekomendasi: "Gunakan APAR atau karung goni basah untuk memutus rantai oksigen api." },
                { teks: "Mengabaikannya karena apinya masih berukuran kecil.", skor: 10, konsekuensi: "Fatal! Dalam hitungan menit api membesar dan melahap seluruh ruangan.", rekomendasi: "Jangan pernah menyepelekan api sekecil apa pun di area industri." },
                { teks: "Mengambil APAR CO2 atau powder dan memadamkan api kecil sesuai teknik PASS.", skor: 100, konsekuensi: "Tepat! Api dikendalikan cepat dan risiko menyebar berkurang.", rekomendasi: "Kondisi aman, lalu laporkan ke tim K3 dan pastikan sumber energi terisolasi." },
                { teks: "Berlari kencang mencari hydrant gedung.", skor: 50, konsekuensi: "Kurang efektif! Hydrant terlalu besar untuk api kecil dan membuang waktu.", rekomendasi: "Gunakan APAR portabel untuk kebakaran tahap awal (mula)." }
            ]
        },
        {
            jenis_risiko: "Evakuasi Gempa Bumi",
            soal: "Terjadi gempa bumi berkekuatan cukup besar saat Anda berada di lantai 3. Tindakan terbaiknya adalah?",
            pilihan: [
                { teks: "Langsung berlari sekencang mungkin menuju tangga darurat.", skor: 40, konsekuensi: "Berisiko! Anda bisa terjatuh di tangga akibat guncangan yang belum berhenti.", rekomendasi: "Tunggu guncangan utama mereda sedikit sebelum melakukan pergerakan evakuasi." },
                { teks: "Berlindung di bawah meja kokoh (Drop, Cover, Hold on), lalu evakuasi setelah guncangan mereda.", skor: 100, konsekuensi: "Tepat! Melindungi tubuh dari reruntuhan dan menjaga keamanan saat awal gempa.", rekomendasi: "Tetap di tempat terlindung hingga gempa berhenti, kemudian ke titik kumpul." },
                { teks: "Menggunakan lift agar lebih cepat sampai ke lantai dasar.", skor: 0, konsekuensi: "Fatal! Listrik mati dan Anda terjebak di dalam lift yang macet.", rekomendasi: "Dilarang keras menggunakan lift saat terjadi gempa bumi atau kebakaran." },
                { teks: "Berdiri diam di dekat tembok beton utama gedung.", skor: 60, konsekuensi: "Kurang tepat, karena Anda tidak memiliki perlindungan kepala dan tubuh dari benda jatuh.", rekomendasi: "Do-Not digunakan; choose Drop-Cover-Hold atau area terbuka setelah guncangan mereda." }
            ]
        }
    ];

    // Fungsi untuk shake kamera pada skenario gempa bumi
    function shakeCamera(duration = 2000) {
        const camera = document.getElementById("cameraK3");
        if (!camera) return;

        const originalPosition = camera.getAttribute("position");
        const startTime = Date.now();
        const shakeStrength = 0.08; // Intensitas guncangan
        const shakeSpeed = 60; // ms per shake

        const shakeInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;

            if (elapsed > duration) {
                clearInterval(shakeInterval);
                camera.setAttribute("position", originalPosition);
                return;
            }

            // Random offset untuk guncangan realistis
            const offsetX = (Math.random() - 0.5) * shakeStrength;
            const offsetY = (Math.random() - 0.5) * shakeStrength;
            
            const newPos = {
                x: parseFloat(originalPosition.x) + offsetX,
                y: parseFloat(originalPosition.y) + offsetY,
                z: parseFloat(originalPosition.z)
            };

            camera.setAttribute("position", `${newPos.x} ${newPos.y} ${newPos.z}`);
        }, shakeSpeed);
    }

    // Fungsi untuk auto-fail saat waktu habis
    function autoFailTimeout() {
        isRunning = false;
        let idPeserta = document.getElementById("pilihPeserta").value;
        let kategoriRisiko = "Tinggi";
        let skor = 0;

        let konsekuensi = "Waktu habis! Anda tidak mengambil tindakan dengan cepat dan risiko meningkat.";
        let rekomendasi = "Tingkatkan kecepatan respons dan keputusan dalam kondisi darurat. Latihan berkala dan simulasi lebih sering akan membantu.";

        // AJAX Mengirim data nilai simulasi ke database
        let formData = new FormData();
        formData.append('id_user', idPeserta);
        formData.append('jenis_risiko', jenisRisikoAktif);
        formData.append('skor', skor);
        formData.append('status_kelulusan', 'GAGAL');
        formData.append('rekomendasi', rekomendasi);
        formData.append('konsekuensi', konsekuensi);
        formData.append('tindakan_dipilih', 'Tidak ada tindakan (Waktu Habis)'); // Tambahan untuk decision log
        formData.append('kategori_risiko', kategoriRisiko);

        fetch('simpan_skor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'sukses') {
                // Show personalized remedial modal instead of simple alert
                showRemedialModal({
                    jenisRisiko: jenisRisikoAktif,
                    skor: skor,
                    kategoriRisiko: kategoriRisiko,
                    konsekuensi: konsekuensi,
                    rekomendasi: rekomendasi,
                    tindakanDipilih: 'Tidak ada tindakan (Waktu Habis)',
                    alasanGagal: 'Waktu habis tanpa tindakan pencegahan'
                });
            } else {
                alert('Error: Gagal menyimpan data timeout');
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi ke server!');
            window.location.reload();
        });
    }

    function startSim(forcedScenario = null) {
        let selectPeserta = document.getElementById("pilihPeserta");

        if (selectPeserta.value === "") { 
            alert("Pilih peserta dulu!"); 
            return; 
        }
        
        isRunning = true;
        waktuBahaya = 0; 
        clearInterval(countdownInterval);
        
        let elHazard = document.getElementById("hazardText");
        let elTimer = document.getElementById("timerText");
        let elSimWin = document.getElementById("simWindow");
        let aiLog = document.getElementById("aiLog");

        if (elHazard) elHazard.style.display = "none";
        if (elTimer) elTimer.style.display = "none";
        if (elSimWin) elSimWin.style.border = "3px solid #3498db";

        // Reset visual A-Frame
        try {
            document.getElementById("efekGenanganOli").setAttribute('visible', 'false');
            document.getElementById("efekListrik").setAttribute('visible', 'false');
            document.getElementById("efekBocorPipa").setAttribute('visible', 'false');
            document.getElementById("efekHancur").setAttribute('visible', 'false');
        } catch(e) { console.log("Beberapa objek 3D tidak ditemukan."); }

        // MEMANGGIL SKENARIO
        // Jika ada forced scenario (dari retry), gunakan itu
        if (forcedScenario) {
            jenisRisikoAktif = forcedScenario;
            if (aiLog) aiLog.textContent = `> [Retry] Skenario remedial: ${jenisRisikoAktif}`;
        } else {
            // Mahasiswa: acak dari daftar skenario training
            // Admin/Dosen: gunakan skenario yang dipilih di pengaturan skenario
            if (currentRole === 'mahasiswa') {
                const randomSkenario = skenarioK3[Math.floor(Math.random() * skenarioK3.length)];
                jenisRisikoAktif = randomSkenario.jenis_risiko;
                if (aiLog) aiLog.textContent = `> [Auto] Skenario mahasiswa: ${jenisRisikoAktif}`;
            } else {
                jenisRisikoAktif = "<?php echo $skenario_dari_admin; ?>";
                if (aiLog) aiLog.textContent = `> [Admin] Skenario aktif: ${jenisRisikoAktif}`;
            }
        }

        let jeda = Math.floor(Math.random() * 2000) + 1000; 
        
        setTimeout(() => {
            if(isRunning) {
                waktuBahaya = new Date().getTime(); 
                
                if (elHazard) {
                    elHazard.innerHTML = "⚠️ BAHAYA: " + jenisRisikoAktif.toUpperCase();
                    elHazard.style.display = "block";
                }
                if (elSimWin) elSimWin.style.border = "3px solid #e74c3c";
                
                // Trigger animasi 3D A-Frame
                try {
                    if (jenisRisikoAktif === "Tumpahan Oli") document.getElementById("efekGenanganOli").setAttribute('visible', 'true');
                    else if (jenisRisikoAktif === "Kebocoran Pipa Gas") document.getElementById("efekBocorPipa").setAttribute('visible', 'true');
                    else if (jenisRisikoAktif === "Korsleting Listrik") document.getElementById("efekListrik").setAttribute('visible', 'true');
                    else {
                        document.getElementById("efekHancur").setAttribute('visible', 'true');
                        // Trigger camera shake untuk gempa bumi
                        if (jenisRisikoAktif === "Evakuasi Gempa Bumi") {
                            shakeCamera(2000); // Shake selama 2 detik
                        }
                    }
                } catch(e) { console.log("Gagal memunculkan efek visual 3D."); }

                let sisaWaktu = 5; 
                let textTimer = document.getElementById("timerText");
                textTimer.innerHTML = "WAKTU: " + sisaWaktu + "s";
                textTimer.style.display = "block";

                countdownInterval = setInterval(() => {
                    sisaWaktu--;
                    textTimer.innerHTML = "WAKTU: " + sisaWaktu + "s";

                    if (sisaWaktu <= 0) {
                        clearInterval(countdownInterval);
                        textTimer.style.display = "none";
                        autoFailTimeout(); // Auto-fail dengan simpan ke database
                    }
                }, 1000);
            }
        }, jeda);
    }

    function ambilTindakan() {
        if (!isRunning || jenisRisikoAktif === "") {
            isRunning = true;
            let acakSkenario = skenarioK3[Math.floor(Math.random() * skenarioK3.length)];
            jenisRisikoAktif = acakSkenario.jenis_risiko;
        }

        clearInterval(countdownInterval); 
        try {
            document.getElementById("timerText").style.display = "none";
        } catch(e) {}
        
        isRunning = false; 

        let detailSkenario = skenarioK3.find(s => s.jenis_risiko === jenisRisikoAktif);
        if(!detailSkenario) detailSkenario = skenarioK3[0];

        let daftarPilihan = detailSkenario.pilihan;
        let teksPrompt = `⚠️ BAHAYA ${jenisRisikoAktif.toUpperCase()} TERDETEKSI! ⚠️\n\n${detailSkenario.soal}\n\nPilih tindakan yang paling tepat:\n\n`;
        
        daftarPilihan.forEach((pil, index) => {
            teksPrompt += `${index + 1}. ${pil.teks}\n`;
        });
        
        teksPrompt += `\nKetik angka pilihan Anda (1, 2, 3, atau 4):`;
        
        let inputUser = prompt(teksPrompt);
        let indexTerpilih = parseInt(inputUser) - 1;

        if (isNaN(indexTerpilih) || indexTerpilih < 0 || indexTerpilih > 3) {
            alert("Pilihan tidak valid! Simulasi dibatalkan.");
            window.location.reload();
            return;
        }

        let hasilPilihan = daftarPilihan[indexTerpilih];
        let idPeserta = document.getElementById("pilihPeserta").value;

        // FITUR METRIK DOSEN: Menentukan Kategori Risiko
        let kategoriRisiko = "Tinggi";
        if (hasilPilihan.skor === 100) kategoriRisiko = "Rendah";
        else if (hasilPilihan.skor >= 50) kategoriRisiko = "Sedang";

        // FITUR NOVELTY: Modul Remedial AI jika jawaban salah
        let modulRemedial = "";
        if (hasilPilihan.skor < 70) {
            modulRemedial = `\n\n📚 [MODUL PEMBELAJARAN REMEDIAL AI]:\nAnda gagal dalam simulasi ini. Pelajari kembali SOP penanganan '${jenisRisikoAktif}'. Selalu utamakan keselamatan diri terlebih dahulu dan pahami penggunaan APAR/Spill Kit yang sesuai dengan SOP K3.`;
        }

        // AJAX Mengirim data nilai simulasi ke database
        let formData = new FormData();
        formData.append('id_user', idPeserta);
        formData.append('jenis_risiko', jenisRisikoAktif);
        formData.append('skor', hasilPilihan.skor);
        formData.append('status_kelulusan', hasilPilihan.skor >= 70 ? 'LULUS' : 'GAGAL');
        formData.append('rekomendasi', hasilPilihan.rekomendasi);
        formData.append('konsekuensi', hasilPilihan.konsekuensi);
        formData.append('tindakan_dipilih', hasilPilihan.teks); // Tambahan untuk decision log
        formData.append('kategori_risiko', kategoriRisiko); // Baru untuk metrik dosen

        fetch('simpan_skor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'sukses') {
                if (hasilPilihan.skor >= 70) {
                    // Lulus - tampilkan alert sederhana
                    let pesan = `✅ LULUS SIMULASI\n${'='.repeat(30)}\n`;
                    pesan += `🎯 Skor: ${hasilPilihan.skor}/100\n`;
                    pesan += `📊 Kategori Risiko: ${kategoriRisiko}\n\n`;
                    pesan += `💡 Rekomendasi: ${hasilPilihan.rekomendasi}\n\n`;
                    pesan += `🎉 Selamat! Anda telah menguasai penanganan ${jenisRisikoAktif}`;

                    alert(pesan);
                    window.location.reload();
                } else {
                    // Gagal - tampilkan modal remedial personalized
                    showRemedialModal({
                        jenisRisiko: jenisRisikoAktif,
                        skor: hasilPilihan.skor,
                        kategoriRisiko: kategoriRisiko,
                        konsekuensi: hasilPilihan.konsekuensi,
                        rekomendasi: hasilPilihan.rekomendasi,
                        tindakanDipilih: hasilPilihan.teks,
                        alasanGagal: 'Tindakan yang dipilih tidak sesuai prosedur K3'
                    });
                }
            } else {
                alert('Gagal menyimpan data: ' + data.pesan);
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan koneksi ke server!');
            window.location.reload();
        });
    }

    // Load Chart.js untuk grafik
    const ctx = document.getElementById('grafikK3').getContext('2d');
    const chartK3 = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Lulus', 'Gagal'],
            datasets: [{
                data: [<?php echo $jumlahLulus; ?>, <?php echo $jumlahGagal; ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });

    async function refreshStatistikKelulusan() {
        try {
            const response = await fetch('api.php?target=stats');
            if (!response.ok) throw new Error('Gagal memuat statistik');

            const stats = await response.json();
            const lulus = parseInt(stats.lulus) || 0;
            const gagal = parseInt(stats.gagal) || 0;
            const total = parseInt(stats.total) || 0;

            chartK3.data.datasets[0].data = [lulus, gagal];
            chartK3.update();

            const summary = document.getElementById('chartStatSummary');
            if (summary) {
                const percentLulus = total ? Math.round((lulus / total) * 100) : 0;
                const percentGagal = total ? Math.round((gagal / total) * 100) : 0;
                summary.textContent = `Lulus: ${lulus} (${percentLulus}%) · Gagal: ${gagal} (${percentGagal}%)`;
            }
        } catch (err) {
            console.error(err);
            const summary = document.getElementById('chartStatSummary');
            if (summary) {
                summary.textContent = 'Gagal memuat statistik kelulusan.';
            }
        }
    }

    refreshStatistikKelulusan();
</script>

<!-- MODAL REMEDIAL PERSONALIZED -->
<div id="remedialModal" class="remedial-modal" style="display: none;">
    <div class="remedial-modal-content">
        <div class="remedial-header">
            <h2 id="remedialTitle">📚 PEMBELAJARAN REMEDIAL PERSONALIZED</h2>
            <span class="remedial-close" onclick="closeRemedialModal()">&times;</span>
        </div>

        <div class="remedial-body">
            <div class="remedial-3d-container">
                <a-scene embedded id="remedialScene" style="height: 300px; width: 100%;">
                    <a-entity camera position="0 1.6 0" look-controls>
                        <a-cursor color="#007bff"></a-cursor>
                    </a-entity>
                    <a-sky color="#f8f9fa"></a-sky>
                    <a-plane position="0 0 -3" rotation="-90 0 0" width="8" height="8" color="#e9ecef" opacity="0.8"></a-plane>

                    <!-- Visualisasi Remedial - akan diisi secara dinamis -->
                    <a-entity id="remedialVisualization"></a-entity>

                    <!-- Panduan Interaktif -->
                    <a-text id="remedialText" value="Klik objek untuk mempelajari lebih lanjut" position="0 2 -2" align="center" color="#007bff" scale="0.8 0.8 0.8"></a-text>
                </a-scene>
            </div>

            <div class="remedial-content">
                <div id="remedialAnalysis" class="remedial-analysis">
                    <h3>🔍 ANALISIS KESALAHAN ANDA</h3>
                    <div id="errorAnalysis"></div>
                </div>

                <div id="remedialSteps" class="remedial-steps">
                    <h3>📋 LANGKAH PERBAIKAN</h3>
                    <div id="correctionSteps"></div>
                </div>

                <div id="remedialResources" class="remedial-resources">
                    <h3>🎯 SUMBER BELAJAR</h3>
                    <div id="learningResources"></div>
                </div>
            </div>
        </div>

        <div class="remedial-footer">
            <button onclick="retrySimulation()" class="retry-btn">🔄 Coba Lagi Simulasi</button>
            <button onclick="closeRemedialModal()" class="continue-btn">✅ Lanjut ke Simulasi Baru</button>
        </div>
    </div>
</div>

<style>
.remedial-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remedial-modal-content {
    background: #ffffff;
    border: 2px solid #007bff;
    border-radius: 15px;
    width: 90%;
    max-width: 1200px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 20px rgba(0, 123, 255, 0.3);
}

.remedial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: #ffffff;
}

.remedial-header h2 {
    color: #ffffff;
    margin: 0;
    font-size: 1.5em;
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
}

.remedial-close {
    color: #ffffff;
    font-size: 2em;
    cursor: pointer;
    transition: 0.3s;
    opacity: 0.8;
}

.remedial-close:hover {
    color: #ffcccc;
    transform: scale(1.2);
    opacity: 1;
}

.remedial-body {
    display: flex;
    gap: 20px;
    padding: 20px;
}

.remedial-3d-container {
    flex: 1;
    border: 2px solid #007bff;
    border-radius: 10px;
    overflow: hidden;
    background: #f8f9fa;
}

.remedial-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.remedial-analysis, .remedial-steps, .remedial-resources {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.remedial-analysis h3, .remedial-steps h3, .remedial-resources h3 {
    color: #007bff;
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.1em;
    font-weight: 600;
}

.remedial-analysis p, .remedial-steps li, .remedial-resources li {
    color: #495057;
    line-height: 1.6;
}

.remedial-steps ol {
    padding-left: 20px;
}

.remedial-steps li {
    margin-bottom: 8px;
}

.remedial-resources ul {
    padding-left: 20px;
}

.remedial-footer {
    display: flex;
    gap: 15px;
    justify-content: center;
    padding: 20px;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
}

.retry-btn, .continue-btn {
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.retry-btn {
    background: #ffc107;
    color: #000;
    border: 2px solid #ffc107;
}

.retry-btn:hover {
    background: #ffca2c;
    border-color: #ffca2c;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.continue-btn {
    background: #007bff;
    color: #fff;
    border: 2px solid #007bff;
}

.continue-btn:hover {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

@media (max-width: 768px) {
    .remedial-body {
        flex-direction: column;
    }

    .remedial-3d-container {
        height: 250px;
    }

    .remedial-footer {
        flex-direction: column;
    }

    .retry-btn, .continue-btn {
        width: 100%;
    }
}
</style>

<script>
    let remedialData = {};

    function showRemedialModal(data) {
        remedialData = data;
        const modal = document.getElementById('remedialModal');
        const title = document.getElementById('remedialTitle');
        const analysis = document.getElementById('errorAnalysis');
        const steps = document.getElementById('correctionSteps');
        const resources = document.getElementById('learningResources');

        // Set title
        title.innerHTML = `📚 PEMBELAJARAN REMEDIAL: ${data.jenisRisiko}`;

        // Set analysis
        analysis.innerHTML = generateErrorAnalysis(data);

        // Set correction steps
        steps.innerHTML = generateCorrectionSteps(data);

        // Set learning resources
        resources.innerHTML = generateLearningResources(data);

        // Setup 3D visualization
        setupRemedialVisualization(data);

        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRemedialModal() {
        const modal = document.getElementById('remedialModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset 3D scene
        const scene = document.getElementById('remedialScene');
        if (scene) {
            const visualization = document.getElementById('remedialVisualization');
            if (visualization) {
                visualization.innerHTML = '';
            }
        }
    }

    function retrySimulation() {
        closeRemedialModal();
        
        // Langsung mulai simulasi pada skenario yang sama
        startSim(remedialData.jenisRisiko);
    }

    function generateErrorAnalysis(data) {
        let analysis = '';

        switch(data.jenisRisiko) {
            case 'Kebocoran Pipa Gas':
                if (data.tindakanDipilih.includes('memperbaiki') || data.tindakanDipilih.includes('langsung')) {
                    analysis = `
                        <p><strong>Kesalahan Utama:</strong> Anda mencoba memperbaiki pipa bocor secara langsung tanpa APD yang memadai.</p>
                        <p><strong>Akibatnya:</strong> Terpapar gas berbahaya yang dapat menyebabkan keracunan, kebakaran, atau ledakan.</p>
                        <p><strong>Analisis:</strong> Prioritas utama dalam K3 adalah keselamatan diri. Tindakan perbaikan harus dilakukan oleh tim teknis yang terlatih dengan peralatan yang sesuai.</p>
                    `;
                } else if (data.skor === 0) {
                    analysis = `
                        <p><strong>Kesalahan Utama:</strong> Waktu habis tanpa tindakan pencegahan yang tepat.</p>
                        <p><strong>Akibatnya:</strong> Gas bocor terus menyebar, membahayakan area kerja dan lingkungan sekitar.</p>
                        <p><strong>Analisis:</strong> Respon cepat dalam situasi darurat K3 sangat krusial. Setiap detik berharga untuk mencegah eskalasi bahaya.</p>
                    `;
                }
                break;

            case 'Korsleting Listrik':
                if (data.tindakanDipilih.includes('air')) {
                    analysis = `
                        <p><strong>Kesalahan Utama:</strong> Menggunakan air untuk memadamkan kebakaran listrik.</p>
                        <p><strong>Akibatnya:</strong> Air adalah konduktor listrik yang dapat memperburuk korsleting dan menyebabkan sengatan listrik fatal.</p>
                        <p><strong>Analisis:</strong> Setiap jenis kebakaran membutuhkan jenis pemadam yang sesuai. Kebakaran listrik harus menggunakan APAR CO2 atau bahan non-konduktif.</p>
                    `;
                }
                break;

            case 'Tumpahan Oli':
                analysis = `
                    <p><strong>Kesalahan Utama:</strong> Penanganan tumpahan oli tidak sesuai prosedur K3.</p>
                    <p><strong>Akibatnya:</strong> Resiko tergelincir, pencemaran lingkungan, dan bahaya kebakaran.</p>
                    <p><strong>Analisis:</strong> Tumpahan bahan kimia berbahaya memerlukan penanganan khusus dengan peralatan Spill Kit yang sesuai.</p>
                `;
                break;

            default:
                analysis = `
                    <p><strong>Kesalahan Utama:</strong> Tindakan tidak sesuai dengan prosedur K3 yang benar.</p>
                    <p><strong>Akibatnya:</strong> Membahayakan keselamatan diri dan orang lain di sekitar area kerja.</p>
                    <p><strong>Analisis:</strong> Simulasi ini menunjukkan perlunya pemahaman mendalam tentang SOP K3 di lingkungan kerja industri.</p>
                `;
        }

        return analysis;
    }

    function generateCorrectionSteps(data) {
        let steps = '<ol>';

        switch(data.jenisRisiko) {
            case 'Kebocoran Pipa Gas':
                steps += `
                    <li>Evakuasi area segera dan aktifkan alarm darurat</li>
                    <li>Hubungi tim teknis/DAMKAR untuk penanganan profesional</li>
                    <li>Gunakan APD lengkap jika harus berada di area terdampak</li>
                    <li>Matikan sumber listrik di area tersebut jika memungkinkan</li>
                    <li>Tunggu tim ahli untuk perbaikan sistem pipa</li>
                `;
                break;

            case 'Korsleting Listrik':
                steps += `
                    <li>Matikan sumber listrik dari MCB utama</li>
                    <li>Jangan sentuh peralatan listrik yang rusak</li>
                    <li>Gunakan APAR CO2 untuk memadamkan kebakaran listrik</li>
                    <li>Evakuasi area dan hubungi tim teknis</li>
                    <li>Laporkan insiden ke supervisor K3</li>
                `;
                break;

            case 'Tumpahan Oli':
                steps += `
                    <li>Kurung area tumpahan dengan safety cone</li>
                    <li>Gunakan Spill Kit untuk menyerap oli</li>
                    <li>Jangan biarkan oli mengalir ke saluran pembuangan</li>
                    <li>Laporkan ke tim lingkungan untuk disposal yang benar</li>
                    <li>Bersihkan area dengan detergent yang sesuai</li>
                `;
                break;

            default:
                steps += `
                    <li>Evaluasi situasi bahaya dengan cepat</li>
                    <li>Prioritaskan keselamatan diri dan rekan kerja</li>
                    <li>Aktifkan prosedur evakuasi jika diperlukan</li>
                    <li>Hubungi tim darurat sesuai jenis insiden</li>
                    <li>Dokumentasikan kejadian untuk pelaporan</li>
                `;
        }

        steps += '</ol>';
        return steps;
    }

    function generateLearningResources(data) {
        let resources = '<ul>';

        switch(data.jenisRisiko) {
            case 'Kebocoran Pipa Gas':
                resources += `
                    <li><strong>SOP Penanganan Kebocoran Gas:</strong> Panduan identifikasi dan penanganan gas berbahaya</li>
                    <li><strong>Pelatihan APD:</strong> Penggunaan alat pelindung diri untuk bahaya kimia</li>
                    <li><strong>Sistem Ventilasi Darurat:</strong> Cara kerja dan penggunaan sistem ventilasi</li>
                    <li><strong>Komunikasi Darurat:</strong> Prosedur pelaporan dan koordinasi tim</li>
                `;
                break;

            case 'Korsleting Listrik':
                resources += `
                    <li><strong>Klasifikasi Kebakaran:</strong> Pemahaman jenis kebakaran dan APAR yang sesuai</li>
                    <li><strong>Sistem Kelistrikan:</strong> Pengetahuan dasar sistem listrik industri</li>
                    <li><strong>P3K Listrik:</strong> Penanganan korban sengatan listrik</li>
                    <li><strong>Maintenance Preventif:</strong> Pemeriksaan rutin peralatan listrik</li>
                `;
                break;

            case 'Tumpahan Oli':
                resources += `
                    <li><strong>Spill Response:</strong> Teknik penanganan tumpahan bahan kimia</li>
                    <li><strong>Environmental Protection:</strong> Dampak pencemaran dan pencegahan</li>
                    <li><strong>Material Safety Data Sheet:</strong> Informasi keamanan bahan kimia</li>
                    <li><strong>Cleanup Procedures:</strong> Prosedur pembersihan yang aman</li>
                `;
                break;

            default:
                resources += `
                    <li><strong>SOP K3 Umum:</strong> Standar operasional prosedur keselamatan</li>
                    <li><strong>Risk Assessment:</strong> Teknik identifikasi dan evaluasi risiko</li>
                    <li><strong>Emergency Response:</strong> Tanggap darurat berbagai jenis insiden</li>
                    <li><strong>Safety Training:</strong> Pelatihan keselamatan kerja berkala</li>
                `;
        }

        resources += '</ul>';
        return resources;
    }

    function setupRemedialVisualization(data) {
        const visualization = document.getElementById('remedialVisualization');
        const remedialText = document.getElementById('remedialText');

        // Clear previous content
        visualization.innerHTML = '';

        switch(data.jenisRisiko) {
            case 'Kebocoran Pipa Gas':
                // Visualisasi pipa bocor dengan warning
                visualization.innerHTML = `
                    <a-box width="0.8" height="0.8" depth="0.8" color="#2c3e50" position="0 0.4 -3"></a-box>
                    <a-cylinder radius="0.06" height="1.2" color="#7f8c8d" position="0 1.2 -3"></a-cylinder>
                    <a-cylinder radius="0.06" height="3" color="#7f8c8d" position="1.5 1.8 -3" rotation="0 0 90"></a-cylinder>
                    <a-sphere radius="0.3" color="#e74c3c" position="1.5 1.8 -3" opacity="0.9" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate;"></a-sphere>
                    <a-text value="❌ BAHAYA!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                    <a-text value="Gas Bocor" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
                `;
                remedialText.setAttribute('value', 'Area berbahaya - Evakuasi segera!');
                break;

            case 'Korsleting Listrik':
                // Visualisasi korsleting dengan percikan api
                visualization.innerHTML = `
                    <a-box width="1" height="0.6" depth="0.4" color="#34495e" position="0 0.3 -3"></a-box>
                    <a-cylinder radius="0.03" height="0.8" color="#f39c12" position="-0.2 0.8 -3" rotation="15 0 0"></a-cylinder>
                    <a-cylinder radius="0.03" height="0.8" color="#f39c12" position="0.2 0.8 -3" rotation="-15 0 0"></a-cylinder>
                    <a-sphere radius="0.15" color="#e74c3c" position="0 0.6 -2.8" opacity="0.8" animation="property: scale; from: 1 1 1; to: 1.3 1.3 1.3; dur: 800; loop: true; dir: alternate;"></a-sphere>
                    <a-sphere radius="0.1" color="#ffaa00" position="-0.1 0.7 -2.9" opacity="0.9" animation="property: scale; from: 1 1 1; to: 1.4 1.4 1.4; dur: 600; loop: true; dir: alternate;"></a-sphere>
                    <a-text value="⚡ KORSLET!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                    <a-text value="JANGAN pakai air!" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
                `;
                remedialText.setAttribute('value', 'Gunakan APAR CO2, bukan air!');
                break;

            case 'Tumpahan Oli':
                // Visualisasi genangan oli
                visualization.innerHTML = `
                    <a-plane position="0 0.01 -3" rotation="-90 0 0" width="4" height="4" color="#2c3e50" opacity="0.8"></a-plane>
                    <a-circle radius="1.5" color="#f1c40f" position="0 0.02 -3" rotation="-90 0 0" opacity="0.7" animation="property: scale; from: 1 1 1; to: 1.1 1.1 1.1; dur: 1200; loop: true; dir: alternate;"></a-circle>
                    <a-cylinder radius="0.1" height="0.05" color="#34495e" position="0.5 0.1 -2.5"></a-cylinder>
                    <a-cylinder radius="0.08" height="0.03" color="#e74c3c" position="-0.3 0.08 -2.8" rotation="90 0 0"></a-cylinder>
                    <a-text value="🛢️ TUMPAHAN" position="0 2.5 -2" align="center" color="#dc3545" scale="0.6 0.6 0.6"></a-text>
                    <a-text value="Bahaya Licin!" position="0 2.2 -2" align="center" color="#007bff" scale="0.5 0.5 0.5"></a-text>
                `;
                remedialText.setAttribute('value', 'Gunakan Spill Kit untuk cleanup!');
                break;

            default:
                // Visualisasi umum bahaya
                visualization.innerHTML = `
                    <a-sphere radius="0.8" color="#e74c3c" position="0 1 -3" opacity="0.8" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 1000; loop: true; dir: alternate;"></a-sphere>
                    <a-text value="⚠️ BAHAYA!" position="0 2.5 -2" align="center" color="#dc3545" scale="0.8 0.8 0.8"></a-text>
                    <a-text value="Ikuti SOP K3" position="0 2.2 -2" align="center" color="#007bff" scale="0.6 0.6 0.6"></a-text>
                `;
                remedialText.setAttribute('value', 'Selalu prioritaskan keselamatan!');
        }
    }
</script>
</body>
</html>