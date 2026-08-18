<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class ReportController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        // Redirection to the first report, or a general index view.
        // Let's load the main reports index.
        require __DIR__ . '/../../resources/views/reports/index.php';
    }

    public function auditLogs() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li><strong>Log Aktivitas</strong> mencatat setiap aksi penting (login, create, update, approve, reject, dll).</li>
                <li>Gunakan filter <strong>Tanggal</strong>, <strong>Oleh (User)</strong>, dan <strong>Aksi</strong> untuk menelusuri jejak audit.</li>
                <li>Kolom <strong>Entitas</strong> menunjukkan objek yang diubah beserta ID-nya untuk penelusuran lanjutan.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Log Aktivitas',
            'base_query' => "SELECT a.*, u.name as user_name FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id",
            'headers' => ['Tanggal', 'Oleh', 'Aksi', 'Entitas', 'ID Entitas', 'IP Address'],
            'columns' => ['created_at', 'user_name', 'action', 'entity', 'entity_id', 'ip_address'],
            'date_column' => 'a.created_at',
            'user_column' => 'a.user_id',
            'action_column' => 'a.action',
            'guide_content' => $guide_content
        ]);
    }

    public function stockLedger() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li><strong>Stok Sebelum:</strong> stok sebelum transaksi</li>
                <li><strong>Jumlah Mutasi:</strong> perubahan stok</li>
                <li><strong>Stok Sesudah:</strong> stok setelah transaksi</li>
            </ul>
        ';
        
        $this->handleReport([
            'title' => 'Laporan Stok & Mutasi',
            'base_query' => "SELECT l.*, m.kode_barang, m.nama as item_name, w.name as warehouse_name, u.name as user_name 
                             FROM stock_ledgers l 
                             JOIN master_items m ON l.master_item_id = m.id 
                             JOIN warehouses w ON l.warehouse_id = w.id 
                             LEFT JOIN users u ON l.performed_by = u.id",
            'headers' => ['Tanggal', 'Kode Barang', 'Nama Barang', 'Gudang', 'Tipe Mutasi', 'Jumlah Mutasi', 'Stok Sebelum', 'Stok Sesudah', 'Diproses Oleh'],
            'columns' => ['created_at', 'kode_barang', 'item_name', 'warehouse_name', 'transaction_type', 'quantity_delta', 'balance_before', 'balance_after', 'user_name'],
            'date_column' => 'l.created_at',
            'warehouse_column' => 'l.warehouse_id',
            'category_column' => 'm.item_category_id',
            'guide_content' => $guide_content
        ]);
    }

    public function serials() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Laporan ini menampilkan setiap <strong>nomor seri</strong>, lokasi gudang, pemegang, status, dan kondisi.</li>
                <li>Filter <strong>Gudang</strong> dan <strong>Kategori</strong> tersedia untuk mempersempit data.</li>
                <li>Status serial dijamin <strong>unik</strong> (tidak ada double assignment) oleh invariant sistem.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Nomor Seri',
            'base_query' => "SELECT sn.*, m.kode_barang, m.nama as item_name, w.name as warehouse_name, u.name as holder_name 
                             FROM serial_numbers sn 
                             JOIN master_items m ON sn.master_item_id = m.id 
                             LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id 
                             LEFT JOIN users u ON sn.current_holder_id = u.id",
            'headers' => ['No. Seri', 'Kode Barang', 'Nama Barang', 'Gudang', 'Pemegang', 'Status', 'Kondisi'],
            'columns' => ['serial_number', 'kode_barang', 'item_name', 'warehouse_name', 'holder_name', 'status', 'condition_status'],
            'date_column' => 'sn.created_at',
            'warehouse_column' => 'sn.current_warehouse_id',
            'category_column' => 'm.item_category_id',
            'guide_content' => $guide_content
        ]);
    }

    public function submissions() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Menampilkan seluruh <strong>pengajuan barang</strong> dari teknisi beserta status persetujuannya.</li>
                <li>Alur: <strong>Draft → Submit → Pending Approval → Approved/Rejected → Fulfilled</strong>.</li>
                <li>Penolakan selalu menyimpan aktor, waktu, dan alasan (accountability).</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Pengajuan',
            'base_query' => "SELECT s.*, u.name as submitter_name, a.name as approver_name 
                             FROM submissions s 
                             JOIN users u ON s.submitted_by = u.id 
                             LEFT JOIN users a ON s.approved_by = a.id",
            'headers' => ['Tanggal', 'Kode Pengajuan', 'Diajukan Oleh', 'Status', 'Disetujui Oleh', 'Catatan'],
            'columns' => ['created_at', 'submission_code', 'submitter_name', 'status', 'approver_name', 'notes'],
            'date_column' => 's.created_at',
            'guide_content' => $guide_content
        ]);
    }

    public function incoming() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Mencatat seluruh <strong>penerimaan barang</strong> dari supplier/vendor ke gudang.</li>
                <li>Setiap penerimaan meningkatkan stok dan mencatat <strong>movement + timeline</strong>.</li>
                <li>Barang serialized wajib mencatat nomor seri pada penerimaan.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Barang Masuk',
            'base_query' => "SELECT i.*, w.name as warehouse_name, u.name as receiver_name 
                             FROM incoming i 
                             JOIN warehouses w ON i.warehouse_id = w.id 
                             JOIN users u ON i.received_by = u.id",
            'headers' => ['Tanggal', 'Kode Transaksi', 'Gudang', 'Diterima Oleh', 'Supplier', 'Status'],
            'columns' => ['created_at', 'transaction_code', 'warehouse_name', 'receiver_name', 'supplier_info', 'status'],
            'date_column' => 'i.created_at',
            'warehouse_column' => 'i.warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    public function outgoing() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Mencatat seluruh <strong>pengeluaran barang</strong> dari gudang (issue/custody).</li>
                <li>Pengurangan stok tidak pernah terjadi tanpa transaksi penjelas (inventory integrity).</li>
                <li>Setiap pengeluaran tercatat sebagai <strong>movement + audit + timeline</strong>.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Barang Keluar',
            'base_query' => "SELECT o.*, w.name as warehouse_name, u.name as processor_name, s.submission_code 
                             FROM outgoing o 
                             JOIN warehouses w ON o.warehouse_id = w.id 
                             JOIN users u ON o.processed_by = u.id 
                             LEFT JOIN submissions s ON o.submission_id = s.id",
            'headers' => ['Tanggal', 'Kode Transaksi', 'Gudang', 'Diproses Oleh', 'Ref Pengajuan'],
            'columns' => ['created_at', 'transaction_code', 'warehouse_name', 'processor_name', 'submission_code'],
            'date_column' => 'o.created_at',
            'warehouse_column' => 'o.warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    public function transfers() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Transfer adalah <strong>perpindahan antar gudang</strong>, bukan incoming/outgoing palsu.</li>
                <li>Ledger mencegah <strong>double counting</strong> pada mutasi transfer.</li>
                <li>Status perjalanan: <strong>Initiated → In Transit → Received</strong>.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Transfer',
            'base_query' => "SELECT t.*, sw.name as source_warehouse, dw.name as destination_warehouse, us.name as sender_name, ur.name as receiver_name 
                             FROM transfers t 
                             JOIN warehouses sw ON t.source_warehouse_id = sw.id 
                             JOIN warehouses dw ON t.destination_warehouse_id = dw.id 
                             JOIN users us ON t.sent_by = us.id 
                             LEFT JOIN users ur ON t.received_by = ur.id",
            'headers' => ['Tanggal Kirim', 'Tanggal Terima', 'Kode Transaksi', 'Gudang Asal', 'Gudang Tujuan', 'Status', 'Pengirim', 'Penerima'],
            'columns' => ['sent_at', 'received_at', 'transaction_code', 'source_warehouse', 'destination_warehouse', 'status', 'sender_name', 'receiver_name'],
            'date_column' => 't.sent_at',
            'warehouse_column' => 't.source_warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    public function loans() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Peminjaman mencatat <strong>peminjam, tanggal pinjam, batas waktu, dan status</strong>.</li>
                <li>Barang serialized dilacak sampai tingkat nomor seri (custody).</li>
                <li>Pinjaman aktif terlihat dari status <strong>Dipinjam / Kembali Sebagian</strong>.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Peminjaman',
            'base_query' => "SELECT l.*, COALESCE(l.loan_date, l.created_at) as loan_date_display, u.name as borrower_name, w.name as warehouse_name, p.name as processor_name 
                             FROM loans l 
                             JOIN users u ON l.borrower_id = u.id 
                             JOIN warehouses w ON l.warehouse_id = w.id 
                             JOIN users p ON l.processed_by = p.id",
            'headers' => ['Tanggal Pinjam', 'Batas Waktu', 'Kode Transaksi', 'Peminjam', 'Gudang', 'Diproses Oleh', 'Status'],
            'columns' => ['loan_date_display', 'due_date', 'transaction_code', 'borrower_name', 'warehouse_name', 'processor_name', 'status'],
            'date_column' => 'COALESCE(l.loan_date, l.created_at)',
            'warehouse_column' => 'l.warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    public function returns() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Mencatat <strong>pengembalian barang pinjaman</strong> beserta kondisi saat kembali.</li>
                <li>Inspeksi menentukan langkah berikutnya: kembali ke stok, perbaikan, atau damage/lost.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Pengembalian',
            'base_query' => "SELECT r.*, w.name as warehouse_name, u.name as returner_name, l.transaction_code as loan_code 
                             FROM returns r 
                             JOIN warehouses w ON r.warehouse_id = w.id 
                             JOIN users u ON r.processed_by = u.id 
                             LEFT JOIN loans l ON r.reference_id = l.id AND r.reference_type = 'LOAN'",
            'headers' => ['Tanggal', 'Kode Transaksi', 'Gudang', 'Diproses Oleh', 'Ref Peminjaman'],
            'columns' => ['created_at', 'transaction_code', 'warehouse_name', 'returner_name', 'loan_code'],
            'date_column' => 'r.created_at',
            'warehouse_column' => 'r.warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    public function installations() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Mencatat <strong>pemasangan/instalasi barang</strong> di lapangan oleh teknisi.</li>
                <li>Status barang berubah menjadi <strong>Terpasang</strong> dengan lokasi terpasang.</li>
                <li>Bukti foto instalasi tersimpan sebagai evidence.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Instalasi',
            'base_query' => "SELECT i.*, u.name as technician_name FROM installations i JOIN users u ON i.technician_id = u.id",
            'headers' => ['Tanggal Instalasi', 'Kode', 'Pelanggan/Site', 'Teknisi', 'Status'],
            'columns' => ['installed_at', 'transaction_code', 'customer_site', 'technician_name', 'status'],
            'date_column' => 'i.installed_at',
            'guide_content' => $guide_content
        ]);
    }

    public function adjustments() {
        $guide_content = '
            <ul class="list-disc pl-4 space-y-1">
                <li>Penyesuaian adalah <strong>koreksi stok yang diaudit</strong> dan wajib memiliki reason/reference.</li>
                <li>Stock Opname ≠ Adjustment: opname menghitung selisih, adjustment mengeksekusi koreksi setelah approval.</li>
            </ul>
        ';

        $this->handleReport([
            'title' => 'Laporan Penyesuaian',
            'base_query' => "SELECT a.*, w.name as warehouse_name, u.name as user_name 
                             FROM stock_adjustments a 
                             JOIN warehouses w ON a.warehouse_id = w.id 
                             JOIN users u ON a.processed_by = u.id",
            'headers' => ['Tanggal', 'Kode', 'Gudang', 'Diproses Oleh', 'Alasan'],
            'columns' => ['created_at', 'transaction_code', 'warehouse_name', 'user_name', 'reason'],
            'date_column' => 'a.created_at',
            'warehouse_column' => 'a.warehouse_id',
            'guide_content' => $guide_content
        ]);
    }

    private function handleReport($config) {
        $headers = $config['headers'] ?? [];
        $columns = $config['columns'] ?? [];
        $pdo = Database::getInstance()->getConnection();
        
        $sql = $config['base_query'];
        $where = [];
        $params = [];
        
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $warehouseId = $_GET['warehouse_id'] ?? '';
        $categoryId = $_GET['category_id'] ?? '';
        $userId = $_GET['user_id'] ?? '';
        $actionType = $_GET['action_type'] ?? '';
        $search = trim($_GET['search'] ?? '');
        
        // Date filter
        $dateColumn = $config['date_column'] ?? 'created_at';
        if (!empty($startDate)) {
            $where[] = "DATE({$dateColumn}) >= ?";
            $params[] = $startDate;
        }
        if (!empty($endDate)) {
            $where[] = "DATE({$dateColumn}) <= ?";
            $params[] = $endDate;
        }
        
        // Warehouse filter (if table or join has warehouse_id)
        if (!empty($warehouseId) && isset($config['warehouse_column'])) {
            $where[] = "{$config['warehouse_column']} = ?";
            $params[] = $warehouseId;
        }

        // Category filter (if applicable)
        if (!empty($categoryId) && isset($config['category_column'])) {
            $where[] = "{$config['category_column']} = ?";
            $params[] = $categoryId;
        }

        // User filter
        if (!empty($userId) && isset($config['user_column'])) {
            $where[] = "{$config['user_column']} = ?";
            $params[] = $userId;
        }

        // Action filter
        if (!empty($actionType) && isset($config['action_column'])) {
            $where[] = "{$config['action_column']} = ?";
            $params[] = $actionType;
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = " WHERE " . implode(" AND ", $where);
        }
        
        // Free-text search dijalankan pada kolom HASIL query (derived table),
        // sehingga nama kolom seperti created_at tidak ambigu walau ada JOIN.
        $searchWhere = '';
        if ($search !== '') {
            $searchClauses = [];
            foreach ($columns as $col) {
                $searchClauses[] = "CAST(`" . str_replace('`', '', $col) . "` AS CHAR) LIKE ?";
            }
            if (count($searchClauses) > 0) {
                $searchWhere = " WHERE (" . implode(" OR ", $searchClauses) . ")";
                foreach ($columns as $col) {
                    $params[] = "%$search%";
                }
            }
        }
        
        $innerSql = $config['base_query'] . $whereSql;
        $derivedSql = "SELECT * FROM ({$innerSql}) AS report_rows" . $searchWhere;
        
        // --- Selection (server-validated) ---
        $rawSelected = $_GET['selected_ids'] ?? [];
        if (!is_array($rawSelected)) {
            $rawSelected = ($rawSelected !== '') ? explode(',', $rawSelected) : [];
        }
        $selectedIds = array_values(array_filter(array_map('intval', $rawSelected), fn($v) => $v > 0));
        $selectAllFiltered = !empty($_GET['select_all_filtered']);
        $printSelected = $_GET['print_selected'] ?? '';
        
        $isExport = $_GET['export'] ?? '';
        
        if ($isExport === 'csv' || $isExport === 'pdf') {
            if (!$selectAllFiltered && count($selectedIds) > 0) {
                // Export HANYA baris terpilih
                $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                $sql = "SELECT * FROM ({$derivedSql}) AS export_rows WHERE export_rows.id IN ({$placeholders}) ORDER BY export_rows." . ($config['order_by'] ?? 'id DESC');
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_merge($params, $selectedIds));
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Export semua baris yang terfilter (tanpa pagination)
                $sql = $derivedSql . " ORDER BY report_rows." . ($config['order_by'] ?? "id DESC");
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // Inject Row Numbering for Exports
            $rowIndex = 1;
            foreach ($data as &$row) {
                $row['__no'] = $rowIndex++;
            }
            unset($row);
            
            array_unshift($config['headers'], 'No');
            array_unshift($config['columns'], '__no');

            if ($isExport === 'csv') {
                $this->exportToCsv($config['title'], $config['headers'], $config['columns'], $data);
            } else {
                $this->exportToPdf($config, $data);
            }
            exit;
        }
        
        // Cetak terpilih: render halaman dengan HANYA baris terpilih + auto print
        $autoPrint = false;
        if ($printSelected === '1') {
            if (!$selectAllFiltered && count($selectedIds) > 0) {
                $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                $sql = "SELECT * FROM ({$derivedSql}) AS print_rows WHERE print_rows.id IN ({$placeholders}) ORDER BY print_rows." . ($config['order_by'] ?? 'id DESC');
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_merge($params, $selectedIds));
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $totalItems = count($data);
                $totalPages = 1;
                $page = 1;
                $limit = max(1, $totalItems);
                $offset = 0;
                $autoPrint = true;
            }
            // tanpa pilihan: fallback ke render normal (JS warning di client)
        }
        
        if (!$autoPrint) {
            // Pagination
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = (int)($config['per_page'] ?? 15);
            $offset = ($page - 1) * $limit;
            
            $countSql = "SELECT COUNT(*) FROM ({$derivedSql}) AS report_count";
            $stmtCount = $pdo->prepare($countSql);
            $stmtCount->execute($params);
            $totalItems = (int)$stmtCount->fetchColumn();
            $totalPages = max(1, (int)ceil($totalItems / $limit));
            
            $sql = $derivedSql . " ORDER BY report_rows." . ($config['order_by'] ?? "id DESC") . " LIMIT {$limit} OFFSET {$offset}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $warehouses = $pdo->query("SELECT id, name FROM warehouses WHERE is_active = 1")->fetchAll();
        $categories = $pdo->query("SELECT id, name FROM item_categories WHERE is_active = 1")->fetchAll();
        $users = $pdo->query("SELECT id, name FROM users")->fetchAll();
        
        $actions = [];
        if (isset($config['action_column'])) {
            if (stripos($config['base_query'], 'audit_logs') !== false) {
                $actStmt = $pdo->query("SELECT DISTINCT action FROM audit_logs WHERE action IS NOT NULL");
                $actions = $actStmt->fetchAll(PDO::FETCH_COLUMN);
            }
        }
        
        if (isset($config['guide_content'])) {
            $guide_content = $config['guide_content'];
        }
        
        require __DIR__ . '/../../resources/views/reports/report_viewer.php';
    }

    private function exportToPdf($config, array $data) {
        $title = $config['title'];
        $headers = $config['headers'];
        $columns = $config['columns'];
        $user = \App\Helpers\AuthHelper::user();
        $actor = ($user['name'] ?? 'Sistem') . ' (' . \App\Helpers\UIHelper::roleLabel($user['role'] ?? 'Unknown') . ')';

        // Prepare structured metadata
        $metadata = [];
        $metadata['Dicetak Oleh'] = $actor;
        $metadata['Tanggal Cetak'] = date('d F Y H:i');
        
        $filters = [];
        if (!empty($_GET['start_date'])) $filters[] = 'Dari ' . $_GET['start_date'];
        if (!empty($_GET['end_date'])) $filters[] = 'Sampai ' . $_GET['end_date'];
        if (!empty($_GET['search'])) $filters[] = 'Pencarian: ' . $_GET['search'];
        
        if (!empty($_GET['warehouse_id'])) {
             // Retrieve warehouse name if possible, or just ID
             $pdo = \App\Core\Database::getInstance();
             $wstmt = $pdo->prepare("SELECT name FROM warehouses WHERE id = ?");
             $wstmt->execute([$_GET['warehouse_id']]);
             $wname = $wstmt->fetchColumn();
             $filters[] = 'Gudang: ' . ($wname ?: $_GET['warehouse_id']);
        }
        
        $metadata['Periode/Filter'] = empty($filters) ? 'Semua Data' : implode(', ', $filters);
        $metadata['Total Data'] = count($data) . ' baris';

        $pdf = \App\Helpers\PdfHelper::tableReport($title, $headers, $data, $metadata, $columns);

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_His') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    private function exportToCsv($title, $headers, $columns, $data) {
        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_His') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // UTF-8 BOM untuk kompatibilitas Excel Windows
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers (flat, machine-readable)
        fputcsv($output, $headers, ';');
        
        // Date columns
        $dateCols = ['created_at', 'sent_at', 'received_at', 'installed_at', 'loan_date_display', 'updated_at', 'reported_date', 'tanggal_mutasi'];
        // Enum columns
        $enumCols = ['status', 'condition_status', 'type', 'transaction_type', 'action'];

        // Write data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($columns as $col) {
                $val = $row[$col] ?? '';
                
                // 1. Tanggal handling
                if (in_array($col, $dateCols, true) && $val !== '') {
                    $ts = strtotime((string)$val);
                    $val = $ts !== false ? date('Y-m-d H:i:s', $ts) : $val;
                }
                
                // 2. Enum translation
                if (in_array($col, $enumCols, true)) {
                    $val = \App\Helpers\UIHelper::statusLabel((string)$val);
                }
                
                // 3. Prevent Excel Formula Injection
                $strVal = (string)$val;
                if ($strVal !== '' && in_array($strVal[0], ['=', '+', '-', '@'], true)) {
                    $val = "'" . $strVal;
                }
                
                $csvRow[] = $val;
            }
            fputcsv($output, $csvRow, ';');
        }
        
        fclose($output);
        exit;
    }
}
