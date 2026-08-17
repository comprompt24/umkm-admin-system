# Sistem Informasi Administrasi UMKM

Website sistem informasi administrasi yang digunakan oleh pemilik/admin UMKM untuk mengelola seluruh kegiatan administrasi usaha secara terkomputerisasi.

## 🎯 Fitur Utama

- Dashboard statistik dengan grafik
- Manajemen kategori produk
- Manajemen produk lengkap
- Manajemen stok barang
- Transaksi penjualan
- Data pelanggan
- Pencatatan pemasukan & pengeluaran
- Laporan penjualan
- Laporan keuangan sederhana
- Laporan stok
- Manajemen profil admin

## 📋 Persyaratan Teknologi

- PHP 7.4+
- MySQL 5.7+
- Bootstrap 5
- Chart.js
- Font Awesome
- SweetAlert2

## 📦 Instalasi

### 1. Setup Database
```bash
# Buka MySQL
mysql -u root -p

# Buat database
CREATE DATABASE db_umkm;
USE db_umkm;

# Import database
source database/db_umkm.sql;
```

### 2. Konfigurasi
Edit file `config/database.php` sesuaikan dengan konfigurasi MySQL Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_umkm');
```

### 3. Jalankan Website
```
http://localhost/umkm-admin/
```

## 🔐 Akun Dummy

**Username:** `admin`  
**Password:** `admin123`

## 📁 Struktur Folder

```
umkm-admin/
├── index.php
├── login.php
├── logout.php
├── config/
│   └── database.php
├── auth/
│   └── auth_check.php
├── admin/
│   ├── dashboard.php
│   ├── produk/
│   ├── kategori/
│   ├── stok/
│   ├── transaksi/
│   ├── pelanggan/
│   ├── pemasukan/
│   ├── pengeluaran/
│   ├── laporan/
│   └── profil/
├── includes/
│   ├── header.php
│   ├── navbar.php
│   ├── sidebar.php
│   ├── footer.php
│   └── functions.php
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
└── database/
    └── db_umkm.sql
```

## 🚀 Pengembangan

Website hanya memiliki **1 jenis pengguna: ADMIN/PEMILIK UMKM**

Tidak ada role kasir atau multi-user.

## 📝 Lisensi

Bebas untuk digunakan dan dikembangkan.
