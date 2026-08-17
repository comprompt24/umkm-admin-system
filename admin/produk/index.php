<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Produk';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Delete
if ($action == 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('SELECT gambar FROM produk WHERE id = ?');
        $stmt->execute([$id]);
        $produk = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produk) {
            hapusGambar($produk['gambar']);
        }
        
        $stmt = $pdo->prepare('DELETE FROM produk WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: index.php?success=Produk berhasil dihapus');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get data
$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $where = [];
    $params = [];
    
    if ($search) {
        $where[] = '(p.nama LIKE ? OR p.kode_produk LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    if ($kategori) {
        $where[] = 'p.kategori_id = ?';
        $params[] = $kategori;
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM produk p ' . $where_sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT p.*, k.nama as kategori_nama FROM produk p JOIN kategori k ON p.kategori_id = k.id ' . $where_sql . ' ORDER BY p.created_at DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get kategori for filter
    $stmt = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC');
    $kategori_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <span><i class="fas fa-box"></i> Daftar Produk</span>
                    <a href="form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search" class="form-control" placeholder="Cari produk...">
                    </div>
                    <div class="col-md-4">
                        <select id="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($kategori_list as $k): ?>
                            <option value="<?php echo $k['id']; ?>" <?php echo $kategori == $k['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k['nama']); ?>
                            </option>
                            <?php endforeach; ?>
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
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produk_list)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada data produk</td></tr>
                            <?php else: ?>
                            <?php foreach ($produk_list as $index => $prod): ?>
                            <?php $stock_status = getStatusStok($prod['stok'], $prod['minimal_stok']); ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td><code><?php echo htmlspecialchars($prod['kode_produk']); ?></code></td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($prod['nama']); ?></strong>
                                        <?php if ($prod['gambar']): ?>
                                        <br><img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo htmlspecialchars($prod['gambar']); ?>" style="max-width: 50px; max-height: 50px;" class="mt-1">
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($prod['kategori_nama']); ?></td>
                                <td><?php echo formatRupiah($prod['harga_jual']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $stock_status['badge']; ?>">
                                        <?php echo $prod['stok']; ?> <?php echo htmlspecialchars($prod['satuan']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $prod['status'] == 'Aktif' ? 'success' : 'secondary'; ?>">
                                        <?php echo $prod['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="form.php?id=<?php echo $prod['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $prod['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_page > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_page; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&kategori=<?php echo urlencode($kategori); ?>"><?php echo $i; ?></a>
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
    window.location.href = '?search=' + encodeURIComponent(search) + '&kategori=' + encodeURIComponent(kategori);
}
</script>