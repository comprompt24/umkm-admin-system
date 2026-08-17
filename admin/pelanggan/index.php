<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Pelanggan';

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Delete
if ($action == 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM pelanggan WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: index.php?success=Pelanggan berhasil dihapus');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $where = 'WHERE tipe = "Regular"';
    $params = [];
    
    if ($search) {
        $where .= ' AND (nama LIKE ? OR nomor_hp LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    // Total
    $sql = 'SELECT COUNT(*) as total FROM pelanggan ' . $where;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_page = ceil($total / $limit);
    
    // Data
    $sql = 'SELECT * FROM pelanggan ' . $where . ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
    $stmt = $pdo->prepare($sql);
    $exec_params = array_merge($params, [$limit, $offset]);
    $stmt->execute($exec_params);
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users"></i> Daftar Pelanggan</span>
                    <a href="form.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Pelanggan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama atau nomor HP..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>No</th>
                                <th>Nama</th>
                                <th>Nomor HP</th>
                                <th>Email</th>
                                <th>Alamat</th>
                                <th>Tanggal Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pelanggan_list)): ?>
                            <tr><td colspan="7" class="text-center text-muted">Tidak ada data pelanggan</td></tr>
                            <?php else: ?>
                            <?php foreach ($pelanggan_list as $index => $pel): ?>
                            <tr>
                                <td><?php echo $offset + $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($pel['nama']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pel['nomor_hp'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($pel['email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars(substr($pel['alamat'] ?? '', 0, 30)); ?></td>
                                <td><?php echo formatTanggal($pel['created_at']); ?></td>
                                <td>
                                    <a href="form.php?id=<?php echo $pel['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?action=delete&id=<?php echo $pel['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">
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
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
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