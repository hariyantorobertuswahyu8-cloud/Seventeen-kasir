<aside id="sidebar">
    <a href="<?= BASE_URL ?>dashboard.php" class="brand-logo">
        <i class="bi bi-shop"></i>
        <span><?= htmlspecialchars($toko['nama_toko']) ?></span>
    </a>

    <div class="py-2">
        <div class="nav-section">Utama</div>
        <a href="<?= BASE_URL ?>dashboard.php" class="nav-link <?= ($activeMenu === 'dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section">Data Master</div>
        <a href="<?= BASE_URL ?>module/barang/index.php" class="nav-link <?= ($activeMenu === 'barang') ? 'active' : '' ?>">
            <i class="bi bi-box-seam-fill"></i>
            <span>Data Barang & Stok</span>
        </a>
        <a href="<?= BASE_URL ?>module/kategori/index.php" class="nav-link <?= ($activeMenu === 'kategori') ? 'active' : '' ?>">
            <i class="bi bi-tags-fill"></i>
            <span>Kategori Produk</span>
        </a>

        <div class="nav-section">Transaksi Kasir</div>
        <a href="<?= BASE_URL ?>module/transaksi/index.php" class="nav-link <?= ($activeMenu === 'transaksi') ? 'active' : '' ?>">
            <i class="bi bi-cart-check-fill"></i>
            <span>Transaksi Jual (POS)</span>
        </a>

        <div class="nav-section">Laporan & Rekap</div>
        <a href="<?= BASE_URL ?>module/laporan/index.php" class="nav-link <?= ($activeMenu === 'laporan') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph-fill"></i>
            <span>Laporan Penjualan</span>
        </a>

        <div class="nav-section">Pengaturan</div>
        <a href="<?= BASE_URL ?>module/toko/index.php" class="nav-link <?= ($activeMenu === 'toko') ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i>
            <span>Pengaturan Toko</span>
        </a>
        <a href="<?= BASE_URL ?>logout.php" class="nav-link text-danger mt-2" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem kasir?')">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar (Logout)</span>
        </a>
    </div>
</aside>

