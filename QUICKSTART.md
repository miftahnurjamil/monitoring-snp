# 🚀 Quick Start - Aplikasi Monitoring SNP

## Instalasi 5 Menit ⏱️

### 1️⃣ Start Laragon

- Jalankan Laragon
- Start **Apache** dan **MySQL**

### 2️⃣ Import Database

Buka browser: http://localhost/phpmyadmin

```sql
-- Jalankan file: config/database_snp.sql
-- Database monitoring_snp_app akan otomatis dibuat
```

### 3️⃣ Akses Aplikasi

```
http://localhost/monitoring-snp/
```

### 4️⃣ Login

```
Username: admin
Password: admin123
```

---

## ✅ Tutorial Singkat

### Langkah 1: Tambah Data Sekolah

1. Menu **Master Sekolah**
2. Isi form: Nama, NPSN, Alamat, Kepala Sekolah, NIP
3. Klik **Simpan**

### Langkah 2: Tambah Data Pengawas (Optional)

1. Menu **Master Pengawas**
2. Isi form: NIP, Nama, Pangkat, Jabatan
3. Klik **Simpan**

### Langkah 3: Tambah Pertanyaan SNP

1. Menu **Master Pertanyaan**
2. Pilih SNP (contoh: SNP-01)
3. Tambah **Aspek** (contoh: "1", "Dokumen Pencapaian Anak")
4. Tambah **Pertanyaan** untuk aspek tersebut
5. Klik **Simpan**

### Langkah 4: Input Penilaian

1. Menu **Input Penilaian Baru**
2. Pilih Sekolah
3. Isi Tahun Ajaran: 2025/2026
4. Pilih SNP yang akan dinilai
5. Klik **Lanjut**
6. Isi skor 0-4 untuk setiap indikator
7. Klik **Simpan Penilaian**

### Langkah 5: Lihat Laporan

1. Menu **Data Penilaian**
2. Klik **Lihat** pada data
3. Klik **Export PDF** untuk download laporan

---

## 📊 Contoh Skor

| Skor | Keterangan                   |
| ---- | ---------------------------- |
| 0    | Tidak ada/Tidak dilaksanakan |
| 1    | Kurang (< 25%)               |
| 2    | Cukup (25-50%)               |
| 3    | Baik (51-75%)                |
| 4    | Sangat Baik (76-100%)        |

---

## 🎯 Hasil Penilaian

Sistem otomatis menghitung:

- **Total Skor Perolehan** (jumlah semua skor)
- **Total Skor Maksimal** (jumlah pertanyaan × 4)
- **Nilai** = (Perolehan / Maksimal) × 100
- **Kategori**:
  - 91-100 = A (Amat Baik)
  - 86-90 = B (Baik)
  - 71-85 = C (Cukup)
  - 55-70 = D (Sedang)
  - < 55 = E (Kurang)

---

## 🔧 Troubleshooting Cepat

**❌ Error: Database tidak terkoneksi**

- Cek MySQL di Laragon sudah berjalan
- Periksa `config/config.php` → DB_USER, DB_PASS

**❌ CSS tidak muncul**

- Edit `config/config.php` → sesuaikan `BASE_URL`

**❌ Redirect terus di login**

- Clear cookies browser
- Cek PHP error log

---

## 📱 Akses URL

```
Dashboard    : http://localhost/monitoring-snp/dashboard.php
Master Data  : http://localhost/monitoring-snp/modules/
Penilaian    : http://localhost/monitoring-snp/modules/penilaian-add.php
```

---

## 🎓 Tips

1. **Isi Master Data Dulu**: Sekolah → Pengawas → Pertanyaan SNP
2. **Gunakan Data Contoh**: Database sudah ada contoh data
3. **Export PDF**: Gunakan tombol Print → Save as PDF jika tidak ada Composer
4. **Backup Database**: Export via phpMyAdmin secara berkala

---

**🎉 Selamat Menggunakan Aplikasi Monitoring SNP!**

Butuh bantuan? Baca [README.md](README.md) untuk dokumentasi lengkap.
