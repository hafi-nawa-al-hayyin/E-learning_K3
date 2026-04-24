<?php
include 'koneksi.php';
global $conn;
$koneksi = $conn;

if (isset($_POST['simpan_skenario'])) {
    $skenario_baru = $_POST['pilihan_skenario'];
    mysqli_query($koneksi, "UPDATE pengaturan_skenario SET skenario_aktif = '$skenario_baru' WHERE id = 1");
    $pesan = "Skenario berhasil diubah menjadi: " . $skenario_baru;
}

// Ambil skenario yang saat ini aktif
$ambil = mysqli_query($koneksi, "SELECT skenario_aktif FROM pengaturan_skenario WHERE id = 1");
$data = mysqli_fetch_assoc($ambil);
$skenario_sekarang = $data['skenario_aktif'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Panel Admin - Atur Skenario K3</title>
    <style>
        body { font-family: Arial, sans-serif; background: #1a1a1a; color: white; text-align: center; padding: 50px; }
        .box { background: #2c2c2c; padding: 30px; border-radius: 10px; display: inline-block; border: 1px solid #3b82f6; }
        select, button { padding: 10px; font-size: 16px; margin: 10px; border-radius: 5px; }
        select { background: #333; color: white; border: 1px solid #555; }
        button { background: #3b82f6; color: white; font-weight: bold; cursor: pointer; border: none; }
        button:hover { background: #2563eb; }
        .pesan { color: #3b82f6; font-weight: bold; }
    </style>
</head>
<body>

    <h2>PENGATURAN SIMULASI K3</h2>
    
    <div class="box">
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

</body>
</html>