<?php
$pageTitle = 'Data Barang & Stok';
$activeMenu = 'barang';
require_once __DIR__ . '/../../includes/header.php';

$msg = '';
$err = '';

// 1. Tambah Barang Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $id_barang     = strtoupper(trim($_POST['id_barang'] ?? ''));
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $nama_barang   = trim($_POST['nama_barang'] ?? '');
    $merk          = trim($_POST['merk'] ?? '');
    $harga_beli    = (float)str_replace(['.', ','], '', $_POST['harga_beli'] ?? '0');
    $harga_jual    = (float)str_replace(['.', ','], '', $_POST['harga_jual'] ?? '0');
    $satuan_barang = strtoupper(trim($_POST['satuan_barang'] ?? 'PCS'));
    $stok          = (int)($_POST['stok'] ?? 0);
    $stok_minimum  = (int)($_POST['stok_minimum'] ?? 5);

    if ($id_barang === '' || $nama_barang === '' || $id_kategori <= 0) {
        $err = 'Mohon lengkapi kode barang, nama barang, dan kategori!';
    } else {
        // Cek duplikasi kode barang
        $check = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_barang = :kode");
        $check->execute(['kode' => $id_barang]);
        if ($check->fetchColumn() > 0) {
            $err = "Kode barang '$id_barang' sudah terdaftar! Gunakan kode lain.";
        } else {
            $tgl = date('j F Y, H:i');
            $stmt = $pdo->prepare("
                INSERT INTO barang (id_barang, id_kategori, nama_barang, merk, harga_beli, harga_jual, satuan_barang, stok, stok_minimum, tgl_input)
                VALUES (:kode, :kat, :nama, :merk, :beli, :jual, :satuan, :stok, :min, :tgl)
            ");
            $stmt->execute([
                'kode'   => $id_barang,
                'kat'    => $id_kategori,
                'nama'   => $nama_barang,
                'merk'   => $merk,
                'beli'   => $harga_beli,
                'jual'   => $harga_jual,
                'satuan' => $satuan_barang,
                'stok'   => $stok,
                'min'    => $stok_minimum,
                'tgl'    => $tgl
            ]);
            $msg = "Produk '$nama_barang' ($id_barang) berhasil ditambahkan!";
        }
    }
}

// 2. Edit Barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id            = (int)($_POST['id'] ?? 0);
    $id_kategori   = (int)($_POST['id_kategori'] ?? 0);
    $nama_barang   = trim($_POST['nama_barang'] ?? '');
    $merk          = trim($_POST['merk'] ?? '');
    $harga_beli    = (float)str_replace(['.', ','], '', $_POST['harga_beli'] ?? '0');
    $harga_jual    = (float)str_replace(['.', ','], '', $_POST['harga_jual'] ?? '0');
    $satuan_barang = strtoupper(trim($_POST['satuan_barang'] ?? 'PCS'));
    $stok          = (int)($_POST['stok'] ?? 0);
    $stok_minimum  = (int)($_POST['stok_minimum'] ?? 5);

    if ($id <= 0 || $nama_barang === '') {
        $err = 'Data perbaikan produk tidak valid!';
    } else {
        $tglUpdate = date('j F Y, H:i');
        $stmt = $pdo->prepare("
            UPDATE barang SET 
                id_kategori = :kat,
                nama_barang = :nama,
                merk = :merk,
                harga_beli = :beli,
                harga_jual = :jual,
                satuan_barang = :satuan,
                stok = :stok,
                stok_minimum = :min,
                tgl_update = :tgl
            WHERE id = :id
        ");
        $stmt->execute([
            'kat'    => $id_kategori,
            'nama'   => $nama_barang,
            'merk'   => $merk,
            'beli'   => $harga_beli,
            'jual'   => $harga_jual,
            'satuan' => $satuan_barang,
            'stok'   => $stok,
            'min'    => $stok_minimum,
            'tgl'    => $tglUpdate,
            'id'     => $id
        ]);
        $msg = "Data produk '$nama_barang' berhasil diperbarui!";
    }
}

// 3. Tambah Stok Cepat (Restock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'restock') {
    $id = (int)($_POST['id'] ?? 0);
    $tambahQty = (int)($_POST['tambah_stok'] ?? 0);
    if ($id > 0 && $tambahQty > 0) {
        $tglUpdate = date('j F Y, H:i');
        $stmt = $pdo->prepare("UPDATE barang SET stok = stok + :qty, tgl_update = :tgl WHERE id = :id");
        $stmt->execute(['qty' => $tambahQty, 'tgl' => $tglUpdate, 'id' => $id]);
        $msg = "Berhasil menambahkan $tambahQty stok produk!";
    } else {
        $err = "Jumlah penambahan stok minimal 1 unit!";
    }
}

// 4. Hapus Barang (Cek Constraint Nota)
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    // Ambil kode barang
    $stmtB = $pdo->prepare("SELECT id_barang, nama_barang FROM barang WHERE id = :id");
    $stmtB->execute(['id' => $idHapus]);
    $bRow = $stmtB->fetch();

    if ($bRow) {
        // Cek apakah pernah ada di nota penjualan
        $checkNota = $pdo->prepare("SELECT COUNT(*) FROM nota WHERE id_barang = :kode");
        $checkNota->execute(['kode' => $bRow['id_barang']]);
        if ($checkNota->fetchColumn() > 0) {
            $err = "Produk '{$bRow['nama_barang']}' tidak boleh dihapus karena sudah tercatat dalam riwayat nota penjualan!";
        } else {
            // Hapus dari keranjang juga jika ada
            $pdo->prepare("DELETE FROM penjualan WHERE id_barang = :kode")->execute(['kode' => $bRow['id_barang']]);
            $pdo->prepare("DELETE FROM barang WHERE id = :id")->execute(['id' => $idHapus]);
            $msg = "Produk '{$bRow['nama_barang']}' berhasil dihapus!";
        }
    }
}

// Filter & Pencarian
$q = trim($_GET['q'] ?? '');
$katFilter = (int)($_GET['kategori'] ?? 0);
$statusFilter = trim($_GET['filter'] ?? '');

$sql = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (b.nama_barang LIKE :q OR b.id_barang LIKE :q OR b.merk LIKE :q)";
    $params['q'] = "%$q%";
}
if ($katFilter > 0) {
    $sql .= " AND b.id_kategori = :kat";
    $params['kat'] = $katFilter;
}
if ($statusFilter === 'menipis') {
    $sql .= " AND b.stok <= b.stok_minimum AND b.stok > 0";
} elseif ($statusFilter === 'habis') {
    $sql .= " AND b.stok = 0";
}

$sql .= " ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$barangList = $stmt->fetchAll();

// Ambil list semua kategori untuk dropdown
$kategoriDropdown = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC")->fetchAll();

// Kode Barang Otomatis untuk Rekomendasi Tambah
$maxId = $pdo->query("SELECT MAX(id) FROM barang")->fetchColumn() ?? 0;
$nextKode = 'BR' . str_pad($maxId + 1, 3, '0', STR_PAD_LEFT);
?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($err)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center justify-content-between">
            <div class="col-md-7">
                <form action="" method="GET" class="row g-2">
                    <div class="col-sm-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Cari nama, kode, merk..." value="<?= htmlspecialchars($q) ?>">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <select name="kategori" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Kategori --</option>
                            <?php foreach ($kategoriDropdown as $k): ?>
                                <option value="<?= $k['id_kategori'] ?>" <?= ($katFilter === (int)$k['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel"></i> Filter</button>
                    </div>
                </form>
            </div>
            <div class="col-md-5 text-md-end">
                <div class="btn-group me-2">
                    <a href="index.php" class="btn btn-sm btn-outline-secondary <?= empty($statusFilter) ? 'active' : '' ?>">Semua</a>
                    <a href="index.php?filter=menipis" class="btn btn-sm btn-outline-warning text-dark <?= ($statusFilter === 'menipis') ? 'active' : '' ?>">
                        <i class="bi bi-exclamation-triangle"></i> Menipis
                    </a>
                    <a href="index.php?filter=habis" class="btn btn-sm btn-outline-danger <?= ($statusFilter === 'habis') ? 'active' : '' ?>">
                        <i class="bi bi-x-circle"></i> Habis
                    </a>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahBarang">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Master Barang -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-box-seam text-success me-2"></i>Katalog Produk Seventeen Petshop (<?= count($barangList) ?> Produk)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok Fisik</th>
                        <th>Batas Min</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="min-width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($barangList)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data produk yang sesuai filter.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barangList as $b): ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary"><?= htmlspecialchars($b['id_barang']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($b['nama_barang']) ?></div>
                                <small class="text-muted">Merk: <?= htmlspecialchars($b['merk']) ?></small>
                            </td>
                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($b['nama_kategori'] ?? 'Umum') ?></span></td>
                            <td class="text-muted small"><?= rupiah($b['harga_beli']) ?></td>
                            <td class="fw-bold text-success"><?= rupiah($b['harga_jual']) ?></td>
                            <td>
                                <span class="fw-bold fs-6 <?= ($b['stok'] == 0) ? 'text-danger' : (($b['stok'] <= $b['stok_minimum']) ? 'text-warning' : 'text-dark') ?>">
                                    <?= $b['stok'] ?>
                                </span>
                                <small class="text-muted"><?= htmlspecialchars($b['satuan_barang']) ?></small>
                            </td>
                            <td class="text-muted small"><?= $b['stok_minimum'] ?> <?= htmlspecialchars($b['satuan_barang']) ?></td>
                            <td>
                                <?php if ($b['stok'] == 0): ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php elseif ($b['stok'] <= $b['stok_minimum']): ?>
                                    <span class="badge bg-warning text-dark">Menipis</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aman</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3">
                                <!-- Restock Modal Trigger -->
                                <button class="btn btn-sm btn-outline-success py-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#modalRestock<?= $b['id'] ?>" title="Tambah Stok Masuk">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                                <!-- Edit Modal Trigger -->
                                <button class="btn btn-sm btn-outline-primary py-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $b['id'] ?>" title="Edit Produk">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Delete Action -->
                                <a href="?hapus=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus Produk" onclick="return confirm('Apakah Anda yakin ingin menghapus produk <?= addslashes(htmlspecialchars($b['nama_barang'])) ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>

                                <!-- Modal Restock Cepat -->
                                <div class="modal fade" id="modalRestock<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog text-start">
                                        <div class="modal-content">
                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="restock">
                                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Tambah Stok Produk (Restock)</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small text-muted">Nama Barang</label>
                                                        <div class="fw-bold"><?= htmlspecialchars($b['nama_barang']) ?> (<?= htmlspecialchars($b['id_barang']) ?>)</div>
                                                        <div class="small text-secondary">Stok Saat Ini: <strong><?= $b['stok'] ?> <?= htmlspecialchars($b['satuan_barang']) ?></strong></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Jumlah Stok Masuk (Unit)</label>
                                                        <input type="number" name="tambah_stok" class="form-control" min="1" value="10" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Simpan Tambah Stok</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Edit Barang -->
                                <div class="modal fade" id="modalEdit<?= $b['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg text-start">
                                        <div class="modal-content">
                                            <form action="" method="POST">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Edit Informasi Produk (<?= htmlspecialchars($b['id_barang']) ?>)</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Nama Produk</label>
                                                        <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($b['nama_barang']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Kategori Produk</label>
                                                        <select name="id_kategori" class="form-select" required>
                                                            <?php foreach ($kategoriDropdown as $k): ?>
                                                                <option value="<?= $k['id_kategori'] ?>" <?= ($b['id_kategori'] == $k['id_kategori']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Merk / Brand</label>
                                                        <input type="text" name="merk" class="form-control" value="<?= htmlspecialchars($b['merk']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Satuan Barang</label>
                                                        <input type="text" name="satuan_barang" class="form-control" value="<?= htmlspecialchars($b['satuan_barang']) ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Harga Beli Modal (Rp)</label>
                                                        <input type="number" name="harga_beli" class="form-control" value="<?= (int)$b['harga_beli'] ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Harga Jual Konsumen (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control" value="<?= (int)$b['harga_jual'] ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Stok Fisik Saat Ini</label>
                                                        <input type="number" name="stok" class="form-control" value="<?= $b['stok'] ?>" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Batas Minimum Stok (Alert)</label>
                                                        <input type="number" name="stok_minimum" class="form-control" value="<?= $b['stok_minimum'] ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang Baru -->
<div class="modal fade" id="modalTambahBarang" tabindex="-1">
    <div class="modal-dialog modal-lg text-start">
        <div class="modal-content">
            <form action="" method="POST">
                <input type="hidden" name="action" value="tambah">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-seam me-2 text-success"></i>Tambah Produk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Barang (ID Unik)</label>
                        <input type="text" name="id_barang" class="form-control" value="<?= $nextKode ?>" required>
                        <div class="form-text small">Contoh: BR009, PET01</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Nama Produk</label>
                        <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Me-O Creamy Treats Salmon 4x15g" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Kategori Produk</label>
                        <select name="id_kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriDropdown as $k): ?>
                                <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Merk / Brand</label>
                        <input type="text" name="merk" class="form-control" placeholder="Contoh: Me-O, Royal Canin, dsb.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Harga Beli Modal (Rp)</label>
                        <input type="number" name="harga_beli" class="form-control" placeholder="Contoh: 18000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Harga Jual Konsumen (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="Contoh: 25000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Satuan</label>
                        <input type="text" name="satuan_barang" class="form-control" value="PCS" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Stok Fisik Awal</label>
                        <input type="number" name="stok" class="form-control" value="20" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Batas Stok Minimum</label>
                        <input type="number" name="stok_minimum" class="form-control" value="5" min="1" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Simpan Produk Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

