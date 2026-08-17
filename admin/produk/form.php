<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Form Produk';
$id = $_GET['id'] ?? '';
$produk = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
    $stmt->execute([$id]);
    $produk = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$produk) {
        header('Location: index.php');
        exit();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $harga_beli = $_POST['harga_beli'] ?? '';
    $harga_jual = $_POST['harga_jual'] ?? '';
    $stok = $_POST['stok'] ?? '';
    $satuan = $_POST['satuan'] ?? '';
    $minimal_stok = $_POST['minimal_stok'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $status = $_POST['status'] ?? 'Aktif';
    $gambar = $produk['gambar'] ?? '';
    
    if (empty($nama) || empty($kategori_id) || empty($harga_beli) || empty($harga_jual)) {
        $error = 'Field yang diperlukan harus diisi';
    } else {
        try {
            // Handle upload gambar
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
                $gambar_lama = $produk['gambar'] ?? '';
                $gambar = uploadGambar($_FILES['gambar']);
                if ($gambar_lama) {
                    hapusGambar($gambar_lama);
                }
            }
            
            if ($id) {
                // Update
                $stmt = $pdo->prepare('UPDATE produk SET nama = ?, kategori_id = ?, harga_beli = ?, harga_jual = ?, stok = ?, satuan = ?, minimal_stok = ?, gambar = ?, deskripsi = ?, status = ? WHERE id = ?');
                $stmt->execute([$nama, $kategori_id, $harga_beli, $harga_jual, $stok, $satuan, $minimal_stok, $gambar, $deskripsi, $status, $id]);
                $success = 'Produk berhasil diperbarui';
            } else {
                // Generate kode produk
                $kode_produk = generateKodeProduk($pdo);
                
                // Insert
                $stmt = $pdo->prepare('INSERT INTO produk (kode_produk, nama, kategori_id, harga_beli, harga_jual, stok, satuan, minimal_stok, gambar, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$kode_produk, $nama, $kategori_id, $harga_beli, $harga_jual, $stok, $satuan, $minimal_stok, $gambar, $deskripsi, $status]);
                
                // Record initial stock
                $produk_id = $pdo->lastInsertId();
                if ($stok > 0) {
                    $stmt = $pdo->prepare('INSERT INTO stok (produk_id, jenis_perubahan, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$produk_id, 'Stok Masuk', $stok, 0, $stok, 'Stok awal']);
                }
                
                $success = 'Produk berhasil ditambahkan';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get kategori
$stmt = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC');
$kategori_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-box"></i> <?php echo $id ? 'Edit' : 'Tambah'; ?> Produk
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
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($produk['nama'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori_id" class="form-control" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($kategori_list as $k): ?>
                                        <option value="<?php echo $k['id']; ?>" <?php echo ($produk['kategori_id'] ?? '') == $k['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['nama']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Beli <span class="text-danger">*</span></label>
                                    <input type="number" name="harga_beli" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($produk['harga_beli'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                    <input type="number" name="harga_jual" class="form-control" required min="0" step="0.01" value="<?php echo htmlspecialchars($produk['harga_jual'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stok" class="form-control" min="0" value="<?php echo htmlspecialchars($produk['stok'] ?? '0'); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Satuan</label>
                                    <input type="text" name="satuan" class="form-control" placeholder="pcs, kg, liter, dll" value="<?php echo htmlspecialchars($produk['satuan'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Minimal Stok</label>
                                    <input type="number" name="minimal_stok" class="form-control" min="0" value="<?php echo htmlspecialchars($produk['minimal_stok'] ?? '5'); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Gambar Produk</label>
                                    <input type="file" name="gambar" class="form-control" accept="image/*">
                                    <small class="text-muted">Format: JPG, JPEG, PNG, GIF (Max 5MB)</small>
                                    <?php if ($produk && $produk['gambar']): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo BASE_URL; ?>assets/uploads/<?php echo htmlspecialchars($produk['gambar']); ?>" style="max-width: 100px; max-height: 100px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="Aktif" <?php echo ($produk['status'] ?? 'Aktif') == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="Tidak Aktif" <?php echo ($produk['status'] ?? '') == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($produk['deskripsi'] ?? ''); ?></textarea>
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