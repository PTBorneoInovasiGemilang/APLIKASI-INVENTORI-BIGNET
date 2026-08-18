<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../';
require __DIR__ . '/../../app/Core/Database.php';
require __DIR__ . '/../../app/Helpers/PdfHelper.php';

$pdo = \App\Core\Database::getInstance();

// 1. Laporan Penyesuaian
$sql = "SELECT sa.*, w.name as warehouse_name, m.kode_barang, m.nama as item_name, u.name as processor_name
        FROM stock_adjustments sa
        JOIN warehouses w ON sa.warehouse_id = w.id
        JOIN master_items m ON sa.master_item_id = m.id
        JOIN users u ON sa.processed_by = u.id
        ORDER BY sa.id DESC LIMIT 100";
$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$title = 'Laporan Penyesuaian Stok';
$headers = ['Tanggal', 'Gudang', 'Barang', 'Tipe', 'Qty', 'Alasan', 'Diproses Oleh'];
$columns = ['created_at', 'warehouse_name', 'item_name', 'type', 'quantity', 'reason', 'processor_name'];
$metadata = ['Dicetak Oleh' => 'Superadmin (SUPERADMIN)', 'Tanggal Cetak' => date('d F Y H:i'), 'Periode/Filter' => 'Semua Data', 'Total Data' => count($data) . ' baris'];
$pdf = \App\Helpers\PdfHelper::tableReport($title, $headers, $data, $metadata, $columns);
file_put_contents(__DIR__ . '/../../tests/artifacts/adjustments_test.pdf', $pdf);

// 2. Laporan Stok & Mutasi (Mutasi / Ledger)
$sql = "SELECT sl.*, m.kode_barang, m.nama as item_name, w.name as warehouse_name
        FROM stock_ledgers sl
        JOIN master_items m ON sl.master_item_id = m.id
        JOIN warehouses w ON sl.warehouse_id = w.id
        ORDER BY sl.id DESC LIMIT 100";
$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$title = 'Laporan Stok & Mutasi';
$headers = ['Tanggal', 'Referensi', 'Gudang', 'Barang', 'Perubahan', 'Sisa Stok'];
$columns = ['created_at', 'reference_code', 'warehouse_name', 'item_name', 'quantity_change', 'ending_stock'];
$metadata = ['Dicetak Oleh' => 'Superadmin (SUPERADMIN)', 'Tanggal Cetak' => date('d F Y H:i'), 'Total Data' => count($data) . ' baris'];
$pdf = \App\Helpers\PdfHelper::tableReport($title, $headers, $data, $metadata, $columns);
file_put_contents(__DIR__ . '/../../tests/artifacts/mutasi_test.pdf', $pdf);

echo "PDFs generated.\n";
