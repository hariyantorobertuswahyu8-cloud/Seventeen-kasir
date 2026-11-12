-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2026 at 01:48 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_seventeen_kasir`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `id_barang` varchar(50) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `merk` varchar(100) NOT NULL,
  `harga_beli` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual` decimal(12,2) NOT NULL DEFAULT 0.00,
  `satuan_barang` varchar(50) NOT NULL DEFAULT 'PCS',
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_minimum` int(11) NOT NULL DEFAULT 5,
  `tgl_input` varchar(50) NOT NULL,
  `tgl_update` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `id_barang`, `id_kategori`, `nama_barang`, `merk`, `harga_beli`, `harga_jual`, `satuan_barang`, `stok`, `stok_minimum`, `tgl_input`, `tgl_update`) VALUES
(1, 'BR001', 1, 'Royal Canin Kitten 400g', 'Royal Canin', 65000.00, 78000.00, 'PCS', 22, 5, '9 November 2026, 09:30', NULL),
(2, 'BR002', 1, 'Whiskas Ocean Fish 1.2kg', 'Whiskas', 58000.00, 69000.00, 'PCS', 15, 5, '9 November 2026, 09:32', NULL),
(3, 'BR003', 2, 'Pedigree Adult Beef & Veg 1.5kg', 'Pedigree', 62000.00, 75000.00, 'PCS', 12, 5, '9 November 2026, 09:35', NULL),
(4, 'BR004', 3, 'Pasir Gumpal Wangi Lemon 5L', 'Bentonite Cat Litter', 28000.00, 38000.00, 'BAG', 27, 8, '9 November 2026, 09:38', NULL),
(5, 'BR005', 4, 'Shampo Kucing Anti Kutu & Jamur 250ml', 'Fluffy Care', 22000.00, 32000.00, 'BTL', 4, 5, '9 November 2026, 09:40', NULL),
(6, 'BR006', 5, 'Nutri-plus Gel Vitamin Hewan 120g', 'Virbac', 115000.00, 135000.00, 'PCS', 2, 5, '9 November 2026, 09:42', NULL),
(7, 'BR007', 6, 'Kalung Kucing Bell Motif Paw', 'PetStyle', 7000.00, 15000.00, 'PCS', 39, 10, '9 November 2026, 09:45', NULL),
(8, 'BR008', 6, 'Kandang Lipat Besi Medium (60x40)', 'Octagon', 110000.00, 145000.00, 'UNIT', 9, 3, '9 November 2026, 09:48', '9 November 2026, 15:49');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `tgl_input` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `tgl_input`) VALUES
(1, 'Makanan Kucing (Cat Food)', '9 November 2026, 09:00'),
(2, 'Makanan Anjing (Dog Food)', '9 November 2026, 09:05'),
(3, 'Pasir & Kebersihan', '9 November 2026, 09:10'),
(4, 'Shampo & Perawatan', '9 November 2026, 09:15'),
(5, 'Obat & Vitamin', '9 November 2026, 09:20'),
(6, 'Aksesoris & Kandang', '9 November 2026, 09:25');

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `id_login` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `id_member` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`id_login`, `user`, `pass`, `id_member`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 1);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `id_member` int(11) NOT NULL,
  `nm_member` varchar(255) NOT NULL,
  `alamat_member` text NOT NULL,
  `telepon` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `NIK` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`id_member`, `nm_member`, `alamat_member`, `telepon`, `email`, `gambar`, `NIK`) VALUES
(1, 'Admin Seventeen Petshop', 'Jl. Petshop No. 17, Tangerang', '081326365024', 'admin@seventeenpetshop.local', 'default.jpg', '3201012345670001');

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `id_nota` int(11) NOT NULL,
  `no_transaksi` varchar(50) NOT NULL,
  `id_barang` varchar(50) NOT NULL,
  `id_member` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_input` varchar(50) NOT NULL,
  `periode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nota`
--

INSERT INTO `nota` (`id_nota`, `no_transaksi`, `id_barang`, `id_member`, `jumlah`, `total`, `tanggal_input`, `periode`) VALUES
(1, 'TR-20261109-154103-821', 'BR007', 1, 1, 15000.00, '9 November 2026, 15:41:03', '11-2026'),
(2, 'TR-20261110-015027-583', 'BR008', 1, 1, 145000.00, '10 November 2026, 01:50:27', '11-2026'),
(3, 'TR-20261110-015700-526', 'BR004', 1, 1, 38000.00, '10 November 2026, 01:57:00', '11-2026'),
(4, 'TR-20261110-020251-677', 'BR002', 1, 1, 69000.00, '10 November 2026, 02:02:51', '11-2026'),
(5, 'TR-20261110-020538-142', 'BR002', 1, 1, 69000.00, '10 November 2026, 02:05:38', '11-2026'),
(6, 'TR-20261110-020810-113', 'BR002', 1, 1, 69000.00, '10 November 2026, 02:08:10', '11-2026'),
(7, 'TR-20261110-021208-958', 'BR001', 1, 1, 78000.00, '10 November 2026, 02:12:08', '11-2026'),
(8, 'TR-20261110-022541-144', 'BR004', 1, 1, 38000.00, '10 November 2026, 02:25:41', '11-2026'),
(9, 'TR-20261110-025331-506', 'BR001', 1, 2, 156000.00, '10 November 2026, 02:53:31', '11-2026'),
(10, 'TR-20261110-055050-467', 'BR004', 1, 1, 38000.00, '10 November 2026, 05:50:50', '11-2026');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL,
  `id_barang` varchar(50) NOT NULL,
  `id_member` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_input` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toko`
--

CREATE TABLE `toko` (
  `id_toko` int(11) NOT NULL,
  `nama_toko` varchar(255) NOT NULL,
  `alamat_toko` text NOT NULL,
  `tlp` varchar(50) NOT NULL,
  `nama_pemilik` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `toko`
--

INSERT INTO `toko` (`id_toko`, `nama_toko`, `alamat_toko`, `tlp`, `nama_pemilik`) VALUES
(1, 'Seventeen Petshop', 'Jl. Atria No. 007, Tidar Baru, Magersari, Magelang Selatan, Kota Magelang', '0813-2636-5024', 'Owner Seventeen Petshop');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_barang` (`id_barang`),
  ADD KEY `fk_barang_kategori` (`id_kategori`),
  ADD KEY `idx_id_barang` (`id_barang`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`id_login`),
  ADD UNIQUE KEY `user` (`user`),
  ADD KEY `fk_login_member` (`id_member`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`id_member`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id_nota`),
  ADD KEY `idx_no_transaksi` (`no_transaksi`),
  ADD KEY `idx_periode` (`periode`),
  ADD KEY `fk_nota_barang` (`id_barang`),
  ADD KEY `fk_nota_member` (`id_member`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id_penjualan`),
  ADD KEY `fk_penjualan_barang` (`id_barang`),
  ADD KEY `fk_penjualan_member` (`id_member`);

--
-- Indexes for table `toko`
--
ALTER TABLE `toko`
  ADD PRIMARY KEY (`id_toko`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `id_login` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `id_member` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nota`
--
ALTER TABLE `nota`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id_penjualan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `toko`
--
ALTER TABLE `toko`
  MODIFY `id_toko` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Constraints for table `login`
--
ALTER TABLE `login`
  ADD CONSTRAINT `fk_login_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `fk_nota_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nota_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON UPDATE CASCADE;

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `fk_penjualan_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penjualan_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
