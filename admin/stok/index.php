<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Manajemen Stok';

$action = $_GET['action'] ?? '';
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$error = '';
$success = '';

// Handle Adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'adjust') {
    $produk_id = $_POST['produk_id'] ?? '';
    $jenis = $_POST['jenis'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($produk_id) || empty($jenis) || empty($jumlah)) {
        $error = 'Field yang diperlukan harus diisi';
    } else {
        try {
            // Get current stock
            $stmt = $pdo->prepare('SELECT stok FROM produk WHERE id = ?');
            $stmt->execute([$produk_id]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            $stok_sebelum = $prod['stok'];
            
            // Calculate new stock
            if ($jenis == 'Stok Masuk') {
                $stok_sesudah = $stok_sebelum + $jumlah;
            } else {
                if ($stok_sebelum < $jumlah) {
                    throw new Exception('Stok tidak mencukupi');
                }
                $stok_sesudah = $stok_sebelum - $jumlah;
            }
            
            // Update stock
            $stmt = $pdo->prepare('UPDATE produk SET stok = ? WHERE id = ?');
            $stmt->execute([$stok_sesudah, $produk_id]);
            
            // Record in stok table
            $stmt = $pdo->prepare('INSERT INTO stok (produk_id, jenis_perubahan, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$produk_id, $jenis, $jumlah, $stok_sebelum, $stok_sesudah, $keterangan]);
            
            $success = 'Stok berhasil diubah';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    $where = '';
    $params = [];
    
    if ($search) {
        $where = 'WHERE p.nama LIKE ? OR p.kode_produk LIKE ?';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM produk p ' . $where;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT p.*, k.nama as kategori_nama FROM produk p JOIN kategori k ON p.kategori_id = k.id ' . $where . ' ORDER BY p.nama ASC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
    $produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-warehouse"></i> Daftar Stok Produk
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" class="d-flex gap-2">
                                    <input type="text" name="search" class="form-control" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Produk</th>
                                        <th>Nama Produk</th>
                                        <th>Stok</th>
                                        <th>Min</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($produk_list)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Tidak ada produk</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($produk_list as $prod): ?>
                                    <?php $stock_status = getStatusStok($prod['stok'], $prod['minimal_stok']); ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($prod['kode_produk']); ?></code></td>
                                        <td><?php echo htmlspecialchars($prod['nama']); ?></td>
                                        <td><strong><?php echo $prod['stok']; ?> <?php echo htmlspecialchars($prod['satuan']); ?></strong></td>
                                        <td><?php echo $prod['minimal_stok']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $stock_status['badge']; ?>">
                                                <?php echo $stock_status['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" onclick="showAdjustModal(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['nama']); ?>')">
                                                <i class="fas fa-edit"></i> Atur
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
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header" style="background: #17a2b8; color: white;">
                        <i class="fas fa-history"></i> Riwayat Stok (Terbaru)
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th style="font-size: 12px;">Produk</th>
                                        <th style="font-size: 12px;">Jenis</th>
                                        <th style="font-size: 12px;">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $stmt = $pdo->query('SELECT s.*, p.nama FROM stok s JOIN produk p ON s.produk_id = p.id ORDER BY s.created_at DESC LIMIT 10');
                                    $riwayat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php if (empty($riwayat)): ?>
                                    <tr><td colspan="3" class="text-center text-muted">Tidak ada riwayat</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($riwayat as $r): ?>
                                    <tr style="font-size: 12px;">
                                        <td><?php echo htmlspecialchars(substr($r['nama'], 0, 15)); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $r['jenis_perubahan'] == 'Stok Masuk' ? 'success' : 'danger'; ?>">
                                                <?php echo substr($r['jenis_perubahan'], 0, 8); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $r['jumlah']; ?></td>
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

<!-- Modal Adjust -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Penyesuaian Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="adjust">
                    <input type="hidden" name="produk_id" id="produk_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Perubahan</label>
                        <select name="jenis" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="Stok Masuk">Stok Masuk</option>
                            <option value="Stok Keluar">Stok Keluar</option>
                            <option value="Penyesuaian">Penyesuaian</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
function showAdjustModal(id, nama) {
    document.getElementById('produk_id').value = id;
    document.getElementById('modalTitle').textContent = 'Penyesuaian Stok: ' + nama;
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}
</script>