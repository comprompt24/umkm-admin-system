-- Database UMKM Administration System
-- ======================================

-- Buat Database
CREATE DATABASE IF NOT EXISTS db_umkm;
USE db_umkm;

-- Tabel Users (Admin)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Kategori Produk
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Produk
CREATE TABLE produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_produk VARCHAR(50) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    kategori_id INT NOT NULL,
    harga_beli DECIMAL(12, 2) NOT NULL,
    harga_jual DECIMAL(12, 2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(50),
    minimal_stok INT NOT NULL DEFAULT 5,
    gambar VARCHAR(255),
    deskripsi TEXT,
    status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT,
    INDEX idx_kategori (kategori_id),
    INDEX idx_kode (kode_produk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pelanggan
CREATE TABLE pelanggan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(150) NOT NULL,
    nomor_hp VARCHAR(15),
    alamat TEXT,
    email VARCHAR(100),
    catatan TEXT,
    tipe ENUM('Regular', 'Umum') DEFAULT 'Regular',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi Penjualan
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_transaksi VARCHAR(50) NOT NULL UNIQUE,
    pelanggan_id INT NOT NULL,
    total_produk INT NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL,
    diskon DECIMAL(12, 2) DEFAULT 0,
    total DECIMAL(12, 2) NOT NULL,
    jumlah_pembayaran DECIMAL(12, 2) NOT NULL,
    kembalian DECIMAL(12, 2),
    metode_pembayaran ENUM('Tunai', 'Transfer', 'E-Wallet') DEFAULT 'Tunai',
    status ENUM('Selesai', 'Pending', 'Batal') DEFAULT 'Selesai',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE RESTRICT,
    INDEX idx_nomor (nomor_transaksi),
    INDEX idx_tanggal (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12, 2) NOT NULL,
    harga_total DECIMAL(12, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Riwayat Stok
CREATE TABLE stok (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produk_id INT NOT NULL,
    jenis_perubahan ENUM('Stok Masuk', 'Stok Keluar', 'Penyesuaian') NOT NULL,
    jumlah INT NOT NULL,
    stok_sebelum INT NOT NULL,
    stok_sesudah INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE CASCADE,
    INDEX idx_produk (produk_id),
    INDEX idx_tanggal (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pemasukan
CREATE TABLE pemasukan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    sumber VARCHAR(100) NOT NULL,
    keterangan TEXT,
    jumlah DECIMAL(12, 2) NOT NULL,
    tipe ENUM('Transaksi', 'Lainnya') DEFAULT 'Lainnya',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pengeluaran
CREATE TABLE pengeluaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    kategori ENUM('Bahan baku', 'Operasional', 'Transportasi', 'Listrik', 'Air', 'Gaji', 'Perawatan', 'Sewa', 'Lainnya') NOT NULL,
    keterangan TEXT,
    jumlah DECIMAL(12, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tanggal (tanggal),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- Data Dummy
-- ======================================

-- Insert Admin User
INSERT INTO users (username, email, password, nama) VALUES 
('admin', 'admin@umkm.local', '$2y$10$YOixghO5e2W9BJ.7bLVKCO2XQ8HsLClRnLQ8LlC2ZhpKtN2jfH.JK', 'Administrator UMKM');

-- Insert Kategori
INSERT INTO kategori (nama, deskripsi) VALUES 
('Donat', 'Produk donat berbagai rasa'),
('Minuman', 'Produk minuman'),
('Snack', 'Produk snack ringan'),
('Lainnya', 'Produk kategori lainnya');

-- Insert Produk
INSERT INTO produk (kode_produk, nama, kategori_id, harga_beli, harga_jual, stok, satuan, minimal_stok, deskripsi, status) VALUES 
('PRD001', 'Donat Coklat', 1, 3000, 7000, 50, 'pcs', 10, 'Donat dengan topping coklat', 'Aktif'),
('PRD002', 'Donat Keju', 1, 3500, 8000, 30, 'pcs', 10, 'Donat dengan topping keju cheddar', 'Aktif'),
('PRD003', 'Donat Strawberry', 1, 3500, 8000, 25, 'pcs', 10, 'Donat dengan topping strawberry', 'Aktif'),
('PRD004', 'Donat Matcha', 1, 4000, 9000, 15, 'pcs', 10, 'Donat dengan topping matcha premium', 'Aktif'),
('PRD005', 'Kopi Hitam', 2, 2000, 5000, 100, 'cup', 20, 'Kopi hitam tanpa gula', 'Aktif'),
('PRD006', 'Teh Tarik', 2, 2000, 5000, 80, 'cup', 15, 'Teh tarik kental dan creamy', 'Aktif'),
('PRD007', 'Jus Jeruk', 2, 3000, 7000, 50, 'cup', 15, 'Jus jeruk segar', 'Aktif'),
('PRD008', 'Keripik Singkong', 3, 5000, 12000, 40, 'bag', 10, 'Keripik singkong renyah', 'Aktif');

-- Insert Pelanggan
INSERT INTO pelanggan (nama, nomor_hp, alamat, email, tipe) VALUES 
('Budi Santoso', '081234567890', 'Jl. Merdeka No. 123, Jakarta', 'budi@email.com', 'Regular'),
('Siti Nurhaliza', '082345678901', 'Jl. Sudirman No. 456, Jakarta', 'siti@email.com', 'Regular'),
('Ahmad Wijaya', '083456789012', 'Jl. Gatot Subroto No. 789, Jakarta', 'ahmad@email.com', 'Regular'),
('Pelanggan Umum', NULL, NULL, NULL, 'Umum');

-- Insert Transaksi & Detail (Data Dummy)
INSERT INTO transaksi (nomor_transaksi, pelanggan_id, total_produk, subtotal, diskon, total, jumlah_pembayaran, kembalian, metode_pembayaran, status, created_at) VALUES 
('TRX-20260816-001', 1, 3, 22000, 0, 22000, 25000, 3000, 'Tunai', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('TRX-20260816-002', 2, 2, 14000, 0, 14000, 15000, 1000, 'Tunai', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('TRX-20260816-003', 3, 5, 36000, 1000, 35000, 40000, 5000, 'Tunai', 'Selesai', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('TRX-20260817-001', 1, 2, 16000, 0, 16000, 20000, 4000, 'Tunai', 'Selesai', NOW()),
('TRX-20260817-002', 4, 3, 21000, 0, 21000, 25000, 4000, 'Tunai', 'Selesai', NOW());

-- Insert Detail Transaksi
INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga_satuan, harga_total) VALUES 
(1, 1, 2, 7000, 14000),
(1, 2, 1, 8000, 8000),
(2, 5, 2, 5000, 10000),
(2, 6, 1, 5000, 5000),
(3, 3, 2, 8000, 16000),
(3, 7, 2, 7000, 14000),
(3, 8, 1, 12000, 12000),
(4, 1, 1, 7000, 7000),
(4, 6, 2, 5000, 10000),
(5, 2, 1, 8000, 8000),
(5, 5, 1, 5000, 5000),
(5, 8, 1, 12000, 12000);

-- Insert Riwayat Stok
INSERT INTO stok (produk_id, jenis_perubahan, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at) VALUES 
(1, 'Stok Masuk', 50, 0, 50, 'Stok awal', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 'Stok Masuk', 30, 0, 30, 'Stok awal', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 'Stok Keluar', 2, 50, 48, 'Penjualan TRX-20260816-001', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'Stok Keluar', 1, 30, 29, 'Penjualan TRX-20260816-001', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 'Stok Keluar', 2, 100, 98, 'Penjualan TRX-20260816-002', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'Stok Keluar', 1, 48, 47, 'Penjualan TRX-20260817-001', NOW()),
(6, 'Stok Keluar', 2, 80, 78, 'Penjualan TRX-20260817-001', NOW());

-- Insert Pemasukan (dari transaksi)
INSERT INTO pemasukan (tanggal, sumber, keterangan, jumlah, tipe) VALUES 
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Penjualan', 'Dari transaksi TRX-20260816-001', 22000, 'Transaksi'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Penjualan', 'Dari transaksi TRX-20260816-002', 14000, 'Transaksi'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Penjualan', 'Dari transaksi TRX-20260816-003', 35000, 'Transaksi'),
(CURDATE(), 'Penjualan', 'Dari transaksi TRX-20260817-001', 16000, 'Transaksi'),
(CURDATE(), 'Penjualan', 'Dari transaksi TRX-20260817-002', 21000, 'Transaksi');

-- Insert Pengeluaran
INSERT INTO pengeluaran (tanggal, kategori, keterangan, jumlah) VALUES 
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Bahan baku', 'Pembelian tepung dan topping donat', 500000),
(DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Operasional', 'Pembelian cup dan kemasan', 200000),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Listrik', 'Tagihan listrik bulan lalu', 150000),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Bahan baku', 'Pembelian bahan tambahan', 300000),
(CURDATE(), 'Operasional', 'Pembelian plastik kemasan', 100000);
