<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K3-VirtuAI - Dashboard Simulation</title>

    <script src="https://aframe.io/releases/1.4.0/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="../frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="../frontend/assets/css/mobile.css">
</head>
<body class="dashboard-theme" data-user-role="<?php echo $_SESSION['role']; ?>" data-admin-scenario="<?php echo $skenario_aktif; ?>" data-stats-lulus="<?php echo $stats['lulus']; ?>" data-stats-gagal="<?php echo $stats['gagal']; ?>">

<nav class="nav">
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
    <div class="nav-brand">K3-VirtuAI 🛡️</div>

    <div class="nav-actions">
        <a href="index.php" class="nav-link nav-dashboard" data-page="dashboard">📊 Dashboard</a>

        <?php if ($_SESSION['role'] === 'mahasiswa') : ?>
            <a href="../backend/profil.php" class="nav-link nav-profil" data-page="profil">👤 Profil Saya</a>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'dosen' || $_SESSION['role'] === 'admin') : ?>
            <a href="../backend/rekap_nilai.php" class="nav-link nav-rekap" data-page="rekap">📈 Rekap Nilai</a>
            <a href="../backend/admin_skenario_fixed.php" class="nav-link nav-skenario" data-page="skenario">⚙️ Atur Skenario</a>
        <?php endif; ?>

        <div id="connectionStatus" class="connection-status">Status: <span class="status-dot">● Terhubung</span></div>

        <a href="../backend/logout.php" class="logout-link">Keluar (<?php echo $_SESSION['nama']; ?>)</a>
    </div>
</nav>

<!-- Mobile Navigation Menu -->
<div class="nav-mobile" id="navMobile">
    <a href="index.php" class="nav-link nav-dashboard" data-page="dashboard">📊 Dashboard</a>
    
    <?php if ($_SESSION['role'] === 'mahasiswa') : ?>
        <a href="../backend/profil.php" class="nav-link nav-profil" data-page="profil">👤 Profil Saya</a>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'dosen' || $_SESSION['role'] === 'admin') : ?>
        <a href="../backend/rekap_nilai.php" class="nav-link nav-rekap" data-page="rekap">📈 Rekap Nilai</a>
        <a href="../backend/admin_skenario_fixed.php" class="nav-link nav-skenario" data-page="skenario">⚙️ Atur Skenario</a>
    <?php endif; ?>

    <div id="statusMobile" class="connection-status">Status: <span class="status-dot">● Terhubung</span></div>
    <a href="../backend/logout.php" class="logout-link logout-link-mobile">🚪 Keluar (<?php echo $_SESSION['nama']; ?>)</a>
</div>

<!-- Breadcrumb Navigation -->
<div class="breadcrumb-container">
    <nav class="breadcrumb" id="breadcrumb">
        <a href="index.php" class="breadcrumb-link">🏠 Dashboard</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current" id="breadcrumbCurrent">Beranda</span>
    </nav>
</div>

<div class="main-container">

    <?php if ($_SESSION['role'] === 'admin') : ?>
    <div class="card">
        <div class="section-title">➕ Manajemen Peserta</div>

        <form method="POST" class="participants-form">
            <input type="text" name="nama" class="input-field" placeholder="Nama Lengkap" required>
            <input type="text" name="nim" class="input-field" placeholder="NIM" required>
            <select name="role" class="input-field" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="tambah_user" class="btn btn-start btn-compact">Simpan</button>
        </form>

        <div class="participants-scroll table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Role</th>
                        <th class="action-cell">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $listUser = mysqli_query($this->db, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                    if(mysqli_num_rows($listUser) == 0){
                        echo "<tr><td colspan='4' class='empty-state'>Belum ada peserta terdaftar.</td></tr>";
                    }
                    while($lu = mysqli_fetch_assoc($listUser)) {
                        echo "<tr>
                                <td>".$lu['nama_lengkap']."</td>
                                <td>".$lu['nim_nidn']."</td>
                                <td>".$lu['role']."</td>
                                <td class='action-cell'>
                                    <a href='index.php?hapus_user=".$lu['id_user']."' class='danger-link' onclick='return confirm(\"Hapus user ini?\")'>Hapus</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">

        <div class="simulation-window" id="simWindow">
            <div id="hazardText" class="hazard-overlay">⚠️ BAHAYA TERDETEKSI!</div>

            <a-scene embedded class="sim-scene">
                <a-entity id="cameraK3" camera position="0 1.6 0" look-controls>
                    <a-cursor id="kursorK3" color="#60a5fa"></a-cursor>
                </a-entity>
                <a-sky color="#060c16"></a-sky>

                <a-plane position="0 0 -3" rotation="-90 0 0" width="10" height="10" color="#10233d" opacity="0.8"></a-plane>

                <a-entity id="indukMesinPipa" visible="true">
                    <a-box id="kubusMesin" width="0.8" height="0.8" depth="0.8" color="#16325c" position="0 0.4 -3"></a-box>
                    <a-cylinder radius="0.06" height="1.2" color="#4b6488" position="0 1.2 -3"></a-cylinder>
                    <a-sphere radius="0.09" color="#3b82f6" position="0 1.8 -3"></a-sphere>
                    <a-cylinder id="pipaHorizontal" radius="0.06" height="3" color="#4b6488" position="1.5 1.8 -3" rotation="0 0 90"></a-cylinder>
                    <a-circle id="efekGenanganOli" radius="1.2" color="#60a5fa" position="0 0.01 -3" rotation="-90 0 0" opacity="0.65" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.15 1.15 1.15; dur: 800; loop: true; dir: alternate;"></a-circle>
                    <a-sphere id="efekBocorPipa" radius="0.3" color="#38bdf8" position="1.5 1.8 -3" opacity="0.75" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.2 1.2 1.2; dur: 700; loop: true; dir: alternate;"></a-sphere>
                    <a-sphere id="efekListrik" radius="0.25" color="#93c5fd" position="0 1.2 -2.5" opacity="0.95" visible="false" onclick="ambilTindakan()" animation="property: scale; from: 1 1 1; to: 1.25 1.25 1.25; dur: 600; loop: true; dir: alternate;"></a-sphere>
                    <a-sphere id="efekHancur" radius="0.6" color="#2563eb" position="0 1.2 -3" opacity="0.78" visible="false" animation="property: scale; from: 1 1 1; to: 1.18 1.18 1.18; dur: 750; loop: true; dir: alternate;"></a-sphere>
                </a-entity>
            </a-scene>

            <div id="timerText" class="timer-text"></div>
        </div>

        <div class="dashboard-sidebar">

            <div class="card control-card">
                <h3 class="card-heading">Simulator Control</h3>

                <label class="field-label">Peserta Aktif:</label>
                <select id="pilihPeserta" class="input-field">
                    <?php if ($_SESSION['role'] === 'mahasiswa') : ?>
                        <option value="<?php echo $_SESSION['id_user']; ?>"><?php echo $_SESSION['nama']; ?> (Pribadi)</option>
                    <?php elseif ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'dosen') : ?>
                        <option value="<?php echo $_SESSION['id_user']; ?>"><?php echo $_SESSION['nama']; ?> (Akun Pribadi)</option>
                    <?php else : ?>
                        <option value="">-- Pilih Nama --</option>
                        <?php
                        $ambilUser = mysqli_query($this->db, "SELECT * FROM users ORDER BY nama_lengkap ASC");
                        while($u = mysqli_fetch_assoc($ambilUser)) {
                            echo "<option value='".$u['id_user']."'>".$u['nama_lengkap']." (".$u['nim_nidn'].")</option>";
                        }
                        ?>
                    <?php endif; ?>
                </select>

                <button class="btn btn-start" onclick="startSim()">MULAI</button>
                <button class="btn btn-emergency btn-tall" onclick="ambilTindakan()">TINDAKAN DARURAT</button>

                <div class="ai-status" id="aiLog">> System Online</div>
            </div>

            <div class="card stats-card">
                <div class="section-title">📊 Statistik Kelulusan</div>

                <div class="chart-wrap">
                    <canvas id="grafikK3"></canvas>
                </div>
                <div id="chartStatSummary" class="chart-summary">
                    Memuat statistik kelulusan...
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="section-header">
            <div class="section-title">🏆 Riwayat Hasil Simulasi</div>

            <?php if ($_SESSION['role'] === 'admin') : ?>
            <form method="POST" class="card-toolbar" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA riwayat simulasi?')">
                <button type="submit" name="kosongkan_riwayat" class="btn-delete">Kosongkan Semua Riwayat</button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Search and Filter Controls -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchRiwayat" class="input-field" placeholder="🔍 Cari nama peserta, risiko, atau status..." onkeyup="filterTable('riwayatTable', this.value)">
            </div>
            <div class="filter-controls">
                <select id="filterStatus" class="input-field filter-select" onchange="applyTableFilters('riwayatTable')">
                    <option value="">Semua Status</option>
                    <option value="LULUS">✓ LULUS</option>
                    <option value="GAGAL">✗ GAGAL</option>
                </select>
                <select id="filterRisiko" class="input-field filter-select" onchange="applyTableFilters('riwayatTable')">
                    <option value="">Semua Risiko</option>
                    <option value="Tumpahan Oli">Tumpahan Oli</option>
                    <option value="Bocor Pipa">Bocor Pipa</option>
                    <option value="Korsleting Listrik">Korsleting Listrik</option>
                    <option value="Mesin Terpukul">Mesin Terpukul</option>
                    <option value="APD Rusak">APD Rusak</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
        <table id="riwayatTable">
            <thead>
                <tr>
                   <th>Nama Peserta</th>
                   <th>Risiko</th>
                   <th>Skor</th>
                   <th>Status</th>
                   <th>Rekomendasi AI</th>
                   <th class="action-cell">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($_SESSION['role'] === 'mahasiswa') {
                    $id_mhs = $_SESSION['id_user'];
                    $ambilSimulasi = mysqli_query($this->db, "SELECT users.nama_lengkap, simulasi.* FROM simulasi
                                                            JOIN users ON simulasi.id_user = users.id_user
                                                            WHERE simulasi.id_user = '$id_mhs'
                                                            ORDER BY id_simulasi DESC");
                } else {
                    $ambilSimulasi = mysqli_query($this->db, "SELECT users.nama_lengkap, simulasi.* FROM simulasi
                                                            JOIN users ON simulasi.id_user = users.id_user
                                                            ORDER BY id_simulasi DESC");
                }

                if(mysqli_num_rows($ambilSimulasi) == 0){
                    echo "<tr><td colspan='6' class='empty-state'>Belum ada riwayat simulasi.</td></tr>";
                }

                while($s = mysqli_fetch_assoc($ambilSimulasi)) {
                    $statusClass = ($s['status_kelulusan'] == "LULUS") ? "status-badge status-lulus" : "status-badge status-gagal";
                    echo "<tr>
                            <td>".$s['nama_lengkap']."</td>
                            <td>".$s['jenis_risiko']."</td>
                            <td>".$s['skor']."</td>
                            <td><span class='".$statusClass."'>".$s['status_kelulusan']."</span></td>
                            <td>".$s['rekomendasi']."</td>
                            <td class='action-cell'>";

                                if ($_SESSION['role'] === 'admin') {
                                    echo "<a href='index.php?hapus_riwayat=".$s['id_simulasi']."' class='btn-delete' onclick='return confirm(\"Hapus rekaman riwayat ini?\")'>Hapus</a>";
                                } else {
                                    echo "<span class='no-action'>No Action</span>";
                                }

                    echo "</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="card">
        <div class="section-title">📋 Riwayat Keputusan Pengguna (Decision Log)</div>

        <!-- Search and Filter Controls for Decision Logs -->
        <div class="search-filter-container">
            <div class="search-box">
                <input type="text" id="searchDecision" class="input-field" placeholder="🔍 Cari nama peserta, risiko, atau tindakan..." onkeyup="filterTable('decisionTable', this.value)">
            </div>
            <div class="filter-controls">
                <select id="filterDecisionStatus" class="input-field filter-select" onchange="applyTableFilters('decisionTable')">
                    <option value="">Semua Status</option>
                    <option value="LULUS">✓ LULUS</option>
                    <option value="GAGAL">✗ GAGAL</option>
                </select>
                <select id="filterDecisionRisiko" class="input-field filter-select" onchange="applyTableFilters('decisionTable')">
                    <option value="">Semua Risiko</option>
                    <option value="Tumpahan Oli">Tumpahan Oli</option>
                    <option value="Bocor Pipa">Bocor Pipa</option>
                    <option value="Korsleting Listrik">Korsleting Listrik</option>
                    <option value="Mesin Terpukul">Mesin Terpukul</option>
                    <option value="APD Rusak">APD Rusak</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
        <table id="decisionTable">
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
                    $ambilLogs = mysqli_query($this->db, "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs
                                                        JOIN users ON decision_logs.id_user = users.id_user
                                                        WHERE decision_logs.id_user = '$id_mhs'
                                                        ORDER BY decision_logs.created_at DESC");
                } else {
                    $ambilLogs = mysqli_query($this->db, "SELECT users.nama_lengkap, decision_logs.* FROM decision_logs
                                                        JOIN users ON decision_logs.id_user = users.id_user
                                                        ORDER BY decision_logs.created_at DESC");
                }

                if(mysqli_num_rows($ambilLogs) == 0){
                    echo "<tr><td colspan='6' class='empty-state'>Belum ada riwayat keputusan.</td></tr>";
                }

                while($l = mysqli_fetch_assoc($ambilLogs)) {
                    $statusClass = ($l['status_kelulusan'] == "LULUS") ? "status-badge status-lulus" : "status-badge status-gagal";
                    echo "<tr>
                            <td>".$l['nama_lengkap']."</td>
                            <td>".$l['jenis_risiko']."</td>
                            <td>".$l['tindakan_dipilih']."</td>
                            <td>".$l['skor']."</td>
                            <td><span class='".$statusClass."'>".$l['status_kelulusan']."</span></td>
                            <td>".date('d/m/Y H:i', strtotime($l['created_at']))."</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL REMEDIAL PERSONALIZED -->
<div id="remedialModal" class="remedial-modal">
    <div class="remedial-modal-content">
        <div class="remedial-header">
            <h2 id="remedialTitle">📚 PEMBELAJARAN REMEDIAL PERSONALIZED</h2>
            <span class="remedial-close" onclick="closeRemedialModal()">&times;</span>
        </div>

        <div class="remedial-body">
            <div class="remedial-3d-container">
                <a-scene embedded id="remedialScene" class="remedial-scene">
                    <a-entity camera position="0 1.6 0" look-controls>
                        <a-cursor color="#60a5fa"></a-cursor>
                    </a-entity>
                    <a-sky color="#08111d"></a-sky>
                    <a-plane position="0 0 -3" rotation="-90 0 0" width="8" height="8" color="#10233d" opacity="0.88"></a-plane>

                    <!-- Visualisasi Remedial - akan diisi secara dinamis -->
                    <a-entity id="remedialVisualization"></a-entity>

                    <!-- Panduan Interaktif -->
                    <a-text id="remedialText" value="Klik objek untuk mempelajari lebih lanjut" position="0 2 -2" align="center" color="#60a5fa" scale="0.8 0.8 0.8"></a-text>
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
            <button onclick="start3DVisualization()" class="start-3d-btn">🎮 Mulai Visualisasi 3D</button>
            <button onclick="retrySimulation()" class="retry-btn">🔄 Coba Lagi Simulasi</button>
            <button onclick="closeRemedialModal()" class="continue-btn">✅ Lanjut ke Simulasi Baru</button>
        </div>
    </div>
</div>

<script src="../frontend/assets/js/dashboard.js"></script>

</body>
</html>
