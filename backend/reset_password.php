<?php
include 'koneksi.php';

if (!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];
$now = date("Y-m-d H:i:s");

// Validasi apakah token ada dan belum kedaluwarsa
$cekToken = mysqli_query($conn, "SELECT * FROM Users WHERE reset_token = '$token' AND token_exp > '$now'");

if (mysqli_num_rows($cekToken) == 0) {
    echo "<script>alert('Token tidak valid atau sudah kedaluwarsa!'); window.location.href='lupa_password.php';</script>";
    exit();
}

if (isset($_POST['update_password'])) {
    $pass_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];
    
    if ($pass_baru !== $konfirmasi) {
        echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
    } else {
        // Enkripsi password baru (SANGAT DIREKOMENDASIKAN)
        $password_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        
        // Update password dan hapus token agar tidak bisa dipakai lagi
        mysqli_query($conn, "UPDATE Users SET password='$password_hash', reset_token=NULL, token_exp=NULL WHERE reset_token='$token'");
        
        echo "<script>alert('Password berhasil diubah! Silakan login.'); window.location.href='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Ganti Password Baru - K3-VirtuAI</title>
    <style>
        /* Gaya CSS sama persis seperti di atas */
        body { font-family: Segoe UI, sans-serif; background: #1a1a2e; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #16213e; padding: 30px; border-radius: 10px; width: 350px; text-align: center; border: 1px solid #0f3460;}
        input { width: 100%; padding: 10px; margin: 10px 0; background: #1a1a2e; border: 1px solid #0f3460; color: #fff; border-radius: 5px; box-sizing: border-box;}
        button { width: 100%; padding: 10px; background: #00ff41; border: none; color: #1a1a2e; font-weight: bold; border-radius: 5px; cursor: pointer;}
        
        /* Password field with toggle */
        .password-container {
            position: relative;
            width: 100%;
            margin: 10px 0;
        }
        .password-container input {
            width: 100%;
            padding-right: 45px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #00ff41;
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
            background: rgba(0,255,65,0.1);
            color: #00ff88;
        }
    </style>
</head>
<body>
    <div class="box">
        <h3>🛠️ Buat Password Baru</h3>
        <form method="POST">
            <div class="password-container">
                <input type="password" name="password_baru" id="password-baru" placeholder="Password Baru" required>
                <button type="button" class="password-toggle" onclick="togglePassword('password-baru')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>
            <div class="password-container">
                <input type="password" name="konfirmasi_password" id="konfirmasi-password" placeholder="Konfirmasi Password Baru" required>
                <button type="button" class="password-toggle" onclick="togglePassword('konfirmasi-password')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>
            <button type="submit" name="update_password">Simpan Password</button>
        </form>
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