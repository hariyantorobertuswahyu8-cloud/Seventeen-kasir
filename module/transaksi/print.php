<?php
require_once __DIR__ . '/../../config/config.php';
check_login();

$no_transaksi = trim($_GET['no_transaksi'] ?? '');

if (empty($no_transaksi)) {
    die("Nomor transaksi tidak ditemukan!");
}

// Ambil info Toko
$stmtToko = $pdo->query("SELECT * FROM toko LIMIT 1");
$toko = $stmtToko->fetch() ?: ['nama_toko' => 'Seventeen Petshop', 'alamat_toko' => 'Tangerang', 'tlp' => '-'];

// Ambil data item nota
$stmtNota = $pdo->prepare("
    SELECT n.*, b.nama_barang, b.harga_jual, b.satuan_barang, m.nm_member 
    FROM nota n 
    JOIN barang b ON n.id_barang = b.id_barang 
    LEFT JOIN member m ON n.id_member = m.id_member 
    WHERE n.no_transaksi = :no 
    ORDER BY n.id_nota ASC
");
$stmtNota->execute(['no' => $no_transaksi]);
$items = $stmtNota->fetchAll();

if (empty($items)) {
    die("Data transaksi dengan nomor $no_transaksi tidak ditemukan di database!");
}

$firstItem = $items[0];
$grandTotal = 0;
foreach ($items as $it) {
    $grandTotal += (float)$it['total'];
}

// Cek apakah ada data bayar di session
$lastTrx = $_SESSION['last_transaksi'] ?? null;
if ($lastTrx && $lastTrx['no_transaksi'] === $no_transaksi) {
    $uangBayar = (float)$lastTrx['uang_bayar'];
    $kembalian = (float)$lastTrx['kembalian'];
} else {
    // Estimasi pas jika dibuka dari riwayat laporan
    $uangBayar = $grandTotal;
    $kembalian = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi - <?= htmlspecialchars($no_transaksi) ?></title>
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Courier New', Courier, monospace;
            padding: 20px 0;
        }
        .struk-card {
            background: #ffffff;
            width: 380px;
            margin: 0 auto;
            padding: 24px 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 6px;
        }
        .struk-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.25rem;
            text-transform: uppercase;
        }
        .struk-sub {
            text-align: center;
            font-size: 0.8rem;
            color: #475569;
            margin-bottom: 12px;
        }
        .struk-line {
            border-top: 1px dashed #64748b;
            margin: 8px 0;
        }
        .struk-meta {
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
        .table-struk {
            width: 100%;
            font-size: 0.85rem;
            border-collapse: collapse;
        }
        .table-struk th {
            border-bottom: 1px dashed #64748b;
            padding: 4px 0;
        }
        .table-struk td {
            padding: 4px 0;
        }
        .no-print {
            max-width: 380px;
            margin: 0 auto 15px auto;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .struk-card {
                width: 100%;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Cetak & Kembali -->
    <div class="no-print d-flex gap-2">
        <a href="<?= BASE_URL ?>module/transaksi/index.php" class="btn btn-secondary flex-fill">
            <i class="bi bi-arrow-left"></i> Kasir POS
        </a>
        <button onclick="window.print()" class="btn btn-success flex-fill fw-bold">
            <i class="bi bi-printer-fill"></i> Cetak Struk
        </button>
    </div>

    <!-- Kontainer Struk Thermal -->
    <div class="struk-card">
        <div class="struk-title"><?= htmlspecialchars($toko['nama_toko']) ?></div>
        <div class="struk-sub">
            <?= htmlspecialchars($toko['alamat_toko']) ?><br>
            Telp: <?= htmlspecialchars($toko['tlp']) ?>
        </div>

        <div class="struk-line"></div>

        <div class="struk-meta">
            <div><strong>No. Nota :</strong> <?= htmlspecialchars($no_transaksi) ?></div>
            <div><strong>Waktu    :</strong> <?= htmlspecialchars($firstItem['tanggal_input']) ?></div>
            <div><strong>Kasir    :</strong> <?= htmlspecialchars($firstItem['nm_member'] ?? 'Admin Kasir') ?></div>
        </div>

        <div class="struk-line"></div>

        <table class="table-struk">
            <thead>
                <tr>
                    <th style="text-align: left;">Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td colspan="3" style="font-weight: bold; padding-top: 4px;">
                        <?= htmlspecialchars($it['nama_barang']) ?>
                    </td>
                </tr>
                <tr>
                    <td style="color: #64748b; font-size: 0.75rem;">
                        @ <?= number_format($it['harga_jual'], 0, ',', '.') ?>
                    </td>
                    <td style="text-align: center;"><?= $it['jumlah'] ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?= number_format($it['total'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="struk-line"></div>

        <table class="table-struk">
            <tr>
                <td style="font-weight: bold;">TOTAL BELANJA</td>
                <td style="text-align: right; font-weight: bold; font-size: 1rem;">
                    Rp <?= number_format($grandTotal, 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td>TUNAI / BAYAR</td>
                <td style="text-align: right;">
                    Rp <?= number_format($uangBayar, 0, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td>KEMBALIAN</td>
                <td style="text-align: right; font-weight: bold;">
                    Rp <?= number_format($kembalian, 0, ',', '.') ?>
                </td>
            </tr>
        </table>

        <div class="struk-line"></div>

        <div style="text-align: center; font-size: 0.75rem; margin-top: 10px; color: #475569;">
            *** TERIMA KASIH ***<br>
            Atas Kunjungan Anda di <?= htmlspecialchars($toko['nama_toko']) ?><br>
            Barang yang dibeli tidak dapat ditukar/dikembalikan.
        </div>
    </div>

    <script>
        // Otomatis buka dialog cetak saat halaman struk selesai dimuat
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
