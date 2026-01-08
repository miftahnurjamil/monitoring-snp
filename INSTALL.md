# 🎯 PANDUAN INSTALASI & CARA MENJALANKAN

## ✅ Aplikasi Monitoring Standar Nasional Pendidikan (SNP)

---

## 📋 PERSIAPAN

### Yang Anda Butuhkan:

- ✅ **Laragon** (sudah terinstall)
- ✅ **Web Browser** (Chrome, Firefox, Edge)
- ✅ **10 Menit Waktu**

---

## 🚀 LANGKAH 1: START LARAGON

1. **Buka Laragon**
2. Klik tombol **"Start All"**
3. Pastikan muncul:
   - ✅ Apache: Running
   - ✅ MySQL: Running

---

## 🗄️ LANGKAH 2: IMPORT DATABASE

### Opsi A: Via phpMyAdmin (Recommended)

1. **Buka browser**, akses:

   ```
   http://localhost/phpmyadmin
   ```

2. **Klik tab "SQL"** (di menu atas)

3. **Klik "Choose File"**

4. **Pilih file:**

   ```
   d:\Project\laragon\www\monitoring-snp\sample\snp-app\config\database_snp.sql
   ```

5. **Klik "Go"**

6. **Tunggu sampai muncul:**
   ```
   ✅ Import has been successfully finished
   ```

### Opsi B: Via Command Line

1. Buka **Terminal/CMD**

2. Jalankan:

   ```bash
   cd d:\Project\laragon\www\monitoring-snp\sample\snp-app
   mysql -u root -p < config/database_snp.sql
   ```

3. Tekan **Enter** (password kosong)

---

## ⚙️ LANGKAH 3: KONFIGURASI (Optional)

File konfigurasi: `config/config.php`

### Jika Database Setting Berbeda:

```php
define('DB_HOST', 'localhost');      // ← Ubah jika perlu
define('DB_USER', 'root');           // ← Ubah jika perlu
define('DB_PASS', '');               // ← Ubah jika ada password
define('DB_NAME', 'monitoring_snp_app');
```

### Jika BASE_URL Berbeda:

```php
// Sesuaikan dengan folder Anda
define('BASE_URL', 'http://localhost/monitoring-snp/');
```

---

## 🌐 LANGKAH 4: AKSES APLIKASI

### Buka Browser:

```
http://localhost/monitoring-snp/
```

### Akan redirect otomatis ke halaman Login

---

## 🔐 LANGKAH 5: LOGIN

### Akun Admin:

```
Username: admin
Password: admin123
```

### Akun Operator:

```
Username: operator
Password: operator123
```

### Klik tombol **"Login"**

---

## 🎉 BERHASIL!

Anda akan masuk ke **Dashboard** dengan tampilan:

- 📊 **Statistik** (Total Sekolah, Pengawas, Penilaian)
- 📈 **Grafik** Penilaian
- 📋 **8 SNP** (Standar Nasional Pendidikan)
- 🕒 **Penilaian Terbaru**

---

## 📖 LANGKAH SELANJUTNYA: MENGGUNAKAN APLIKASI

### 1️⃣ Tambah Data Sekolah

**Menu:** Master Sekolah

1. Klik sidebar **"Master Sekolah"**
2. Isi form di sebelah kiri:
   - NPSN: `69962561`
   - Nama Sekolah: `PAUD/TK Permata Bunda`
   - Alamat: `Jl. Otista No. 12 Tasikmalaya`
   - Kepala Sekolah: `Mimin Suminar, S.Pd.`
   - NIP: `19820408 202002 2 001`
3. Klik **"Simpan"**

✅ **Data sudah tersimpan!** (akan muncul di tabel sebelah kanan)

---

### 2️⃣ Tambah Data Pengawas (Optional)

**Menu:** Master Pengawas

1. Klik sidebar **"Master Pengawas"**
2. Isi form:
   - NIP: `197506152006042012`
   - Nama: `Dr. Hj. Nengsih, M.Pd.`
   - Pangkat: `Pembina Tk.I / IV-b`
   - Jabatan: `Pengawas TK/PAUD`
   - Wilayah Binaan: `Kecamatan Tasikmalaya`
3. Klik **"Simpan"**

---

### 3️⃣ Tambah Pertanyaan SNP

**Menu:** Master Pertanyaan

1. Klik sidebar **"Master Pertanyaan"**
2. **Pilih SNP** (dropdown): `SNP-01 - Standar Kompetensi Lulusan`
3. **Tambah Aspek Baru:**
   - Kode Aspek: `1`
   - Nama Aspek: `Dokumen Pencapaian Pertumbuhan Anak`
   - Urutan: `1`
   - Klik **"Tambah Aspek"**
4. **Tambah Pertanyaan:**
   - Aspek: Pilih yang baru dibuat
   - No. Pertanyaan: `1`
   - Pertanyaan: `Dokumen rekap Pencapaian Pertumbuhan anak`
   - Jenis Jawaban: `Checkbox (Sub Pertanyaan)`
   - Klik **"Tambah Pertanyaan"**

📌 **Database sudah ada contoh pertanyaan!**

---

### 4️⃣ Input Penilaian

**Menu:** Input Penilaian Baru

1. Klik sidebar **"Input Penilaian Baru"**
2. Isi form:
   - **Sekolah:** Pilih sekolah
   - **Pengawas:** Pilih pengawas (optional)
   - **Tahun Ajaran:** `2025/2026`
   - **Semester:** `1`
   - **Tanggal:** `2026-01-08` (hari ini)
   - **Pilih SNP:** `SNP-01 - Standar Kompetensi Lulusan`
3. Klik **"Lanjut ke Form Penilaian"**

---

### 5️⃣ Isi Skor Penilaian

Anda akan melihat form penilaian dengan:

- **Data Sekolah** (header)
- **Tabel Indikator** dengan opsi skor **0 - 4**

**Pilih Skor:**

- **0** = Tidak ada/Tidak dilaksanakan
- **1** = Kurang
- **2** = Cukup
- **3** = Baik
- **4** = Sangat Baik

Klik tombol skor untuk setiap indikator, lalu klik **"Simpan Penilaian"**

✅ **Sistem otomatis menghitung:**

- Total Skor Perolehan
- Total Skor Maksimal
- Nilai (0-100)
- Kategori (A, B, C, D, E)

---

### 6️⃣ Lihat Laporan

**Menu:** Data Penilaian

1. Klik sidebar **"Data Penilaian"**
2. Klik tombol **👁️ Lihat** pada data
3. Lihat **Rekapitulasi Penilaian**
4. Klik **"Export PDF"** untuk download laporan
5. Atau klik **"Print"** untuk print

---

## 🎯 CONTOH DATA

Database sudah berisi:

### ✅ 2 Sekolah:

1. PAUD/TK Permata Bunda
2. TK Harapan Bangsa

### ✅ 2 Pengawas:

1. Dr. Hj. Nengsih, M.Pd.
2. Dra. Euis Susilawati

### ✅ 8 SNP:

1. SNP-01: Standar Kompetensi Lulusan
2. SNP-02: Standar Isi
3. SNP-03: Standar Proses
4. SNP-04: Standar Penilaian Pendidikan
5. SNP-05: Standar Pendidik dan Tenaga Kependidikan
6. SNP-06: Standar Sarana dan Prasarana
7. SNP-07: Standar Pengelolaan
8. SNP-08: Standar Pembiayaan

---

## ❓ TROUBLESHOOTING

### ❌ Problem: "Database connection failed"

**Solusi:**

1. Pastikan MySQL di Laragon sudah **Running**
2. Cek file `config/config.php`:
   ```php
   define('DB_USER', 'root');  // Pastikan 'root'
   define('DB_PASS', '');      // Pastikan kosong
   ```

---

### ❌ Problem: "Page Not Found" / CSS tidak muncul

**Solusi:**

1. Edit file `config/config.php`
2. Sesuaikan `BASE_URL`:
   ```php
   // Sesuaikan dengan folder Anda
   define('BASE_URL', 'http://localhost/monitoring-snp/');
   ```

---

### ❌ Problem: Login redirect terus

**Solusi:**

1. Clear cookies browser (Ctrl + Shift + Del)
2. Coba browser lain
3. Restart Laragon

---

### ❌ Problem: PDF tidak bisa export

**Solusi:**

1. Gunakan tombol **"Print"** → **Save as PDF**
2. Atau install Composer:
   ```bash
   cd d:\Project\laragon\www\monitoring-snp\sample\snp-app
   composer install
   ```

---

## 📱 AKSES CEPAT

### URL Penting:

```
Dashboard:    http://localhost/monitoring-snp/dashboard.php
Login:        http://localhost/monitoring-snp/login.php
phpMyAdmin:   http://localhost/phpmyadmin
```

---

## 📚 DOKUMENTASI

- **README.md** - Dokumentasi lengkap
- **QUICKSTART.md** - Panduan cepat
- **STRUCTURE.md** - Struktur aplikasi

---

## 🎓 VIDEO TUTORIAL (Jika ada)

Coming soon...

---

## 💡 TIPS

1. ✅ **Backup Database** rutin via phpMyAdmin (Export)
2. ✅ **Ganti Password** default setelah install
3. ✅ **Test** di browser berbeda (Chrome, Firefox)
4. ✅ **Baca** dokumentasi lengkap di README.md

---

## 📞 BANTUAN

Jika masih ada masalah:

1. **Cek PHP Error Log** di Laragon
2. **Buka Chrome DevTools** (F12) → Console
3. **Screenshot error** dan tanyakan ke developer

---

**🎉 SELAMAT MENGGUNAKAN APLIKASI MONITORING SNP!**

---

**© 2026 Aplikasi Monitoring SNP v1.0.0**

Dibuat dengan ❤️ menggunakan PHP, MySQL, Bootstrap 5
