<div class="navbar-top">
    <div class="navbar-brand-text">
        <i class="fas fa-bars d-md-none me-2" style="cursor: pointer;"></i>
        <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?>
    </div>
    
    <div class="user-menu">
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
                <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/profil/index.php"><i class="fas fa-user"></i> Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>