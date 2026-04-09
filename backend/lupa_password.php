<?php
include 'koneksi.php';

if (isset($_POST['proses_reset'])) {
    $email = $_POST['email'];
    
    // Cek apakah email terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM Users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek) > 0) {
        // Buat token acak & waktu kedaluwarsa (misal: 30 menit dari sekarang)
        $token = bin2hex(random_bytes(16));
        $exp = date("Y-m-d H:i:s", strtotime("+30 minutes"));
        
        // Simpan token ke database
        mysqli_query($conn, "UPDATE Users SET reset_token='$token', token_exp='$exp' WHERE email='$email'");
        
        // Di localhost: Kita langsung arahkan ke link resetnya (Pura-puranya ini link dari email)
        echo "<script>
                alert('Token berhasil dibuat! Mengalihkan ke halaman reset...');
                window.location.href='reset_password.php?token=$token';
              </script>";
    } else {
        echo "<script>alert('Email tidak terdaftar!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Lupa Password - K3-VirtuAI</title>
    <style>
        body { font-family: Segoe UI, sans-serif; background: #1a1a2e; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #16213e; padding: 30px; border-radius: 10px; width: 350px; text-align: center; border: 1px solid #0f3460;}
        input { width: 100%; padding: 10px; margin: 10px 0; background: #1a1a2e; border: 1px solid #0f3460; color: #fff; border-radius: 5px; box-sizing: border-box;}
        button { width: 100%; padding: 10px; background: #e94560; border: none; color: #fff; font-weight: bold; border-radius: 5px; cursor: pointer;}
    </style>
</head>
<body>
    <div class="box">
        <h3>🔑 Lupa Password</h3>
        <p style="font-size: 0.85em; color: #95a5a6;">Masukkan email akun K3-VirtuAI Anda</p>
        <form method="POST">
            <input type="email" name="email" placeholder="Alamat Email" required>
            <button type="submit" name="proses_reset">Kirim Permintaan</button>
        </form>
        <br>
        <a href="login.php" style="color: #3498db; text-decoration: none; font-size: 0.85em;">Kembali ke Login</a>
    </div>
</body>
</html>