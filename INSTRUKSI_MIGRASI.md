# INSTRUKSI PERBAIKAN STRUKTUR DATABASE

## 🔧 Langkah-Langkah Migrasi

### 1. **Jalankan SQL Update** (WAJIB!)

Buka phpMyAdmin atau tool database Anda, pilih database `monitoring_snp_app`, lalu jalankan SQL berikut:

```sql
-- Tambahkan kolom skor_maksimal ke tabel sub_pertanyaan
ALTER TABLE sub_pertanyaan
ADD COLUMN skor_maksimal INT DEFAULT 4 AFTER sub_pertanyaan;

-- Set default nilai untuk data yang sudah ada
UPDATE sub_pertanyaan SET skor_maksimal = 4 WHERE skor_maksimal IS NULL;

-- Tambah index untuk performa
ALTER TABLE sub_pertanyaan ADD INDEX idx_pertanyaan_id (pertanyaan_id);
ALTER TABLE pertanyaan_snp ADD INDEX idx_aspek_id (aspek_id);
ALTER TABLE aspek_snp ADD INDEX idx_snp_id (snp_id);
```

### 2. **Struktur Hierarki yang Benar**

```
📊 SNP (Standar Nasional Pendidikan)
  └─ 📁 ASPEK (Level 1)
      └─ 📝 INDIKATOR (Level 2)
          └─ ✅ SUB-INDIKATOR (Level 3) → PUNYA SKOR!
```

**Contoh:**

- SNP-01: Standar Kompetensi Lulusan
  - Aspek 1: Dokumen Pencapaian Pertumbuhan Anak
    - Indikator 1: Dokumen rekap Pencapaian Pertumbuhan anak
      - Sub-Indikator a: Tinggi Badan Menurut Umur (Skor Max: 4)
      - Sub-Indikator b: Berat Badan Menurut Badan (Skor Max: 4)
      - Sub-Indikator c: Lingkar kepala Menurut Usia (Skor Max: 4)

### 3. **File yang Telah Dibuat/Diupdate**

✅ **File Baru:**

- `modules/sub-indikator.php` - Halaman kelola sub-indikator dengan skor
- `config/update_add_skor.sql` - Script SQL untuk update database
- `config/migration_fix_struktur.sql` - Script migrasi lengkap

✅ **File Diupdate:**

- `modules/master-pertanyaan.php` - Update terminologi dan link ke sub-indikator
- `modules/penilaian-detail.php` - Sudah menggunakan skor dari sub-indikator

### 4. **Cara Menggunakan Sistem yang Benar**

1. **Pilih SNP** (misalnya: SNP-01)
2. **Tambah Aspek** (misalnya: Dokumen Pencapaian)
3. **Tambah Indikator** untuk aspek tersebut
4. **Klik "Kelola Sub-Indikator"** pada indikator
5. **Tambah Sub-Indikator** dengan skor maksimal masing-masing (biasanya 0-4)
6. Saat penilaian, **setiap sub-indikator dinilai** dan diberi skor

### 5. **Perbedaan Sebelum & Sesudah**

| Sebelum                                          | Sesudah                                   |
| ------------------------------------------------ | ----------------------------------------- |
| ❌ Aspek = "Aspek/Sub Indikator" (membingungkan) | ✅ Aspek = Level 1                        |
| ❌ Pertanyaan = skor ada di sini                 | ✅ Indikator = Level 2 (tidak punya skor) |
| ❌ Sub-pertanyaan = tidak punya skor             | ✅ Sub-Indikator = Level 3 (PUNYA SKOR!)  |

### 6. **Update Logika Penilaian**

Sistem sudah otomatis mengambil skor dari `sub_pertanyaan.skor_maksimal` saat:

- Menampilkan detail penilaian
- Menghitung total skor per SNP
- Generate laporan PDF

## ⚠️ PENTING!

Setelah menjalankan SQL update, Anda perlu:

1. ✅ Tambahkan sub-indikator untuk setiap indikator yang ada
2. ✅ Set skor maksimal untuk setiap sub-indikator (default: 4)
3. ✅ Urutkan sub-indikator sesuai kebutuhan

## 📝 Catatan

- Skor biasanya 0-4, tapi bisa disesuaikan per sub-indikator
- Total skor SNP = jumlah skor semua sub-indikator
- Setiap indikator HARUS punya minimal 1 sub-indikator
