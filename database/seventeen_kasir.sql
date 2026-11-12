-- =============================================================================
-- DATABASE SCHEMA: db_seventeen_kasir
-- Referensi: PERANCANGAN APLIKASI SISTEM KASIR BERBASIS WEB MENGGUNAKAN
-- METODE WATERFALL PADA SEVENTEEN PETSHOP (JATI Vol. 8 No. 4, Agustus 2024)
-- 7 Entitas: member, kategori, barang, penjualan, nota, toko, login
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `db_seventeen_kasir` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_seventeen_kasir`;

-- -----------------------------------------------------------------------------
-- 1. Tabel: member (Pengguna / Kasir / Profil Pengelola)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id_member` INT(11) NOT NULL AUTO_INCREMENT,
  `nm_member` VARCHAR(255) NOT NULL,
  `alamat_member` TEXT NOT NULL,
  `telepon` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `gambar` VARCHAR(255) DEFAULT 'default.jpg',
  `NIK` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Tabel: login (Autentikasi Akun Admin / Kasir)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `id_login` INT(11) NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(100) NOT NULL UNIQUE,
  `pass` VARCHAR(255) NOT NULL,
  `id_member` INT(11) NOT NULL,
  PRIMARY KEY (`id_login`),
  KEY `fk_login_member` (`id_member`),
  CONSTRAINT `fk_login_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabel: toko (Identitas Usaha Seventeen Petshop)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `toko`;
CREATE TABLE `toko` (
  `id_toko` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` VARCHAR(255) NOT NULL,
  `alamat_toko` TEXT NOT NULL,
  `tlp` VARCHAR(50) NOT NULL,
  `nama_pemilik` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Tabel: kategori (Kategori Produk Hewan)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `kategori`;
CREATE TABLE `kategori` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(255) NOT NULL,
  `tgl_input` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Tabel: barang (Katalog Produk Petshop & Stok)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `barang`;
CREATE TABLE `barang` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_barang` VARCHAR(50) NOT NULL UNIQUE,
  `id_kategori` INT(11) NOT NULL,
  `nama_barang` VARCHAR(255) NOT NULL,
  `merk` VARCHAR(100) NOT NULL,
  `harga_beli` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `harga_jual` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `satuan_barang` VARCHAR(50) NOT NULL DEFAULT 'PCS',
  `stok` INT(11) NOT NULL DEFAULT 0,
  `stok_minimum` INT(11) NOT NULL DEFAULT 5,
  `tgl_input` VARCHAR(50) NOT NULL,
  `tgl_update` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_barang_kategori` (`id_kategori`),
  KEY `idx_id_barang` (`id_barang`),
  CONSTRAINT `fk_barang_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. Tabel: penjualan (Keranjang Transaksi POS Sementara Kasir)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `penjualan`;
CREATE TABLE `penjualan` (
  `id_penjualan` INT(11) NOT NULL AUTO_INCREMENT,
  `id_barang` VARCHAR(50) NOT NULL,
  `id_member` INT(11) NOT NULL,
  `jumlah` INT(11) NOT NULL DEFAULT 1,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_input` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_penjualan`),
  KEY `fk_penjualan_barang` (`id_barang`),
  KEY `fk_penjualan_member` (`id_member`),
  CONSTRAINT `fk_penjualan_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  CONSTRAINT `fk_penjualan_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. Tabel: nota (Riwayat Detail Checkout Penjualan & Pembayaran Struk)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `nota`;
CREATE TABLE `nota` (
  `id_nota` INT(11) NOT NULL AUTO_INCREMENT,
  `no_transaksi` VARCHAR(50) NOT NULL,
  `id_barang` VARCHAR(50) NOT NULL,
  `id_member` INT(11) NOT NULL,
  `jumlah` INT(11) NOT NULL DEFAULT 1,
  `total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tanggal_input` VARCHAR(50) NOT NULL,
  `periode` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id_nota`),
  KEY `idx_no_transaksi` (`no_transaksi`),
  KEY `idx_periode` (`periode`),
  KEY `fk_nota_barang` (`id_barang`),
  KEY `fk_nota_member` (`id_member`),
  CONSTRAINT `fk_nota_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE,
  CONSTRAINT `fk_nota_member` FOREIGN KEY (`id_member`) REFERENCES `member` (`id_member`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA AWAL (SEVENTEEN PETSHOP)
-- =============================================================================

-- Seed Profil Member (Admin Toko)
INSERT INTO `member` (`id_member`, `nm_member`, `alamat_member`, `telepon`, `email`, `gambar`, `NIK`) VALUES
(1, 'Admin Seventeen Petshop', 'Jl. Atria No. 007, Tidar Baru, Magersari, Magelang Selatan, Kota Magelang', '081326365024', 'admin@seventeenpetshop.local', 'default.jpg', '3201012345670001');

-- Seed Login (Username: admin | Password hash bcrypt 'admin123' & md5 compatibility)
-- Hash bcrypt default untuk 'admin123': $2y$10$wE1Vp2qN0c5uM4U4rK6c8.1UuB9eTq8M.E4fD8mHq6mK0O7q6R3re
-- Password MD5 'admin123': 0192023a7bbd73250516f069df18b500
-- Password MD5 '123': 202cb962ac59075b964b07152d234b70
INSERT INTO `login` (`id_login`, `user`, `pass`, `id_member`) VALUES
(1, 'admin', '$2y$10$3YmXqK61kXgXy0W5kM17IuxhI/V1UqWpZt82YtNqWJbW63Q4C0gq6', 1);

-- Seed Data Toko
INSERT INTO `toko` (`id_toko`, `nama_toko`, `alamat_toko`, `tlp`, `nama_pemilik`) VALUES
(1, 'Seventeen Petshop', 'Jl. Atria No. 007, Tidar Baru, Magersari, Magelang Selatan, Kota Magelang', '0813-2636-5024', 'Owner Seventeen Petshop');

-- Seed Kategori Produk Petshop
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `tgl_input`) VALUES
(1, 'Makanan Kucing (Cat Food)', '9 November 2026, 09:00'),
(2, 'Makanan Anjing (Dog Food)', '9 November 2026, 09:05'),
(3, 'Pasir & Kebersihan', '9 November 2026, 09:10'),
(4, 'Shampo & Perawatan', '9 November 2026, 09:15'),
(5, 'Obat & Vitamin', '9 November 2026, 09:20'),
(6, 'Aksesoris & Kandang', '9 November 2026, 09:25');

-- Seed Data Barang (Produk Seventeen Petshop)
INSERT INTO `barang` (`id`, `id_barang`, `id_kategori`, `nama_barang`, `merk`, `harga_beli`, `harga_jual`, `satuan_barang`, `stok`, `stok_minimum`, `tgl_input`, `tgl_update`) VALUES
(1, 'BR001', 1, 'Royal Canin Kitten 400g', 'Royal Canin', 65000.00, 78000.00, 'PCS', 25, 5, '9 November 2026, 09:30', NULL),
(2, 'BR002', 1, 'Whiskas Ocean Fish 1.2kg', 'Whiskas', 58000.00, 69000.00, 'PCS', 18, 5, '9 November 2026, 09:32', NULL),
(3, 'BR003', 2, 'Pedigree Adult Beef & Veg 1.5kg', 'Pedigree', 62000.00, 75000.00, 'PCS', 12, 5, '9 November 2026, 09:35', NULL),
(4, 'BR004', 3, 'Pasir Gumpal Wangi Lemon 5L', 'Bentonite Cat Litter', 28000.00, 38000.00, 'BAG', 30, 8, '9 November 2026, 09:38', NULL),
(5, 'BR005', 4, 'Shampo Kucing Anti Kutu & Jamur 250ml', 'Fluffy Care', 22000.00, 32000.00, 'BTL', 4, 5, '9 November 2026, 09:40', NULL), -- Contoh stok menipis (< stok_minimum)
(6, 'BR006', 5, 'Nutri-plus Gel Vitamin Hewan 120g', 'Virbac', 115000.00, 135000.00, 'PCS', 2, 5, '9 November 2026, 09:42', NULL), -- Contoh stok menipis (< stok_minimum)
(7, 'BR007', 6, 'Kalung Kucing Bell Motif Paw', 'PetStyle', 7000.00, 15000.00, 'PCS', 40, 10, '9 November 2026, 09:45', NULL),
(8, 'BR008', 6, 'Kandang Lipat Besi Medium (60x40)', 'Octagon', 110000.00, 145000.00, 'UNIT', 0, 3, '9 November 2026, 09:48', NULL); -- Contoh stok habis (0)

