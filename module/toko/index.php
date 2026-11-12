<?php
$pageTitle = 'Pengaturan Toko & Profil';
$activeMenu = 'toko';
require_once __DIR__ . '/../../includes/header.php';

$msg = '';
$err = '';

// Update Informasi Toko
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_toko') {
    $nama_toko    = trim($_POST['nama_toko'] ?? '');
    $alamat_toko  = trim($_POST['alamat_toko'] ?? '');
    $tlp          = trim($_POST['tlp'] ?? '');
    $nama_pemilik = trim($_POST['nama_pemilik'] ?? '');

    if ($nama_toko === '' || $alamat_toko === '') {
        $err = 'Nama dan alamat toko tidak boleh kosong!';
    } else {
        $stmt = $pdo->prepare("UPDATE toko SET nama_toko = :nama, alamat_toko = :alamat, tlp = :tlp, nama_pemilik = :pemilik WHERE id_toko = 1");
        $stmt->execute([
            'nama'    => $nama_toko,
            'alamat'  => $alamat_toko,
            'tlp'     => $tlp,
            'pemilik' => $nama_pemilik
        ]);
        $msg = 'Pengaturan informasi toko Seventeen Petshop berhasil diperbarui!';
        // Refresh info toko
        $toko = $pdo->query("SELECT * FROM toko LIMIT 1")->fetch();
    }
}

// Update Password Akun Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ganti_password') {
    $pass_baru = trim($_POST['pass_baru'] ?? '');
    $konfirmasi = trim($_POST['konfirmasi_pass'] ?? '');

    if (strlen($pass_baru) < 4) {
        $err = 'Password baru minimal 4 karakter!';
    } elseif ($pass_baru !== $konfirmasi) {
        $err = 'Konfirmasi password tidak cocok!';
    } else {
        $id_login = $_SESSION['admin_id_login'] ?? 1;
        $hashBcrypt = password_hash($pass_baru, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE login SET pass = :pass WHERE id_login = :id");
        $stmt->execute(['pass' => $hashBcrypt, 'id' => $id_login]);
        $msg = 'Password akun admin berhasil diubah! Gunakan password baru saat login berikutnya.';
    }
}

$stmtMember = $pdo->prepare("SELECT * FROM member WHERE id_member = :id");
$stmtMember->execute(['id' => $_SESSION['admin_id_member'] ?? 1]);
$member = $stmtMember->fetch() ?: ['nm_member' => 'Admin Kasir', 'email' => 'admin@petshop.local'];
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
    <!-- Pengaturan Profil Toko -->
    <div class="col-lg-7">
        <div class="card card-custom">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-shop text-success me-2"></i>Informasi Identitas Usaha (Kop Struk Nota)</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_toko">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Toko / Usaha</label>
                        <input type="text" name="nama_toko" class="form-control" value="<?= htmlspecialchars($toko['nama_toko']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Lengkap</label>
                        <textarea name="alamat_toko" class="form-control" rows="3" required><?= htmlspecialchars($toko['alamat_toko']) ?></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="tlp" class="form-control" value="<?= htmlspecialchars($toko['tlp']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Pemilik Usaha</label>
                            <input type="text" name="nama_pemilik" class="form-control" value="<?= htmlspecialchars($toko['nama_pemilik']) ?>" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i> Simpan Pengaturan Toko
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Password & Info Pengguna -->
    <div class="col-lg-5">
        <div class="card card-custom mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Profil Kasir Aktif</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-3">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($member['nm_member']) ?></h6>
                        <span class="text-muted small">Username: <code><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></code></span>
                    </div>
                </div>
                <div class="small text-muted">
                    <div><i class="bi bi-envelope me-2"></i> <?= htmlspecialchars($member['email']) ?></div>
                    <div><i class="bi bi-telephone me-2"></i> <?= htmlspecialchars($member['telepon'] ?? '-') ?></div>
                </div>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-shield-lock text-warning me-2"></i>Ganti Password Admin</h6>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="ganti_password">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password Baru</label>
                        <input type="password" name="pass_baru" class="form-control" placeholder="Minimal 4 karakter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ulangi Password Baru</label>
                        <input type="password" name="konfirmasi_pass" class="form-control" placeholder="Konfirmasi password" required>
                    </div>
                    <button type="submit" class="btn btn-warning text-dark fw-bold w-100">
                        <i class="bi bi-key-fill me-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

