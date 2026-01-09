# 🚀 Panduan Deploy ke Hostinger

Panduan lengkap untuk deploy aplikasi **Monitoring SNP** ke hosting Hostinger.

---

## 📋 Persiapan

### 1. Yang Anda Butuhkan:

- ✅ Akun Hostinger (Shared Hosting / Cloud Hosting)
- ✅ Domain (contoh: `monitoring-snp.com` atau subdomain `snp.namaanda.com`)
- ✅ Akses cPanel / hPanel
- ✅ File aplikasi dari local development

---

## 🗂️ LANGKAH 1: UPLOAD FILE

### Via File Manager (Hostinger hPanel):

1. Login ke **hPanel Hostinger** (https://hpanel.hostinger.com)
2. Pilih domain/website Anda
3. Klik **File Manager**
4. Masuk ke folder `public_html` (untuk domain utama)
   - Atau `public_html/subdomain_folder` (untuk subdomain)
5. **Upload semua file** dari project `monitoring-snp`:
   ```
   - 404.php
   - check.php
   - composer.json
   - dashboard.php
   - index.php
   - login.php
   - logout.php
   - assets/
   - config/
   - includes/
   - modules/
   - uploads/
   - .htaccess
   ```
6. Jangan upload folder `sample/`, `bahan/`, file `*.md`

### Via FTP (FileZilla):

1. Download **FileZilla** (https://filezilla-project.org)
2. Buka FileZilla, koneksi ke:
   ```
   Host: ftp.namaanda.com (atau IP dari Hostinger)
   Username: (dari Hostinger)
   Password: (dari Hostinger)
   Port: 21
   ```
3. Upload semua file ke folder `public_html`

---

## 🗄️ LANGKAH 2: BUAT DATABASE

### Di hPanel Hostinger:

1. Masuk ke **Databases** → **MySQL Databases**
2. Klik **Create New Database**
3. Isi nama database: `u123456789_monitoring_snp`
4. Klik **Create**
5. **Catat informasi:**
   ```
   Database Name: u123456789_monitoring_snp
   Database User: u123456789_admin (otomatis dibuat)
   Password: (password yang Anda buat)
   Hostname: localhost
   ```

---

## 📊 LANGKAH 3: IMPORT DATABASE

### Via phpMyAdmin:

1. Di hPanel, klik **phpMyAdmin**
2. Login menggunakan user database Anda
3. Pilih database `u123456789_monitoring_snp`
4. Klik tab **Import**
5. **Choose File** → pilih `config/database_snp.sql` dari komputer Anda
6. Klik **Go** / **Import**
7. Tunggu sampai selesai (muncul pesan sukses)

### File SQL yang Dibutuhkan:

```
1. database_snp.sql           - Database utama
2. update_add_skor.sql        - Update untuk kolom skor (jika ada)
3. migration_fix_struktur.sql - Fix struktur (jika diperlukan)
```

**Import urutan:**

1. `database_snp.sql` (HARUS yang pertama)
2. `update_add_skor.sql` (jika ada error kolom skor)
3. `migration_fix_struktur.sql` (jika ada error struktur)

---

## ⚙️ LANGKAH 4: KONFIGURASI config.php

### Edit File config/config.php:

Via File Manager atau FTP, edit file `config/config.php`:

```php
<?php
// ==============================================
// CONFIGURATION SETTINGS - PRODUCTION
// ==============================================

// Database Configuration - SESUAIKAN DENGAN DATABASE HOSTINGER
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_monitoring_snp');  // Ganti dengan nama database Anda
define('DB_USER', 'u123456789_admin');            // Ganti dengan user database Anda
define('DB_PASS', 'PasswordDatabase123!');        // Ganti dengan password database Anda
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'Monitoring SNP');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistem Monitoring Standar Nasional Pendidikan');

// Base URL - SESUAIKAN DENGAN DOMAIN ANDA
define('BASE_URL', 'https://monitoring-snp.com/');  // Ganti dengan domain Anda
// Atau jika pakai subdomain:
// define('BASE_URL', 'https://snp.namaanda.com/');
// Atau jika di subfolder:
// define('BASE_URL', 'https://namaanda.com/monitoring-snp/');

define('BASE_PATH', __DIR__ . '/../');

// Upload Settings
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx']);

// Session Settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS only - PENTING untuk production
define('SESSION_LIFETIME', 3600); // 1 hour

// Security Settings
define('ENCRYPTION_KEY', 'GANTI_DENGAN_KEY_RANDOM_YANG_KUAT_MIN_32_KARAKTER_1234567890');
define('PASSWORD_SALT', 'GANTI_DENGAN_SALT_RANDOM_YANG_KUAT_MIN_32_KARAKTER_0987654321');

// Error Reporting - PRODUCTION MODE
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);      // Jangan tampilkan error ke user
ini_set('log_errors', 1);          // Simpan error ke log file
ini_set('error_log', BASE_PATH . 'logs/error.log');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_error) {
            // Jangan tampilkan detail error di production
            error_log('Database Connection Error: ' . $this->connection->connect_error);
            die('Database connection failed. Please contact administrator.');
        }

        $this->connection->set_charset(DB_CHARSET);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }
}

// Helper Functions
function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit();
}

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Auto-start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
```

### ⚠️ PENTING - Sesuaikan:

1. **Database:**

   ```php
   define('DB_NAME', 'u123456789_monitoring_snp');  // Nama database Anda
   define('DB_USER', 'u123456789_admin');            // User database Anda
   define('DB_PASS', 'PasswordDatabase123!');        // Password database Anda
   ```

2. **BASE_URL:**

   ```php
   // Jika domain utama:
   define('BASE_URL', 'https://monitoring-snp.com/');

   // Jika subdomain:
   define('BASE_URL', 'https://snp.namaanda.com/');

   // Jika di subfolder:
   define('BASE_URL', 'https://namaanda.com/monitoring-snp/');
   ```

3. **Security Keys (WAJIB diganti!):**
   ```php
   define('ENCRYPTION_KEY', 'buat_key_random_32_karakter_atau_lebih');
   define('PASSWORD_SALT', 'buat_salt_random_32_karakter_atau_lebih');
   ```

---

## 🔒 LANGKAH 5: SET PERMISSIONS (Izin Folder)

### Via File Manager:

1. Klik kanan folder **uploads/** → **Permissions**
2. Set ke `755` atau `775`
3. Centang **Apply to subdirectories**

### Atau buat folder logs:

1. Buat folder baru: `logs/`
2. Set permissions: `755`
3. Buat file `.htaccess` di dalam folder `logs/`:
   ```apache
   # Deny access to log files
   Order allow,deny
   Deny from all
   ```

---

## 🌐 LANGKAH 6: KONFIGURASI .htaccess

### Periksa file .htaccess di root:

```apache
# Security Settings
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Force HTTPS (jika sudah install SSL)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# PHP Settings
<IfModule mod_php7.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value max_input_time 300
</IfModule>

# Prevent Directory Browsing
Options -Indexes

# Error Pages
ErrorDocument 404 /404.php

# Default Charset
AddDefaultCharset UTF-8

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

# Protect config files
<FilesMatch "^(config\.php|database_snp\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect .md files
<FilesMatch "\.md$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## 🔐 LANGKAH 7: INSTALL SSL CERTIFICATE

### Di hPanel Hostinger:

1. Masuk ke **SSL** → **SSL Certificates**
2. Pilih domain Anda
3. Klik **Install SSL** (FREE dari Let's Encrypt)
4. Tunggu beberapa menit hingga aktif
5. Akses website dengan `https://` (harus otomatis redirect)

---

## ✅ LANGKAH 8: TEST APLIKASI

### 1. Akses Website:

```
https://namaanda.com/
```

### 2. Test Login:

```
Username: admin
Password: admin123
```

### 3. Cek Fitur:

- ✅ Login berhasil
- ✅ Dashboard muncul
- ✅ Master Data bisa diakses
- ✅ Input penilaian berfungsi
- ✅ Export PDF berfungsi
- ✅ CSS/JS load dengan benar

---

## 🔧 TROUBLESHOOTING

### ❌ Error: Database Connection Failed

**Penyebab:**

- Database belum dibuat
- Username/password salah
- Hostname salah

**Solusi:**

1. Cek kembali `config/config.php`
2. Pastikan `DB_HOST = 'localhost'`
3. Pastikan user dan password sesuai dengan yang di Hostinger
4. Test koneksi database via phpMyAdmin

---

### ❌ Error: Page Not Found / 404

**Penyebab:**

- BASE_URL salah

**Solusi:**

1. Edit `config/config.php`
2. Sesuaikan `BASE_URL` dengan domain Anda:

   ```php
   // Contoh jika domain: https://snp.namaanda.com
   define('BASE_URL', 'https://snp.namaanda.com/');

   // WAJIB pakai trailing slash (/) di akhir!
   ```

---

### ❌ CSS/JS Tidak Load

**Penyebab:**

- BASE_URL salah
- File tidak terupload

**Solusi:**

1. Cek `BASE_URL` di `config/config.php`
2. Pastikan folder `assets/` sudah terupload lengkap
3. Periksa permissions folder `assets/` → set ke `755`
4. Clear cache browser (Ctrl + F5)

---

### ❌ Error: Cannot Upload File

**Penyebab:**

- Folder uploads tidak ada write permission

**Solusi:**

1. Set permissions folder `uploads/` ke `755` atau `775`
2. Cek `php.ini` settings (via hPanel → PHP Configuration):
   ```
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```

---

### ❌ Error: Session / Login Redirect Loop

**Penyebab:**

- Session tidak bisa di-save

**Solusi:**

1. Periksa permissions folder `/tmp` (biasanya otomatis OK di Hostinger)
2. Clear cookies browser
3. Coba browser lain / incognito mode
4. Edit `config/config.php`, tambahkan:
   ```php
   session_save_path(BASE_PATH . 'sessions/');
   ```
5. Buat folder `sessions/` dengan permission `755`

---

### ❌ Error: 500 Internal Server Error

**Penyebab:**

- Syntax error di PHP
- .htaccess tidak compatible

**Solusi:**

1. Cek **Error Log** di hPanel → **Advanced** → **Error Logs**
2. Jika error di `.htaccess`, coba disable beberapa module:
   ```apache
   # Matikan jika error
   # Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
   ```
3. Pastikan PHP version minimal 7.4 (cek di hPanel → PHP Configuration)

---

### ❌ Database Import Error: "MySQL server has gone away"

**Penyebab:**

- File SQL terlalu besar
- Timeout

**Solusi:**

1. Split file SQL jadi beberapa bagian
2. Atau import via SSH (jika punya akses):
   ```bash
   mysql -u u123456789_admin -p u123456789_monitoring_snp < database_snp.sql
   ```

---

## 📊 MONITORING & MAINTENANCE

### 1. Backup Database Rutin:

Di phpMyAdmin:

1. Select database
2. Tab **Export**
3. Format: **SQL**
4. Klik **Go**
5. Save file dengan nama: `backup_monitoring_snp_2026-01-08.sql`

### 2. Backup File:

Via File Manager:

1. Compress folder `public_html`
2. Download file `.zip`
3. Simpan di komputer/cloud storage

### 3. Update Aplikasi:

1. Backup dulu database + file
2. Upload file baru via FTP
3. Test semua fitur

### 4. Monitor Error Log:

Di hPanel → **Advanced** → **Error Logs**

---

## 🎯 CHECKLIST FINAL

Pastikan semua sudah OK sebelum go-live:

- [ ] ✅ Database sudah dibuat dan diimport
- [ ] ✅ config.php sudah disesuaikan (database, BASE_URL, security keys)
- [ ] ✅ SSL certificate sudah aktif (HTTPS)
- [ ] ✅ .htaccess force HTTPS sudah aktif
- [ ] ✅ Folder uploads/ permissions sudah 755
- [ ] ✅ Folder logs/ sudah dibuat
- [ ] ✅ Test login berhasil
- [ ] ✅ Test semua menu berfungsi
- [ ] ✅ Test upload file berfungsi
- [ ] ✅ Test export PDF berfungsi
- [ ] ✅ CSS/JS load dengan benar
- [ ] ✅ Ganti password default admin
- [ ] ✅ Error reporting = OFF (production mode)

---

## 🔐 KEAMANAN TAMBAHAN

### 1. Ganti Password Admin:

Login → Menu **User Management** → Edit user `admin` → Ganti password

### 2. Nonaktifkan Display Errors:

Di `config/config.php`:

```php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

### 3. Protect Folder Config:

File `.htaccess` sudah melindungi file `config.php`

### 4. Backup Berkala:

- Database: 1x seminggu
- File: 1x sebulan

---

## 📞 SUPPORT

### Jika Ada Masalah:

1. **Cek Error Log** di hPanel terlebih dahulu
2. **Cek file** `logs/error.log` (jika sudah dibuat)
3. **Test database connection** via `check.php`
4. **Contact Hostinger Support** (24/7 live chat)

---

## 🎓 TIPS PRODUCTION

### Performance:

1. **Enable OPcache** di PHP Configuration (hPanel)
2. **Use CDN** untuk assets (optional)
3. **Optimize images** sebelum upload
4. **Enable Gzip compression** (sudah ada di .htaccess)

### Security:

1. **Update PHP** ke versi terbaru
2. **Ganti password** database secara berkala
3. **Backup rutin** database dan file
4. **Monitor access log** untuk aktivitas mencurigakan

---

**SELAMAT! Aplikasi Monitoring SNP Anda sudah LIVE di Hostinger! 🎉**
