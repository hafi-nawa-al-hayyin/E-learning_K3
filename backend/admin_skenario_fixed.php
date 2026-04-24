<?php
include 'koneksi.php';
$conn or die("No connection");

$create_table = "CREATE TABLE IF NOT EXISTS pengaturan_skenario (
    id INT PRIMARY KEY DEFAULT 1,
    skenario_aktif VARCHAR(255) DEFAULT 'Kebocoran Pipa Gas'
)";
mysqli_query($conn, $create_table);

$check = mysqli_query($conn, "SELECT COUNT(*) as count FROM pengaturan_skenario");
$data_check = mysqli_fetch_assoc($check);
if ($data_check['count'] == 0) {
    mysqli_query($conn, "INSERT INTO pengaturan_skenario (id, skenario_aktif) VALUES (1, 'Kebocoran Pipa Gas')");
}

if (isset($_POST['simpan_skenario'])) {
    $skenario_baru = mysqli_real_escape_string($conn, $_POST['pilihan_skenario']);
    mysqli_query($conn, "UPDATE pengaturan_skenario SET skenario_aktif = '$skenario_baru' WHERE id = 1");
    $pesan = "Skenario aktif berhasil diubah menjadi: " . $skenario_baru;
}

$ambil = mysqli_query($conn, "SELECT skenario_aktif FROM pengaturan_skenario WHERE id = 1");
$data = mysqli_fetch_assoc($ambil);
$skenario_sekarang = $data['skenario_aktif'] ?? 'Kebocoran Pipa Gas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Atur Skenario K3</title>
    <style>
        :root {
            --bg: #04070d;
            --panel: rgba(10, 18, 32, 0.94);
            --panel-strong: rgba(12, 22, 39, 0.98);
            --border: rgba(96, 165, 250, 0.24);
            --border-strong: rgba(96, 165, 250, 0.48);
            --text: #e5f0ff;
            --text-soft: #9cb2cf;
            --text-muted: #6c809d;
            --accent: #3b82f6;
            --accent-strong: #2563eb;
            --accent-soft: #60a5fa;
            --success: #38bdf8;
            --shadow: 0 22px 55px rgba(1, 8, 20, 0.5);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.18), transparent 28%),
                radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.15), transparent 30%),
                linear-gradient(180deg, #02050a 0%, #050b14 46%, #02060d 100%);
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.16), transparent 70%);
            filter: blur(14px);
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            top: -120px;
            left: -100px;
        }

        body::after {
            right: -120px;
            bottom: -120px;
        }

        .nav,
        .page-wrap {
            position: relative;
            z-index: 1;
        }

        .nav {
            position: sticky;
            top: 0;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 18px 34px;
            background: rgba(3, 7, 13, 0.88);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(96, 165, 250, 0.16);
            box-shadow: 0 18px 30px rgba(2, 10, 24, 0.28);
        }

        .nav-brand {
            font-size: 1.32rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #f8fbff;
            text-shadow: 0 0 18px rgba(96, 165, 250, 0.25);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 14px;
            border: 1px solid transparent;
            border-radius: 999px;
            color: var(--text-soft);
            text-decoration: none;
            transition: color 0.25s ease, border-color 0.25s ease, background-color 0.25s ease, transform 0.25s ease;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(96, 165, 250, 0.28);
            transform: translateY(-1px);
        }

        .logout-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(96, 165, 250, 0.3);
            background: linear-gradient(135deg, rgba(10, 18, 32, 0.95), rgba(18, 35, 59, 0.92));
            color: #f8fbff;
            text-decoration: none;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .logout-link:hover {
            transform: translateY(-1px);
            border-color: rgba(96, 165, 250, 0.56);
            box-shadow: 0 14px 30px rgba(7, 19, 36, 0.35);
        }

        .page-wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 34px 18px 40px;
        }

        .page-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .page-title {
            margin: 0 0 10px;
            color: #f8fbff;
            font-size: clamp(1.7rem, 3vw, 2.4rem);
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .page-desc {
            max-width: 700px;
            margin: 0 auto;
            color: var(--text-soft);
            line-height: 1.75;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
            gap: 20px;
            align-items: start;
        }

        .card,
        .info-card {
            position: relative;
            overflow: hidden;
            padding: 24px;
            border-radius: 24px;
            background:
                linear-gradient(180deg, rgba(14, 24, 40, 0.94), rgba(9, 16, 29, 0.96)),
                var(--panel);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .card::before,
        .info-card::before {
            content: "";
            position: absolute;
            inset: 0 auto auto 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, rgba(96, 165, 250, 0.45), rgba(96, 165, 250, 0));
        }

        .section-title {
            margin: 0 0 12px;
            color: #eef6ff;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .section-text {
            margin: 0 0 18px;
            color: var(--text-soft);
            line-height: 1.75;
        }

        .success-message {
            margin-bottom: 18px;
            padding: 13px 14px;
            border-radius: 14px;
            color: #dff6ff;
            background: rgba(8, 85, 122, 0.28);
            border: 1px solid rgba(56, 189, 248, 0.24);
            line-height: 1.55;
        }

        .field-label {
            display: block;
            margin-bottom: 8px;
            color: #d8e8ff;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .input-field {
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

        .input-field:focus {
            outline: none;
            border-color: var(--border-strong);
            background: rgba(8, 18, 30, 0.95);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .input-note {
            margin-top: 10px;
            color: var(--text-muted);
            font-size: 0.86rem;
            line-height: 1.6;
        }

        .btn {
            width: 100%;
            margin-top: 18px;
            padding: 14px 16px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent), var(--accent-strong));
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.2);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 38px rgba(37, 99, 235, 0.28);
        }

        .scenario-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .scenario-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(8, 16, 29, 0.64);
            border: 1px solid rgba(96, 165, 250, 0.12);
        }

        .scenario-item strong {
            display: block;
            margin-bottom: 4px;
            color: #eff6ff;
            font-size: 0.96rem;
        }

        .scenario-item span {
            color: var(--text-soft);
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .active-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 14px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(96, 165, 250, 0.18);
            color: var(--accent-soft);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            color: var(--accent-soft);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.25s ease;
        }

        .back-link:hover {
            color: #dbeafe;
        }

        @media (max-width: 768px) {
            .nav {
                padding: 14px 16px;
                flex-direction: column;
                align-items: stretch;
            }

            .nav-actions {
                justify-content: center;
            }

            .page-wrap {
                padding: 24px 12px 30px;
            }

            .layout {
                grid-template-columns: 1fr;
            }

            .card,
            .info-card {
                padding: 18px;
                border-radius: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-brand">K3-VirtuAI 🛡️</div>
        <div class="nav-actions">
            <a href="index.php" class="nav-link">Dashboard</a>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="page-header">
            <h1 class="page-title">Pengaturan Simulasi K3</h1>
            <p class="page-desc">
                Pilih skenario yang ingin diaktifkan untuk simulasi. Tampilan halaman ini sudah diselaraskan dengan tema hitam-biru utama agar lebih jelas, lebih modern, dan lebih nyaman dibaca.
            </p>
        </div>

        <div class="layout">
            <div class="card">
                <h2 class="section-title">Pilih Skenario yang Akan Diujikan</h2>
                <p class="section-text">
                    Perubahan skenario aktif akan langsung memengaruhi simulasi yang digunakan pada dashboard sesuai pengaturan admin.
                </p>

                <?php if (isset($pesan)) echo "<div class='success-message'>$pesan</div>"; ?>

                <form method="POST">
                    <label class="field-label" for="pilihan_skenario">Skenario Aktif</label>
                    <select name="pilihan_skenario" id="pilihan_skenario" class="input-field">
                        <option value="Kebocoran Pipa Gas" <?php if($skenario_sekarang == 'Kebocoran Pipa Gas') echo 'selected'; ?>>1. Kebocoran Pipa Gas</option>
                        <option value="Korsleting Listrik" <?php if($skenario_sekarang == 'Korsleting Listrik') echo 'selected'; ?>>2. Korsleting Listrik</option>
                        <option value="Tumpahan Oli" <?php if($skenario_sekarang == 'Tumpahan Oli') echo 'selected'; ?>>3. Tumpahan Oli</option>
                        <option value="Kebakaran Area Panel" <?php if($skenario_sekarang == 'Kebakaran Area Panel') echo 'selected'; ?>>4. Kebakaran Area Panel</option>
                        <option value="Evakuasi Gempa Bumi" <?php if($skenario_sekarang == 'Evakuasi Gempa Bumi') echo 'selected'; ?>>5. Evakuasi Gempa Bumi</option>
                    </select>
                    <div class="input-note">Gunakan daftar ini untuk menentukan jenis simulasi prioritas yang akan muncul pada sistem.</div>

                    <button type="submit" name="simpan_skenario" class="btn">Terapkan Skenario</button>
                </form>

                <a href="index.php" class="back-link">← Kembali ke Dashboard</a>
            </div>

            <div class="info-card">
                <h2 class="section-title">Ringkasan Skenario</h2>
                <p class="section-text">
                    Panel ini membantu melihat status skenario yang sedang dipakai dan gambaran singkat pilihan yang tersedia.
                </p>

                <div class="active-badge">● Aktif sekarang: <?php echo htmlspecialchars($skenario_sekarang); ?></div>

                <div class="scenario-list">
                    <div class="scenario-item">
                        <strong>Kebocoran Pipa Gas</strong>
                        <span>Fokus pada evakuasi, isolasi area, dan koordinasi tanggap darurat.</span>
                    </div>
                    <div class="scenario-item">
                        <strong>Korsleting Listrik</strong>
                        <span>Menekankan pemutusan sumber listrik dan penggunaan APAR yang tepat.</span>
                    </div>
                    <div class="scenario-item">
                        <strong>Tumpahan Oli</strong>
                        <span>Berorientasi pada pengamanan area dan penggunaan spill kit sesuai SOP.</span>
                    </div>
                    <div class="scenario-item">
                        <strong>Kebakaran Area Panel / Gempa</strong>
                        <span>Menguji ketepatan keputusan awal saat kondisi darurat berkembang cepat.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
