<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Laporan Keuangan';

$bulan = $_GET['bulan'] ?? date('Y-m');
$tahun = $_GET['tahun'] ?? date('Y');

try {
    // Get month data
    $bulan_arr = explode('-', $bulan);
    $tahun_bulan = $bulan_arr[0] . '-' . $bulan_arr[1];
    
    // Pemasukan
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(jumlah), 0) as total FROM pemasukan WHERE DATE_FORMAT(tanggal, "%Y-%m") = ?');
    $stmt->execute([$tahun_bulan]);
    $total_pemasukan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pengeluaran
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(jumlah), 0) as total FROM pengeluaran WHERE DATE_FORMAT(tanggal, "%Y-%m") = ?');
    $stmt->execute([$tahun_bulan]);
    $total_pengeluaran = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Laba
    $laba = $total_pemasukan - $total_pengeluaran;
    
    // Detail Pemasukan
    $stmt = $pdo->prepare('SELECT sumber, COALESCE(SUM(jumlah), 0) as total FROM pemasukan WHERE DATE_FORMAT(tanggal, "%Y-%m") = ? GROUP BY sumber');
    $stmt->execute([$tahun_bulan]);
    $detail_pemasukan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Detail Pengeluaran
    $stmt = $pdo->prepare('SELECT kategori, COALESCE(SUM(jumlah), 0) as total FROM pengeluaran WHERE DATE_FORMAT(tanggal, "%Y-%m") = ? GROUP BY kategori');
    $stmt->execute([$tahun_bulan]);
    $detail_pengeluaran = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Penjualan
    $stmt = $pdo->prepare('SELECT COUNT(*) as total_transaksi, COALESCE(SUM(total), 0) as total_penjualan FROM transaksi WHERE DATE_FORMAT(created_at, "%Y-%m") = ? AND status = "Selesai"');
    $stmt->execute([$tahun_bulan]);
    $penjualan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Top Produk
    $stmt = $pdo->prepare('SELECT p.nama, SUM(dt.jumlah) as total_terjual, SUM(dt.harga_total) as total_nilai FROM detail_transaksi dt JOIN produk p ON dt.produk_id = p.id JOIN transaksi t ON dt.transaksi_id = t.id WHERE DATE_FORMAT(t.created_at, "%Y-%m") = ? GROUP BY p.id ORDER BY total_terjual DESC LIMIT 10');
    $stmt->execute([$tahun_bulan]);
    $top_produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-pie"></i> Laporan Keuangan</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Bulan</label>
                        <input type="month" id="bulan" class="form-control" value="<?php echo htmlspecialchars($bulan); ?>" onchange="filterReport()">
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h6 class="card-title">Total Pemasukan</h6>
                                <h3><?php echo formatRupiah($total_pemasukan); ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-white bg-danger">
                            <div class="card-body">
                                <h6 class="card-title">Total Pengeluaran</h6>
                                <h3><?php echo formatRupiah($total_pengeluaran); ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-white" style="background: <?php echo $laba >= 0 ? '#28a745' : '#dc3545'; ?>;">
                            <div class="card-body">
                                <h6 class="card-title">Laba Bersih</h6>
                                <h3><?php echo formatRupiah($laba); ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h6 class="card-title">Total Penjualan</h6>
                                <h3><?php echo formatRupiah($penjualan['total_penjualan']); ?></h3>
                                <small><?php echo $penjualan['total_transaksi']; ?> Transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Detail Pemasukan -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header" style="background: #28a745; color: white;">
                                <i class="fas fa-arrow-down"></i> Detail Pemasukan
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Sumber</th>
                                                <th class="text-right">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($detail_pemasukan)): ?>
                                            <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($detail_pemasukan as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['sumber']); ?></td>
                                                <td class="text-right"><strong><?php echo formatRupiah($item['total']); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detail Pengeluaran -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header" style="background: #dc3545; color: white;">
                                <i class="fas fa-arrow-up"></i> Detail Pengeluaran
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Kategori</th>
                                                <th class="text-right">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($detail_pengeluaran)): ?>
                                            <tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($detail_pengeluaran as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['kategori']); ?></td>
                                                <td class="text-right"><strong><?php echo formatRupiah($item['total']); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Produk -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-star"></i> 10 Produk Terlaris
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Produk</th>
                                                <th class="text-right">Jumlah Terjual</th>
                                                <th class="text-right">Total Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($top_produk)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data penjualan</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($top_produk as $index => $prod): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($prod['nama']); ?></td>
                                                <td class="text-right"><?php echo $prod['total_terjual']; ?></td>
                                                <td class="text-right"><strong><?php echo formatRupiah($prod['total_nilai']); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
function filterReport() {
    const bulan = document.getElementById('bulan').value;
    window.location.href = '?bulan=' + bulan;
}
</script>