-- ============================================
-- UPDATE DATABASE: TAMBAH FIELD FOTO_PROFIL
-- Jalankan query ini untuk menambahkan field foto_profil
-- ============================================

-- Tambah field foto_profil di tabel mahasiswa
ALTER TABLE `mahasiswa` 
ADD COLUMN `foto_profil` varchar(255) NULL DEFAULT NULL AFTER `password`;

-- Tambah field foto_profil di tabel dosen
ALTER TABLE `dosen` 
ADD COLUMN `foto_profil` varchar(255) NULL DEFAULT NULL AFTER `password`;

-- ============================================
-- CATATAN:
-- Field foto_profil akan menyimpan path relatif ke file foto
-- Contoh: 'uploads/profil/mahasiswa_714_1234567890.jpg'
-- ============================================
