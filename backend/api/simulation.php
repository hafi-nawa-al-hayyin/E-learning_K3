<?php
require_once __DIR__ . '/../../config/database.php';
header('Content-Type: application/json');

class SimulationAPI {
    private $db;

    public function __construct() {
        $this->db = getDBConnection();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        switch ($method) {
            case 'POST':
                $this->handlePost($action);
                break;
            case 'GET':
                $this->handleGet($action);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }

    private function handlePost($action) {
        switch ($action) {
            case 'start_simulation':
                $this->startSimulation();
                break;
            case 'save_result':
                $this->saveSimulationResult();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    }

    private function handleGet($action) {
        switch ($action) {
            case 'stats':
                $this->getStatistics();
                break;
            case 'users':
                $this->getUsers();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    }

    private function startSimulation() {
        // Logic for starting simulation
        $user = getCurrentUser();
        $data = json_decode(file_get_contents('php://input'), true);

        // Return simulation data
        echo json_encode([
            'status' => 'success',
            'user_id' => $user['id'],
            'scenario' => $data['scenario'] ?? 'default'
        ]);
    }

    private function saveSimulationResult() {
        $user = getCurrentUser();
        $data = $this->getRequestData();
        $targetUserId = $this->resolveTargetUserId($user, $data);
        $jenisRisiko = (string) ($data['jenis_risiko'] ?? '');
        $skor = (int) ($data['skor'] ?? 0);
        $statusKelulusan = (string) ($data['status_kelulusan'] ?? '');
        $rekomendasi = (string) ($data['rekomendasi'] ?? '');
        $konsekuensi = (string) ($data['konsekuensi'] ?? '');
        $kategoriRisiko = (string) ($data['kategori_risiko'] ?? '');

        if (!$targetUserId || $jenisRisiko === '' || !isset($data['skor']) || $statusKelulusan === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Data simulasi tidak lengkap.']);
            return;
        }

        // Save simulation result to database
        $query = "INSERT INTO simulasi (id_user, jenis_risiko, skor, status_kelulusan, rekomendasi, konsekuensi, kategori_risiko)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'isissss',
            $targetUserId,
            $jenisRisiko,
            $skor,
            $statusKelulusan,
            $rekomendasi,
            $konsekuensi,
            $kategoriRisiko
        );

        if (mysqli_stmt_execute($stmt)) {
            // Also save to decision logs
            $this->saveDecisionLog($targetUserId, $data);

            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => mysqli_error($this->db)]);
        }
    }

    private function saveDecisionLog($targetUserId, $data) {
        $jenisRisiko = (string) ($data['jenis_risiko'] ?? '');
        $tindakanDipilih = (string) ($data['tindakan_dipilih'] ?? '');
        $skor = (int) ($data['skor'] ?? 0);
        $statusKelulusan = (string) ($data['status_kelulusan'] ?? '');
        $kategoriRisiko = (string) ($data['kategori_risiko'] ?? '');

        $query = "INSERT INTO decision_logs (id_user, jenis_risiko, tindakan_dipilih, skor, status_kelulusan, kategori_risiko)
                  VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ississ',
            $targetUserId,
            $jenisRisiko,
            $tindakanDipilih,
            $skor,
            $statusKelulusan,
            $kategoriRisiko
        );

        mysqli_stmt_execute($stmt);
    }

    private function getRequestData() {
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
            return $jsonData;
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        return [];
    }

    private function resolveTargetUserId($user, $data) {
        $requestedUserId = isset($data['id_user']) ? (int) $data['id_user'] : 0;

        if ($requestedUserId > 0 && in_array($user['role'], ['admin', 'dosen'], true)) {
            return $requestedUserId;
        }

        return (int) ($user['id'] ?? 0);
    }

    private function getStatistics() {
        $qLulus = mysqli_query($this->db, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'LULUS'");
        $dLulus = mysqli_fetch_assoc($qLulus);

        $qGagal = mysqli_query($this->db, "SELECT COUNT(*) as total FROM simulasi WHERE status_kelulusan = 'GAGAL'");
        $dGagal = mysqli_fetch_assoc($qGagal);

        $qTotal = mysqli_query($this->db, "SELECT COUNT(*) as total FROM simulasi");
        $dTotal = mysqli_fetch_assoc($qTotal);

        echo json_encode([
            'lulus' => $dLulus['total'] ?? 0,
            'gagal' => $dGagal['total'] ?? 0,
            'total' => $dTotal['total'] ?? 0
        ]);
    }

    private function getUsers() {
        $user = getCurrentUser();

        if ($user['role'] === 'mahasiswa') {
            // Students can only see themselves
            $query = "SELECT id_user, nama_lengkap, nim_nidn, role FROM users WHERE id_user = ?";
            $stmt = mysqli_prepare($this->db, $query);
            mysqli_stmt_bind_param($stmt, 'i', $user['id']);
        } else {
            // Admins and lecturers can see all users
            $query = "SELECT id_user, nama_lengkap, nim_nidn, role FROM users ORDER BY nama_lengkap ASC";
            $stmt = mysqli_prepare($this->db, $query);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }

        echo json_encode($users);
    }
}

// Initialize and handle request
requireLogin('../login.php');
$api = new SimulationAPI();
$api->handleRequest();
?>
