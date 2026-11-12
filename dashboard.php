<?php
$pageTitle = 'Dashboard Utama';
$activeMenu = 'dashboard';
require_once __DIR__ . '/includes/header.php';

// Metrik 1: Total Barang & Kategori
$totalBarang = $pdo->query("SELECT COUNT(*) FROM barang")->fetchColumn();
$totalKategori = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();

// Metrik 2: Penjualan Hari Ini
$todayStr = date('d F Y'); // Format tanggal jurnal atau LIKE
$todaySql = date('Y-m-d');
$stmtHariIni = $pdo->prepare("SELECT COUNT(DISTINCT no_transaksi) as total_trx, COALESCE(SUM(total), 0) as total_omzet FROM nota WHERE tanggal_input LIKE :tgl1 OR tanggal_input LIKE :tgl2");
$stmtHariIni->execute([
    'tgl1' => "%" . date('j F Y') . "%",
    'tgl2' => "%" . date('Y-m-d') . "%"
]);
$trxHariIni = $stmtHariIni->fetch();

// Metrik 3: Barang Menipis & Habis
$stmtAlert = $pdo->query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.stok <= b.stok_minimum ORDER BY b.stok ASC");
$alertBarang = $stmtAlert->fetchAll();

// Metrik 4: Transaksi Terakhir
$stmtRecent = $pdo->query("
    SELECT n.no_transaksi, n.tanggal_input, m.nm_member, 
           COUNT(n.id_nota) as total_item, SUM(n.total) as grand_total 
    FROM nota n 
    LEFT JOIN member m ON n.id_member = m.id_member 
    GROUP BY n.no_transaksi, n.tanggal_input, m.nm_member 
    ORDER BY n.id_nota DESC 
    LIMIT 5
");
$recentTrx = $stmtRecent->fetchAll();
?>

<!-- Welcome Banner -->
<div class="card card-custom mb-4 bg-gradient border-3" style="background: linear-gradient(135deg, #10b981 30%, #059669 100%); color: blue;">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-emoji-smile me-2"></i>Selamat Datang, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Kasir') ?>!</h4>
            <p class="mb-0 opacity-90">Sistem Kasir & Manajemen Stok <?= htmlspecialchars($toko['nama_toko']) ?> siap melayani transaksi.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>module/transaksi/index.php" class="btn btn-light text-success fw-bold px-4 py-2 shadow-sm rounded-pill">
                <i class="bi bi-cart-plus-fill me-1"></i> Buka Kasir (POS)
            </a>
            <a href="<?= BASE_URL ?>module/barang/index.php?action=tambah" class="btn btn-outline-light fw-bold px-3 py-2 rounded-pill">
                <i class="bi bi-plus-circle me-1"></i> Tambah Barang
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Produk</span>
                    <h3 class="fw-bold my-1 text-dark"><?= number_format($totalBarang) ?></h3>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= $totalKategori ?> Kategori</span>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-3">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Transaksi Hari Ini</span>
                    <h3 class="fw-bold my-1 text-dark"><?= number_format($trxHariIni['total_trx']) ?></h3>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai Checkout</span>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle fs-3">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Omzet Hari Ini</span>
                    <h3 class="fw-bold my-1 text-dark"><?= rupiah($trxHariIni['total_omzet']) ?></h3>
                    <span class="badge bg-info-subtle text-info border border-info-subtle"><?= date('d M Y') ?></span>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle fs-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Alert Stok Kurang</span>
                    <h3 class="fw-bold my-1 <?= count($alertBarang) > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= count($alertBarang) ?>
                    </h3>
                    <span class="badge <?= count($alertBarang) > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success' ?>">
                        <?= count($alertBarang) > 0 ? 'Perlu Restock!' : 'Stok Aman' ?>
                    </span>
                </div>
                <div class="<?= count($alertBarang) > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> p-3 rounded-circle fs-3">
                    <i class="bi bi-exclamation-octagon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Stok Menipis (Fitur Kritis Jurnal & Aturan Bisnis Step 8) -->
<?php if (!empty($alertBarang)): ?>
<div class="card card-custom border-start border-warning border-4 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill text-warning fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Pemberitahuan Sistem: Produk Mendekati Stok Minimum / Habis</h6>
        </div>
        <a href="<?= BASE_URL ?>module/barang/index.php" class="btn btn-sm btn-outline-warning text-dark fw-semibold">
            Kelola Stok Master <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Sisa Stok</th>
                        <th>Batas Min</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertBarang as $b): ?>
                    <tr>
                        <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($b['id_barang']) ?></td>
                        <td><?= htmlspecialchars($b['nama_barang']) ?> <small class="text-muted">(<?= htmlspecialchars($b['merk']) ?>)</small></td>
                        <td><span class="badge bg-light text-secondary"><?= htmlspecialchars($b['nama_kategori'] ?? 'Umum') ?></span></td>
                        <td>
                            <strong class="<?= $b['stok'] == 0 ? 'text-danger' : 'text-warning' ?> fs-6">
                                <?= $b['stok'] ?> <?= htmlspecialchars($b['satuan_barang']) ?>
                            </strong>
                        </td>
                        <td class="text-muted"><?= $b['stok_minimum'] ?> <?= htmlspecialchars($b['satuan_barang']) ?></td>
                        <td>
                            <?php if ($b['stok'] == 0): ?>
                                <span class="badge bg-danger">STOK HABIS (0)</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">MENIPIS (&le; <?= $b['stok_minimum'] ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <a href="<?= BASE_URL ?>module/barang/index.php?restock_id=<?= $b['id'] ?>" class="btn btn-sm btn-success py-1 px-3">
                                <i class="bi bi-plus-lg"></i> Restock
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Ringkasan Transaksi Terakhir -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-receipt-cutoff text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">5 Transaksi Kasir Terakhir</h6>
        </div>
        <a href="<?= BASE_URL ?>module/laporan/index.php" class="btn btn-sm btn-outline-primary fw-semibold">
            Lihat Semua Laporan <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentTrx)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                Belum ada transaksi checkout yang tercatat. Silakan lakukan transaksi di menu Kasir (POS).
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">No. Transaksi</th>
                            <th>Tanggal & Waktu</th>
                            <th>Kasir</th>
                            <th>Jumlah Item</th>
                            <th>Total Belanja</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTrx as $trx): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary">
                                <i class="bi bi-receipt me-1"></i> <?= htmlspecialchars($trx['no_transaksi']) ?>
                            </td>
                            <td><?= htmlspecialchars($trx['tanggal_input']) ?></td>
                            <td><?= htmlspecialchars($trx['nm_member'] ?? 'Admin Kasir') ?></td>
                            <td><span class="badge bg-secondary"><?= $trx['total_item'] ?> Item</span></td>
                            <td class="fw-bold text-success"><?= rupiah($trx['grand_total']) ?></td>
                            <td class="text-end pe-3">
                                <a href="<?= BASE_URL ?>module/transaksi/print.php?no_transaksi=<?= urlencode($trx['no_transaksi']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer"></i> Cetak Struk
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

