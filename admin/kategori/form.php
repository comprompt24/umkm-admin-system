<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Form Kategori';
$id = $_GET['id'] ?? '';
$kategori = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id = ?');
    $stmt->execute([$id]);
    $kategori = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kategori) {
        header('Location: index.php');
        exit();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($nama)) {
        $error = 'Nama kategori harus diisi';
    } else {
        try {
            if ($id) {
                // Update
                $stmt = $pdo->prepare('UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?');
                $stmt->execute([$nama, $deskripsi, $id]);
                $success = 'Kategori berhasil diperbarui';
            } else {
                // Insert
                $stmt = $pdo->prepare('INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)');
                $stmt->execute([$nama, $deskripsi]);
                $success = 'Kategori berhasil ditambahkan';
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = 'Nama kategori sudah ada';
            } else {
                $error = $e->getMessage();
            }
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
                        <i class="fas fa-list"></i> <?php echo $id ? 'Edit' : 'Tambah'; ?> Kategori
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
                                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($kategori['nama'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($kategori['deskripsi'] ?? ''); ?></textarea>
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