<?php
$pageTitle = 'Kategori Produk';
$activeMenu = 'kategori';
require_once __DIR__ . '/../../includes/header.php';

$msg = '';
$err = '';

// Proses Tambah Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $namaKategori = trim($_POST['nama_kategori'] ?? '');
    if ($namaKategori === '') {
        $err = 'Nama kategori tidak boleh kosong!';
    } else {
        $tglInput = date('j F Y, H:i');
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, tgl_input) VALUES (:nama, :tgl)");
        $stmt->execute(['nama' => $namaKategori, 'tgl' => $tglInput]);
        $msg = 'Kategori berhasil ditambahkan!';
    }
}

// Proses Edit Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $idKat = (int)($_POST['id_kategori'] ?? 0);
    $namaKategori = trim($_POST['nama_kategori'] ?? '');
    if ($idKat <= 0 || $namaKategori === '') {
        $err = 'Data kategori tidak valid!';
    } else {
        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = :nama WHERE id_kategori = :id");
        $stmt->execute(['nama' => $namaKategori, 'id' => $idKat]);
        $msg = 'Kategori berhasil diperbarui!';
    }
}

// Proses Hapus Kategori
if (isset($_GET['hapus'])) {
    $idHapus = (int)$_GET['hapus'];
    // Cek apakah ada barang dengan kategori ini
    $check = $pdo->prepare("SELECT COUNT(*) FROM barang WHERE id_kategori = :id");
    $check->execute(['id' => $idHapus]);
    if ($check->fetchColumn() > 0) {
        $err = 'Kategori ini tidak dapat dihapus karena masih digunakan oleh beberapa produk!';
    } else {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = :id");
        $stmt->execute(['id' => $idHapus]);
        $msg = 'Kategori berhasil dihapus!';
    }
}

// Ambil Daftar Kategori dengan Jumlah Produk
$stmtList = $pdo->query("
    SELECT k.*, COUNT(b.id) as total_produk 
    FROM kategori k 
    LEFT JOIN barang b ON k.id_kategori = b.id_kategori 
    GROUP BY k.id_kategori, k.nama_kategori, k.tgl_input 
    ORDER BY k.id_kategori ASC
");
$kategoriList = $stmtList->fetchAll();
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

<div class="row g-4">
    <!-- Form Tambah Kategori -->
    <div class="col-lg-4">
        <div class="card card-custom">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-folder-plus text-success me-2"></i>Tambah Kategori Baru</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kategori Produk</label>
                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Vitamin & Suplemen" required>
                        <div class="form-text small">Kategori memudahkan pengelompokan saat kasir mencari produk.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i> Simpan Kategori
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-tags text-primary me-2"></i>Daftar Kategori Produk (<?= count($kategoriList) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 60px;">No</th>
                                <th>Nama Kategori</th>
                                <th>Total Produk</th>
                                <th>Tanggal Dibuat</th>
                                <th class="text-end pe-3" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kategoriList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada kategori yang terdaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($kategoriList as $k): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?= $no++ ?></td>
                                    <td class="fw-semibold text-dark"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $k['total_produk'] ?> Barang</span></td>
                                    <td class="small text-muted"><?= htmlspecialchars($k['tgl_input']) ?></td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id_kategori'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $k['id_kategori'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori <?= addslashes(htmlspecialchars($k['nama_kategori'])) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>

                                        <!-- Modal Edit -->
                                        <div class="modal fade" id="modalEdit<?= $k['id_kategori'] ?>" tabindex="-1">
                                            <div class="modal-dialog text-start">
                                                <div class="modal-content">
                                                    <form action="" method="POST">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id_kategori" value="<?= $k['id_kategori'] ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">Edit Kategori</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label small fw-semibold">Nama Kategori</label>
                                                            <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($k['nama_kategori']) ?>" required>
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
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

