<?php
include '../../config/database.php';
include '../../auth/auth_check.php';
include '../../includes/functions.php';

$id = $_GET['id'] ?? '';

if (!$id) {
    echo 'Transaksi tidak ditemukan';
    exit();
}

try {
    $stmt = $pdo->prepare('SELECT t.*, p.nama as pelanggan_nama, p.nomor_hp FROM transaksi t LEFT JOIN pelanggan p ON t.pelanggan_id = p.id WHERE t.id = ?');
    $stmt->execute([$id]);
    $trx = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trx) {
        echo 'Transaksi tidak ditemukan';
        exit();
    }
    
    $stmt = $pdo->prepare('SELECT dt.*, p.nama as produk_nama FROM detail_transaksi dt JOIN produk p ON dt.produk_id = p.id WHERE dt.transaksi_id = ?');
    $stmt->execute([$id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>

<style>
    .struk { font-family: 'Courier New', monospace; }
</style>

<div class="struk">
    <h5 style="text-align: center; margin: 0;">STRUK PENJUALAN</h5>
    <hr style="margin: 5px 0;">
    
    <div style="font-size: 13px;">
        <p style="margin: 3px 0;"><strong>No. Transaksi:</strong> <?php echo htmlspecialchars($trx['nomor_transaksi']); ?></p>
        <p style="margin: 3px 0;"><strong>Tanggal:</strong> <?php echo formatWaktu($trx['created_at']); ?></p>
        <p style="margin: 3px 0;"><strong>Pelanggan:</strong> <?php echo htmlspecialchars($trx['pelanggan_nama'] ?? 'Umum'); ?></p>
        <p style="margin: 3px 0;"><strong>Metode:</strong> <?php echo htmlspecialchars($trx['metode_pembayaran']); ?></p>
    </div>
    
    <hr style="margin: 5px 0;">
    
    <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">Produk</th>
                <th style="text-align: right; width: 50px;">Qty</th>
                <th style="text-align: right; width: 80px;">Harga</th>
                <th style="text-align: right; width: 80px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $detail): ?>
            <tr style="border-bottom: 1px solid #ccc;">
                <td><?php echo htmlspecialchars(substr($detail['produk_nama'], 0, 20)); ?></td>
                <td style="text-align: right;"><?php echo $detail['jumlah']; ?></td>
                <td style="text-align: right;"><?php echo formatRupiah($detail['harga_satuan']); ?></td>
                <td style="text-align: right;"><?php echo formatRupiah($detail['harga_total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <hr style="margin: 5px 0;">
    
    <div style="font-size: 13px; text-align: right;">
        <p style="margin: 3px 0;"><strong>Subtotal:</strong> <?php echo formatRupiah($trx['subtotal']); ?></p>
        <?php if ($trx['diskon'] > 0): ?>
        <p style="margin: 3px 0;"><strong>Diskon:</strong> -<?php echo formatRupiah($trx['diskon']); ?></p>
        <?php endif; ?>
        <p style="margin: 3px 0; font-size: 16px; border-top: 1px solid #000; padding-top: 5px;"><strong>Total:</strong> <?php echo formatRupiah($trx['total']); ?></p>
        <p style="margin: 3px 0;"><strong>Pembayaran:</strong> <?php echo formatRupiah($trx['jumlah_pembayaran']); ?></p>
        <p style="margin: 3px 0;"><strong>Kembalian:</strong> <?php echo formatRupiah($trx['kembalian']); ?></p>
    </div>
    
    <hr style="margin: 5px 0;">
    <p style="text-align: center; font-size: 12px; margin: 10px 0; color: #666;">
        Terima kasih telah berbelanja
    </p>
</div>