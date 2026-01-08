-- =====================================================
-- MIGRATION: Fix Struktur Aspek-Indikator-Sub Indikator
-- Date: 2026-01-08
-- =====================================================

-- 1. Tambahkan kolom bobot/skor ke sub_pertanyaan
ALTER TABLE sub_pertanyaan 
ADD COLUMN bobot INT DEFAULT 1 AFTER sub_pertanyaan,
ADD COLUMN skor_maksimal INT DEFAULT 4 AFTER bobot;

-- 2. Update existing data - set default skor maksimal = 4
UPDATE sub_pertanyaan SET skor_maksimal = 4 WHERE skor_maksimal IS NULL;
UPDATE sub_pertanyaan SET bobot = 1 WHERE bobot IS NULL;

-- 3. Tambah index untuk performa
ALTER TABLE sub_pertanyaan ADD INDEX idx_pertanyaan_id (pertanyaan_id);
ALTER TABLE pertanyaan_snp ADD INDEX idx_aspek_id (aspek_id);
ALTER TABLE aspek_snp ADD INDEX idx_snp_id (snp_id);

-- =====================================================
-- CATATAN STRUKTUR YANG BENAR:
-- =====================================================
-- master_snp (8 SNP)
--   └── aspek_snp (ASPEK - level 1)
--        └── pertanyaan_snp (INDIKATOR - level 2) 
--             └── sub_pertanyaan (SUB INDIKATOR - level 3, punya skor)
-- =====================================================
