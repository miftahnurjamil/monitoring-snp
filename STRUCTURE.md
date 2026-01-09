# 📂 STRUKTUR APLIKASI MONITORING SNP

## 📁 File dan Folder

```
snp-app/
│
├── 📁 config/                      # Konfigurasi
│   ├── config.php                  # Konfigurasi database & helper functions
│   └── database_snp.sql            # File SQL untuk membuat database
│
├── 📁 includes/                    # Template & Middleware
│   ├── auth.php                    # Middleware autentikasi
│   ├── header.php                  # Template header + sidebar
│   └── footer.php                  # Template footer + scripts
│
├── 📁 modules/                     # Modul Aplikasi
│   ├── master-snp.php              # CRUD 8 SNP
│   ├── master-sekolah.php          # CRUD Master Sekolah
│   ├── master-penilik.php         # CRUD Master Penilik
│   ├── master-pertanyaan.php       # CRUD Pertanyaan/Indikator SNP
│   ├── penilaian-add.php           # Form create penilaian baru
│   ├── penilaian-form.php          # Form input skor penilaian
│   ├── penilaian-list.php          # Daftar semua penilaian
│   ├── penilaian-detail.php        # Detail & rekapitulasi penilaian
│   └── laporan-pdf.php             # Export laporan ke PDF
│
├── 📁 vendor/                      # Library (Composer) - optional
│   └── tecnickcom/tcpdf/           # Library PDF
│
├── index.php                       # Redirect ke login
├── login.php                       # Halaman login
├── logout.php                      # Proses logout
├── dashboard.php                   # Dashboard utama
├── 404.php                         # Error 404 page
├── .htaccess                       # Apache configuration
├── composer.json                   # Composer dependencies
├── README.md                       # Dokumentasi lengkap
└── QUICKSTART.md                   # Panduan cepat
```

## 🗄️ Struktur Database

### Tabel Master

- `users` - User aplikasi (admin, operator, penilik)
- `master_snp` - 8 Standar Nasional Pendidikan
- `master_sekolah` - Data sekolah (NPSN, nama, kepala sekolah, dll)
- `master_penilik` - Data penilik sekolah
- `aspek_snp` - Aspek/sub indikator setiap SNP
- `pertanyaan_snp` - Pertanyaan/indikator untuk penilaian
- `sub_pertanyaan` - Sub-item dari pertanyaan

### Tabel Transaksi

- `transaksi_penilaian` - Header penilaian (sekolah, tanggal, tahun ajaran)
- `detail_penilaian` - Detail skor untuk setiap pertanyaan
- `rekapitulasi_snp` - Hasil rekapitulasi per SNP

## 🔄 Alur Kerja Aplikasi

### 1. Persiapan Data Master

```
Login → Master Sekolah → Master Penilik → Master Pertanyaan SNP
```

### 2. Input Penilaian

```
Input Penilaian Baru
  ↓
Pilih Sekolah, Penilik, Tahun Ajaran, SNP
  ↓
Form Penilaian (Isi Skor 0-4)
  ↓
Simpan → Auto Calculate → Rekapitulasi
```

### 3. Laporan

```
Data Penilaian → Detail → Export PDF / Print
```

## 🎨 Teknologi Frontend

- **Bootstrap 5** - Framework CSS
- **Bootstrap Icons** - Icon set
- **jQuery** - JavaScript library
- **DataTables** - Table sorting, searching, pagination
- **Chart.js** - Grafik dashboard

## ⚙️ Fungsi Helper (config.php)

| Fungsi                      | Deskripsi                   |
| --------------------------- | --------------------------- |
| `redirect($url)`            | Redirect ke halaman lain    |
| `isLoggedIn()`              | Cek user sudah login        |
| `hasRole($role)`            | Cek role user               |
| `formatTanggal($tanggal)`   | Format tanggal Indonesia    |
| `generateKode($prefix)`     | Generate kode unik          |
| `getKategoriNilai($nilai)`  | Hitung kategori nilai (A-E) |
| `setFlash($type, $message)` | Set flash message           |
| `getFlash()`                | Get flash message           |
| `cleanInput($data)`         | Sanitize input              |
| `post($key, $default)`      | Get POST data               |
| `get($key, $default)`       | Get GET data                |

## 🔐 Level Akses User

### Admin

✅ Semua fitur
✅ Manajemen user
✅ CRUD semua master
✅ Input & lihat penilaian
✅ Export laporan

### Operator

✅ CRUD master (kecuali user)
✅ Input & lihat penilaian
✅ Export laporan

### Penilik (untuk development)

👁️ Lihat penilaian saja
📄 Export laporan saja

## 📊 Perhitungan Nilai

### Formula

```
Total Skor Perolehan = Σ skor semua pertanyaan
Total Skor Maksimal = Jumlah pertanyaan × 4

Nilai = (Total Skor Perolehan / Total Skor Maksimal) × 100

Kategori:
- 91-100 = A (Amat Baik)
- 86-90  = B (Baik)
- 71-85  = C (Cukup)
- 55-70  = D (Sedang)
- < 55   = E (Kurang)
```

### Contoh

```
Jumlah pertanyaan: 10
Skor perolehan: 35
Skor maksimal: 10 × 4 = 40

Nilai = (35 / 40) × 100 = 87.50
Kategori = B (Baik)
```

## 🎯 Fitur Utama Per Modul

### Dashboard

- Statistik total sekolah, penilik, penilaian
- Grafik penilaian 6 bulan terakhir
- List 8 SNP
- Penilaian terbaru

### Master Sekolah

- CRUD sekolah
- Data: NPSN, nama, alamat, kepala sekolah, NIP
- DataTables untuk pencarian & sorting

### Master Penilik

- CRUD penilik
- Data: NIP, nama, pangkat, jabatan, wilayah binaan

### Master Pertanyaan

- Pilih SNP
- Tambah aspek/indikator
- Tambah pertanyaan dengan sub-item
- Support jenis: skor (0-4), checkbox, ya/tidak

### Penilaian

- Form wizard style
- Skor visual selector (0-4)
- Auto calculate nilai & kategori
- Support sub-pertanyaan
- Rekapitulasi per SNP

### Laporan

- View detail penilaian
- Tabel rekapitulasi per SNP
- Export PDF (TCPDF atau HTML fallback)
- Print friendly

## 🔗 URL Routes

```
/                           → Redirect to login
/login.php                  → Login page
/dashboard.php              → Dashboard
/modules/master-snp.php     → Master SNP
/modules/master-sekolah.php → Master Sekolah
/modules/master-penilik.php → Master Penilik
/modules/master-pertanyaan.php?snp=1 → Master Pertanyaan
/modules/penilaian-add.php  → Form create penilaian
/modules/penilaian-form.php?id=1&snp=1 → Form input skor
/modules/penilaian-list.php → Daftar penilaian
/modules/penilaian-detail.php?kode=PNL-xxx → Detail penilaian
/modules/laporan-pdf.php?kode=PNL-xxx → Export PDF
/logout.php                 → Logout
```

## 📝 Catatan Penting

1. **BASE_URL** harus disesuaikan di `config/config.php`
2. Database otomatis dibuat saat import `database_snp.sql`
3. Data contoh sudah tersedia (2 sekolah, 2 penilik, 8 SNP)
4. Password user di-hash dengan MD5 (untuk production gunakan password_hash)
5. Session timeout: 1 jam (bisa diubah di config)
6. Upload max: 5MB (bisa diubah di config)

## 🚀 Tips Development

- Gunakan **Chrome DevTools** untuk debugging
- Cek **PHP error log** di Laragon jika ada error
- Backup database sebelum modifikasi besar
- Test di browser berbeda (Chrome, Firefox, Edge)
- Gunakan **Composer** untuk library tambahan

---

**📧 Need Help?** Baca [README.md](README.md) atau [QUICKSTART.md](QUICKSTART.md)
