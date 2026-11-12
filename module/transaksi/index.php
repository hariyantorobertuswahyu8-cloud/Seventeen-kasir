<?php
require_once __DIR__ . '/../../config/config.php';
check_login();

$pageTitle = 'Transaksi Penjualan (POS)';
$activeMenu = 'transaksi';

$id_member = $_SESSION['admin_id_member'] ?? 1;
$msg = '';
$err = '';

// 1. Tambah Barang ke Keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tambah_keranjang') {
    $id_barang = trim($_POST['id_barang'] ?? '');
    $qty_input = max(1, (int)($_POST['qty'] ?? 1));

    if (empty($id_barang)) {
        $err = 'Silakan pilih produk yang akan dibeli!';
    } else {
        // Ambil data barang & cek stok
        $stmtB = $pdo->prepare("SELECT * FROM barang WHERE id_barang = :kode");
        $stmtB->execute(['kode' => $id_barang]);
        $barang = $stmtB->fetch();

        if (!$barang) {
            $err = 'Barang tidak ditemukan!';
        } elseif ($barang['stok'] <= 0) {
            $err = "Stok produk '{$barang['nama_barang']}' saat ini HABIS (0)!";
        } else {
            // Cek apakah item sudah ada di keranjang
            $stmtC = $pdo->prepare("SELECT * FROM penjualan WHERE id_barang = :kode AND id_member = :mem");
            $stmtC->execute(['kode' => $id_barang, 'mem' => $id_member]);
            $cartItem = $stmtC->fetch();

            $totalQty = $qty_input + ($cartItem ? (int)$cartItem['jumlah'] : 0);

            if ($totalQty > $barang['stok']) {
                $err = "Kuantitas melebihi stok yang tersedia! Stok fisik saat ini: {$barang['stok']} {$barang['satuan_barang']}.";
            } else {
                $tgl = date('j F Y, H:i');
                $subtotal = $totalQty * (float)$barang['harga_jual'];

                if ($cartItem) {
                    $stmtUp = $pdo->prepare("UPDATE penjualan SET jumlah = :qty, total = :tot WHERE id_penjualan = :id");
                    $stmtUp->execute(['qty' => $totalQty, 'tot' => $subtotal, 'id' => $cartItem['id_penjualan']]);
                } else {
                    $stmtIn = $pdo->prepare("INSERT INTO penjualan (id_barang, id_member, jumlah, total, tanggal_input) VALUES (:b, :m, :j, :t, :tgl)");
                    $stmtIn->execute([
                        'b'   => $id_barang,
                        'm'   => $id_member,
                        'j'   => $qty_input,
                        't'   => $qty_input * (float)$barang['harga_jual'],
                        'tgl' => $tgl
                    ]);
                }
                $msg = "Produk '{$barang['nama_barang']}' berhasil dimasukkan ke keranjang kasir.";
            }
        }
    }
}

// 2. Ubah Kuantitas Item di Keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ubah_qty') {
    $id_penjualan = (int)($_POST['id_penjualan'] ?? 0);
    $qty_baru     = (int)($_POST['qty_baru'] ?? 1);

    if ($id_penjualan > 0) {
        $stmtC = $pdo->prepare("SELECT p.*, b.stok, b.harga_jual, b.nama_barang, b.satuan_barang FROM penjualan p JOIN barang b ON p.id_barang = b.id_barang WHERE p.id_penjualan = :id AND p.id_member = :mem");
        $stmtC->execute(['id' => $id_penjualan, 'mem' => $id_member]);
        $row = $stmtC->fetch();

        if ($row) {
            if ($qty_baru <= 0) {
                // Hapus item
                $pdo->prepare("DELETE FROM penjualan WHERE id_penjualan = :id")->execute(['id' => $id_penjualan]);
                $msg = "Item telah dikeluarkan dari keranjang.";
            } elseif ($qty_baru > $row['stok']) {
                $err = "Kuantitas ($qty_baru) melebihi stok yang tersedia ({$row['stok']} {$row['satuan_barang']})!";
            } else {
                $subtotal = $qty_baru * (float)$row['harga_jual'];
                $pdo->prepare("UPDATE penjualan SET jumlah = :qty, total = :tot WHERE id_penjualan = :id")
                    ->execute(['qty' => $qty_baru, 'tot' => $subtotal, 'id' => $id_penjualan]);
                $msg = "Kuantitas produk '{$row['nama_barang']}' diperbarui.";
            }
        }
    }
}

// 3. Hapus 1 Item dari Keranjang
if (isset($_GET['hapus_cart'])) {
    $idHapus = (int)$_GET['hapus_cart'];
    $pdo->prepare("DELETE FROM penjualan WHERE id_penjualan = :id AND id_member = :mem")
        ->execute(['id' => $idHapus, 'mem' => $id_member]);
    $msg = "Item berhasil dihapus dari keranjang.";
}

// 4. Reset Seluruh Keranjang
if (isset($_GET['reset_cart'])) {
    $pdo->prepare("DELETE FROM penjualan WHERE id_member = :mem")->execute(['mem' => $id_member]);
    $msg = "Keranjang kasir telah dikosongkan.";
}

// 5. Checkout Transaksi & Potong Stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $total_bayar  = (float)str_replace(['.', ','], '', $_POST['total_bayar'] ?? '0');
    $uang_bayar   = (float)str_replace(['.', ','], '', $_POST['uang_bayar'] ?? '0');

    // Ambil isi keranjang
    $stmtKeranjang = $pdo->prepare("
        SELECT p.*, b.nama_barang, b.harga_jual, b.stok 
        FROM penjualan p 
        JOIN barang b ON p.id_barang = b.id_barang 
        WHERE p.id_member = :mem
    ");
    $stmtKeranjang->execute(['mem' => $id_member]);
    $items = $stmtKeranjang->fetchAll();

    if (empty($items)) {
        $err = "Keranjang kasir masih kosong! Silakan pilih barang terlebih dahulu.";
    } elseif ($uang_bayar < $total_bayar) {
        $err = "Uang pembayaran kurang! Total belanja: " . rupiah($total_bayar) . ", Uang diterima: " . rupiah($uang_bayar);
    } else {
        // Cek validitas stok sekali lagi sebelum commit
        $stokKurang = false;
        foreach ($items as $item) {
            if ($item['jumlah'] > $item['stok']) {
                $err = "Stok untuk {$item['nama_barang']} tidak mencukupi (Tersisa: {$item['stok']})!";
                $stokKurang = true;
                break;
            }
        }

        if (!$stokKurang) {
            try {
                $pdo->beginTransaction();

                // Generate No Transaksi Unik: TR-YYYYMMDD-HHMMSS
                $no_transaksi = 'TR-' . date('Ymd-His') . '-' . mt_rand(100, 999);
                $tanggal_input = date('j F Y, H:i:s');
                $periode = date('m-Y');

                // Pindahkan ke tabel NOTA dan kurangi stok di tabel BARANG
                $stmtInsertNota = $pdo->prepare("
                    INSERT INTO nota (no_transaksi, id_barang, id_member, jumlah, total, tanggal_input, periode)
                    VALUES (:no, :barang, :mem, :qty, :tot, :tgl, :periode)
                ");
                $stmtPotongStok = $pdo->prepare("
                    UPDATE barang SET stok = stok - :qty WHERE id_barang = :barang
                ");

                foreach ($items as $item) {
                    $stmtInsertNota->execute([
                        'no'      => $no_transaksi,
                        'barang'  => $item['id_barang'],
                        'mem'     => $id_member,
                        'qty'     => $item['jumlah'],
                        'tot'     => $item['total'],
                        'tgl'     => $tanggal_input,
                        'periode' => $periode
                    ]);

                    $stmtPotongStok->execute([
                        'qty'    => $item['jumlah'],
                        'barang' => $item['id_barang']
                    ]);
                }

                // Bersihkan keranjang kasir
                $pdo->prepare("DELETE FROM penjualan WHERE id_member = :mem")->execute(['mem' => $id_member]);

                $pdo->commit();

                // Simpan data pembayaran ke session untuk halaman struk
                $_SESSION['last_transaksi'] = [
                    'no_transaksi' => $no_transaksi,
                    'total_belanja'=> $total_bayar,
                    'uang_bayar'   => $uang_bayar,
                    'kembalian'    => $uang_bayar - $total_bayar
                ];

                $printUrl = "print.php?no_transaksi=" . urlencode($no_transaksi);
                if (!headers_sent()) {
                    header("Location: " . $printUrl);
                } else {
                    echo "<script>window.location.href='" . $printUrl . "';</script>";
                }
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $err = "Gagal memproses transaksi: " . $e->getMessage();
            }
        }
    }
}

// Ambil Keranjang Kasir Aktif
$stmtCart = $pdo->prepare("
    SELECT p.*, b.nama_barang, b.merk, b.harga_jual, b.stok, b.satuan_barang 
    FROM penjualan p 
    JOIN barang b ON p.id_barang = b.id_barang 
    WHERE p.id_member = :mem
    ORDER BY p.id_penjualan ASC
");
$stmtCart->execute(['mem' => $id_member]);
$cartList = $stmtCart->fetchAll();

$grandTotal = 0;
foreach ($cartList as $c) {
    $grandTotal += (float)$c['total'];
}

// Ambil Semua Produk yang Masih Tersedia untuk Dropdown / Quick Select
$produkList = $pdo->query("SELECT * FROM barang ORDER BY nama_barang ASC")->fetchAll();

// Muat template layout header & sidebar setelah pemrosesan backend selesai
require_once __DIR__ . '/../../includes/header.php';
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
    <!-- Kolom Kiri: Pemilihan Produk & Keranjang POS -->
    <div class="col-lg-8">
        <!-- Card Pilih Produk -->
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-search text-success me-2"></i>Cari / Pilih Produk Konsumen</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST" class="row g-2 align-items-center">
                    <input type="hidden" name="action" value="tambah_keranjang">
                    <div class="col-md-8">
                        <select name="id_barang" id="selectBarang" class="form-select select-search" required>
                            <option value="">-- Ketik / Pilih Produk Hewan --</option>
                            <?php foreach ($produkList as $pr): ?>
                                <?php $isHabis = ($pr['stok'] <= 0); ?>
                                <option value="<?= htmlspecialchars($pr['id_barang']) ?>" data-harga="<?= $pr['harga_jual'] ?>" data-stok="<?= $pr['stok'] ?>" <?= $isHabis ? 'disabled' : '' ?>>
                                    [<?= htmlspecialchars($pr['id_barang']) ?>] <?= htmlspecialchars($pr['nama_barang']) ?> - <?= rupiah($pr['harga_jual']) ?> (Sisa: <?= $pr['stok'] ?> <?= htmlspecialchars($pr['satuan_barang']) ?>) <?= $isHabis ? ' - [HABIS]' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="qty" class="form-control text-center" value="1" min="1" placeholder="Qty" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus-lg"></i> Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Keranjang Penjualan Kasir -->
        <div class="card card-custom">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-cart3 text-primary me-2"></i>Daftar Item Belanja Kasir (<?= count($cartList) ?> Produk)</h6>
                <?php if (!empty($cartList)): ?>
                    <a href="?reset_cart=1" class="btn btn-sm btn-outline-danger" onclick="return confirm('Kosongkan semua barang di keranjang kasir?')">
                        <i class="bi bi-trash3"></i> Kosongkan Keranjang
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Nama Produk</th>
                                <th>Harga Satuan</th>
                                <th style="width: 140px;">Jumlah (Qty)</th>
                                <th>Subtotal</th>
                                <th class="text-end pe-3" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cartList)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Keranjang kasir masih kosong. Silakan pilih produk hewan di atas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($cartList as $item): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['nama_barang']) ?></div>
                                        <small class="text-muted">[<?= htmlspecialchars($item['id_barang']) ?>] Sisa: <?= $item['stok'] ?> <?= htmlspecialchars($item['satuan_barang']) ?></small>
                                    </td>
                                    <td><?= rupiah($item['harga_jual']) ?></td>
                                    <td>
                                        <!-- Form Ubah Qty Cepat -->
                                        <form action="" method="POST" class="d-flex align-items-center gap-1">
                                            <input type="hidden" name="action" value="ubah_qty">
                                            <input type="hidden" name="id_penjualan" value="<?= $item['id_penjualan'] ?>">
                                            <input type="number" name="qty_baru" class="form-control form-control-sm text-center" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['stok'] ?>" style="width: 65px;" onchange="this.form.submit()">
                                            <small class="text-muted"><?= htmlspecialchars($item['satuan_barang']) ?></small>
                                        </form>
                                    </td>
                                    <td class="fw-bold text-success"><?= rupiah($item['total']) ?></td>
                                    <td class="text-end pe-3">
                                        <a href="?hapus_cart=<?= $item['id_penjualan'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus dari nota" onclick="return confirm('Hapus item ini?')">
                                            <i class="bi bi-x-lg"></i>
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
    </div>

    <!-- Kolom Kanan: Ringkasan Pembayaran & Checkout -->
    <div class="col-lg-4">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-success text-white py-3">
                <h6 class="card-title mb-0 fw-bold"><i class="bi bi-calculator me-2"></i>Rincian Pembayaran Kasir</h6>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST" id="formCheckout">
                    <input type="hidden" name="action" value="checkout">
                    <input type="hidden" name="total_bayar" id="inputTotalBayar" value="<?= $grandTotal ?>">

                    <div class="mb-3 text-center p-3 rounded bg-light border">
                        <span class="text-muted small text-uppercase fw-bold">Grand Total Belanja</span>
                        <h2 class="fw-bold text-success mb-0 mt-1" id="displayGrandTotal"><?= rupiah($grandTotal) ?></h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nominal Uang Bayar (Rp)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white fw-bold text-muted">Rp</span>
                            <input type="number" name="uang_bayar" id="inputUangBayar" class="form-control text-end fw-bold" placeholder="0" min="<?= $grandTotal ?>" required <?= empty($cartList) ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="d-flex gap-1 mb-3 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-cash="<?= $grandTotal ?>">Uang Pas</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-cash="50000">50.000</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-cash="100000">100.000</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-quick-cash" data-cash="200000">200.000</button>
                    </div>

                    <div class="mb-4 text-center p-3 rounded bg-light border">
                        <span class="text-muted small text-uppercase fw-bold">Uang Kembalian</span>
                        <h3 class="fw-bold text-primary mb-0 mt-1" id="displayKembalian">Rp 0</h3>
                    </div>

                    <button type="submit" id="btnProsesBayar" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow-sm" <?= empty($cartList) ? 'disabled' : '' ?>>
                        <i class="bi bi-printer-fill me-2"></i> Bayar & Cetak Struk
                    </button>
                </form>
            </div>
            <div class="card-footer bg-white border-0 text-center pb-3 pt-0">
                <small class="text-muted"><i class="bi bi-shield-check text-success"></i> Stok otomatis terpotong saat struk dicetak.</small>
            </div>
        </div>
    </div>
</div>

<script>
    const grandTotal = <?= (float)$grandTotal ?>;
    const inputUangBayar = document.getElementById('inputUangBayar');
    const displayKembalian = document.getElementById('displayKembalian');
    const btnProsesBayar = document.getElementById('btnProsesBayar');

    function hitungKembalian() {
        if (!inputUangBayar) return;
        const bayar = parseFloat(inputUangBayar.value) || 0;
        const kembalian = bayar - grandTotal;

        if (kembalian >= 0) {
            displayKembalian.innerText = 'Rp ' + kembalian.toLocaleString('id-ID');
            displayKembalian.className = 'fw-bold text-primary mb-0 mt-1';
            btnProsesBayar.disabled = false;
        } else {
            displayKembalian.innerText = 'Kurang Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
            displayKembalian.className = 'fw-bold text-danger mb-0 mt-1';
            btnProsesBayar.disabled = true;
        }
    }

    if (inputUangBayar) {
        inputUangBayar.addEventListener('input', hitungKembalian);
    }

    // Quick Cash buttons
    document.querySelectorAll('.btn-quick-cash').forEach(btn => {
        btn.addEventListener('click', function() {
            if (inputUangBayar) {
                inputUangBayar.value = this.getAttribute('data-cash');
                hitungKembalian();
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

