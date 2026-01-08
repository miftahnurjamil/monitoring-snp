# 📋 Aplikasi Monitoring Standar Nasional Pendidikan (SNP)

Aplikasi web berbasis PHP untuk monitoring dan penilaian 8 Standar Nasional Pendidikan (SNP) di sekolah.

## ✨ Fitur Utama

### 1. **Data Master**

- ✅ Master 8 SNP (Standar Nasional Pendidikan)
- ✅ Master Sekolah (NPSN, Nama, Alamat, Kepala Sekolah, NIP)
- ✅ Master Pengawas (NIP, Nama, Pangkat, Jabatan, Wilayah Binaan)
- ✅ Master Pertanyaan/Indikator SNP
- ✅ Manajemen User (Admin, Operator, Pengawas)

### 2. **Transaksi Penilaian**

- ✅ Form penilaian dengan skor skala 0-4
- ✅ Support multiple aspek per SNP
- ✅ Support sub-pertanyaan/sub-indikator
- ✅ Perhitungan otomatis skor dan nilai
- ✅ Kategorisasi nilai (A, B, C, D, E)

### 3. **Laporan**

- ✅ Rekapitulasi penilaian per SNP
- ✅ Export laporan ke PDF
- ✅ Print laporan
- ✅ Dashboard dengan statistik dan grafik

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 5, jQuery, Chart.js, DataTables
- **PDF**: HTML to PDF (fallback) / TCPDF (optional)

## 📦 Persyaratan Sistem

- PHP >= 7.4
- MySQL/MariaDB >= 5.7
- Apache/Nginx Web Server
- Laragon/XAMPP/WAMP (untuk development)
- Composer (optional, untuk library tambahan)

## 🚀 Instalasi

### 1. Setup Database

Buka phpMyAdmin atau MySQL client, jalankan file SQL:

```bash
# Import database
mysql -u root -p < config/database_snp.sql
```

Atau via phpMyAdmin:

1. Buka http://localhost/phpmyadmin
2. Klik "Import"
3. Pilih file `config/database_snp.sql`
4. Klik "Go"

Database `monitoring_snp_app` akan dibuat otomatis beserta semua tabel dan data contoh.

### 2. Konfigurasi Database

Edit file `config/config.php` jika perlu mengubah setting database:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'monitoring_snp_app');
```

### 3. Setup BASE_URL

Edit `BASE_URL` di `config/config.php` sesuai struktur folder Anda:

```php
// Contoh jika folder ada di: d:\laragon\www\monitoring-snp\
define('BASE_URL', 'http://localhost/monitoring-snp/');
```

### 4. Install Dependencies (Optional)

Untuk fitur export PDF yang lebih baik, install TCPDF via Composer:

```bash
cd snp-app
composer install
```

Jika tidak menggunakan Composer, aplikasi akan menggunakan HTML to PDF fallback.

### 5. Akses Aplikasi

Buka browser:

```
http://localhost/monitoring-snp/
```

## 🔑 Login

### Akun Default

**Admin:**

- Username: `admin`
- Password: `admin123`

**Operator:**

- Username: `operator`
- Password: `operator123`

## 📖 Cara Penggunaan

### A. Kelola Data Master

#### 1. Master Sekolah

- Menu: **Data Master > Master Sekolah**
- Isi data: NPSN, Nama Sekolah, Alamat, Kepala Sekolah, NIP, dll
- Klik **Simpan**

#### 2. Master Pengawas

- Menu: **Data Master > Master Pengawas**
- Isi data: NIP, Nama, Pangkat/Golongan, Jabatan, Wilayah Binaan
- Klik **Simpan**

#### 3. Master Pertanyaan SNP

- Menu: **Data Master > Master Pertanyaan**
- Pilih SNP yang akan ditambahkan pertanyaan
- Tambahkan aspek/indikator
- Tambahkan sub-pertanyaan jika ada
- Klik **Simpan**

### B. Input Penilaian

#### 1. Buat Penilaian Baru

- Menu: **Transaksi > Input Penilaian Baru**
- Pilih Sekolah
- Pilih Pengawas (optional)
- Isi Tahun Ajaran, Semester, Tanggal
- Pilih SNP yang akan dinilai
- Klik **Lanjut ke Form Penilaian**

#### 2. Isi Detail Penilaian

- Sistem akan menampilkan form penilaian dengan aspek dan indikator
- Pilih skor 0-4 untuk setiap indikator
  - **0**: Tidak ada/Tidak dilaksanakan
  - **1**: Kurang
  - **2**: Cukup
  - **3**: Baik
  - **4**: Sangat Baik
- Klik **Simpan Penilaian**

#### 3. Lihat Hasil

- Sistem otomatis menghitung:
  - Total Skor Perolehan
  - Total Skor Maksimal
  - Nilai (0-100)
  - Kategori (A, B, C, D, E)

### C. Laporan

#### 1. Lihat Detail Penilaian

- Menu: **Transaksi > Data Penilaian**
- Klik tombol **👁️ Lihat** pada data yang diinginkan
- Tampil rekapitulasi per SNP

#### 2. Export PDF

- Di halaman detail penilaian
- Klik tombol **Export PDF**
- File PDF akan otomatis terunduh

#### 3. Print

- Di halaman detail penilaian
- Klik tombol **Print**
- Pilih printer atau Save as PDF

## 📊 8 Standar Nasional Pendidikan

1. **SNP-01**: Standar Kompetensi Lulusan
2. **SNP-02**: Standar Isi
3. **SNP-03**: Standar Proses
4. **SNP-04**: Standar Penilaian Pendidikan
5. **SNP-05**: Standar Pendidik dan Tenaga Kependidikan
6. **SNP-06**: Standar Sarana dan Prasarana
7. **SNP-07**: Standar Pengelolaan
8. **SNP-08**: Standar Pembiayaan

## 📈 Kategori Nilai

| Nilai    | Kategori | Keterangan |
| -------- | -------- | ---------- |
| 91 - 100 | A        | Amat Baik  |
| 86 - 90  | B        | Baik       |
| 71 - 85  | C        | Cukup      |
| 55 - 70  | D        | Sedang     |
| < 55     | E        | Kurang     |

## 📁 Struktur Folder

```
snp-app/
├── config/
│   ├── config.php              # Konfigurasi aplikasi
│   └── database_snp.sql        # File database
├── includes/
│   ├── auth.php                # Middleware autentikasi
│   ├── header.php              # Template header
│   └── footer.php              # Template footer
├── modules/
│   ├── master-sekolah.php      # CRUD Master Sekolah
│   ├── master-pengawas.php     # CRUD Master Pengawas
│   ├── master-pertanyaan.php   # CRUD Master Pertanyaan
│   ├── penilaian-add.php       # Form tambah penilaian
│   ├── penilaian-form.php      # Form detail penilaian (skor)
│   ├── penilaian-list.php      # Daftar penilaian
│   ├── penilaian-detail.php    # Detail & rekapitulasi
│   └── laporan-pdf.php         # Export PDF
├── login.php                   # Halaman login
├── logout.php                  # Proses logout
├── dashboard.php               # Dashboard utama
└── composer.json               # Dependencies
```

## 🔧 Troubleshooting

### Error: "Database connection failed"

**Solusi:**

- Cek apakah MySQL sudah berjalan di Laragon
- Periksa username/password di `config/config.php`
- Pastikan database `monitoring_snp_app` sudah dibuat

### Error: "Page not found" atau CSS tidak muncul

**Solusi:**

- Periksa `BASE_URL` di `config/config.php`
- Sesuaikan dengan struktur folder Anda

### Halaman login redirect terus

**Solusi:**

- Pastikan session PHP sudah aktif
- Cek apakah ada error di PHP error log
- Clear cookies browser

### PDF tidak bisa export

**Solusi:**

- Aplikasi menggunakan HTML to PDF fallback secara default
- Untuk hasil lebih baik, install Composer dan jalankan `composer install`
- Atau gunakan tombol **Print** lalu Save as PDF

## 🎯 Fitur Tambahan (Development)

- [ ] Multi-SNP penilaian dalam 1 transaksi
- [ ] Upload dokumen pendukung
- [ ] Grafik perbandingan antar sekolah
- [ ] Notifikasi email
- [ ] Export Excel
- [ ] API REST untuk mobile app

## 📞 Support

Jika ada pertanyaan atau kendala, silakan hubungi:

- Email: support@snp-app.com
- WhatsApp: +62xxx-xxxx-xxxx

## 📄 Lisensi

MIT License - Free to use and modify

---

**© 2026 Aplikasi Monitoring SNP v1.0.0**

Dibuat dengan ❤️ menggunakan PHP, MySQL, dan Bootstrap 5
