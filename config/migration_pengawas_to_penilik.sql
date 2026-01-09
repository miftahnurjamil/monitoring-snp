-- =====================================================
-- MIGRASI: PENGAWAS -> PENILIK
-- File: migration_pengawas_to_penilik.sql
-- Deskripsi: Mengubah seluruh referensi pengawas menjadi penilik
-- =====================================================

-- Backup data terlebih dahulu (optional, untuk keamanan)
-- CREATE TABLE backup_master_pengawas AS SELECT * FROM master_pengawas;
-- CREATE TABLE backup_transaksi_penilaian AS SELECT * FROM transaksi_penilaian;
-- CREATE TABLE backup_users AS SELECT * FROM users;

-- =====================================================
-- 1. RENAME TABEL master_pengawas -> master_penilik
-- =====================================================
RENAME TABLE master_pengawas TO master_penilik;

-- =====================================================
-- 2. RENAME COLUMN pengawas_id -> penilik_id di transaksi_penilaian
-- =====================================================
-- Drop foreign key constraint dulu
ALTER TABLE transaksi_penilaian 
DROP FOREIGN KEY transaksi_penilaian_ibfk_2;

-- Rename column
ALTER TABLE transaksi_penilaian 
CHANGE COLUMN pengawas_id penilik_id INT;

-- Tambahkan kembali foreign key dengan nama constraint baru
ALTER TABLE transaksi_penilaian 
ADD CONSTRAINT fk_transaksi_penilik 
FOREIGN KEY (penilik_id) REFERENCES master_penilik(id);

-- =====================================================
-- 3. DROP dan CREATE INDEX dengan nama baru
-- =====================================================
DROP INDEX idx_pengawas_nip ON master_penilik;
CREATE INDEX idx_penilik_nip ON master_penilik(nip);

-- =====================================================
-- 4. UPDATE ROLE 'pengawas' -> 'penilik' di tabel users
-- =====================================================
-- Update data yang sudah ada
UPDATE users SET role = 'penilik' WHERE role = 'pengawas';

-- Modify ENUM untuk mengganti 'pengawas' dengan 'penilik'
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'penilik', 'operator') DEFAULT 'operator';

-- =====================================================
-- 5. UPDATE JABATAN di master_penilik (contoh data)
-- =====================================================
UPDATE master_penilik 
SET jabatan = REPLACE(jabatan, 'Pengawas', 'Penilik') 
WHERE jabatan LIKE '%Pengawas%';

-- =====================================================
-- 6. DROP dan CREATE VIEW dengan nama kolom baru
-- =====================================================
DROP VIEW IF EXISTS laporan_lengkap;

CREATE VIEW laporan_lengkap AS
SELECT 
    t.id,
    t.kode_penilaian,
    s.nama_sekolah,
    s.nama_kepala_sekolah,
    s.nip_kepala_sekolah,
    p.nama_lengkap as nama_penilik,
    t.tahun_ajaran,
    t.semester,
    t.tanggal_penilaian,
    t.status,
    snp.nama_snp,
    r.total_skor_perolehan,
    r.total_skor_maksimal,
    r.nilai,
    r.kategori
FROM transaksi_penilaian t
LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
LEFT JOIN master_penilik p ON t.penilik_id = p.id
LEFT JOIN rekapitulasi_snp r ON t.id = r.transaksi_id
LEFT JOIN master_snp snp ON r.snp_id = snp.id;

-- =====================================================
-- SELESAI MIGRASI
-- =====================================================
-- Verifikasi hasil:
-- SELECT * FROM master_penilik LIMIT 5;
-- SELECT * FROM transaksi_penilaian LIMIT 5;
-- SELECT * FROM users WHERE role = 'penilik';
-- SHOW CREATE TABLE transaksi_penilaian;
-- SHOW CREATE TABLE users;
-- =====================================================
