<?php
namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class LabelController {

    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    /**
     * Halaman lembar label: ?serial=SN-xxx (satu), ?serial_ids=1,2,3 (batch),
     * ?master_item_id=ID (semua SN item), ?bulk=KODE:qty,KODE2:qty (non-serialized).
     */
    public function index() {
        $pdo = Database::getInstance()->getConnection();

        $serials = [];
        $serial = trim($_GET['serial'] ?? '');
        $serialIds = $_GET['serial_ids'] ?? '';
        $masterItemId = (int)($_GET['master_item_id'] ?? 0);
        $bulkParam = trim($_GET['bulk'] ?? '');

        if ($serial !== '') {
            $stmt = $pdo->prepare("SELECT sn.id, sn.serial_number, m.kode_barang, m.nama, m.is_serialized FROM serial_numbers sn JOIN master_items m ON sn.master_item_id = m.id WHERE sn.serial_number = ?");
            $stmt->execute([$serial]);
            $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($serialIds !== '') {
            $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $serialIds))))); // D-017: proteksi duplikat
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("SELECT sn.id, sn.serial_number, m.kode_barang, m.nama, m.is_serialized FROM serial_numbers sn JOIN master_items m ON sn.master_item_id = m.id WHERE sn.id IN ($in)");
                $stmt->execute($ids);
                $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } elseif ($masterItemId > 0) {
            $stmt = $pdo->prepare("SELECT sn.id, sn.serial_number, m.kode_barang, m.nama, m.is_serialized FROM serial_numbers sn JOIN master_items m ON sn.master_item_id = m.id WHERE sn.master_item_id = ?");
            $stmt->execute([$masterItemId]);
            $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Non-serialized (bulk) item labels: ?kode=BRG-0001 atau ?bulk=BRG-0001:5,BRG-0002:3 (D-017)
        if ($bulkParam !== '') {
            $kodes = [];
            foreach (explode(',', $bulkParam) as $part) {
                $part = trim($part);
                if ($part === '') continue;
                if (str_contains($part, ':')) {
                    [$k, $q] = explode(':', $part, 2);
                    $q = (int)$q;
                } else {
                    $k = $part;
                    $q = 1;
                }
                $k = trim($k);
                $q = max(1, min(100, $q));
                if ($k === '') continue;
                $kodes[$k] = ($kodes[$k] ?? 0) + $q; // gabung + proteksi duplikat
            }
            if (!empty($kodes)) {
                foreach ($kodes as $k => $qty) {
                    $stmt = $pdo->prepare("SELECT 0 AS id, '' AS serial_number, kode_barang, nama, is_serialized FROM master_items WHERE kode_barang = ? AND is_serialized = 0");
                    $stmt->execute([$k]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($item) {
                        for ($i = 0; $i < $qty; $i++) {
                            $serials[] = $item;
                        }
                    }
                }
            }
        } elseif (empty($serials) && isset($_GET['kode'])) {
            $kode = trim($_GET['kode']);
            $stmt = $pdo->prepare("SELECT 0 AS id, '' AS serial_number, kode_barang, nama, is_serialized FROM master_items WHERE kode_barang = ?");
            $stmt->execute([$kode]);
            $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $pageTitle = 'Cetak Label Barcode';
        require __DIR__ . '/../../resources/views/labels/index.php';
    }

    /**
     * D-017: Ruang kerja pemilihan label massal.
     * Filter (search/kategori/gudang/status) → pilih SN &/atau qty item non-serialized → Cetak.
     */
    public function create() {
        $pdo = Database::getInstance()->getConnection();

        $search = trim($_GET['search'] ?? '');
        $categoryId = (int)($_GET['category_id'] ?? 0);
        $warehouseId = (int)($_GET['warehouse_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');

        // Serialized: daftar SN dengan filter
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = "(m.kode_barang LIKE ? OR m.nama LIKE ? OR sn.serial_number LIKE ?)";
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like);
        }
        if ($categoryId > 0) {
            $where[] = "m.item_category_id = ?";
            $params[] = $categoryId;
        }
        if ($warehouseId > 0) {
            $where[] = "sn.current_warehouse_id = ?";
            $params[] = $warehouseId;
        }
        if ($status !== '' && in_array($status, ['AVAILABLE', 'INSTALLED', 'IN_USE', 'IN_TRANSIT', 'BROKEN', 'REPAIR', 'LOST'], true)) {
            $where[] = "sn.status = ?";
            $params[] = $status;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT sn.id, sn.serial_number, m.kode_barang, m.nama, sn.status, w.name AS warehouse
                FROM serial_numbers sn
                JOIN master_items m ON sn.master_item_id = m.id
                LEFT JOIN warehouses w ON sn.current_warehouse_id = w.id
                $whereSql
                ORDER BY sn.id DESC LIMIT 500";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $serials = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Non-serialized: item dengan pilihan jumlah label
        $w2 = ['m.is_serialized = 0'];
        $p2 = [];
        if ($search !== '') {
            $w2[] = "(m.kode_barang LIKE ? OR m.nama LIKE ?)";
            $like = '%' . $search . '%';
            array_push($p2, $like, $like);
        }
        if ($categoryId > 0) {
            $w2[] = "m.item_category_id = ?";
            $p2[] = $categoryId;
        }
        $sql2 = "SELECT m.id, m.kode_barang, m.nama, c.name AS category
                FROM master_items m
                LEFT JOIN item_categories c ON m.item_category_id = c.id
                WHERE " . implode(' AND ', $w2) . "
                ORDER BY m.id DESC LIMIT 200";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute($p2);
        $bulkItems = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $categories = $pdo->query("SELECT id, name FROM item_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $warehouses = $pdo->query("SELECT id, name FROM warehouses ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Cetak Label Massal';
        require __DIR__ . '/../../resources/views/labels/create.php';
    }

    /**
     * D-017: Terima seleksi → validasi → audit → buka lembar cetak.
     */
    public function printSheet() {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Permintaan tidak valid (CSRF).";
            header("Location: " . url('/labels/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();

        // Serialized IDs — terima array (form) atau string koma (client), lalu unik + valid
        $rawSerialIds = $_POST['serial_ids'] ?? [];
        if (is_string($rawSerialIds)) {
            $rawSerialIds = explode(',', $rawSerialIds);
        }
        $serialIds = array_values(array_unique(array_filter(array_map('intval', $rawSerialIds))));
        $validIds = [];
        if (!empty($serialIds)) {
            $in = implode(',', array_fill(0, count($serialIds), '?'));
            $stmt = $pdo->prepare("SELECT id FROM serial_numbers WHERE id IN ($in)");
            $stmt->execute($serialIds);
            $validIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        // Bulk items: kode:qty
        $bulkParts = [];
        foreach (($_POST['bulk'] ?? []) as $entry) {
            $entry = trim($entry);
            if ($entry === '') continue;
            if (str_contains($entry, ':')) {
                [$k, $q] = explode(':', $entry, 2);
                $q = max(1, min(100, (int)$q));
            } else {
                $k = $entry;
                $q = 1;
            }
            $k = trim($k);
            if ($k === '') continue;
            $stmt = $pdo->prepare("SELECT id FROM master_items WHERE kode_barang = ? AND is_serialized = 0");
            $stmt->execute([$k]);
            if ($stmt->fetchColumn()) {
                $bulkParts[$k] = ($bulkParts[$k] ?? 0) + $q;
            }
        }
        $bulkStr = implode(',', array_map(fn($k, $q) => $k . ':' . $q, array_keys($bulkParts), $bulkParts));

        $total = count($validIds) + array_sum($bulkParts);
        if ($total === 0) {
            $_SESSION['error'] = "Tidak ada label yang dipilih. Pilih minimal 1 nomor seri atau tentukan jumlah item non-serialized.";
            header("Location: " . url('/labels/create'));
            exit;
        }

        // Audit cetak (D-017)
        $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, new_data, ip_address, user_agent) VALUES (?, 'LABEL_PRINT', 'labels', ?, ?, ?)");
        $audit->execute([
            $user['id'],
            json_encode(['total' => $total, 'serial_count' => count($validIds), 'bulk' => $bulkStr]),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $url = '/labels?serial_ids=' . implode(',', $validIds);
        if ($bulkStr !== '') {
            $url .= '&bulk=' . urlencode($bulkStr);
        }
        header("Location: " . url($url));
        exit;
    }

    /**
     * Lookup barcode/QR: terima input scanner (kode barang / serial) -> redirect ke detail.
     * GET /scan?q=... ; juga POST /scan
     */
    public function scan() {
        $q = trim($_POST['q'] ?? $_GET['q'] ?? '');
        if ($q === '') {
            $pageTitle = 'Scan Barcode / QR';
            require __DIR__ . '/../../resources/views/labels/scan.php';
            return;
        }

        $pdo = Database::getInstance()->getConnection();

        // 1) Cocok dengan serial number
        $stmt = $pdo->prepare("SELECT sn.id AS sn_id, sn.master_item_id, sn.serial_number FROM serial_numbers sn WHERE sn.serial_number = ?");
        $stmt->execute([$q]);
        $sn = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($sn) {
            header("Location: " . url('/items/show/' . $sn['master_item_id'] . '?sn_id=' . $sn['sn_id']));
            exit;
        }

        // 2) Cocok dengan kode barang
        $stmt = $pdo->prepare("SELECT id FROM master_items WHERE kode_barang = ?");
        $stmt->execute([$q]);
        $itemId = $stmt->fetchColumn();
        if ($itemId) {
            header("Location: " . url('/items/show/' . $itemId));
            exit;
        }

        // 3) Tidak ditemukan -> arahkan ke search
        $_SESSION['error'] = "Tidak ditemukan barang/serial dengan kode \"$q\". Menampilkan hasil pencarian.";
        header("Location: " . url('/search?q=' . urlencode($q)));
        exit;
    }
}
