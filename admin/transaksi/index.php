<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$page_title = 'Transaksi Penjualan';

$error = '';
$success = '';
$cart = [];
$total_cart = 0;

// Get pelanggan umum
$stmt = $pdo->query('SELECT id FROM pelanggan WHERE tipe = "Umum" LIMIT 1');
$pelanggan_umum = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save') {
    try {
        $pelanggan_id = $_POST['pelanggan_id'] ?? $pelanggan_umum['id'];
        $items = json_decode($_POST['items'], true);
        $subtotal = floatval($_POST['subtotal']);
        $diskon = floatval($_POST['diskon'] ?? 0);
        $total = floatval($_POST['total']);
        $jumlah_pembayaran = floatval($_POST['jumlah_pembayaran']);
        $kembalian = floatval($_POST['kembalian'] ?? 0);
        $metode = $_POST['metode'] ?? 'Tunai';

        if (empty($items) || count($items) == 0) {
            throw new Exception('Keranjang tidak boleh kosong');
        }

        if ($jumlah_pembayaran < $total) {
            throw new Exception('Jumlah pembayaran tidak mencukupi');
        }

        // Generate nomor transaksi
        $nomor_transaksi = generateNomorTransaksi($pdo);

        // Begin transaction
        $pdo->beginTransaction();

        // Insert transaksi
        $stmt = $pdo->prepare('INSERT INTO transaksi (nomor_transaksi, pelanggan_id, total_produk, subtotal, diskon, total, jumlah_pembayaran, kembalian, metode_pembayaran, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "Selesai")');
        $stmt->execute([
            $nomor_transaksi,
            $pelanggan_id,
            count($items),
            $subtotal,
            $diskon,
            $total,
            $jumlah_pembayaran,
            $kembalian,
            $metode
        ]);

        $transaksi_id = $pdo->lastInsertId();

        // Insert detail transaksi dan update stok
        foreach ($items as $item) {
            $produk_id = $item['id'];
            $jumlah = $item['qty'];
            $harga_satuan = $item['harga'];
            $harga_total = $item['total'];

            // Insert detail
            $stmt = $pdo->prepare('INSERT INTO detail_transaksi (transaksi_id, produk_id, jumlah, harga_satuan, harga_total) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$transaksi_id, $produk_id, $jumlah, $harga_satuan, $harga_total]);

            // Update stok
            $stmt = $pdo->prepare('SELECT stok FROM produk WHERE id = ?');
            $stmt->execute([$produk_id]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            $stok_sebelum = $prod['stok'];
            $stok_sesudah = $stok_sebelum - $jumlah;

            $stmt = $pdo->prepare('UPDATE produk SET stok = ? WHERE id = ?');
            $stmt->execute([$stok_sesudah, $produk_id]);

            // Record stok history
            $stmt = $pdo->prepare('INSERT INTO stok (produk_id, jenis_perubahan, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$produk_id, 'Stok Keluar', $jumlah, $stok_sebelum, $stok_sesudah, 'Penjualan ' . $nomor_transaksi]);
        }

        // Insert pemasukan
        $stmt = $pdo->prepare('INSERT INTO pemasukan (tanggal, sumber, keterangan, jumlah, tipe) VALUES (?, ?, ?, ?, "Transaksi")');
        $stmt->execute([date('Y-m-d'), 'Penjualan', 'Transaksi ' . $nomor_transaksi, $total]);

        $pdo->commit();

        $success = 'Transaksi berhasil disimpan: ' . $nomor_transaksi;
        // Redirect untuk menghindari double submit
        header('Location: riwayat.php?success=' . urlencode($success));
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get pelanggan untuk dropdown
$stmt = $pdo->query('SELECT id, nama FROM pelanggan WHERE tipe = "Regular" ORDER BY nama ASC');
$pelanggan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get produk aktif
$stmt = $pdo->query('SELECT p.*, k.nama as kategori_nama FROM produk p JOIN kategori k ON p.kategori_id = k.id WHERE p.status = "Aktif" AND p.stok > 0 ORDER BY p.nama ASC');
$produk_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="main-wrapper">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="content-wrapper">
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
                        <i class="fas fa-shopping-cart"></i> Buat Transaksi Penjualan
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Pelanggan</label>
                                <select id="pelanggan_id" class="form-control">
                                    <option value="<?php echo $pelanggan_umum['id']; ?>">Pelanggan Umum</option>
                                    <?php foreach ($pelanggan_list as $pel): ?>
                                    <option value="<?php echo $pel['id']; ?>"><?php echo htmlspecialchars($pel['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Produk</label>
                                <select id="produk_select" class="form-control">
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($produk_list as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>" data-nama="<?php echo htmlspecialchars($prod['nama']); ?>" data-harga="<?php echo $prod['harga_jual']; ?>" data-stok="<?php echo $prod['stok']; ?>">
                                        <?php echo htmlspecialchars($prod['nama']); ?> - <?php echo formatRupiah($prod['harga_jual']); ?> (Stok: <?php echo $prod['stok']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Jumlah</label>
                                <input type="number" id="qty_input" class="form-control" min="1" value="1">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100" onclick="addToCart()">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm" id="cartTable">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga Satuan</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header" style="background: #28a745; color: white;">
                        <i class="fas fa-receipt"></i> Ringkasan Transaksi
                    </div>
                    <div class="card-body">
                        <form method="POST" id="transactionForm">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="pelanggan_id" id="form_pelanggan_id">
                            <input type="hidden" name="items" id="form_items" value="[]">
                            
                            <div class="mb-3">
                                <label class="form-label">Subtotal</label>
                                <div class="form-control" style="background: #f9f9f9; border: none;">
                                    <strong id="subtotal_display">Rp 0</strong>
                                </div>
                                <input type="hidden" name="subtotal" id="form_subtotal" value="0">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Diskon</label>
                                <input type="number" name="diskon" id="diskon_input" class="form-control" min="0" value="0" onchange="updateTotal()">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Total</label>
                                <div class="form-control" style="background: #f9f9f9; border: none;">
                                    <strong id="total_display" style="font-size: 18px; color: #28a745;">Rp 0</strong>
                                </div>
                                <input type="hidden" name="total" id="form_total" value="0">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="metode" class="form-control">
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Jumlah Pembayaran</label>
                                <input type="number" name="jumlah_pembayaran" id="jumlah_bayar" class="form-control" min="0" step="0.01" value="0" onchange="updateKembalian()" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kembalian</label>
                                <div class="form-control" style="background: #f9f9f9; border: none;">
                                    <strong id="kembalian_display">Rp 0</strong>
                                </div>
                                <input type="hidden" name="kembalian" id="form_kembalian" value="0">
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 mb-2" onclick="return validateTransaction()">
                                <i class="fas fa-check"></i> Simpan Transaksi
                            </button>
                            <a href="riwayat.php" class="btn btn-secondary w-100">
                                <i class="fas fa-history"></i> Lihat Riwayat
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar.php'; ?>
<?php include '../../includes/footer.php'; ?>

<script>
let cart = [];

function addToCart() {
    const produk_id = document.getElementById('produk_select').value;
    const qty = parseInt(document.getElementById('qty_input').value) || 1;
    
    if (!produk_id) {
        alert('Pilih produk terlebih dahulu');
        return;
    }
    
    const option = document.querySelector(`#produk_select option[value="${produk_id}"]`);
    const nama = option.getAttribute('data-nama');
    const harga = parseFloat(option.getAttribute('data-harga'));
    const stok = parseInt(option.getAttribute('data-stok'));
    
    if (qty > stok) {
        alert('Stok tidak mencukupi');
        return;
    }
    
    const existing = cart.find(item => item.id == produk_id);
    if (existing) {
        if (existing.qty + qty > stok) {
            alert('Stok tidak mencukupi');
            return;
        }
        existing.qty += qty;
    } else {
        cart.push({id: produk_id, nama: nama, harga: harga, qty: qty, total: harga * qty});
    }
    
    document.getElementById('produk_select').value = '';
    document.getElementById('qty_input').value = '1';
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id != id);
    renderCart();
}

function updateQty(id, qty) {
    qty = parseInt(qty);
    if (qty <= 0) {
        removeFromCart(id);
        return;
    }
    
    const option = document.querySelector(`#produk_select option[value="${id}"]`);
    const stok = parseInt(option.getAttribute('data-stok'));
    
    if (qty > stok) {
        alert('Stok tidak mencukupi');
        return;
    }
    
    const item = cart.find(i => i.id == id);
    if (item) {
        item.qty = qty;
        item.total = item.harga * qty;
        renderCart();
    }
}

function renderCart() {
    const tbody = document.getElementById('cartBody');
    tbody.innerHTML = '';
    
    let subtotal = 0;
    
    cart.forEach(item => {
        const row = `<tr>
            <td>${item.nama}</td>
            <td>${formatRupiah(item.harga)}</td>
            <td><input type="number" class="form-control form-control-sm" style="width: 70px;" value="${item.qty}" onchange="updateQty(${item.id}, this.value)"></td>
            <td>${formatRupiah(item.total)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})"><i class="fas fa-trash"></i></button></td>
        </tr>`;
        tbody.innerHTML += row;
        subtotal += item.total;
    });
    
    document.getElementById('form_subtotal').value = subtotal;
    document.getElementById('subtotal_display').textContent = formatRupiah(subtotal);
    document.getElementById('form_items').value = JSON.stringify(cart);
    document.getElementById('form_pelanggan_id').value = document.getElementById('pelanggan_id').value;
    
    updateTotal();
}

function updateTotal() {
    const subtotal = parseFloat(document.getElementById('form_subtotal').value);
    const diskon = parseFloat(document.getElementById('diskon_input').value) || 0;
    const total = subtotal - diskon;
    
    document.getElementById('form_total').value = total;
    document.getElementById('total_display').textContent = formatRupiah(total);
    
    updateKembalian();
}

function updateKembalian() {
    const total = parseFloat(document.getElementById('form_total').value);
    const bayar = parseFloat(document.getElementById('jumlah_bayar').value) || 0;
    const kembalian = bayar - total;
    
    document.getElementById('form_kembalian').value = kembalian;
    document.getElementById('kembalian_display').textContent = formatRupiah(kembalian);
}

function validateTransaction() {
    if (cart.length === 0) {
        alert('Keranjang tidak boleh kosong');
        return false;
    }
    
    const total = parseFloat(document.getElementById('form_total').value);
    const bayar = parseFloat(document.getElementById('jumlah_bayar').value);
    
    if (bayar < total) {
        alert('Jumlah pembayaran tidak mencukupi');
        return false;
    }
    
    return confirm('Simpan transaksi ini?');
}
</script>