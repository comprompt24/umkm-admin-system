<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Laporan Stok';

try {
    // Get all produk with stok info
    $stmt = $pdo->query('SELECT p.*, k.nama as kategori_nama, COUNT(dt.id) as total_terjual FROM produk p JOIN kategori k ON p.kategori_id = k.id LEFT JOIN detail_transaksi dt ON p.id = dt.produk_id GROUP BY p.id ORDER BY p.nama ASC');
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Stok summary
    $stmt = $pdo->query('SELECT SUM(stok) as total_stok, COUNT(*) as total_produk FROM produk');
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Stok menipis
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM produk WHERE stok <= minimal_stok AND stok > 0');
    $stok_menipis = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Stok habis
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM produk WHERE stok = 0');
    $stok_habis = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    $error = $e->getMessage();
}

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h6 class="card-title">Total Produk</h6>
                        <h3><?php echo $summary['total_produk']; ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h6 class="card-title">Total Stok</h6>
                        <h3><?php echo $summary['total_stok']; ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">Stok Menipis</h6>
                        <h3><?php echo $stok_menipis; ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h6 class="card-title">Stok Habis</h6>
                        <h3><?php echo $stok_habis; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-warehouse"></i> Laporan Stok Produk</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Min</th>
                                <th>Status</th>
                                <th>Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produk_list as $index => $prod): ?>
                            <?php $status = getStatusStok($prod['stok'], $prod['minimal_stok']); ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><code><?php echo htmlspecialchars($prod['kode_produk']); ?></code></td>
                                <td><?php echo htmlspecialchars($prod['nama']); ?></td>
                                <td><?php echo htmlspecialchars($prod['kategori_nama']); ?></td>
                                <td><strong><?php echo $prod['stok']; ?> <?php echo htmlspecialchars($prod['satuan']); ?></strong></td>
                                <td><?php echo $prod['minimal_stok']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status['badge']; ?>">
                                        <?php echo $status['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $prod['total_terjual']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>