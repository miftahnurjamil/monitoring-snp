-- Jalankan script ini untuk menambahkan kolom skor_maksimal ke tabel sub_pertanyaan

ALTER TABLE sub_pertanyaan 
ADD COLUMN skor_maksimal INT DEFAULT 4 AFTER sub_pertanyaan;

UPDATE sub_pertanyaan SET skor_maksimal = 4 WHERE skor_maksimal IS NULL;
