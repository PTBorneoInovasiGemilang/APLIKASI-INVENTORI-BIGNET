<?php
require_once 'app/Core/Database.php';
require_once 'app/Helpers/UIHelper.php';

$pdo = \App\Core\Database::getInstance();

$sql = "SELECT o.*, w.name as warehouse_name, u.name as processor_name 
        FROM outgoing o 
        JOIN warehouses w ON o.warehouse_id = w.id 
        JOIN users u ON o.processed_by = u.id 
        ORDER BY o.id DESC LIMIT 5";
$data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$headers = ['Tanggal', 'Gudang', 'Tujuan', 'Status', 'Diproses Oleh'];
$columns = ['created_at', 'warehouse_name', 'destination', 'status', 'processor_name'];

$output = fopen(__DIR__ . '/artifacts/outgoing_test.csv', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
fputcsv($output, $headers, ';');

$dateCols = ['created_at', 'sent_at', 'received_at', 'installed_at', 'loan_date_display', 'updated_at', 'reported_date', 'tanggal_mutasi'];
$enumCols = ['status', 'condition_status', 'type', 'transaction_type', 'action'];

foreach ($data as $row) {
    $csvRow = [];
    foreach ($columns as $col) {
        $val = $row[$col] ?? '';
        
        if (in_array($col, $dateCols, true) && $val !== '') {
            $ts = strtotime((string)$val);
            $val = $ts !== false ? date('Y-m-d H:i:s', $ts) : $val;
        }
        
        if (in_array($col, $enumCols, true)) {
            $val = \App\Helpers\UIHelper::statusLabel((string)$val);
        }
        
        $strVal = (string)$val;
        if ($strVal !== '' && in_array($strVal[0], ['=', '+', '-', '@'], true)) {
            $val = "'" . $strVal;
        }
        
        $csvRow[] = $val;
    }
    fputcsv($output, $csvRow, ';');
}
fclose($output);
echo "CSV Generated.\n";
