<?php
require_once __DIR__ . '/../config/config.php';
check_login();

// Ambil info profil toko
$stmtToko = $pdo->query("SELECT * FROM toko LIMIT 1");
$toko = $stmtToko->fetch() ?: ['nama_toko' => 'Seventeen Petshop'];

// Notifikasi stok menipis & habis untuk navbar
$countAlertStok = $pdo->query("SELECT COUNT(*) FROM barang WHERE stok <= stok_minimum")->fetchColumn();
$pageTitle = $pageTitle ?? 'Dashboard Kasir';
$activeMenu = $activeMenu ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($toko['nama_toko']) ?></title>
    <!-- Favicon & Local Bootstrap 5 & Icons -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/img/favicon.ico" type="image/x-icon">
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --pet-primary: #198754;
            --pet-secondary: #0d6efd;
            --pet-dark: #212529;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f4f6f9;
            min-height: 100vh;
        }
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: #1e293b;
            color: #cbd5e1;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        #sidebar .brand-logo {
            padding: 1.25rem 1.5rem;
            background: #0f172a;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.15rem;
            font-weight: 700;
            text-decoration: none;
            border-bottom: 1px solid #334155;
        }
        #sidebar .brand-logo i {
            color: #4ade80;
            font-size: 1.5rem;
        }
        #sidebar .nav-section {
            padding: 0.75rem 1.25rem 0.25rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        #sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        #sidebar .nav-link:hover {
            color: #f8fafc;
            background: rgba(255,255,255,0.05);
        }
        #sidebar .nav-link.active {
            color: #ffffff;
            background: #0f172a;
            border-left-color: #22c55e;
            font-weight: 600;
        }
        #sidebar .nav-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .topbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .content-body {
            padding: 1.75rem;
            flex: 1;
        }
        .card-custom {
            border: none;
            border-radius: 0.85rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            background: #ffffff;
        }
        .btn-pos {
            background-color: #198754;
            color: white;
            font-weight: 600;
        }
        .btn-pos:hover {
            background-color: #157347;
            color: white;
        }
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.show {
                margin-left: 0;
            }
            #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <?php include_once __DIR__ . '/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div id="main-content" class="w-100">
            <!-- Topbar -->
            <header class="topbar d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" id="toggleSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div class="page-header">
                        <h5 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($pageTitle) ?></h5>
                        <small class="text-muted d-none d-sm-inline"><?= htmlspecialchars($toko['nama_toko']) ?> - Sistem Kasir</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <!-- Notification Bell for Low Stock -->
                    <a href="<?= BASE_URL ?>module/barang/index.php?filter=menipis" class="btn btn-light position-relative rounded-pill px-3 py-1 text-secondary" title="Peringatan Stok Menipis">
                        <i class="bi bi-bell-fill text-warning"></i>
                        <span class="ms-1 small fw-bold">Alert Stok</span>
                        <?php if ($countAlertStok > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $countAlertStok ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light text-dark dropdown-toggle d-flex align-items-center gap-2 border py-1 px-3 rounded-pill" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-success"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Kasir') ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><span class="dropdown-header small">Pengguna Aktif: <strong><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></strong></span></li>
                            <li><a class="dropdown-item small" href="<?= BASE_URL ?>module/toko/index.php"><i class="bi bi-shop me-2 text-primary"></i>Profil Toko</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar (Logout)</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="content-body">
