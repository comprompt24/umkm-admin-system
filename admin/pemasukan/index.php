<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Pemasukan';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Delete
if ($action == 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM pemasukan WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: index.php?success=Pemasukan berhasil dihapus');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$sumber = $_GET['sumber'] ?? '';
$bulan = $_GET['bulan'] ?? date('Y-m');
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $where = [];
    $params = [];
    
    if ($search) {
        $where[] = 'keterangan LIKE ?';
        $params[] = '%' . $search . '%';
    }
    
    if ($sumber) {
        $where[] = 'sumber = ?';
        $params[] = $sumber;
    }
    
    if ($bulan) {
        $where[] = 'DATE_FORMAT(tanggal, "%Y-%m") = ?';
        $params[] = $bulan;
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM pemasukan ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT * FROM pemasukan ' . $where_sql . ' ORDER BY tanggal DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
    $pemasukan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total pemasukan
    $sql = 'SELECT COALESCE(SUM(jumlah), 0) as total FROM pemasukan ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total_pemasukan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-arrow-down"></i> Daftar Pemasukan</span>
                    <a href="form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pemasukan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" id="search" class="form-control" placeholder="Cari keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select id="sumber" class="form-control">
                            <option value="">Semua Sumber</option>
                            <option value="Penjualan" <?php echo $sumber == 'Penjualan' ? 'selected' : ''; ?>>Penjualan</option>
                            <option value="Lainnya" <?php echo $sumber == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="month" id="bulan" class="form-control" value="<?php echo htmlspecialchars($bulan); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary" onclick="applyFilter()">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <strong>Total Pemasukan:</strong> <?php echo formatRupiah($total_pemasukan); ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Sumber</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pemasukan_list)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data pemasukan</td></tr>
                            <?php else: ?>
                            <?php foreach ($pemasukan_list as $index => $pem): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td><?php echo formatTanggal($pem['tanggal']); ?></td>
                                <td>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($pem['sumber']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($pem['keterangan']); ?></td>
                                <td><strong><?php echo formatRupiah($pem['jumlah']); ?></strong></td>
                                <td>
                                    <a href="form.php?id=<?php echo $pem['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $pem['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
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
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sumber=<?php echo urlencode($sumber); ?>&bulan=<?php echo urlencode($bulan); ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
function applyFilter() {
    const search = document.getElementById('search').value;
    const sumber = document.getElementById('sumber').value;
    const bulan = document.getElementById('bulan').value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&sumber=' + encodeURIComponent(sumber) + '&bulan=' + encodeURIComponent(bulan);
}
</script>