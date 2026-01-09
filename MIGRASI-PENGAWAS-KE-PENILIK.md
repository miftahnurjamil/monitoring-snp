# MIGRASI: PENGAWAS → PENILIK

## 📋 Deskripsi

File migrasi ini mengubah seluruh referensi "Pengawas" menjadi "Penilik" di seluruh sistem, termasuk:

- Tabel database
- Field/kolom
- Enum values
- Index
- View

## ⚠️ PENTING - Backup Database Terlebih Dahulu!

Sebelum menjalankan migrasi, **WAJIB** backup database:

```sql
-- Via phpMyAdmin: Export database
-- Atau via command line:
mysqldump -u root -p database_snp > backup_sebelum_migrasi.sql
```

## 🔄 Perubahan Yang Dilakukan

### 1. **Tabel Database**

- `master_pengawas` → `master_penilik`

### 2. **Kolom/Field**

- `transaksi_penilaian.pengawas_id` → `transaksi_penilaian.penilik_id`

### 3. **Index**

- `idx_pengawas_nip` → `idx_penilik_nip`

### 4. **Foreign Key**

- `transaksi_penilaian_ibfk_2` → `fk_transaksi_penilik`

### 5. **Enum Role**

- `users.role`: `'pengawas'` → `'penilik'`

### 6. **View**

- `laporan_lengkap`: kolom `nama_pengawas` → `nama_penilik`

### 7. **Data**

- Update semua user dengan role `'pengawas'` → `'penilik'`
- Update jabatan yang berisi kata "Pengawas" → "Penilik"

## 🚀 Cara Menjalankan Migrasi

### Opsi 1: Via phpMyAdmin

1. Buka **phpMyAdmin**
2. Pilih database `database_snp`
3. Klik tab **SQL**
4. Copy-paste isi file `migration_pengawas_to_penilik.sql`
5. Klik **Go/Kirim**

### Opsi 2: Via Command Line

```bash
mysql -u root -p database_snp < config/migration_pengawas_to_penilik.sql
```

### Opsi 3: Via Laragon Terminal

```bash
cd d:\Project\laragon\www\monitoring-snp
mysql -u root database_snp < config/migration_pengawas_to_penilik.sql
```

## ✅ Verifikasi Hasil Migrasi

Setelah migrasi selesai, jalankan query berikut untuk verifikasi:

```sql
-- 1. Cek tabel master_penilik sudah ada
SHOW TABLES LIKE 'master_penilik';

-- 2. Cek struktur tabel transaksi_penilaian
SHOW COLUMNS FROM transaksi_penilaian LIKE 'penilik_id';

-- 3. Cek foreign key
SHOW CREATE TABLE transaksi_penilaian;

-- 4. Cek data penilik
SELECT * FROM master_penilik LIMIT 5;

-- 5. Cek role users
SELECT username, role FROM users WHERE role = 'penilik';

-- 6. Cek view
SELECT * FROM laporan_lengkap LIMIT 5;

-- 7. Cek index
SHOW INDEX FROM master_penilik WHERE Key_name = 'idx_penilik_nip';
```

## 🔙 Rollback (Jika Diperlukan)

Jika migrasi gagal atau ingin rollback:

```sql
-- Restore dari backup
mysql -u root -p database_snp < backup_sebelum_migrasi.sql
```

## 📝 Checklist Post-Migrasi

- [ ] ✅ Tabel `master_penilik` sudah ada
- [ ] ✅ Kolom `penilik_id` di `transaksi_penilaian` sudah ada
- [ ] ✅ Foreign key `fk_transaksi_penilik` sudah ada
- [ ] ✅ Index `idx_penilik_nip` sudah ada
- [ ] ✅ Role `'penilik'` di tabel users sudah update
- [ ] ✅ View `laporan_lengkap` menggunakan kolom `nama_penilik`
- [ ] ✅ Data jabatan sudah diupdate
- [ ] ✅ Aplikasi bisa diakses tanpa error
- [ ] ✅ Menu "Master Penilik" berfungsi normal
- [ ] ✅ Form penilaian bisa pilih penilik
- [ ] ✅ Laporan PDF menampilkan nama penilik

## 🐛 Troubleshooting

### Error: "Table 'master_pengawas' doesn't exist"

✅ **Solusi:** Tabel sudah berhasil di-rename. Lanjutkan dengan langkah berikutnya.

### Error: "Foreign key constraint fails"

✅ **Solusi:** Pastikan tidak ada data transaksi yang mereferensi id yang tidak ada di master_penilik.

### Error: "Duplicate column name"

✅ **Solusi:** Migrasi sudah pernah dijalankan sebelumnya. Skip langkah yang error.

## 📞 Dukungan

Jika mengalami masalah saat migrasi:

1. **Stop** proses migrasi
2. **Backup** database current state
3. **Restore** dari backup sebelum migrasi
4. **Kontak** developer untuk assistance

## 📅 Informasi Migrasi

- **Tanggal:** 9 Januari 2026
- **Versi:** 1.0
- **Author:** System Migration
- **Estimasi Waktu:** < 1 menit
- **Downtime:** Tidak ada (jika aplikasi tidak diakses saat migrasi)

---

**⚠️ CATATAN PENTING:**
Setelah migrasi database selesai, pastikan:

1. File PHP sudah diupdate (sudah dilakukan)
2. Cache browser di-clear
3. Session logout dan login kembali
4. Test semua fitur terkait penilik
