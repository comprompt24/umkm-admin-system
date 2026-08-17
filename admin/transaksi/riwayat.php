<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Riwayat Transaksi';

$search = $_GET['search'] ?? '';
$pelanggan = $_GET['pelanggan'] ?? '';
$metode = $_GET['metode'] ?? '';
$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $where = ['t.status = "Selesai"'];
    $params = [];
    
    if ($search) {
        $where[] = 't.nomor_transaksi LIKE ?';
        $params[] = '%' . $search . '%';
    }
    
    if ($pelanggan) {
        $where[] = 't.pelanggan_id = ?';
        $params[] = $pelanggan;
    }
    
    if ($metode) {
        $where[] = 't.metode_pembayaran = ?';
        $params[] = $metode;
    }
    
    if ($tanggal_dari) {
        $where[] = 'DATE(t.created_at) >= ?';
        $params[] = $tanggal_dari;
    }
    
    if ($tanggal_sampai) {
        $where[] = 'DATE(t.created_at) <= ?';
        $params[] = $tanggal_sampai;
    }
    
    $where_sql = 'WHERE ' . implode(' AND ', $where);
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM transaksi t ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT t.*, p.nama as pelanggan_nama FROM transaksi t LEFT JOIN pelanggan p ON t.pelanggan_id = p.id ' . $where_sql . ' ORDER BY t.created_at DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
    $transaksi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get pelanggan untuk filter
    $stmt = $pdo->query('SELECT id, nama FROM pelanggan WHERE tipe = "Regular" ORDER BY nama ASC');
    $pelanggan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history"></i> Riwayat Transaksi Penjualan</span>
                    <a href="index.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Transaksi Baru
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" id="search" class="form-control" placeholder="No. Transaksi..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select id="pelanggan" class="form-control">
                            <option value="">Semua Pelanggan</option>
                            <?php foreach ($pelanggan_list as $pel): ?>
                            <option value="<?php echo $pel['id']; ?>" <?php echo $pelanggan == $pel['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pel['nama']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="metode" class="form-control">
                            <option value="">Semua Metode</option>
                            <option value="Tunai" <?php echo $metode == 'Tunai' ? 'selected' : ''; ?>>Tunai</option>
                            <option value="Transfer" <?php echo $metode == 'Transfer' ? 'selected' : ''; ?>>Transfer</option>
                            <option value="E-Wallet" <?php echo $metode == 'E-Wallet' ? 'selected' : ''; ?>>E-Wallet</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-primary" onclick="applyFilter()">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transaksi_list)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada transaksi</td></tr>
                            <?php else: ?>
                            <?php foreach ($transaksi_list as $trx): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($trx['nomor_transaksi']); ?></code></td>
                                <td><?php echo formatWaktu($trx['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($trx['pelanggan_nama'] ?? 'Umum'); ?></td>
                                <td><strong><?php echo formatRupiah($trx['total']); ?></strong></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($trx['metode_pembayaran']); ?></span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" onclick="showDetail(<?php echo $trx['id']; ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_page > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_page; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&pelanggan=<?php echo urlencode($pelanggan); ?>&metode=<?php echo urlencode($metode); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printDetail()">
                    <i class="fas fa-print"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
function applyFilter() {
    const search = document.getElementById('search').value;
    const pelanggan = document.getElementById('pelanggan').value;
    const metode = document.getElementById('metode').value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&pelanggan=' + encodeURIComponent(pelanggan) + '&metode=' + encodeURIComponent(metode);
}

function showDetail(id) {
    fetch('detail.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        });
}

function printDetail() {
    const content = document.getElementById('detailContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Struk Penjualan</title></head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>