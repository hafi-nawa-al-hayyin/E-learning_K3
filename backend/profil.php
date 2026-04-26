<?php 
session_start();

// 1. CEK APAKAH USER SUDAH LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php'; 

// Ambil ID user dari session yang aktif
$id_user = $_SESSION['id_user'];

// 2. AMBIL DATA USER DARI DATABASE
// Kita gunakan mysqli_real_escape_string demi keamanan
$id_bersih = mysqli_real_escape_string($conn, $id_user);
$queryUser = mysqli_query($conn, "SELECT * FROM Users WHERE id_user = '$id_bersih'");

if ($queryUser && mysqli_num_rows($queryUser) > 0) {
    $dataUser = mysqli_fetch_assoc($queryUser);
    
    // Berikan nilai fallback jika kolom di database kosong
    $nama_user = !empty($dataUser['nama_lengkap']) ? $dataUser['nama_lengkap'] : 'Nama tidak diatur';
    
    // Mapping role ke jabatan yang lebih deskriptif
    $role = $dataUser['role'];
    $jabatan_mapping = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'admin' => 'Administrator Sistem'
    ];
    $jabatan_user = isset($jabatan_mapping[$role]) ? $jabatan_mapping[$role] : 'Jabatan tidak diatur';
} else {
    // Jika ID di session tidak ada di database, hancurkan session & paksa login ulang
    session_destroy();
    header("Location: login.php?pesan=sesi_tidak_valid");
    exit();
}

// 3. LOGIKA UPDATE PASSWORD
if (isset($_POST['update_profil'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];
    
    // Validasi apakah password lama yang diinput cocok dengan hash di database
    if (password_verify($password_lama, $dataUser['password'])) {
        
        if ($password_baru !== $konfirmasi) {
            echo "<script>alert('Konfirmasi password baru tidak cocok!');</script>";
        } else {
            // Enkripsi password baru
            $password_aman = password_hash($password_baru, PASSWORD_DEFAULT);
            
            $update = mysqli_query($conn, "UPDATE Users SET password = '$password_aman' WHERE id_user = '$id_bersih'");
            
            if ($update) {
                echo "<script>alert('Password berhasil diperbarui!'); window.location.href='profil.php';</script>";
            } else {
                echo "<script>alert('Gagal memperbarui password.');</script>";
            }
        }
        
    } else {
        echo "<script>alert('Password lama Anda salah!');</script>";
    }
}

// 4. AMBIL STATISTIK PRIBADI
$qTotalSimulasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM Simulasi WHERE id_user = '$id_bersih'");
$dTotal = mysqli_fetch_assoc($qTotalSimulasi);

$qLulus = mysqli_query($conn, "SELECT COUNT(*) as total FROM Simulasi WHERE id_user = '$id_bersih' AND status_kelulusan = 'LULUS'");
$dLulus = mysqli_fetch_assoc($qLulus);

$qGagal = mysqli_query($conn, "SELECT COUNT(*) as total FROM Simulasi WHERE id_user = '$id_bersih' AND status_kelulusan = 'GAGAL'");
$dGagal = mysqli_fetch_assoc($qGagal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - K3-VirtuAI</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; margin: 0; background: #04070d; color: #e5f0ff; }
        
        .nav { position: sticky; top: 0; z-index: 999; background: #000000; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333333; }
        .main-container { padding: 40px 20px; max-width: 800px; margin: 0 auto; }

        .card { background: #0f172a; padding: 30px; border-radius: 12px; border: 2px solid #3b82f6; box-shadow: 0 4px 8px rgba(59,130,246,0.1); margin-bottom: 20px; }
        .section-title { margin-bottom: 20px; font-size: 1.3em; color: #007bff; font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;}

        .info-grid { display: grid; grid-template-columns: 150px 1fr; gap: 15px; margin-bottom: 20px; font-size: 0.95em; }
        .info-label { color: #60a5fa; font-weight: bold; }
        .info-value { color: #e5f0ff; }

        .stat-grid { display: flex; gap: 15px; margin-bottom: 30px; text-align: center; }
        .stat-box { flex: 1; background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #1e40af; }
        .stat-number { font-size: 1.8em; font-weight: bold; color: #007bff; }
        .stat-label { font-size: 0.8em; color: #6c757d; margin-top: 5px; }

        .input-field { padding: 12px; background: #0f172a; border: 2px solid #3b82f6; color: #e5f0ff; border-radius: 6px; margin-bottom: 15px; width: 100%; font-size: 0.9em; }
        .input-field:focus { border-color: #0056b3; box-shadow: 0 0 5px rgba(0,123,255,0.3); }
        
        /* Password field with toggle */
        .password-container {
            position: relative;
            width: 100%;
            margin-bottom: 15px;
        }
        .password-container input {
            width: 100%;
            padding: 12px;
            padding-right: 50px;
            background: #ffffff;
            border: 2px solid #007bff;
            color: #000000;
            border-radius: 6px;
            font-size: 0.9em;
            box-sizing: border-box;
        }
        .password-container input:focus {
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
            outline: none;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 1.1em;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover {
            background: rgba(0,123,255,0.1);
            color: #0056b3;
        }
        .btn { padding: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; color: white; font-size: 0.95em; }
        .btn-update { background: #3b82f6; color: #e5f0ff; }
        .btn-update:hover { background: #2563eb; }
    </style>
</head>
<body>

<nav class="nav">
    <div style="font-size: 1.4em; font-weight: bold; color: #ffffff;">K3-VirtuAI 🛡️</div>
    <div style="display: flex; gap: 15px; align-items: center;">
        <a href="index.php" style="color: #ffffff; text-decoration: none;">Dashboard</a>
        <a href="profil.php" style="color: #ffffff; text-decoration: none; font-weight: bold;">Profil Saya</a>
        <a href="logout.php" style="color: #ffffff; text-decoration: none; border: 1px solid #ffffff; padding: 5px 10px; border-radius: 4px;">Keluar</a>
    </div>
</nav>

<div class="main-container">

    <div class="card">
        <div class="section-title">👤 Informasi Akun</div>
        
        <div class="info-grid">
            <div class="info-label">Nama Lengkap</div>
            <div class="info-value"><?php echo htmlspecialchars($nama_user); ?></div>

            <div class="info-label">NIM/NIDN</div>
            <div class="info-value"><?php echo htmlspecialchars($dataUser['nim_nidn']); ?></div>

            <div class="info-label">Jabatan Unit</div>
            <div class="info-value"><?php echo htmlspecialchars($jabatan_user); ?></div>

            <div class="info-label">Role Akses</div>
            <div class="info-value" style="text-transform: capitalize; color: #3498db; font-weight: bold;"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
        </div>

        <div class="section-title" style="margin-top: 30px;">📈 Statistik Simulasi Saya</div>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $dTotal['total']; ?></div>
                <div class="stat-label">Total Percobaan</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" style="color: #3b82f6;"><?php echo $dLulus['total']; ?></div>
                <div class="stat-label">Berhasil (Lulus)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number" style="color: #1e40af;"><?php echo $dGagal['total']; ?></div>
                <div class="stat-label">Gagal</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="section-title">🔒 Ganti Password</div>
        <p style="font-size: 0.85em; color: #95a5a6; margin-top: -10px; margin-bottom: 20px;">Amankan akun Anda dengan mengganti password secara berkala.</p>
        
        <form method="POST">
            <label style="font-size: 0.85em; color: #3498db;">Password Lama:</label>
            <div class="password-container">
                <input type="password" name="password_lama" id="password-lama" placeholder="Masukkan password saat ini" required>
                <button type="button" class="password-toggle" onclick="togglePassword('password-lama')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>

            <label style="font-size: 0.85em; color: #3498db;">Password Baru:</label>
            <div class="password-container">
                <input type="password" name="password_baru" id="password-baru" placeholder="Masukkan password baru" required>
                <button type="button" class="password-toggle" onclick="togglePassword('password-baru')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>

            <label style="font-size: 0.85em; color: #3498db;">Konfirmasi Password Baru:</label>
            <div class="password-container">
                <input type="password" name="konfirmasi_password" id="konfirmasi-password" placeholder="Ulangi password baru" required>
                <button type="button" class="password-toggle" onclick="togglePassword('konfirmasi-password')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>

            <button type="submit" name="update_profil" class="btn btn-update">Simpan Perubahan</button>
        </form>
    </div>

</div>

<script>
    function togglePassword(inputId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = passwordInput.nextElementSibling;
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.innerHTML = '🙈';
            toggleButton.title = 'Sembunyikan Password';
        } else {
            passwordInput.type = 'password';
            toggleButton.innerHTML = '👁️';
            toggleButton.title = 'Tampilkan Password';
        }
    }
</script>

</body>
</html>