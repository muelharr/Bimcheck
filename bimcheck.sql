-- ============================================
-- DATABASE BIMCHECK - SQL DUMP LENGKAP
-- File ini siap untuk diimport ke MySQL/MariaDB
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bimcheck`
-- Buat database jika belum ada
--
CREATE DATABASE IF NOT EXISTS `bimcheck` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bimcheck`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id_mahasiswa` int NOT NULL AUTO_INCREMENT,
  `npm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mahasiswa`),
  UNIQUE KEY `npm` (`npm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--
CREATE TABLE IF NOT EXISTS `dosen` (
  `id_dosen` int NOT NULL AUTO_INCREMENT,
  `kode_dosen` varchar(20) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) NULL DEFAULT NULL,
  `keahlian` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dosen`),
  UNIQUE KEY `kode_dosen` (`kode_dosen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--
CREATE TABLE IF NOT EXISTS `antrian` (
  `id_antrian` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `id_dosen` int NOT NULL,
  `nomor_antrian` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_panggil` datetime DEFAULT NULL,
  `waktu_kehadiran` datetime DEFAULT NULL,
  `topik` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `waktu_booking` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('menunggu','dipanggil','proses','selesai','revisi','dilewati') DEFAULT 'menunggu',
  PRIMARY KEY (`id_antrian`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  KEY `id_dosen` (`id_dosen`),
  KEY `idx_tanggal` (`tanggal`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_status` (`tanggal`, `status`),
  KEY `idx_id_dosen_tanggal` (`id_dosen`, `tanggal`),
  KEY `idx_id_mahasiswa_tanggal` (`id_mahasiswa`, `tanggal`),
  CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  CONSTRAINT `antrian_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_bimbingan`
--
CREATE TABLE IF NOT EXISTS `riwayat_bimbingan` (
  `id_riwayat` int NOT NULL AUTO_INCREMENT,
  `id_antrian` int NOT NULL,
  `catatan` text,
  `tanggal_selesai` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_riwayat`),
  KEY `id_antrian` (`id_antrian`),
  CONSTRAINT `riwayat_bimbingan_ibfk_1` FOREIGN KEY (`id_antrian`) REFERENCES `antrian` (`id_antrian`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Dumping data untuk table `users`
-- Password default: admin (sudah di-hash)
--
INSERT INTO `users` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'admin', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', 'admin');

-- --------------------------------------------------------

--
-- Dumping data untuk table `mahasiswa`
-- Password default: 123456 (sudah di-hash)
--
INSERT INTO `mahasiswa` (`id_mahasiswa`, `npm`, `nama`, `email`, `prodi`, `password`, `created_at`) VALUES
(1, '714', 'Mahasiswa Tester', 'test@mhs.id', 'Teknik Informatika', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', NOW()),
(2, '715', 'Mahasiswa Sample', 'sample@mhs.id', 'Sistem Informasi', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', NOW());

-- --------------------------------------------------------

--
-- Dumping data untuk table `dosen`
-- Password default: 123456 (sudah di-hash)
--
INSERT INTO `dosen` (`id_dosen`, `kode_dosen`, `nama_dosen`, `email`, `password`, `keahlian`, `created_at`) VALUES
(1, 'DSN01', 'Dr. Dosen Tester', 'dosen@test.id', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', 'Teknik Informatika', NOW()),
(2, 'DSN02', 'Prof. Dosen Sample', 'dosen2@test.id', '$2y$10$44Rme0qv30vnjETvQ9RDre4/Zw5lHQ27/HekY5jtFbWAWRrXppoYe', 'Sistem Informasi', NOW());

-- --------------------------------------------------------

--
-- Dumping data untuk table `antrian` (sample data)
--
INSERT INTO `antrian` (`id_antrian`, `id_mahasiswa`, `id_dosen`, `nomor_antrian`, `tanggal`, `waktu_mulai`, `waktu_panggil`, `waktu_kehadiran`, `topik`, `deskripsi`, `waktu_booking`, `status`) VALUES
(1, 1, 1, 1, CURDATE(), '09:00:00', NULL, NULL, 'Bimbingan Proposal', 'Konsultasi proposal skripsi', NOW(), 'menunggu'),
(2, 2, 1, 2, CURDATE(), '10:00:00', NULL, NULL, 'Revisi BAB 1', 'Perbaikan latar belakang', NOW(), 'menunggu');

-- --------------------------------------------------------

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================
-- CATATAN IMPORT:
-- 1. File ini sudah include CREATE DATABASE
-- 2. Index sudah ditambahkan untuk optimasi
-- 3. Foreign key constraints sudah aktif
-- 4. Data sample sudah disertakan
-- 
-- CREDENTIAL DEFAULT:
-- Admin: username='admin', password='123456'
-- Mahasiswa: npm='714', password='123456'
-- Dosen: kode_dosen='DSN01', password='123456'
-- ============================================
