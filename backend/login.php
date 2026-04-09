<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung lempar ke index sesuai rolenya
if (isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

$pesan = "";
$tampilkan_register = false; // Flag untuk menentukan kotak mana yang muncul

// PROSES REGISTER
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validasi: Cegah registrasi sebagai admin melalui form register
    if ($role === 'admin') {
        $pesan = "<div style='color:#ff4d4d; margin-bottom: 15px;'>❌ Registrasi sebagai Admin tidak diperbolehkan melalui form ini!</div>";
        $tampilkan_register = true;
    } else {
        // Cek apakah NIM/NIDN sudah terdaftar di tabel users
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE nim_nidn='$nim'");
        
        if (mysqli_num_rows($cek) > 0) {
            $pesan = "<div style='color:#ff4d4d; margin-bottom: 15px;'>NIM/NIDN sudah terdaftar!</div>";
            $tampilkan_register = true; // Tetap di box register jika gagal
        } else {
            // Masukkan data baru
            $insert = mysqli_query($conn, "INSERT INTO users (nama_lengkap, nim_nidn, password, role) VALUES ('$nama', '$nim', '$password', '$role')");
            if ($insert) {
                $pesan = "<div style='color:#00ff41; margin-bottom: 15px;'>Registrasi berhasil! Silakan Login.</div>";
                $tampilkan_register = false; // Pindah ke box login jika sukses
            } else {
                $pesan = "<div style='color:#ff4d4d; margin-bottom: 15px;'>Gagal mendaftar. Coba lagi.</div>";
                $tampilkan_register = true;
            }
        }
    }
}

// PROSES LOGIN
if (isset($_POST['login'])) {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $password = $_POST['password'];
    $role_input = mysqli_real_escape_string($conn, $_POST['role']);

    // Cari berdasarkan NIM dan Role (sesuai Gambar 2)
    $ambil = mysqli_query($conn, "SELECT * FROM users WHERE nim_nidn='$nim' AND role='$role_input'");
    
    if (mysqli_num_rows($ambil) === 1) {
        $row = mysqli_fetch_assoc($ambil);
        
        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['nim'] = $row['nim_nidn'];
            $_SESSION['role'] = $row['role']; 
            
            header("Location: index.php");
            exit();
        } else {
            $pesan = "<div style='color:#ff4d4d; margin-bottom: 15px;'>Password salah!</div>";
        }
    } else {
        $pesan = "<div style='color:#ff4d4d; margin-bottom: 15px;'>NIM/NIDN tidak ditemukan untuk role ini!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Multi-User - Simulasi K3</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"></style>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #e9ecef 100%); 
            color: #000; 
            font-family: 'Segoe UI', -apple-system, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            animation: gradientShift 10s ease infinite; 
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        .box { 
            width: min(90vw, 380px); 
            padding: 40px 30px; 
            backdrop-filter: blur(20px); 
            background: rgba(255, 255, 255, 0.95); 
            border: 2px solid #007bff; 
            margin: 20px; 
            box-shadow: 0 20px 40px rgba(0,123,255,0.1), 0 0 30px rgba(0,123,255,0.05); 
            border-radius: 20px; 
            transition: all 0.4s ease; 
            position: relative; 
            overflow: hidden; 
        }
        .box::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent); transition: 0.7s;
        }
        .box:hover::before { left: 100%; }
        .box:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(0,123,255,0.15), 0 0 50px rgba(0,123,255,0.1); }
        h2 { 
            margin-bottom: 25px; 
            color: #007bff; 
            font-size: 1.8em; 
            letter-spacing: 1px; 
            position: relative; 
            text-align: center;
            font-family: 'Poppins', 'Segoe UI', -apple-system, sans-serif;
            font-weight: 600;
        }
        h2::after { content: ''; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 50px; height: 3px; background: #007bff; border-radius: 2px; }
        input, select { 
            width: 100%; 
            padding: 15px; 
            margin: 15px 0; 
            background: #ffffff; 
            border: 2px solid #007bff; 
            color: #000; 
            font-size: 1em; 
            border-radius: 12px; 
            transition: all 0.3s ease; 
            font-family: inherit; 
        }
        
        /* Password field with toggle */
        .password-container {
            position: relative;
            width: 100%;
            margin: 15px 0;
        }
        .password-container input {
            width: 100%;
            padding-right: 50px;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 1.2em;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle:hover {
            background: rgba(0,123,255,0.1);
            color: #0056b3;
        }
        input::placeholder { color: #6c757d; }
        input:focus, select:focus { 
            outline: none; 
            border-color: #0056b3; 
            box-shadow: 0 0 20px rgba(0,123,255,0.2); 
            transform: scale(1.02); 
            background: #f8f9fa; 
        }
        option { background: #ffffff; color: #000; }
        button { 
            width: 100%; 
            padding: 16px; 
            background: linear-gradient(45deg, #007bff, #0056b3); 
            color: #fff; 
            border: none; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1.1em; 
            border-radius: 12px; 
            margin-top: 10px; 
            transition: all 0.3s ease; 
            position: relative; 
            overflow: hidden; 
        }
        button::before {
            content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.3); border-radius: 50%; transform: translate(-50%,-50%); transition: 0.6s;
        }
        button:hover::before { width: 300px; height: 300px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,123,255,0.3); }
        button:active { transform: translateY(0); }
        .link { 
            color: #007bff !important; 
            cursor: pointer; 
            text-decoration: none; 
            font-size: 0.9em; 
            font-weight: 500;
            transition: 0.3s;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .link:hover { color: #0056b3 !important; text-shadow: 0 0 10px rgba(0,123,255,0.3); }
        .forgot-link { font-size: 0.85em; text-decoration: underline; opacity: 0.9; }
        .forgot-link:hover { opacity: 1; }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .box { 
                padding: 30px 20px; 
                margin: 10px;
                width: min(95vw, 450px);
                border-radius: 15px;
            }
            h2 { 
                font-size: 1.5em;
                margin-bottom: 20px;
            }
            h2::after {
                width: 40px;
                height: 2px;
            }
            input, select {
                padding: 13px;
                margin: 12px 0;
                font-size: 16px;
                border-radius: 10px;
            }
            .password-container {
                margin: 12px 0;
            }
            .password-toggle {
                right: 12px;
                width: 40px;
                height: 40px;
                font-size: 1.1em;
            }
            button {
                padding: 14px;
                font-size: 1em;
                margin-top: 8px;
                border-radius: 10px;
            }
            p {
                font-size: 0.85em;
                margin-top: 15px;
            }
            .link {
                font-size: 0.85em;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
                min-height: 100vh;
            }
            .box { 
                padding: 20px 15px; 
                margin: 5px;
                width: min(98vw, 100%);
                border-radius: 12px;
                border: 1.5px solid #007bff;
                box-shadow: 0 10px 25px rgba(0,123,255,0.08);
            }
            .box:hover {
                transform: none;
                box-shadow: 0 15px 35px rgba(0,123,255,0.12);
            }
            h2 { 
                font-size: 1.2em;
                margin-bottom: 15px;
                font-weight: 600;
            }
            h2::after {
                width: 30px;
                height: 2px;
                bottom: -8px;
            }
            input, select {
                width: 100%;
                padding: 12px;
                margin: 10px 0;
                font-size: 16px;
                border-radius: 8px;
                border: 1.5px solid #007bff;
                -webkit-appearance: none;
                appearance: none;
            }
            input:focus, select:focus {
                transform: scale(1);
                border-color: #0056b3;
                box-shadow: 0 0 10px rgba(0,123,255,0.2);
            }
            .password-container {
                position: relative;
                width: 100%;
                margin: 10px 0;
            }
            .password-container input {
                padding-right: 45px;
                width: 100%;
            }
            .password-toggle {
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                width: 35px;
                height: 35px;
                font-size: 1em;
            }
            button { 
                width: 100%;
                padding: 13px;
                font-size: 0.95em;
                margin-top: 10px;
                border-radius: 8px;
                font-weight: 600;
                contact-action: manipulation;
            }
            button:hover::before {
                width: 200px;
                height: 200px;
            }
            button:hover {
                transform: translateY(-1px);
            }
            .link {
                font-size: 0.8em;
            }
            p {
                font-size: 0.8em;
                margin-top: 12px;
                text-align: center;
            }
            div[style*="text-align: right"] {
                text-align: center !important;
            }
            div[style*="text-align: right"] a {
                font-size: 0.8em !important;
                display: inline-block;
                margin-top: 8px;
            }
        }

        /* Tablet landscape */
        @media (min-width: 481px) and (max-width: 1024px) {
            .box {
                width: min(90vw, 400px);
            }
            h2 {
                font-size: 1.4em;
            }
            input, select {
                padding: 12px;
                margin: 12px 0;
            }
        }

        /* Extra small phones */
        @media (max-height: 600px) {
            .box {
                padding: 15px 12px;
            }
            h2 {
                font-size: 1em;
                margin-bottom: 10px;
            }
            input, select {
                padding: 10px;
                margin: 8px 0;
                font-size: 14px;
            }
            button {
                padding: 11px;
                font-size: 0.9em;
            }
        }

        /* Touch-friendly adjustments */
        @media (hover: none) and (pointer: coarse) {
            input, select, button {
                min-height: 44px;
            }
            .password-toggle {
                min-height: 44px;
                min-width: 44px;
            }
            button:active {
                background: linear-gradient(45deg, #0056b3, #003d82);
            }
        }
        
        /* Icons for roles */
        .role-icon { font-size: 1.2em; margin-right: 8px; }
        select option[value="mahasiswa"]::before { content: " "; }
        select option[value="dosen"]::before { content: " "; }
        select option[value="admin"]::before { content: " "; }
    </style>
</head>
<body>

    <div class="box" id="box-login" style="<?php echo $tampilkan_register ? 'display:none;' : 'display:block;'; ?>">
        <h2>LOGIN</h2>
        <?php echo $pesan; ?>
        <form method="POST" action="">
<select name="role" required>
                <option value="mahasiswa">👨‍🎓 Sebagai: Mahasiswa</option>
                <option value="dosen">👨‍🏫 Sebagai: Dosen</option>
                <option value="admin">👨‍💼 Sebagai: Admin</option>
            </select>
            <input type="text" name="nim" placeholder="NIM / NIDN / Username" required>
            <div class="password-container">
                <input type="password" name="password" id="login-password" placeholder="Masukkan Password" required>
                <button type="button" class="password-toggle" onclick="togglePassword('login-password')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>
            <div style="text-align: right; margin: 10px 0;">
                <a href="lupa_password.php" style="color: #00ff41; text-decoration: none; font-size: 0.85em;">Lupa Password?</a>
            </div>
            <button type="submit" name="login">MASUK</button>
        </form>
        <p style="font-size: 12px; margin-top: 20px;">Belum punya akun? <span class="link" onclick="toggleForm()">Daftar di sini</span></p>
    </div>

    <div class="box" id="box-register" style="<?php echo $tampilkan_register ? 'display:block;' : 'display:none;'; ?>">
        <h2>📝 REGISTRASI</h2>
        <?php echo $pesan; ?>
        <form method="POST" action="">
<select name="role" required>
                <option value="mahasiswa">👨‍🎓 Daftar sebagai: Mahasiswa</option>
                <option value="dosen">👨‍🏫 Daftar sebagai: Dosen</option>
            </select>
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="text" name="nim" placeholder="NIM / NIDN / Username" required>
            <div class="password-container">
                <input type="password" name="password" id="register-password" placeholder="Password" required>
                <button type="button" class="password-toggle" onclick="togglePassword('register-password')" title="Tampilkan/Sembunyikan Password">
                    👁️
                </button>
            </div>
            <button type="submit" name="register">DAFTAR</button>
        </form>
        <p style="font-size: 12px; margin-top: 20px;">Sudah punya akun? <span class="link" onclick="toggleForm()">Login di sini</span></p>
    </div>

    <script>
        function toggleForm() {
            var login = document.getElementById('box-login');
            var register = document.getElementById('box-register');
            
            if (login.style.display === "none") {
                login.style.display = "block";
                register.style.display = "none";
            } else {
                login.style.display = "none";
                register.style.display = "block";
            }
        }

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