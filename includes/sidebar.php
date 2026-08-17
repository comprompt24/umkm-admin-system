<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-chart-line"></i> UMKM Admin
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/produk/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'produk') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Produk
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/kategori/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'kategori') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Kategori
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/stok/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'stok') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-warehouse"></i> Stok
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/transaksi/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'transaksi') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cash-register"></i> Transaksi
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/pelanggan/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'pelanggan') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Pelanggan
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/pemasukan/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'pemasukan') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-arrow-down"></i> Pemasukan
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/pengeluaran/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'pengeluaran') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-arrow-up"></i> Pengeluaran
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/laporan/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'laporan') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Laporan
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>admin/profil/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'profil') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i> Profil
            </a>
        </li>
        <li style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="<?php echo BASE_URL; ?>logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>