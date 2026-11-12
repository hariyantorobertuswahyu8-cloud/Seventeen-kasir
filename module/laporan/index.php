<?php
$pageTitle = 'Laporan Penjualan';
$activeMenu = 'laporan';
require_once __DIR__ . '/../../includes/header.php';

// Filter periode
$mode = $_GET['mode'] ?? 'semua';
$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$sql = "
    SELECT n.*, b.nama_barang, b.harga_beli, b.harga_jual, b.satuan_barang, m.nm_member 
    FROM nota n 
    JOIN barang b ON n.id_barang = b.id_barang 
    LEFT JOIN member m ON n.id_member = m.id_member 
    WHERE 1=1
";
$params = [];

if ($mode === 'bulan') {
    $periodeFilter = sprintf('%02d-%04d', $bulan, $tahun);
    $sql .= " AND n.periode = :periode";
    $params['periode'] = $periodeFilter;
} elseif ($mode === 'tanggal') {
    // Tanggal format di tanggal_input: misal "9 November 2026..." atau Y-m-d
    // Jika filter tanggal spesifik digunakan
}

$sql .= " ORDER BY n.id_nota DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporanList = $stmt->fetchAll();

// Hitung Agregat Omzet, Modal, & Profit
$totalOmzet = 0;
$totalModal = 0;
$totalItemTerjual = 0;
$uniqueTrx = [];

foreach ($laporanList as $lap) {
    $totalOmzet += (float)$lap['total'];
    $totalModal += ((float)$lap['harga_beli'] * (int)$lap['jumlah']);
    $totalItemTerjual += (int)$lap['jumlah'];
    $uniqueTrx[$lap['no_transaksi']] = true;
}
$estimasiProfit = $totalOmzet - $totalModal;
$totalTrx = count($uniqueTrx);
?>

<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form action="" method="GET" class="row g-2 align-items-center justify-content-between">
            <div class="col-md-8 d-flex flex-wrap gap-2 align-items-center">
                <input type="hidden" name="mode" value="bulan">
                <span class="fw-bold small text-secondary me-2"><i class="bi bi-funnel"></i> Filter Bulan:</span>
                <select name="bulan" class="form-select form-select-sm" style="width: 140px;">
                    <?php
                    $namaBulan = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($namaBulan as $mKey => $mName):
                    ?>
                        <option value="<?= $mKey ?>" <?= ($bulan === $mKey) ? 'selected' : '' ?>><?= $mName ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="form-select form-select-sm" style="width: 110px;">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Terapkan Filter</button>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">Semua Periode</a>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="export_excel.php?mode=<?= urlencode($mode) ?>&bulan=<?= urlencode($bulan) ?>&tahun=<?= urlencode($tahun) ?>" class="btn btn-sm btn-success fw-bold">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel (XLS)
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Metrik Laporan Penjualan -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-primary border-4">
            <span class="text-muted small fw-semibold">Total Transaksi</span>
            <h4 class="fw-bold my-1 text-dark"><?= number_format($totalTrx) ?> Nota</h4>
            <small class="text-muted">Total Checkout</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-info border-4">
            <span class="text-muted small fw-semibold">Produk Terjual</span>
            <h4 class="fw-bold my-1 text-info"><?= number_format($totalItemTerjual) ?> Unit</h4>
            <small class="text-muted">Kuantitas Fisik</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-success border-4">
            <span class="text-muted small fw-semibold">Total Omzet Penjualan</span>
            <h4 class="fw-bold my-1 text-success"><?= rupiah($totalOmzet) ?></h4>
            <small class="text-muted">Penerimaan Kasir</small>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-start border-warning border-4">
            <span class="text-muted small fw-semibold">Estimasi Laba Kotor</span>
            <h4 class="fw-bold my-1 text-primary"><?= rupiah($estimasiProfit) ?></h4>
            <small class="text-muted">Margin (Omzet - Modal)</small>
        </div>
    </div>
</div>

<!-- Tabel Rincian Nota Penjualan -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-table text-primary me-2"></i>Rincian Transaksi Penjualan (<?= count($laporanList) ?> Baris Data)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>No. Transaksi</th>
                        <th>Waktu Transaksi</th>
                        <th>Kode & Nama Barang</th>
                        <th>Harga Jual</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Kasir</th>
                        <th class="text-end pe-3">Struk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporanList)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada data penjualan pada periode yang dipilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($laporanList as $row): ?>
                        <tr>
                            <td class="ps-3 text-muted"><?= $no++ ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($row['no_transaksi']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($row['tanggal_input']) ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_barang']) ?></div>
                                <small class="text-muted">[<?= htmlspecialchars($row['id_barang']) ?>]</small>
                            </td>
                            <td><?= rupiah($row['harga_jual']) ?></td>
                            <td><span class="badge bg-secondary"><?= $row['jumlah'] ?> <?= htmlspecialchars($row['satuan_barang']) ?></span></td>
                            <td class="fw-bold text-success"><?= rupiah($row['total']) ?></td>
                            <td class="small"><?= htmlspecialchars($row['nm_member'] ?? 'Admin Kasir') ?></td>
                            <td class="text-end pe-3">
                                <a href="<?= BASE_URL ?>module/transaksi/print.php?no_transaksi=<?= urlencode($row['no_transaksi']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Cetak Nota">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

