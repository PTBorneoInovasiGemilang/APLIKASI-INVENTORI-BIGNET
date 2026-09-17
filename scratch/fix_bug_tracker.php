<?php
$file = __DIR__ . '/../docs/BUG_TRACKER.md';
$lines = file($file, FILE_IGNORE_NEW_LINES);
$newLines = [];
foreach ($lines as $line) {
    if (strpos(str_replace(' ', '', $line), 'BUG-049') !== false) {
        continue;
    }
    $newLines[] = $line;
}
$newLines[] = "| BUG-049 | Report Laporan Transfer | Laporan Transfer `/reports/transfers` harus dapat dibuka dan menampilkan data dengan normal tanpa SQL/PHP error. | GET `/reports/transfers` sebelumnya menghasilkan HTTP 500 karena duplicate column name `receiver_name`. | HTTP 200, tabel Laporan Transfer tampil normal, search/filter/pagination/export/print tetap berfungsi. | P2 | CLOSED | Playwright `test_bug_043.js` (2026-09-18), HTTP 200 untuk SUPERADMIN dan ADMIN serta regression test report/filter/search/export. | `ReportController::transfers()` menggunakan alias unik `actual_receiver_name` dengan: `COALESCE(NULLIF(t.receiver_name, ''), ur.name)` dan konfigurasi `columns` menggunakan `actual_receiver_name`. |";
file_put_contents($file, implode("\n", $newLines) . "\n");
echo "Done\n";
