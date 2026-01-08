<?php
/**
 * Konfigurasi Aplikasi Monitoring SNP
 * Database dan Konstanta Aplikasi
 */

// Mulai Session - HARUS DI AWAL sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengaturan Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pengaturan Timezone
date_default_timezone_set('Asia/Jakarta');

// =====================================================
// KONFIGURASI DATABASE
// =====================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'monitoring_snp_app');

// =====================================================
// KONFIGURASI APLIKASI
// =====================================================
define('APP_NAME', 'Monitoring SNP');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistem Monitoring Standar Nasional Pendidikan');

// Base URL - Sesuaikan dengan struktur folder Anda
define('BASE_URL', 'http://localhost/monitoring-snp/');
define('BASE_PATH', __DIR__ . '/../');

// Upload Settings
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Session Settings
define('SESSION_TIMEOUT', 3600); // 1 jam

// =====================================================
// KONEKSI DATABASE
// =====================================================
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Koneksi database gagal: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8");
        } catch (Exception $e) {
            die("ERROR: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    public function lastInsertId() {
        return $this->conn->insert_id;
    }
}

// =====================================================
// FUNGSI HELPER
// =====================================================

/**
 * Redirect ke halaman tertentu
 */
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Cek role user
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

/**
 * Generate Kode Unik
 */
function generateKode($prefix = 'TRX') {
    return $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Hitung Kategori Nilai
 */
function getKategoriNilai($nilai) {
    if ($nilai >= 91 && $nilai <= 100) {
        return 'A (Amat Baik)';
    } elseif ($nilai >= 86 && $nilai <= 90) {
        return 'B (Baik)';
    } elseif ($nilai >= 71 && $nilai <= 85) {
        return 'C (Cukup)';
    } elseif ($nilai >= 55 && $nilai <= 70) {
        return 'D (Sedang)';
    } else {
        return 'E (Kurang)';
    }
}

/**
 * Alert/Notifikasi Flash Message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Sanitize Input
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if request is POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Get POST data
 */
function post($key, $default = '') {
    return isset($_POST[$key]) ? cleanInput($_POST[$key]) : $default;
}

/**
 * Get GET data
 */
function get($key, $default = '') {
    return isset($_GET[$key]) ? cleanInput($_GET[$key]) : $default;
}
