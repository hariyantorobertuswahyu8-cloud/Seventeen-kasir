<?php
require_once __DIR__ . '/../../config/config.php';
check_login();

// Filter periode
$mode = $_GET['mode'] ?? 'semua';
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
}

$sql .= " ORDER BY n.id_nota ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

// Header HTTP untuk Download file Excel (.xls)
$filename = "Laporan_Penjualan_SeventeenPetshop_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$totalOmzet = 0;
$totalModal = 0;
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="9" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #2e7d32; color: #ffffff; height: 35px;">
                LAPORAN PENJUALAN - SEVENTEEN PETSHOP
            </th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; height: 25px;">
                Periode: <?= ($mode === 'bulan') ? "Bulan $bulan Tahun $tahun" : "Semua Data Transaksi" ?> | Dicetak: <?= date('d-m-Y H:i:s') ?>
            </th>
        </tr>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th>No</th>
            <th>No. Transaksi</th>
            <th>Tanggal Transaksi</th>
            <th>Kode Barang</th>
            <th>Nama Produk Hewan</th>
            <th>Harga Beli (Rp)</th>
            <th>Harga Jual (Rp)</th>
            <th>Jumlah (Qty)</th>
            <th>Subtotal Omzet (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($data as $r): 
            $totalOmzet += (float)$r['total'];
            $totalModal += ((float)$r['harga_beli'] * (int)$r['jumlah']);
        ?>
        <tr>
            <td style="text-align: center;"><?= $no++ ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($r['no_transaksi']) ?></td>
            <td><?= htmlspecialchars($r['tanggal_input']) ?></td>
            <td style="text-align: center;"><?= htmlspecialchars($r['id_barang']) ?></td>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td style="text-align: right;"><?= number_format($r['harga_beli'], 0, ',', '.') ?></td>
            <td style="text-align: right;"><?= number_format($r['harga_jual'], 0, ',', '.') ?></td>
            <td style="text-align: center;"><?= $r['jumlah'] ?> <?= htmlspecialchars($r['satuan_barang']) ?></td>
            <td style="text-align: right; font-weight: bold;"><?= number_format($r['total'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #e8f5e9; font-weight: bold;">
            <td colspan="8" style="text-align: right;">TOTAL OMZET PENJUALAN :</td>
            <td style="text-align: right;">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></td>
        </tr>
        <tr style="background-color: #fff3e0; font-weight: bold;">
            <td colspan="8" style="text-align: right;">TOTAL MODAL POKOK (HPP) :</td>
            <td style="text-align: right;">Rp <?= number_format($totalModal, 0, ',', '.') ?></td>
        </tr>
        <tr style="background-color: #e3f2fd; font-weight: bold;">
            <td colspan="8" style="text-align: right;">ESTIMASI LABA KOTOR :</td>
            <td style="text-align: right;">Rp <?= number_format($totalOmzet - $totalModal, 0, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>

