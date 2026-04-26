<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

$pesan = "";
$pesan_tipe = "";
$tampilkan_register = false;

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if ($role === 'admin') {
        $pesan = "Registrasi sebagai Admin tidak diperbolehkan melalui form ini.";
        $pesan_tipe = "error";
        $tampilkan_register = true;
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE nim_nidn='$nim'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "NIM/NIDN sudah terdaftar!";
            $pesan_tipe = "error";
            $tampilkan_register = true;
        } else {
            $insert = mysqli_query($conn, "INSERT INTO users (nama_lengkap, nim_nidn, password, role) VALUES ('$nama', '$nim', '$password', '$role')");
            if ($insert) {
                $pesan = "Registrasi berhasil! Silakan login.";
                $pesan_tipe = "success";
                $tampilkan_register = false;
            } else {
                $pesan = "Gagal mendaftar. Coba lagi.";
                $pesan_tipe = "error";
                $tampilkan_register = true;
            }
        }
    }
}

if (isset($_POST['login'])) {
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $password = $_POST['password'];
    $role_input = mysqli_real_escape_string($conn, $_POST['role']);

    $ambil = mysqli_query($conn, "SELECT * FROM users WHERE nim_nidn='$nim' AND role='$role_input'");

    if (mysqli_num_rows($ambil) === 1) {
        $row = mysqli_fetch_assoc($ambil);

        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['nim'] = $row['nim_nidn'];
            $_SESSION['role'] = $row['role'];

            header("Location: index.php");
            exit();
        } else {
            $pesan = "Password salah!";
            $pesan_tipe = "error";
        }
    } else {
        $pesan = "NIM/NIDN tidak ditemukan untuk role ini!";
        $pesan_tipe = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Multi-User - Simulasi K3</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg: #03060d;
            --panel: rgba(9, 17, 30, 0.94);
            --panel-strong: rgba(11, 21, 37, 0.98);
            --border: rgba(96, 165, 250, 0.28);
            --border-strong: rgba(96, 165, 250, 0.52);
            --text: #e5f0ff;
            --text-soft: #9cb2cf;
            --accent: #3b82f6;
            --accent-strong: #2563eb;
            --accent-soft: #60a5fa;
            --danger: #f87171;
            --success: #38bdf8;
            --shadow: 0 24px 60px rgba(1, 8, 20, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text);
            font-family: "Segoe UI", -apple-system, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.18), transparent 30%),
                linear-gradient(160deg, #02050a 0%, #07101a 46%, #091933 100%);
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18), transparent 70%);
            filter: blur(18px);
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            top: -110px;
            left: -110px;
        }

        body::after {
            right: -110px;
            bottom: -110px;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(460px, 100%);
        }

        .hero-panel,
        .box {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            background:
                linear-gradient(180deg, rgba(15, 25, 42, 0.94), rgba(7, 14, 26, 0.96)),
                var(--panel);
            box-shadow: var(--shadow);
        }

        .hero-panel::before,
        .box::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, rgba(96, 165, 250, 0.52), rgba(96, 165, 250, 0));
        }

        .hero-panel {
            display: none;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid rgba(96, 165, 250, 0.18);
            background: rgba(59, 130, 246, 0.08);
            color: var(--accent-soft);
            font-size: 0.92rem;
            font-weight: 600;
        }

        .hero-title {
            margin: 18px 0 16px;
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            line-height: 1.05;
            color: #f8fbff;
        }

        .hero-title span {
            color: var(--accent-soft);
        }

        .hero-desc {
            max-width: 500px;
            color: var(--text-soft);
            font-size: 1rem;
            line-height: 1.8;
        }

        .hero-points {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .hero-point {
            display: grid;
            grid-template-columns: 42px 1fr;
            gap: 12px;
            align-items: start;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(8, 16, 29, 0.64);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .hero-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.94), rgba(37, 99, 235, 0.9));
            box-shadow: 0 14px 26px rgba(37, 99, 235, 0.16);
        }

        .hero-point strong {
            display: block;
            margin-bottom: 4px;
            color: #eff6ff;
            font-size: 0.98rem;
        }

        .hero-point span {
            color: var(--text-soft);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .hero-footer {
            margin-top: 26px;
            padding: 16px 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(10, 20, 36, 0.86), rgba(17, 36, 63, 0.84));
            border: 1px solid rgba(96, 165, 250, 0.12);
            color: var(--text-soft);
            line-height: 1.7;
        }

        .box {
            width: 100%;
            padding: 34px 28px;
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .box::after {
            content: "";
            position: absolute;
            top: -60px;
            right: -60px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2), transparent 72%);
            pointer-events: none;
        }

        .box:hover {
            transform: translateY(-4px);
            border-color: var(--border-strong);
            box-shadow: 0 28px 65px rgba(1, 8, 20, 0.56);
        }

        .panel-title {
            margin-bottom: 8px;
            text-align: center;
            color: #f8fbff;
            font-size: 1.9rem;
            letter-spacing: 0.02em;
            font-family: "Poppins", "Segoe UI", sans-serif;
            font-weight: 600;
        }

        .panel-subtitle {
            margin-bottom: 24px;
            color: var(--text-soft);
            text-align: center;
            font-size: 0.96rem;
            line-height: 1.7;
        }

        .message {
            margin-bottom: 18px;
            padding: 13px 14px;
            border-radius: 14px;
            font-size: 0.92rem;
            line-height: 1.5;
            border: 1px solid transparent;
        }

        .message.error {
            color: #ffe3e3;
            background: rgba(127, 29, 29, 0.34);
            border-color: rgba(248, 113, 113, 0.28);
        }

        .message.success {
            color: #dff6ff;
            background: rgba(8, 85, 122, 0.28);
            border-color: rgba(56, 189, 248, 0.24);
        }

        .field {
            margin-bottom: 14px;
        }

        .field-label {
            display: block;
            margin-bottom: 8px;
            color: #d8e8ff;
            font-size: 0.92rem;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid rgba(96, 165, 250, 0.26);
            border-radius: 14px;
            background: rgba(5, 12, 22, 0.86);
            color: var(--text);
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
        }

        input::placeholder {
            color: #7f93af;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--border-strong);
            background: rgba(8, 18, 30, 0.95);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        option {
            background: #0b1320;
            color: var(--text);
        }

        .select-hint {
            margin-top: 8px;
            color: var(--text-soft);
            font-size: 0.83rem;
            line-height: 1.55;
        }

        .password-container {
            position: relative;
            width: 100%;
        }

        .password-container input {
            padding-right: 52px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            margin: 0;
            padding: 5px;
            border: none;
            border-radius: 50%;
            background: none;
            color: var(--accent-soft);
            font-size: 1.05rem;
            line-height: 1;
            cursor: pointer;
            box-shadow: none;
            transition: background-color 0.25s ease, color 0.25s ease, transform 0.25s ease;
        }

        .password-toggle:hover {
            background: rgba(59, 130, 246, 0.12);
            color: #dbeafe;
            transform: translateY(-50%) scale(1.03);
        }

        .aux-row {
            display: flex;
            justify-content: flex-end;
            margin: 10px 0 2px;
        }

        button {
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-top: 8px;
            padding: 15px 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.2);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.25s ease;
        }

        button::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.24);
            transform: translate(-50%, -50%);
            transition: 0.6s;
        }

        button:hover::before {
            width: 300px;
            height: 300px;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 38px rgba(37, 99, 235, 0.28);
        }

        button:active {
            transform: translateY(0);
        }

        .link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--accent-soft) !important;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.25s ease, text-shadow 0.25s ease;
        }

        .link:hover {
            color: #dbeafe !important;
            text-shadow: 0 0 12px rgba(59, 130, 246, 0.25);
        }

        .forgot-link {
            font-size: 0.85rem;
            text-decoration: underline;
            opacity: 0.9;
        }

        .form-footer {
            margin-top: 20px;
            color: var(--text-soft);
            font-size: 0.82rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            body {
                padding: 14px;
            }

            .box {
                border-radius: 22px;
            }

            .box {
                padding: 26px 20px;
            }

            .panel-title {
                font-size: 1.55rem;
            }

            input,
            select {
                padding: 13px;
                font-size: 16px;
                border-radius: 12px;
            }

            .password-toggle {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }

            button {
                padding: 14px;
                border-radius: 12px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 8px;
            }

            .box {
                border-radius: 18px;
            }

            .box {
                padding: 20px 16px;
            }

            input,
            select {
                padding: 12px;
                font-size: 16px;
                -webkit-appearance: none;
                appearance: none;
            }

            .password-container input {
                padding-right: 45px;
            }

            .password-toggle {
                right: 10px;
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            button {
                padding: 13px;
                font-size: 0.95rem;
                touch-action: manipulation;
            }

            .aux-row {
                justify-content: center;
            }
        }

        @media (max-height: 600px) {
            .box {
                padding: 18px 14px;
            }

            .panel-title {
                font-size: 1.1rem;
            }

            input,
            select {
                padding: 10px;
                font-size: 14px;
            }

            button {
                padding: 11px;
                font-size: 0.9rem;
            }
        }

        @media (hover: none) and (pointer: coarse) {
            input,
            select,
            button {
                min-height: 44px;
            }

            .password-toggle {
                min-width: 44px;
                min-height: 44px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <section class="hero-panel">
            <div>
                <div class="hero-kicker">
                    <i class="fas fa-shield-halved"></i>
                    Portal Simulasi K3-VirtuAI
                </div>
                <h1 class="hero-title">Masuk ke sistem <span>hitam-biru</span> yang selaras dengan dashboard.</h1>

                <div class="hero-points">
                    <div class="hero-point">
                        <div class="hero-icon"><i class="fas fa-layer-group"></i></div>
                        <div>
                            <strong>Tampilan Konsisten</strong>
                            <span>Warna, panel, dan aksen diselaraskan dengan tema dashboard K3-VirtuAI.</span>
                        </div>
                    </div>
                    <div class="hero-point">
                        <div class="hero-icon"><i class="fas fa-eye"></i></div>
                        <div>
                            <strong>Lebih Mudah Dibaca</strong>
                            <span>Kontras teks, input, dan tombol ditingkatkan agar halaman tetap jelas di layar terang maupun gelap.</span>
                        </div>
                    </div>
                    <div class="hero-point">
                        <div class="hero-icon"><i class="fas fa-mobile-screen-button"></i></div>
                        <div>
                            <strong>Nyaman di Mobile</strong>
                            <span>Ukuran elemen form dan jarak sentuh dibuat lebih nyaman untuk perangkat kecil.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-footer">
                Gunakan akun sesuai peran Anda untuk masuk ke simulasi, rekap nilai, atau pengelolaan skenario. Jika belum memiliki akun, registrasi tersedia untuk <strong>Mahasiswa</strong> dan <strong>Dosen</strong>.
            </div>
        </section>

        <div class="box" id="box-login" style="<?php echo $tampilkan_register ? 'display:none;' : 'display:block;'; ?>">
            <h2 class="panel-title">Masuk</h2>
            <?php if ($pesan !== "" && !$tampilkan_register) : ?>
                <div class="message <?php echo $pesan_tipe === "success" ? "success" : "error"; ?>"><?php echo htmlspecialchars($pesan); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label class="field-label" for="login-role">Masuk sebagai</label>
                    <select name="role" id="login-role" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                        <option value="admin">Admin</option>
                    </select>
                    <div class="select-hint">Pilih peran yang sesuai dengan akun Anda sebelum login.</div>
                </div>

                <div class="field">
                    <label class="field-label" for="login-nim">NIM / NIDN / Username</label>
                    <input type="text" name="nim" id="login-nim" placeholder="Masukkan NIM, NIDN, atau username" required>
                </div>

                <div class="field">
                    <label class="field-label" for="login-password">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="login-password" placeholder="Masukkan password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('login-password')" title="Tampilkan/Sembunyikan Password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="aux-row">
                    <a href="lupa_password.php" class="link forgot-link">Lupa Password?</a>
                </div>

                <button type="submit" name="login">Masuk ke Dashboard</button>
            </form>

            <p class="form-footer">Belum punya akun? <span class="link" onclick="toggleForm()">Daftar di sini</span></p>
        </div>

        <div class="box" id="box-register" style="<?php echo $tampilkan_register ? 'display:block;' : 'display:none;'; ?>">
            <h2 class="panel-title">Registrasi</h2>
            <?php if ($pesan !== "" && $tampilkan_register) : ?>
                <div class="message <?php echo $pesan_tipe === "success" ? "success" : "error"; ?>"><?php echo htmlspecialchars($pesan); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="field">
                    <label class="field-label" for="register-role">Daftar sebagai</label>
                    <select name="role" id="register-role" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                    </select>
                </div>

                <div class="field">
                    <label class="field-label" for="register-nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="register-nama" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="field">
                    <label class="field-label" for="register-nim">NIM / NIDN / Username</label>
                    <input type="text" name="nim" id="register-nim" placeholder="Masukkan identitas akun" required>
                </div>

                <div class="field">
                    <label class="field-label" for="register-password">Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="register-password" placeholder="Buat password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('register-password')" title="Tampilkan/Sembunyikan Password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register">Buat Akun</button>
            </form>

            <p class="form-footer">Sudah punya akun? <span class="link" onclick="toggleForm()">Login di sini</span></p>
        </div>
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
                toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
                toggleButton.title = 'Sembunyikan Password';
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
                toggleButton.title = 'Tampilkan Password';
            }
        }
    </script>
</body>
</html>
