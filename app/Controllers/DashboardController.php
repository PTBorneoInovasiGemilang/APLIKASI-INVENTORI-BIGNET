<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class DashboardController {

    public function index() {
        AuthHelper::requireLogin();

        $user = AuthHelper::user();
        $pdo = Database::getInstance()->getConnection();

        $stats = [
            'total_items' => 0,
            'low_stock' => 0,
            'active_submissions' => 0,
            'active_loans' => 0,
            'incoming_today' => 0,
            'outgoing_today' => 0,
            'transfers_in_transit' => 0,
            'installed_assets' => 0,
            'open_incidents' => 0
        ];

        $today = date('Y-m-d');

        if (AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            // ===== KPI =====
            $stats['total_items'] = (int)$pdo->query("SELECT COUNT(*) FROM master_items WHERE is_active = 1")->fetchColumn();
            $stats['total_stock'] = (int)$pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM stocks")->fetchColumn();
            $stats['low_stock'] = (int)$pdo->query("SELECT COUNT(DISTINCT s.master_item_id) FROM stocks s JOIN master_items m ON s.master_item_id = m.id WHERE s.quantity <= m.stok_minimum")->fetchColumn();
            $stats['active_submissions'] = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status = 'PENDING'")->fetchColumn();
            $stats['active_loans'] = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'BORROWED'")->fetchColumn();
            $stats['incoming_today'] = (int)$pdo->query("SELECT COUNT(*) FROM incoming WHERE DATE(created_at) = '$today'")->fetchColumn();
            $stats['outgoing_today'] = (int)$pdo->query("SELECT COUNT(*) FROM outgoing WHERE DATE(created_at) = '$today'")->fetchColumn();
            $stats['transfers_in_transit'] = (int)$pdo->query("SELECT COUNT(*) FROM transfers WHERE status = 'IN_TRANSIT'")->fetchColumn();
            $stats['installed_assets'] = (int)$pdo->query("SELECT COUNT(*) FROM installations WHERE status = 'INSTALLED'")->fetchColumn();
            $stats['open_incidents'] = (int)$pdo->query("SELECT COUNT(*) FROM incidents WHERE status = 'PENDING'")->fetchColumn();

            // ===== Trend (6 bulan terakhir) =====
            $trends = $this->monthlyTrend($pdo, [
                'incoming' => 'incoming',
                'outgoing' => 'outgoing',
                'transfer' => 'transfers'
            ]);

            // ===== Pergerakan terakhir (stock ledger) =====
            $recentLedger = $pdo->query("
                SELECT l.*, m.nama as item_name, m.kode_barang, w.name as warehouse_name, u.name as user_name
                FROM stock_ledgers l
                JOIN master_items m ON l.master_item_id = m.id
                JOIN warehouses w ON l.warehouse_id = w.id
                LEFT JOIN users u ON l.performed_by = u.id
                ORDER BY l.created_at DESC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Stok rendah (list) =====
            $lowStockList = $pdo->query("
                SELECT s.master_item_id, s.warehouse_id, m.nama, m.kode_barang, m.is_serialized,
                       s.quantity, m.stok_minimum, w.name as warehouse_name
                FROM stocks s
                JOIN master_items m ON s.master_item_id = m.id
                JOIN warehouses w ON s.warehouse_id = w.id
                WHERE s.quantity <= m.stok_minimum
                ORDER BY (s.quantity - m.stok_minimum) ASC
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Prediksi stok & rekomendasi restock =====
            // Dasar prediksi: Barang Keluar 30 hari terakhir.
            // Tidak menghitung Peminjaman karena barang pinjaman masih dianggap bagian dari stok fisik.
            $restockRecommendations = [];
            $stockForecastRows = $pdo->query("
                SELECT
                    s.warehouse_id,
                    w.name AS warehouse_name,
                    m.id AS master_item_id,
                    m.nama AS item_name,
                    m.kode_barang,
                    m.is_serialized,
                    m.stok_minimum,
                    u.name AS unit_name,
                    s.quantity AS stock_quantity,
                    COALESCE((
                        SELECT SUM(oi.quantity)
                        FROM outgoing_items oi
                        JOIN outgoing o ON o.id = oi.outgoing_id
                        WHERE oi.master_item_id = m.id
                          AND o.warehouse_id = s.warehouse_id
                          AND o.status = 'COMPLETED'
                          AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ), 0) AS outgoing_30d
                FROM stocks s
                JOIN warehouses w ON w.id = s.warehouse_id
                JOIN master_items m ON m.id = s.master_item_id
                LEFT JOIN units u ON u.id = m.unit_id
                WHERE m.is_active = 1
                ORDER BY w.name ASC, m.nama ASC
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($stockForecastRows as $forecast) {
                $physical = (int)$forecast['stock_quantity'];

                if ((int)$forecast['is_serialized'] === 1) {
                    $physical = (int)$pdo->query(
                        "SELECT COUNT(*) FROM serial_numbers
                         WHERE master_item_id = " . (int)$forecast['master_item_id'] .
                         " AND current_warehouse_id = " . (int)$forecast['warehouse_id']
                    )->fetchColumn();
                } else {
                    // Peminjaman tidak mengurangi stok fisik.
                    $borrowed = (int)$pdo->query(
                        "SELECT COALESCE(SUM(li.quantity - li.returned_quantity), 0)
                         FROM loan_items li
                         JOIN loans l ON l.id = li.loan_id
                         WHERE li.master_item_id = " . (int)$forecast['master_item_id'] .
                         " AND l.warehouse_id = " . (int)$forecast['warehouse_id'] .
                         " AND l.status IN ('BORROWED','PARTIAL_RETURNED')"
                    )->fetchColumn();
                    $physical = max(0, $physical - $borrowed);
                }

                $outgoing30d = (int)$forecast['outgoing_30d'];
                $avgDaily = $outgoing30d / 30;
                $daysUntilEmpty = $avgDaily > 0 ? floor($physical / $avgDaily) : null;
                $minimum = max(0, (int)$forecast['stok_minimum']);

                // Target persediaan 30 hari; minimal tetap mengikuti batas minimum Master Barang.
                $target30d = max($minimum, (int)ceil($avgDaily * 30));
                $recommendedQty = max(0, $target30d - $physical);

                $needsRestock = $physical <= $minimum && $minimum > 0;
                $criticalForecast = $daysUntilEmpty !== null && $daysUntilEmpty <= 7;
                $activeRecommendation = $needsRestock || $criticalForecast;

                if (!$activeRecommendation) {
                    continue;
                }

                $restockRecommendations[] = [
                    'warehouse_id' => (int)$forecast['warehouse_id'],
                    'warehouse_name' => $forecast['warehouse_name'],
                    'master_item_id' => (int)$forecast['master_item_id'],
                    'item_name' => $forecast['item_name'],
                    'kode_barang' => $forecast['kode_barang'],
                    'unit_name' => $forecast['unit_name'] ?: 'unit',
                    'physical' => $physical,
                    'minimum' => $minimum,
                    'outgoing_30d' => $outgoing30d,
                    'avg_daily' => round($avgDaily, 2),
                    'days_until_empty' => $daysUntilEmpty,
                    'target_30d' => $target30d,
                    'recommended_qty' => $recommendedQty,
                    'critical' => $criticalForecast || ($needsRestock && $physical === 0)
                ];
            }

            usort($restockRecommendations, function ($a, $b) {
                if ($a['critical'] !== $b['critical']) return $a['critical'] ? -1 : 1;
                if (($a['days_until_empty'] ?? PHP_INT_MAX) !== ($b['days_until_empty'] ?? PHP_INT_MAX)) {
                    return ($a['days_until_empty'] ?? PHP_INT_MAX) <=> ($b['days_until_empty'] ?? PHP_INT_MAX);
                }
                return $b['recommended_qty'] <=> $a['recommended_qty'];
            });
            $restockRecommendations = array_slice($restockRecommendations, 0, 8);

            // ===== Barang paling sering keluar =====
            // Ranking berdasarkan total kuantitas aktual pada transaksi Barang Keluar.
            $topOutgoingItems = $pdo->query("
                SELECT m.id, m.nama, m.kode_barang, u.name AS unit_name,
                       COALESCE(SUM(oi.quantity), 0) AS outgoing_quantity,
                       COUNT(DISTINCT oi.outgoing_id) AS outgoing_transactions,
                       MAX(o.created_at) AS last_outgoing_at
                FROM outgoing_items oi
                JOIN outgoing o ON o.id = oi.outgoing_id
                JOIN master_items m ON m.id = oi.master_item_id
                LEFT JOIN units u ON u.id = m.unit_id
                WHERE o.status = 'COMPLETED'
                GROUP BY m.id, m.nama, m.kode_barang, u.name
                ORDER BY outgoing_quantity DESC, outgoing_transactions DESC, last_outgoing_at DESC, m.nama ASC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Teknisi paling sering mengajukan =====
            // Ranking berdasarkan seluruh pengajuan yang masih tersimpan, khusus akun TEKNISI_NOC.
            $topSubmitters = $pdo->query("
                SELECT u.id, u.name,
                       COUNT(s.id) AS submission_count,
                       MAX(s.created_at) AS last_submission_at
                FROM users u
                JOIN submissions s ON s.submitted_by = u.id
                WHERE u.role = 'TEKNISI_NOC'
                GROUP BY u.id, u.name
                ORDER BY submission_count DESC, last_submission_at DESC, u.name ASC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Pengajuan aktif (list) =====
            $activeSubmissions = $pdo->query("
                SELECT s.id, s.submission_code, s.status, s.created_at, u.name as submitter_name
                FROM submissions s
                JOIN users u ON s.submitted_by = u.id
                WHERE s.status = 'PENDING'
                ORDER BY s.created_at DESC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Outstanding return (peminjaman belum dikembalikan) =====
            $outstandingLoans = $pdo->query("
                SELECT l.id, l.transaction_code, l.loan_date, l.due_date, l.status,
                       u.name as borrower_name, w.name as warehouse_name
                FROM loans l
                JOIN users u ON l.borrower_id = u.id
                JOIN warehouses w ON l.warehouse_id = w.id
                WHERE l.status = 'BORROWED'
                ORDER BY l.due_date ASC
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
            $overdueCount = 0;
            foreach ($outstandingLoans as &$loan) {
                $loan['is_overdue'] = !empty($loan['due_date']) && $loan['due_date'] < $today;
                if ($loan['is_overdue']) $overdueCount++;
            }
            unset($loan);

            // ===== Installed asset (list) =====
            $recentInstallations = $pdo->query("
                SELECT i.id, i.transaction_code, i.customer_name, i.customer_site, i.installed_at, i.status,
                       u.name as technician_name
                FROM installations i
                JOIN users u ON i.technician_id = u.id
                WHERE i.status = 'INSTALLED'
                ORDER BY i.installed_at DESC
                LIMIT 6
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Incidents / Damage-Lost =====
            $recentIncidents = $pdo->query("
                SELECT i.id, i.transaction_code, i.incident_type, i.status, i.reported_date, i.warehouse_id,
                       u.name as reporter_name, w.name as warehouse_name
                FROM incidents i
                LEFT JOIN users u ON i.reported_by = u.id
                LEFT JOIN warehouses w ON i.warehouse_id = w.id
                ORDER BY i.reported_date DESC
                LIMIT 6
            ")->fetchAll(PDO::FETCH_ASSOC);

            // ===== Alerts / anomaly =====
            $alerts = [];
            if ($stats['low_stock'] > 0) {
                $alerts[] = ['level' => 'danger', 'icon' => 'fa-exclamation-triangle', 'title' => 'Stok Rendah', 'desc' => $stats['low_stock'] . ' jenis barang memiliki stok di bawah/sama dengan batas minimum.', 'url' => url('/stocks?filter=low_stock')];
            }
            if ($overdueCount > 0) {
                $alerts[] = ['level' => 'danger', 'icon' => 'fa-clock', 'title' => 'Peminjaman Terlambat', 'desc' => $overdueCount . ' peminjaman melewati jatuh tempo.', 'url' => url('/loans')];
            }
            if ($stats['open_incidents'] > 0) {
                $alerts[] = ['level' => 'warning', 'icon' => 'fa-exclamation-circle', 'title' => 'Insiden Terbuka', 'desc' => $stats['open_incidents'] . ' laporan Damage/Lost menunggu review.', 'url' => url('/incidents?status=PENDING')];
            }
            if ($stats['transfers_in_transit'] > 0) {
                $alerts[] = ['level' => 'warning', 'icon' => 'fa-truck', 'title' => 'Transfer Dalam Perjalanan', 'desc' => $stats['transfers_in_transit'] . ' transfer menunggu penerimaan.', 'url' => url('/transfers')];
            }
            if ($stats['active_submissions'] > 0) {
                $alerts[] = ['level' => 'info', 'icon' => 'fa-clipboard-list', 'title' => 'Pengajuan Perlu Diproses', 'desc' => $stats['active_submissions'] . ' pengajuan berstatus aktif.', 'url' => url('/submissions')];
            }

            // ===== Quick actions per role =====
            if (AuthHelper::hasRole('SUPERADMIN')) {
                // Super Admin: fokus monitoring/system, bukan CRUD operasional gudang
                $quickActions = [
                    ['icon' => 'fa-users-cog', 'label' => 'Kelola Pengguna', 'desc' => 'User, role & approval', 'url' => url('/users'), 'color' => 'indigo'],
                    ['icon' => 'fa-cogs', 'label' => 'Pengaturan Sistem', 'desc' => 'Branding & keamanan', 'url' => url('/settings'), 'color' => 'gray'],
                    ['icon' => 'fa-shield-alt', 'label' => 'Log Aktivitas', 'desc' => 'Jejak audit sistem', 'url' => url('/reports/audit'), 'color' => 'slate'],
                    ['icon' => 'fa-chart-bar', 'label' => 'Laporan', 'desc' => 'Semua laporan operasional', 'url' => url('/reports'), 'color' => 'blue'],
                    ['icon' => 'fa-history', 'label' => 'Timeline', 'desc' => 'Riwayat pergerakan barang', 'url' => url('/timeline'), 'color' => 'purple'],
                    ['icon' => 'fa-search', 'label' => 'Pencarian Barang', 'desc' => 'Cari serial / kode / nama', 'url' => url('/search'), 'color' => 'teal'],
                ];
            } else {
                $quickActions = [
                    ['icon' => 'fa-arrow-down', 'label' => 'Barang Masuk', 'desc' => 'Terima inventory', 'url' => url('/incoming/create'), 'color' => 'green'],
                    ['icon' => 'fa-arrow-up', 'label' => 'Barang Keluar', 'desc' => 'Issue / custody', 'url' => url('/outgoing/create'), 'color' => 'red'],
                    ['icon' => 'fa-exchange-alt', 'label' => 'Transfer Gudang', 'desc' => 'Pindah antar gudang', 'url' => url('/transfers/create'), 'color' => 'yellow'],
                    ['icon' => 'fa-clipboard-check', 'label' => 'Proses Pengajuan', 'desc' => 'Approve request teknisi', 'url' => url('/submissions'), 'color' => 'blue'],
                    ['icon' => 'fa-clipboard-list', 'label' => 'Stock Opname', 'desc' => 'Hitung fisik vs sistem', 'url' => url('/opnames/create'), 'color' => 'indigo'],
                    ['icon' => 'fa-exclamation-triangle', 'label' => 'Damage / Lost', 'desc' => 'Lapor insiden aset', 'url' => url('/incidents/create'), 'color' => 'orange'],
                ];
            }

            // ===== Recent activity (audit) =====
            $stmt = $pdo->query("SELECT a.*, u.name as actor_name, u.role as actor_role 
                                 FROM audit_logs a 
                                 JOIN users u ON a.user_id = u.id 
                                 WHERE a.action NOT IN ('LOGIN', 'LOGOUT') 
                                 ORDER BY a.created_at DESC LIMIT 10");
            $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $teknis_view = false;
        } else {
            // ===== TEKNISI_NOC Stats (personal) =====
            $stats['active_submissions'] = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE status IN ('PENDING', 'PARTIAL_APPROVED') AND submitted_by = " . (int)$user['id'])->fetchColumn();
            $stats['total_submissions'] = (int)$pdo->query("SELECT COUNT(*) FROM submissions WHERE submitted_by = " . (int)$user['id'])->fetchColumn();
            $stats['active_loans'] = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'BORROWED' AND borrower_id = " . (int)$user['id'])->fetchColumn();
            $stats['held_serials'] = (int)$pdo->query("SELECT COUNT(*) FROM serial_numbers WHERE current_holder_id = " . (int)$user['id'] . " AND status IN ('ISSUED', 'LOANED')")->fetchColumn();
            $stats['installations_today'] = (int)$pdo->query("SELECT COUNT(*) FROM installations WHERE technician_id = " . (int)$user['id'] . " AND DATE(installed_at) = '" . $today . "'")->fetchColumn();

            // Personal recent: pengajuan saya
            $mySubmissions = $pdo->query("
                SELECT s.id, s.submission_code, s.status, s.created_at
                FROM submissions s
                WHERE s.submitted_by = " . (int)$user['id'] . "
                ORDER BY s.created_at DESC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Personal recent: peminjaman saya
            $myLoans = $pdo->query("
                SELECT l.id, l.transaction_code, l.loan_date, l.due_date, l.status, w.name as warehouse_name
                FROM loans l
                JOIN warehouses w ON l.warehouse_id = w.id
                WHERE l.borrower_id = " . (int)$user['id'] . "
                ORDER BY l.created_at DESC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Personal recent: instalasi saya
            $myInstallations = $pdo->query("
                SELECT i.id, i.transaction_code, i.customer_site, i.status, i.installed_at
                FROM installations i
                WHERE i.technician_id = " . (int)$user['id'] . "
                ORDER BY i.installed_at DESC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Personal alerts
            $alerts = [];
            if ($stats['active_submissions'] > 0) {
                $alerts[] = ['level' => 'info', 'icon' => 'fa-clipboard-list', 'title' => 'Pengajuan Menunggu', 'desc' => $stats['active_submissions'] . ' pengajuan Anda masih aktif.', 'url' => url('/submissions')];
            }
            if ($stats['active_loans'] > 0) {
                $alerts[] = ['level' => 'warning', 'icon' => 'fa-hand-holding', 'title' => 'Barang Dipinjam', 'desc' => $stats['active_loans'] . ' pinjaman belum dikembalikan.', 'url' => url('/loans')];
            }

            $quickActions = [
                ['icon' => 'fa-paper-plane', 'label' => 'Buat Pengajuan', 'desc' => 'Ajukan kebutuhan barang', 'url' => url('/submissions/create'), 'color' => 'blue'],
                ['icon' => 'fa-undo', 'label' => 'Pengembalian', 'desc' => 'Kembalikan barang pinjaman', 'url' => url('/returns/create'), 'color' => 'green'],
                ['icon' => 'fa-hand-holding', 'label' => 'Barang Saya', 'desc' => 'Custody & peminjaman', 'url' => url('/loans'), 'color' => 'indigo'],
                ['icon' => 'fa-tools', 'label' => 'Instalasi', 'desc' => 'Pasang / lapor instalasi', 'url' => url('/installations/create'), 'color' => 'teal'],
                ['icon' => 'fa-barcode', 'label' => 'Nomor Seri Saya', 'desc' => 'Serial yang Anda pegang', 'url' => url('/serials'), 'color' => 'purple'],
                ['icon' => 'fa-history', 'label' => 'Riwayat Saya', 'desc' => 'Timeline barang Anda', 'url' => url('/timeline'), 'color' => 'gray'],
            ];

            // Placeholder kosong agar view tidak error untuk role teknisi
            $recentLedger = $lowStockList = $activeSubmissions = $outstandingLoans = $recentInstallations = $recentIncidents = [];
            $trends = ['incoming' => [], 'outgoing' => [], 'transfer' => []];
            $recentActivities = $myInstallations;
            $teknis_view = true;
        }

        // Page Guide terpusat (lihat App\Helpers\PageGuideHelper) — halaman ini
        // mengeset $guide agar konten tetap terkendali di satu tempat.
        $guide = \App\Helpers\PageGuideHelper::forPath('/dashboard');

        require __DIR__ . '/../../resources/views/dashboard/index.php';
    }

    /**
     * Hitung trend per bulan (6 bulan terakhir termasuk bulan berjalan) untuk beberapa tabel.
     * Kunci $tables: nama seri => nama tabel. Kolom tanggal default created_at, kecuali transfers pakai sent_at.
     */
    private function monthlyTrend(PDO $pdo, array $tables) {
        $months = [];
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        for ($i = 5; $i >= 0; $i--) {
            $ts = strtotime(date('Y-m-01') . " -$i months");
            $months[date('Y-m', $ts)] = ['label' => $labels[(int)date('n', $ts) - 1] . ' ' . substr(date('Y', $ts), 2), 'count' => 0];
        }

        $result = [];
        $dateColMap = ['incoming' => 'created_at', 'outgoing' => 'created_at', 'transfer' => 'sent_at'];
        $from = array_key_first($months);

        foreach ($tables as $series => $table) {
            $dateCol = $dateColMap[$series] ?? 'created_at';
            $stmt = $pdo->prepare("SELECT DATE_FORMAT({$dateCol}, '%Y-%m') m, COUNT(*) c FROM {$table} WHERE {$dateCol} >= ? GROUP BY m");
            $stmt->execute([$from . '-01']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $data = $months;
            foreach ($rows as $row) {
                if (isset($data[$row['m']])) {
                    $data[$row['m']]['count'] = (int)$row['c'];
                }
            }
            $result[$series] = array_values($data);
        }
        return $result;
    }
}
