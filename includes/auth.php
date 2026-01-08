<?php
/**
 * Auth Middleware
 * Proteksi halaman yang memerlukan login
 */

require_once __DIR__ . '/../config/config.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    setFlash('warning', 'Silakan login terlebih dahulu!');
    redirect('login.php');
}

// Cek session timeout
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_TIMEOUT)) {
    session_destroy();
    setFlash('warning', 'Session Anda telah berakhir. Silakan login kembali!');
    redirect('login.php');
}

// Update waktu aktivitas terakhir
$_SESSION['login_time'] = time();
