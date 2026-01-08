-- =====================================================
-- DATABASE MONITORING STANDAR NASIONAL PENDIDIKAN (SNP)
-- =====================================================

-- Hapus database jika sudah ada
DROP DATABASE IF EXISTS monitoring_snp_app;

-- Buat database baru
CREATE DATABASE monitoring_snp_app;
USE monitoring_snp_app;

-- =====================================================
-- TABEL MASTER USERS (Pengguna Aplikasi)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'pengawas', 'operator') DEFAULT 'operator',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL MASTER SNP (8 Standar Nasional Pendidikan)
-- =====================================================
CREATE TABLE master_snp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_snp VARCHAR(10) UNIQUE NOT NULL,
    nama_snp VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    urutan INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL MASTER SEKOLAH
-- =====================================================
CREATE TABLE master_sekolah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    npsn VARCHAR(20) UNIQUE,
    nama_sekolah VARCHAR(200) NOT NULL,
    jenis_sekolah ENUM('TK', 'SD', 'SMP', 'SMA', 'SMK') DEFAULT 'TK',
    alamat TEXT,
    kecamatan VARCHAR(100),
    kabupaten VARCHAR(100),
    provinsi VARCHAR(100),
    kode_pos VARCHAR(10),
    telepon VARCHAR(20),
    email VARCHAR(100),
    nama_kepala_sekolah VARCHAR(100),
    nip_kepala_sekolah VARCHAR(25),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL MASTER PENGAWAS
-- =====================================================
CREATE TABLE master_pengawas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(25) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    pangkat_golongan VARCHAR(50),
    jabatan VARCHAR(100),
    wilayah_binaan VARCHAR(200),
    telepon VARCHAR(20),
    email VARCHAR(100),
    alamat TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL ASPEK/SUB INDIKATOR SNP
-- =====================================================
CREATE TABLE aspek_snp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    snp_id INT NOT NULL,
    kode_aspek VARCHAR(10) NOT NULL,
    nama_aspek VARCHAR(200) NOT NULL,
    urutan INT,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (snp_id) REFERENCES master_snp(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL PERTANYAAN/INDIKATOR SNP
-- =====================================================
CREATE TABLE pertanyaan_snp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    aspek_id INT NOT NULL,
    snp_id INT NOT NULL,
    nomor_pertanyaan VARCHAR(10),
    pertanyaan TEXT NOT NULL,
    jenis_jawaban ENUM('skor', 'checkbox', 'ya_tidak') DEFAULT 'skor',
    bobot INT DEFAULT 1,
    urutan INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aspek_id) REFERENCES aspek_snp(id) ON DELETE CASCADE,
    FOREIGN KEY (snp_id) REFERENCES master_snp(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL SUB PERTANYAAN (untuk pertanyaan yang memiliki sub-item)
-- =====================================================
CREATE TABLE sub_pertanyaan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pertanyaan_id INT NOT NULL,
    kode_sub VARCHAR(10),
    sub_pertanyaan TEXT NOT NULL,
    urutan INT,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (pertanyaan_id) REFERENCES pertanyaan_snp(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL TRANSAKSI PENILAIAN (Header)
-- =====================================================
CREATE TABLE transaksi_penilaian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_penilaian VARCHAR(50) UNIQUE NOT NULL,
    sekolah_id INT NOT NULL,
    pengawas_id INT,
    tahun_ajaran VARCHAR(20) NOT NULL,
    semester ENUM('1', '2') DEFAULT '1',
    tanggal_penilaian DATE NOT NULL,
    status ENUM('draft', 'selesai', 'terverifikasi') DEFAULT 'draft',
    catatan TEXT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sekolah_id) REFERENCES master_sekolah(id),
    FOREIGN KEY (pengawas_id) REFERENCES master_pengawas(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =====================================================
-- TABEL DETAIL PENILAIAN SNP
-- =====================================================
CREATE TABLE detail_penilaian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id INT NOT NULL,
    snp_id INT NOT NULL,
    pertanyaan_id INT NOT NULL,
    sub_pertanyaan_id INT,
    skor INT DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi_penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (snp_id) REFERENCES master_snp(id),
    FOREIGN KEY (pertanyaan_id) REFERENCES pertanyaan_snp(id),
    FOREIGN KEY (sub_pertanyaan_id) REFERENCES sub_pertanyaan(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL HASIL REKAPITULASI PER SNP
-- =====================================================
CREATE TABLE rekapitulasi_snp (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id INT NOT NULL,
    snp_id INT NOT NULL,
    total_skor_perolehan INT DEFAULT 0,
    total_skor_maksimal INT DEFAULT 0,
    nilai DECIMAL(5,2) DEFAULT 0,
    kategori VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi_penilaian(id) ON DELETE CASCADE,
    FOREIGN KEY (snp_id) REFERENCES master_snp(id)
);

-- =====================================================
-- INSERT DATA AWAL
-- =====================================================

-- Insert User Admin Default
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin', MD5('admin123'), 'Administrator', 'admin@snp.com', 'admin'),
('operator', MD5('operator123'), 'Operator SNP', 'operator@snp.com', 'operator');

-- Insert 8 Standar Nasional Pendidikan
INSERT INTO master_snp (kode_snp, nama_snp, deskripsi, urutan) VALUES
('SNP-01', 'Standar Kompetensi Lulusan', 'Kriteria mengenai kualifikasi kemampuan lulusan', 1),
('SNP-02', 'Standar Isi', 'Kriteria mengenai ruang lingkup materi dan tingkat kompetensi', 2),
('SNP-03', 'Standar Proses', 'Kriteria mengenai pelaksanaan pembelajaran', 3),
('SNP-04', 'Standar Penilaian Pendidikan', 'Kriteria mengenai mekanisme, prosedur, dan instrumen penilaian', 4),
('SNP-05', 'Standar Pendidik dan Tenaga Kependidikan', 'Kriteria mengenai pendidikan prajabatan dan kelayakan', 5),
('SNP-06', 'Standar Sarana dan Prasarana', 'Kriteria mengenai ruang belajar, tempat berolahraga, dll', 6),
('SNP-07', 'Standar Pengelolaan', 'Kriteria mengenai perencanaan, pelaksanaan, dan pengawasan', 7),
('SNP-08', 'Standar Pembiayaan', 'Kriteria mengenai komponen dan besarnya biaya operasi', 8);

-- Insert Contoh Sekolah
INSERT INTO master_sekolah (npsn, nama_sekolah, jenis_sekolah, alamat, kecamatan, nama_kepala_sekolah, nip_kepala_sekolah) VALUES
('69962561', 'PAUD/TK Permata Bunda', 'TK', 'Jl. Otista No. 12 Tasikmalaya', 'Tasikmalaya', 'Mimin Suminar, S.Pd.', '19820408 202002 2 001'),
('69962562', 'TK Harapan Bangsa', 'TK', 'Jl. Merdeka No. 45 Tasikmalaya', 'Indihiang', 'Siti Nurjanah, S.Pd.', '19850512 200801 2 003');

-- Insert Contoh Pengawas
INSERT INTO master_pengawas (nip, nama_lengkap, pangkat_golongan, jabatan, wilayah_binaan) VALUES
('197506152006042012', 'Dr. Hj. Nengsih, M.Pd.', 'Pembina Tk.I / IV-b', 'Pengawas TK/PAUD', 'Kecamatan Tasikmalaya'),
('198201102008012015', 'Dra. Euis Susilawati', 'Pembina / IV-a', 'Pengawas TK/PAUD', 'Kecamatan Indihiang');

-- Insert Contoh Aspek untuk SNP-01 (Standar Pencapaian Perkembangan Anak)
INSERT INTO aspek_snp (snp_id, kode_aspek, nama_aspek, urutan) VALUES
(1, '1', 'Dokumen Pencapaian Pertumbuhan Anak', 1),
(1, '2', 'Dokumen Pencapaian Perkembangan Anak', 2);

-- Insert Contoh Pertanyaan untuk SNP-01
INSERT INTO pertanyaan_snp (aspek_id, snp_id, nomor_pertanyaan, pertanyaan, jenis_jawaban, urutan) VALUES
(1, 1, '1', 'Dokumen rekap Pencapaian Pertumbuhan anak yang', 'checkbox', 1),
(2, 1, '2', 'Dokumen Tingkat Pencapaian Perkembangan Anak sesuai dengan kelompok usia, yang meliputi: 6 aspek perkembangan yaitu 1. Nilai Agama dan Moral. 2. Fisik Motorik. 3. Kognitif. 4. Bahasa. 5. Sosial Emosional. 6. Seni', 'skor', 2),
(2, 1, '3', 'Dokumen Pencapaian Perkembangan dalam 4 bentuk rekaman yang berbentuk catatan, foto/video', 'checkbox', 3);

-- Insert Sub Pertanyaan untuk pertanyaan nomor 1
INSERT INTO sub_pertanyaan (pertanyaan_id, kode_sub, sub_pertanyaan, urutan) VALUES
(1, 'a', 'Berat Badan Menurut Umur', 1),
(1, 'b', 'Tinggi Badan Menurut Umur', 2),
(1, 'c', 'Berat Badan Menurut Tinggi Badan', 3),
(1, 'd', 'Lingkar kepala Menurut Usia dan Jenis Kelamin', 4);

-- Insert Sub Pertanyaan untuk pertanyaan nomor 3
INSERT INTO sub_pertanyaan (pertanyaan_id, kode_sub, sub_pertanyaan, urutan) VALUES
(3, 'a', 'Harian', 1),
(3, 'b', 'Mingguan', 2),
(3, 'c', 'Bulanan', 3),
(3, 'd', 'Semesteran', 4);

-- =====================================================
-- INDEXES untuk Performa
-- =====================================================
CREATE INDEX idx_sekolah_npsn ON master_sekolah(npsn);
CREATE INDEX idx_pengawas_nip ON master_pengawas(nip);
CREATE INDEX idx_transaksi_kode ON transaksi_penilaian(kode_penilaian);
CREATE INDEX idx_transaksi_sekolah ON transaksi_penilaian(sekolah_id);
CREATE INDEX idx_detail_transaksi ON detail_penilaian(transaksi_id);
CREATE INDEX idx_detail_snp ON detail_penilaian(snp_id);

-- =====================================================
-- VIEWS untuk Laporan
-- =====================================================
CREATE VIEW v_rekapitulasi_penilaian AS
SELECT 
    t.id as transaksi_id,
    t.kode_penilaian,
    s.nama_sekolah,
    s.nama_kepala_sekolah,
    s.nip_kepala_sekolah,
    p.nama_lengkap as nama_pengawas,
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
LEFT JOIN master_pengawas p ON t.pengawas_id = p.id
LEFT JOIN rekapitulasi_snp r ON t.id = r.transaksi_id
LEFT JOIN master_snp snp ON r.snp_id = snp.id;

-- =====================================================
-- SELESAI
-- =====================================================
