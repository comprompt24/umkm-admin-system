<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Pengeluaran';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Delete
if ($action == 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM pengeluaran WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: index.php?success=Pengeluaran berhasil dihapus');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';
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
    
    if ($kategori) {
        $where[] = 'kategori = ?';
        $params[] = $kategori;
    }
    
    if ($bulan) {
        $where[] = 'DATE_FORMAT(tanggal, "%Y-%m") = ?';
        $params[] = $bulan;
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM pengeluaran ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT * FROM pengeluaran ' . $where_sql . ' ORDER BY tanggal DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
    $pengeluaran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Total pengeluaran
    $sql = 'SELECT COALESCE(SUM(jumlah), 0) as total FROM pengeluaran ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total_pengeluaran = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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
                    <span><i class="fas fa-arrow-up"></i> Daftar Pengeluaran</span>
                    <a href="form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pengeluaran
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" id="search" class="form-control" placeholder="Cari keterangan..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <select id="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <option value="Operasional" <?php echo $kategori == 'Operasional' ? 'selected' : ''; ?>>Operasional</option>
                            <option value="Pembelian" <?php echo $kategori == 'Pembelian' ? 'selected' : ''; ?>>Pembelian</option>
                            <option value="Lainnya" <?php echo $kategori == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
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
                
                <div class="alert alert-danger">
                    <strong>Total Pengeluaran:</strong> <?php echo formatRupiah($total_pengeluaran); ?>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pengeluaran_list)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data pengeluaran</td></tr>
                            <?php else: ?>
                            <?php foreach ($pengeluaran_list as $index => $peng): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td><?php echo formatTanggal($peng['tanggal']); ?></td>
                                <td>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars($peng['kategori']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($peng['keterangan']); ?></td>
                                <td><strong><?php echo formatRupiah($peng['jumlah']); ?></strong></td>
                                <td>
                                    <a href="form.php?id=<?php echo $peng['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $peng['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
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
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($kategori); ?>&bulan=<?php echo urlencode($bulan); ?>"><?php echo $i; ?></a>
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
    const kategori = document.getElementById('kategori').value;
    const bulan = document.getElementById('bulan').value;
    window.location.href = '?search=' + encodeURIComponent(search) + '&kategori=' + encodeURIComponent(kategori) + '&bulan=' + encodeURIComponent(bulan);
}
</script>