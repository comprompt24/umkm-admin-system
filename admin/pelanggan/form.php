<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Form Pelanggan';
$id = $_GET['id'] ?? '';
$pelanggan = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM pelanggan WHERE id = ?');
    $stmt->execute([$id]);
    $pelanggan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pelanggan) {
        header('Location: index.php');
        exit();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $nomor_hp = $_POST['nomor_hp'] ?? '';
    $email = $_POST['email'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    
    if (empty($nama)) {
        $error = 'Nama pelanggan harus diisi';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE pelanggan SET nama = ?, nomor_hp = ?, email = ?, alamat = ?, catatan = ? WHERE id = ?');
                $stmt->execute([$nama, $nomor_hp, $email, $alamat, $catatan, $id]);
                $success = 'Pelanggan berhasil diperbarui';
            } else {
                $stmt = $pdo->prepare('INSERT INTO pelanggan (nama, nomor_hp, email, alamat, catatan, tipe) VALUES (?, ?, ?, ?, ?, "Regular")');
                $stmt->execute([$nama, $nomor_hp, $email, $alamat, $catatan]);
                $success = 'Pelanggan berhasil ditambahkan';
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
                        <i class="fas fa-user"></i> <?php echo $id ? 'Edit' : 'Tambah'; ?> Pelanggan
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
                                <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($pelanggan['nama'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nomor HP</label>
                                <input type="tel" name="nomor_hp" class="form-control" value="<?php echo htmlspecialchars($pelanggan['nomor_hp'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($pelanggan['email'] ?? ''); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($pelanggan['alamat'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2"><?php echo htmlspecialchars($pelanggan['catatan'] ?? ''); ?></textarea>
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