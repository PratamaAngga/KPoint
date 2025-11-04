-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2025 at 05:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `k.point`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(255) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok_barang` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `harga`, `stok_barang`) VALUES
(5, 'Torabika cappucino', 3000.00, 24),
(10, 'Biskuit Roma Kelapa', 7000.00, 58),
(11, 'Kahf Face Wash', 40000.00, 9),
(12, 'Sunscreen Azarine', 30000.00, 16),
(13, 'Kopi Yaa SP', 6000.00, 80),
(14, 'Teh Botol', 5000.00, 49),
(15, 'Aqua Botol 600ml', 3500.00, 95),
(16, 'Indomie Goreng', 3000.00, 185),
(17, 'Energen Vanila Sachet', 2500.00, 146),
(18, 'Nescafe Sachet', 2000.00, 90),
(19, 'Pepsodent', 12000.00, 57),
(20, 'Lifebuoy Sabun Mandi', 4000.00, 100),
(21, 'Rexonna Roll On', 15000.00, 45),
(22, 'Wipol Pembersih Lantai', 18000.00, 25),
(23, 'Ember Plastik', 20000.00, 13),
(24, 'Mie Sedap Ayam Bawang', 3000.00, 200),
(25, 'Shampoo Zinc 320ml', 25000.00, 39);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_barang`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(2, 1, 5, 5, 2000.00, 10000.00),
(4, 2, 5, 5, 2000.00, 10000.00),
(12, 7, 5, 5, 2000.00, 10000.00),
(14, 9, 5, 5, 2000.00, 10000.00),
(16, 10, 5, 10, 2000.00, 20000.00),
(18, 12, 5, 5, 2000.00, 10000.00),
(19, 13, 15, 5, 3500.00, 17500.00),
(20, 14, 12, 2, 30000.00, 60000.00),
(21, 14, 19, 1, 12000.00, 12000.00),
(22, 15, 16, 10, 3000.00, 30000.00),
(23, 16, 11, 1, 40000.00, 40000.00),
(24, 16, 18, 10, 2000.00, 20000.00),
(25, 17, 5, 1, 3000.00, 3000.00),
(26, 17, 25, 1, 25000.00, 25000.00),
(27, 17, 19, 2, 12000.00, 24000.00),
(28, 18, 10, 2, 7000.00, 14000.00),
(29, 19, 23, 2, 20000.00, 40000.00),
(30, 19, 14, 1, 5000.00, 5000.00),
(31, 20, 12, 2, 30000.00, 60000.00),
(32, 20, 16, 5, 3000.00, 15000.00),
(33, 21, 17, 4, 2500.00, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kasir`
--

CREATE TABLE `kasir` (
  `id_user` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kasir`
--

INSERT INTO `kasir` (`id_user`, `username`, `password`, `nama`) VALUES
(1, 'Amura', '$2y$10$LGbUQhF/bVnfNRYsFmbpJe4xeZi5LnqsnY9G8UYRBf0.cZf/C6MaK', 'Angga'),
(7, 'hasbiy', '$2y$10$uzdmPAa1ojHGEUW9X3CYU.eK9XiTkz/ffY9xN8CfoF98huHNZmakS', 'Hasbiy');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Minuman'),
(3, 'Makanan'),
(6, 'Skincare'),
(7, 'Alat Mandi'),
(8, 'Perabot Rumah Tangga'),
(9, 'Kopi');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_barang`
--

CREATE TABLE `kategori_barang` (
  `id_barang` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori_barang`
--

INSERT INTO `kategori_barang` (`id_barang`, `id_kategori`) VALUES
(5, 1),
(5, 9),
(10, 3),
(11, 6),
(11, 7),
(12, 6),
(13, 1),
(13, 9),
(14, 1),
(15, 1),
(16, 3),
(17, 1),
(17, 3),
(18, 9),
(19, 7),
(20, 7),
(21, 6),
(22, 8),
(23, 8),
(24, 3),
(25, 6),
(25, 7);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama_pelanggan` varchar(255) DEFAULT NULL,
  `no_telp` varchar(12) DEFAULT NULL,
  `poin` int(3) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_telp`, `poin`) VALUES
(2, 'angga', '081234567890', 11),
(5, 'Ailsa', '1234567890', 5);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal_transaksi` datetime DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_transaksi`, `total`, `id_user`, `id_pelanggan`) VALUES
(1, '2025-06-20 00:00:00', 30000.00, 1, 2),
(2, '2025-06-20 00:00:00', 30000.00, 1, 2),
(6, '2025-06-23 00:00:00', 30000.00, 1, 2),
(7, '2025-06-23 00:00:00', 60000.00, 1, 2),
(8, '2025-06-23 00:00:00', 50000.00, 1, 2),
(9, '2025-06-23 00:00:00', 10000.00, 1, 2),
(10, '2025-06-23 00:00:00', 70000.00, 1, 2),
(12, '2025-06-23 00:00:00', 10000.00, NULL, 2),
(13, '2025-06-21 00:00:00', 17500.00, 1, 2),
(14, '2025-06-21 00:00:00', 72000.00, 1, 5),
(15, '2025-06-21 00:00:00', 30000.00, 1, 5),
(16, '2025-06-21 00:00:00', 60000.00, 1, 2),
(17, '2025-06-21 00:00:00', 52000.00, 1, 2),
(18, '2025-06-22 00:00:00', 14000.00, 7, 2),
(19, '2025-06-22 00:00:00', 45000.00, 7, 5),
(20, '2025-06-22 00:00:00', 75000.00, 7, 2),
(21, '2025-06-22 00:00:00', 10000.00, 7, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `kategori_barang`
--
ALTER TABLE `kategori_barang`
  ADD PRIMARY KEY (`id_barang`,`id_kategori`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_pelanggan` (`id_pelanggan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `kategori_barang`
--
ALTER TABLE `kategori_barang`
  ADD CONSTRAINT `id_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `id_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_kasir` FOREIGN KEY (`id_user`) REFERENCES `kasir` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
