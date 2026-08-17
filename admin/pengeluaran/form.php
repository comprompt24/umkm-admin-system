<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Form Pengeluaran';
$id = $_GET['id'] ?? '';
$pengeluaran = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM pengeluaran WHERE id = ?');
    $stmt->execute([$id]);
    $pengeluaran = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pengeluaran) {
        header('Location: index.php');
        exit();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    
    if (empty($tanggal) || empty($kategori) || empty($jumlah)) {
        $error = 'Field yang diperlukan harus diisi';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE pengeluaran SET tanggal = ?, kategori = ?, keterangan = ?, jumlah = ? WHERE id = ?');
                $stmt->execute([$tanggal, $kategori, $keterangan, $jumlah, $id]);
                $success = 'Pengeluaran berhasil diperbarui';
            } else {
                $stmt = $pdo->prepare('INSERT INTO pengeluaran (tanggal, kategori, keterangan, jumlah) VALUES (?, ?, ?, ?)');
                $stmt->execute([$tanggal, $kategori, $keterangan, $jumlah]);
                $success = 'Pengeluaran berhasil ditambahkan';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-arrow-up"></i> <?php echo $id ? 'Edit' : 'Tambah'; ?> Pengeluaran
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" required value="<?php echo htmlspecialchars($pengeluaran['tanggal'] ?? date('Y-m-d')); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Operasional" <?php echo ($pengeluaran['kategori'] ?? '') == 'Operasional' ? 'selected' : ''; ?>>Operasional</option>
                                    <option value="Pembelian" <?php echo ($pengeluaran['kategori'] ?? '') == 'Pembelian' ? 'selected' : ''; ?>>Pembelian</option>
                                    <option value="Lainnya" <?php echo ($pengeluaran['kategori'] ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($pengeluaran['keterangan'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($pengeluaran['jumlah'] ?? ''); ?>">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>