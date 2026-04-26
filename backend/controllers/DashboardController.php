<?php
require_once __DIR__ . '/../../config/database.php';

class DashboardController {
    private $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    public function index() {
        $user = getCurrentUser();

        // Get active scenario
        $query_skenario = mysqli_query($this->db, "SELECT skenario_aktif FROM pengaturan_skenario WHERE id = 1");
        $data_skenario = mysqli_fetch_assoc($query_skenario);
        $skenario_aktif = $data_skenario['skenario_aktif'] ?? 'Kebocoran Pipa Gas';

        // Handle POST requests for admin actions
        $this->handlePostRequests();

        // Get statistics
        $stats = $this->getStatistics();

        // Load the view
        require_once __DIR__ . '/../../frontend/templates/dashboard.php';
    }

    private function handlePostRequests() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $user = getCurrentUser();

        // Admin: Add user
        if (isset($_POST['tambah_user']) && $user['role'] === 'admin') {
            $this->addUser();
        }

        // Admin: Delete user
        if (isset($_GET['hapus_user']) && $user['role'] === 'admin') {
            $this->deleteUser();
        }

        // Admin: Delete single simulation record
        if (isset($_GET['hapus_riwayat']) && $user['role'] === 'admin') {
            $this->deleteSimulationRecord();
        }

        // Admin: Clear all simulation records
        if (isset($_POST['kosongkan_riwayat']) && $user['role'] === 'admin') {
            $this->clearAllSimulationRecords();
        }
    }

    private function addUser() {
        $nama = trim(mysqli_real_escape_string($this->db, $_POST['nama']));
        $nim = trim(mysqli_real_escape_string($this->db, $_POST['nim']));
        $role = mysqli_real_escape_string($this->db, $_POST['role']);
        $password = password_hash('123456', PASSWORD_DEFAULT);

        if ($nama === '' || $nim === '') {
            echo "<script>alert('Nama dan NIM wajib diisi.');</script>";
            return;
        }

        // Check for duplicate NIM
        $cekUser = mysqli_query($this->db, "SELECT * FROM users WHERE nim_nidn = '$nim' LIMIT 1");
        if (mysqli_num_rows($cekUser) > 0) {
            echo "<script>alert('Error: NIM/NIDN tersebut sudah terdaftar!');</script>";
            return;
        }

        $query = "INSERT INTO users (nama_lengkap, nim_nidn, role, password) VALUES ('$nama', '$nim', '$role', '$password')";
        if (mysqli_query($this->db, $query)) {
            echo "<script>alert('Peserta berhasil ditambahkan! Password default: 123456'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . mysqli_error($this->db);
        }
    }

    private function deleteUser() {
        $id_user = mysqli_real_escape_string($this->db, $_GET['hapus_user']);
        $query = "DELETE FROM users WHERE id_user = '$id_user'";

        if (mysqli_query($this->db, $query)) {
            echo "<script>alert('Peserta berhasil dihapus!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . mysqli_error($this->db);
        }
    }

    private function deleteSimulationRecord() {
        $id_simulasi = mysqli_real_escape_string($this->db, $_GET['hapus_riwayat']);
        $query = "DELETE FROM simulasi WHERE id_simulasi = '$id_simulasi'";

        if (mysqli_query($this->db, $query)) {
            echo "<script>alert('Satu rekaman riwayat berhasil dihapus!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . mysqli_error($this->db);
        }
    }

    private function clearAllSimulationRecords() {
        $query = "DELETE FROM simulasi";

        if (mysqli_query($this->db, $query)) {
            echo "<script>alert('Semua riwayat berhasil dikosongkan!'); window.location.href='index.php';</script>";
        } else {
            echo "Error: " . mysqli_error($this->db);
        }
    }

    private function getStatistics() {
        $user = getCurrentUser();

        // Get pass/fail statistics
        $qLulus = mysqli_query($this->db, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'LULUS'");
        $dLulus = mysqli_fetch_assoc($qLulus);

        $qGagal = mysqli_query($this->db, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'GAGAL'");
        $dGagal = mysqli_fetch_assoc($qGagal);

        return [
            'lulus' => $dLulus['total'] ?? 0,
            'gagal' => $dGagal['total'] ?? 0
        ];
    }
}
?>
