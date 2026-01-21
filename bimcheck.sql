-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 21, 2026 at 04:52 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bimcheck`
--

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_dosen` int NOT NULL,
  `nomor_antrian` int NOT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_panggil` datetime DEFAULT NULL,
  `waktu_kehadiran` datetime DEFAULT NULL,
  `topik` varchar(255) DEFAULT NULL,
  `deskripsi` text,
  `file_dokumen` varchar(255) DEFAULT NULL,
  `waktu_booking` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('menunggu','dipanggil','proses','selesai','revisi','dilewati','dibatalkan') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `antrian`
--

INSERT INTO `antrian` (`id_antrian`, `id_mahasiswa`, `id_dosen`, `nomor_antrian`, `tanggal`, `waktu_mulai`, `waktu_panggil`, `waktu_kehadiran`, `topik`, `deskripsi`, `file_dokumen`, `waktu_booking`, `status`) VALUES
(9, 2, 4, 1, '2026-01-15', '17:13:00', '2026-01-15 14:12:23', '2026-01-15 14:12:41', 'Test', 'mencoba [Catatan: revisi]', NULL, '2026-01-15 14:11:05', 'revisi'),
(10, 2, 4, 2, '2026-01-15', '05:24:00', NULL, '2026-01-15 15:54:46', 'Test2', '', NULL, '2026-01-15 14:13:34', 'selesai'),
(11, 2, 4, 3, '2026-01-15', '17:52:00', '2026-01-15 15:53:26', NULL, 'test', 'mecoba', NULL, '2026-01-15 15:52:59', 'dilewati'),
(12, 2, 4, 1, '2026-01-21', '12:38:00', '2026-01-21 14:32:45', '2026-01-21 14:34:24', 'Test', '[Feedback Dosen: wel]', NULL, '2026-01-19 12:38:35', 'selesai'),
(13, 2, 3, 1, '2026-01-19', '12:40:00', '2026-01-19 12:41:14', '2026-01-19 13:07:41', 'Test', 'wwww', NULL, '2026-01-19 12:40:58', 'proses'),
(14, 2, 4, 1, '2026-01-19', '17:52:00', '2026-01-19 17:54:40', '2026-01-19 17:57:59', 'Test', 'coba\n\n[Feedback Dosen: perbaiki bab 5]', NULL, '2026-01-19 17:52:45', 'revisi'),
(15, 2, 4, 2, '2026-01-21', '14:34:00', '2026-01-21 14:55:21', '2026-01-21 15:05:40', 'Tt', 'Yy', NULL, '2026-01-21 14:34:41', 'selesai'),
(16, 2, 4, 3, '2026-01-21', '15:06:00', '2026-01-21 15:06:23', '2026-01-21 15:06:28', 'H', '[Feedback Dosen: test]', NULL, '2026-01-21 15:06:18', 'selesai'),
(17, 2, 4, 4, '2026-01-21', '21:59:00', NULL, NULL, 'Ts', 'Ueh', NULL, '2026-01-21 21:59:21', 'menunggu'),
(18, 2, 4, 5, '2026-01-21', '23:33:00', NULL, NULL, 'Test', 'ss', NULL, '2026-01-21 23:33:43', 'menunggu');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal_status` (`tanggal`,`status`),
  ADD KEY `idx_id_dosen_tanggal` (`id_dosen`,`tanggal`),
  ADD KEY `idx_id_mahasiswa_tanggal` (`id_mahasiswa`,`tanggal`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `antrian_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
