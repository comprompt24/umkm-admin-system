<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Dashboard';
$stats = getDashboardStats($pdo);
$grafikPenjualan = getGrafikPenjualan7Hari($pdo);
$grafikPemasukanPengeluaran = getGrafikPemasukanPengeluaran($pdo);
$produkTerlaris = getProdukTerlaris($pdo);
$transaksiTerbaru = getTransaksiTerbaru($pdo);
$stokMenipis = getProdukStokMenipis($pdo);
$stokHabis = getProdukStokHabis($pdo);

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card success">
                    <div class="stat-card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-card-label">Total Produk</div>
                    <div class="stat-card-value"><?php echo $stats['total_produk']; ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card info">
                    <div class="stat-card-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="stat-card-label">Total Stok</div>
                    <div class="stat-card-value"><?php echo $stats['total_stok']; ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card" style="border-left-color: #6f42c1;">
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-label">Total Pelanggan</div>
                    <div class="stat-card-value"><?php echo $stats['total_pelanggan']; ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card" style="border-left-color: #fd7e14;">
                    <div class="stat-card-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-card-label">Total Transaksi</div>
                    <div class="stat-card-value"><?php echo $stats['total_transaksi']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Pendapatan Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card" style="border-left-color: #20c997;">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-card-label">Pendapatan Hari Ini</div>
                    <div class="stat-card-value" style="font-size: 20px;"><?php echo formatRupiah($stats['pendapatan_hari']); ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card" style="border-left-color: #0dcaf0;">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-card-label">Pendapatan Bulan Ini</div>
                    <div class="stat-card-value" style="font-size: 20px;"><?php echo formatRupiah($stats['pendapatan_bulan']); ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card danger">
                    <div class="stat-card-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-card-label">Pengeluaran Bulan Ini</div>
                    <div class="stat-card-value" style="font-size: 20px;"><?php echo formatRupiah($stats['pengeluaran_bulan']); ?></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                <div class="stat-card" style="border-left-color: #198754;">
                    <div class="stat-card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-card-label">Laba Bersih Bulan Ini</div>
                    <div class="stat-card-value" style="font-size: 20px; color: <?php echo $stats['laba_bulan'] >= 0 ? '#28a745' : '#dc3545'; ?>;"><?php echo formatRupiah($stats['laba_bulan']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line"></i> Penjualan 7 Hari Terakhir
                    </div>
                    <div class="card-body">
                        <canvas id="chartPenjualan"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar"></i> Pemasukan vs Pengeluaran (Bulan Ini)
                    </div>
                    <div class="card-body">
                        <canvas id="chartPemasukanPengeluaran"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-star"></i> 10 Produk Terlaris
                    </div>
                    <div class="card-body">
                        <canvas id="chartProdukTerlaris"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shopping-cart"></i> Transaksi Terbaru
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No. Transaksi</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksiTerbaru as $trx): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($trx['nomor_transaksi']); ?></code></td>
                                        <td><?php echo htmlspecialchars($trx['pelanggan_nama'] ?? 'Umum'); ?></td>
                                        <td><strong><?php echo formatRupiah($trx['total']); ?></strong></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($trx['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-header" style="background: #ffc107; color: #333;">
                                <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Stok</th>
                                                <th>Min</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($stokMenipis)): ?>
                                            <tr><td colspan="3" class="text-center text-muted">Tidak ada produk</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($stokMenipis as $produk): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produk['nama']); ?></td>
                                                <td><badge class="badge bg-warning"><?php echo $produk['stok']; ?></badge></td>
                                                <td><?php echo $produk['minimal_stok']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header" style="background: #dc3545; color: white;">
                                <i class="fas fa-times-circle"></i> Stok Habis
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($stokHabis)): ?>
                                            <tr><td class="text-center text-muted">Tidak ada produk</td></tr>
                                            <?php else: ?>
                                            <?php foreach ($stokHabis as $produk): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($produk['nama']); ?></td>
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
    // Grafik Penjualan 7 Hari
    const dataPenjualan = <?php echo json_encode($grafikPenjualan); ?>;
    const labelsPenjualan = dataPenjualan.map(d => {
        const date = new Date(d.tanggal);
        return date.getDate() + '/' + (date.getMonth() + 1);
    });
    const valuesPenjualan = dataPenjualan.map(d => d.total);
    
    const ctxPenjualan = document.getElementById('chartPenjualan').getContext('2d');
    new Chart(ctxPenjualan, {
        type: 'line',
        data: {
            labels: labelsPenjualan,
            datasets: [{
                label: 'Total Penjualan',
                data: valuesPenjualan,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    
    // Grafik Pemasukan vs Pengeluaran
    const dataPemasukanPengeluaran = <?php echo json_encode($grafikPemasukanPengeluaran); ?>;
    const ctxPemasukanPengeluaran = document.getElementById('chartPemasukanPengeluaran').getContext('2d');
    new Chart(ctxPemasukanPengeluaran, {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                label: 'Bulan Ini',
                data: [dataPemasukanPengeluaran.pemasukan, dataPemasukanPengeluaran.pengeluaran],
                backgroundColor: ['#28a745', '#dc3545'],
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    
    // Grafik Produk Terlaris
    const dataProdukTerlaris = <?php echo json_encode($produkTerlaris); ?>;
    const labelsProduk = dataProdukTerlaris.map(p => p.nama.substring(0, 20));
    const valuesProduk = dataProdukTerlaris.map(p => p.total_terjual);
    
    const colors = [
        '#667eea', '#764ba2', '#f093fb', '#4facfe', '#00f2fe',
        '#43e97b', '#38f9d7', '#fa709a', '#fee140', '#30cfd0'
    ];
    
    const ctxProduk = document.getElementById('chartProdukTerlaris').getContext('2d');
    new Chart(ctxProduk, {
        type: 'bar',
        data: {
            labels: labelsProduk,
            datasets: [{
                label: 'Jumlah Terjual',
                data: valuesProduk,
                backgroundColor: colors.slice(0, dataProdukTerlaris.length),
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'x',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
</script>