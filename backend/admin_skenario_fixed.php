<?php
include 'koneksi.php';
$conn or die("No connection");

// Buat tabel pengaturan_skenario jika belum ada
$create_table = "CREATE TABLE IF NOT EXISTS pengaturan_skenario (
    id INT PRIMARY KEY DEFAULT 1,
    skenario_aktif VARCHAR(255) DEFAULT 'Kebocoran Pipa Gas'
)";
mysqli_query($conn, $create_table);

// Insert default jika belum ada data
$check = mysqli_query($conn, "SELECT COUNT(*) as count FROM pengaturan_skenario");
$data_check = mysqli_fetch_assoc($check);
if ($data_check['count'] == 0) {
    mysqli_query($conn, "INSERT INTO pengaturan_skenario (id, skenario_aktif) VALUES (1, 'Kebocoran Pipa Gas')");
}

if (isset($_POST['simpan_skenario'])) {
    $skenario_baru = mysqli_real_escape_string($conn, $_POST['pilihan_skenario']);
    mysqli_query($conn, "UPDATE pengaturan_skenario SET skenario_aktif = '$skenario_baru' WHERE id = 1");
    $pesan = "Skenario berhasil diubah menjadi: " . $skenario_baru;
}

// Ambil skenario yang saat ini aktif
$ambil = mysqli_query($conn, "SELECT skenario_aktif FROM pengaturan_skenario WHERE id = 1");
$data = mysqli_fetch_assoc($ambil);
$skenario_sekarang = $data['skenario_aktif'] ?? 'Kebocoran Pipa Gas';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Panel Admin - Atur Skenario K3</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; margin: 0; background: #ffffff; color: #000000; text-align: center; padding: 50px; }
        
        .nav { position: sticky; top: 0; z-index: 999; background: #000000; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333333; }
        
        .card { background: #ffffff; padding: 30px; border-radius: 12px; display: inline-block; border: 2px solid #007bff; box-shadow: 0 4px 8px rgba(0,123,255,0.1); }
        
        select, button { padding: 10px; font-size: 16px; margin: 10px; border-radius: 5px; }
        select { background: #ffffff; color: #000000; border: 1px solid #007bff; }
        button { background: #007bff; color: #ffffff; font-weight: bold; cursor: pointer; border: none; transition: 0.3s; }
        button:hover { background: #0056b3; }
        .pesan { color: #28a745; font-weight: bold; }
        
        h2 { color: #007bff; margin-bottom: 30px; }
        h3 { color: #000000; }
    </style>
</head>
<body>

<nav class="nav">
    <div style="font-size: 1.4em; font-weight: bold; color: #ffffff;">K3-VirtuAI 🛡️</div>
    <div>
        <a href="index.php" style="color: #ffffff; text-decoration: none; margin-right: 20px;">Dashboard</a>
        <a href="logout.php" style="color: #ffffff; text-decoration: none;">Logout</a>
    </div>
</nav>

<div style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h2>PENGATURAN SIMULASI K3</h2>
    
    <div class="card">
        <h3>Pilih Skenario yang Akan Diujikan:</h3>
        
        <?php if(isset($pesan)) echo "<p class='pesan'>$pesan</p>"; ?>

        <form method="POST">
            <select name="pilihan_skenario">
                <option value="Kebocoran Pipa Gas" <?php if($skenario_sekarang == 'Kebocoran Pipa Gas') echo 'selected'; ?>>1. Kebocoran Pipa Gas</option>
                <option value="Korsleting Listrik" <?php if($skenario_sekarang == 'Korsleting Listrik') echo 'selected'; ?>>2. Korsleting Listrik</option>
                <option value="Tumpahan Oli" <?php if($skenario_sekarang == 'Tumpahan Oli') echo 'selected'; ?>>3. Tumpahan Oli</option>
                <option value="Kebakaran Area Panel" <?php if($skenario_sekarang == 'Kebakaran Area Panel') echo 'selected'; ?>>4. Kebakaran Area Panel</option>
                <option value="Evakuasi Gempa Bumi" <?php if($skenario_sekarang == 'Evakuasi Gempa Bumi') echo 'selected'; ?>>5. Evakuasi Gempa Bumi</option>
            </select>
            <br>
            <button type="submit" name="simpan_skenario">Terapkan Skenario</button>
        </form>
    </div>
</div>
        <p><a href="index.php" style="color: #00ff41;">← Kembali Dashboard</a></p>
    </div>

</body>
</html>
