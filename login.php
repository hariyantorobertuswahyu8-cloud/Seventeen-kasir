<?php
require_once __DIR__ . '/config/config.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

// Ambil info nama toko
$stmtToko = $pdo->query("SELECT * FROM toko LIMIT 1");
$toko = $stmtToko->fetch() ?: ['nama_toko' => 'Seventeen Petshop', 'alamat_toko' => 'Tangerang'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Silakan masukkan username dan password!';
    } else {
        $stmt = $pdo->prepare("SELECT l.*, m.nm_member, m.email FROM login l JOIN member m ON l.id_member = m.id_member WHERE l.user = :u LIMIT 1");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        $valid = false;
        if ($user) {
            // Cek hash bcrypt atau md5 atau fallback plain
            if (password_verify($password, $user['pass'])) {
                $valid = true;
            } elseif ($user['pass'] === md5($password)) {
                $valid = true;
            } elseif ($user['pass'] === $password) {
                $valid = true;
            } elseif ($username === 'admin' && ($password === 'admin' || $password === 'admin123' || $password === '123')) {
                // Fallback darurat admin
                $valid = true;
            }
        }

        if ($valid) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id_login']  = $user['id_login'];
            $_SESSION['admin_user']      = $user['user'];
            $_SESSION['admin_id_member'] = $user['id_member'];
            $_SESSION['admin_name']      = $user['nm_member'];
            header("Location: " . BASE_URL . "dashboard.php");
            exit();
        } else {
            $error = 'Username atau Password salah! Periksa kembali kredensial Anda.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <!-- Favicon & Local Bootstrap 5 & Icons -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6efd 0%, #198754 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 1.5rem;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }
        .login-header .icon-brand {
            width: 70px;
            height: 70px;
            background: #e8f5e9;
            color: #198754;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 10px rgba(25,135,84,0.15);
        }
        .login-body {
            padding: 2rem;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        .btn-login {
            background: #198754;
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .btn-login:hover {
            background: #157347;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25,135,84,0.3);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="icon-brand">
                <i class="bi bi-shop"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($toko['nama_toko']) ?></h4>
            <p class="text-muted small mb-0">Sistem Informasi Kasir & Manajemen Stok (POS)</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center py-2 px-3 small mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required value="admin123">
                    </div>
                    <div class="form-text small text-muted">Akun default: <code>admin</code> / <code>admin123</code></div>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login text-white">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sistem Kasir
                </button>
            </form>
        </div>
        <div class="card-footer text-center bg-white border-0 pb-3 pt-0">
            <small class="text-muted">&copy; <?= date('Y') ?> <?= htmlspecialchars($toko['nama_toko']) ?> - Metode Waterfall</small>
        </div>
    </div>
</body>
</html>
