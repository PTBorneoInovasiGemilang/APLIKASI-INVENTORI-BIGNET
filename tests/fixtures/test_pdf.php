<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../';
require __DIR__ . '/../../app/Core/Database.php';
require __DIR__ . '/../../app/Helpers/PdfHelper.php';

$pdo = \App\Core\Database::getInstance();
$sql = "SELECT o.*, w.name as warehouse_name, u.name as processor_name, s.submission_code 
        FROM outgoing o 
        JOIN warehouses w ON o.warehouse_id = w.id 
        JOIN users u ON o.processed_by = u.id 
        LEFT JOIN submissions s ON o.submission_id = s.id
        ORDER BY o.id DESC LIMIT 100";
$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$title = 'Laporan Barang Keluar';
$headers = ['Tanggal', 'Kode Transaksi', 'Gudang', 'Diproses Oleh', 'Ref Pengajuan'];
$columns = ['created_at', 'transaction_code', 'warehouse_name', 'processor_name', 'submission_code'];

$metadata = [];
$metadata['Dicetak Oleh'] = 'Superadmin (SUPERADMIN)';
$metadata['Tanggal Cetak'] = date('d F Y H:i');
$metadata['Periode/Filter'] = 'Semua Data';
$metadata['Total Data'] = count($data) . ' baris';

$pdf = \App\Helpers\PdfHelper::tableReport($title, $headers, $data, $metadata, $columns);
file_put_contents(__DIR__ . '/../../tests/artifacts/outgoing_test.pdf', $pdf);
echo "PDF Generated at tests/artifacts/outgoing_test.pdf\n";
