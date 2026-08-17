<?php
define('BASE_URL', 'http://localhost/umkm-admin/');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_FILE_SIZE', 5242880); // 5MB

// Format Rupiah
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format Tanggal Indonesia
function formatTanggal($date, $format = 'j F Y') {
    $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $bulan[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// Format Waktu Indonesia
function formatWaktu($datetime) {
    return date('d F Y H:i', strtotime($datetime));
}

// Generate Nomor Transaksi
function generateNomorTransaksi($pdo) {
    $tanggal = date('Ymd');
    
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM transaksi WHERE DATE(created_at) = CURDATE()');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomor = str_pad($result['total'] + 1, 3, '0', STR_PAD_LEFT);
    
    return 'TRX-' . $tanggal . '-' . $nomor;
}

// Generate Kode Produk
function generateKodeProduk($pdo) {
    $stmt = $pdo->prepare('SELECT MAX(CAST(SUBSTRING(kode_produk, 4) AS UNSIGNED)) as max_kode FROM produk WHERE kode_produk LIKE "PRD%"');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nomor = ($result['max_kode'] ?? 0) + 1;
    
    return 'PRD' . str_pad($nomor, 3, '0', STR_PAD_LEFT);
}

// Upload Gambar Produk
function uploadGambar($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error upload file');
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        throw new Exception('Format file tidak diizinkan');
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Ukuran file terlalu besar');
    }
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    $filename = 'produk_' . time() . '.' . $ext;
    $filepath = UPLOAD_DIR . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Gagal menyimpan file');
    }
    
    return $filename;
}

// Hapus File Gambar
function hapusGambar($filename) {
    if ($filename && file_exists(UPLOAD_DIR . $filename)) {
        unlink(UPLOAD_DIR . $filename);
    }
}

// Status Stok
function getStatusStok($stok, $minimal) {
    if ($stok == 0) {
        return ['status' => 'Habis', 'badge' => 'danger'];
    } elseif ($stok <= $minimal) {
        return ['status' => 'Menipis', 'badge' => 'warning'];
    } else {
        return ['status' => 'Aman', 'badge' => 'success'];
    }
}

// Hitung Margin Keuntungan
function hitungMargin($harga_beli, $harga_jual) {
    if ($harga_beli == 0) return 0;
    return (($harga_jual - $harga_beli) / $harga_beli) * 100;
}

// Dashboard Statistics
function getDashboardStats($pdo) {
    $stats = [];
    
    // Total Produk
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM produk WHERE status = "Aktif"');
    $stats['total_produk'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Stok
    $stmt = $pdo->query('SELECT SUM(stok) as total FROM produk');
    $stats['total_stok'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total Pelanggan
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM pelanggan WHERE tipe = "Regular"');
    $stats['total_pelanggan'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Transaksi
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM transaksi WHERE status = "Selesai"');
    $stats['total_transaksi'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pendapatan Hari Ini
    $stmt = $pdo->query('SELECT COALESCE(SUM(total), 0) as total FROM transaksi WHERE DATE(created_at) = CURDATE() AND status = "Selesai"');
    $stats['pendapatan_hari'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pendapatan Bulan Ini
    $stmt = $pdo->query('SELECT COALESCE(SUM(total), 0) as total FROM transaksi WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) AND status = "Selesai"');
    $stats['pendapatan_bulan'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total Pengeluaran Bulan Ini
    $stmt = $pdo->query('SELECT COALESCE(SUM(jumlah), 0) as total FROM pengeluaran WHERE YEAR(tanggal) = YEAR(NOW()) AND MONTH(tanggal) = MONTH(NOW())');
    $stats['pengeluaran_bulan'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Laba Bersih Bulan Ini
    $stats['laba_bulan'] = $stats['pendapatan_bulan'] - $stats['pengeluaran_bulan'];
    
    return $stats;
}

// Get Data untuk Grafik
function getGrafikPenjualan7Hari($pdo) {
    $stmt = $pdo->query('SELECT DATE(created_at) as tanggal, COALESCE(SUM(total), 0) as total FROM transaksi WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status = "Selesai" GROUP BY DATE(created_at) ORDER BY tanggal ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGrafikPemasukanPengeluaran($pdo) {
    $bulan_ini = date('Y-m');
    
    $stmt = $pdo->query('SELECT 
        COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at, "%Y-%m") = "' . $bulan_ini . '" THEN total ELSE 0 END), 0) as pemasukan,
        COALESCE((SELECT SUM(jumlah) FROM pengeluaran WHERE DATE_FORMAT(tanggal, "%Y-%m") = "' . $bulan_ini . '"), 0) as pengeluaran
    FROM transaksi WHERE status = "Selesai"');
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProdukTerlaris($pdo) {
    $stmt = $pdo->query('SELECT p.nama, p.id, SUM(dt.jumlah) as total_terjual FROM detail_transaksi dt JOIN produk p ON dt.produk_id = p.id JOIN transaksi t ON dt.transaksi_id = t.id WHERE t.status = "Selesai" GROUP BY dt.produk_id ORDER BY total_terjual DESC LIMIT 10');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTransaksiTerbaru($pdo) {
    $stmt = $pdo->query('SELECT t.*, p.nama as pelanggan_nama FROM transaksi t LEFT JOIN pelanggan p ON t.pelanggan_id = p.id WHERE t.status = "Selesai" ORDER BY t.created_at DESC LIMIT 10');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProdukStokMenipis($pdo) {
    $stmt = $pdo->query('SELECT * FROM produk WHERE stok <= minimal_stok AND stok > 0 AND status = "Aktif" ORDER BY stok ASC LIMIT 5');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProdukStokHabis($pdo) {
    $stmt = $pdo->query('SELECT * FROM produk WHERE stok = 0 AND status = "Aktif" LIMIT 5');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>