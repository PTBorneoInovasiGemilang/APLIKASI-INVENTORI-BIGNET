<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use App\Services\StockService;
use PDO;

class OutgoingController {
    
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $pdo = Database::getInstance()->getConnection();
        $search = trim($_GET['search'] ?? '');
        $warehouseId = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $whereClauses = ["1=1"];
        $params = [];

        if ($warehouseId > 0) {
            $whereClauses[] = "o.warehouse_id = ?";
            $params[] = $warehouseId;
        }

        if ($search !== '') {
            $whereClauses[] = "(o.transaction_code LIKE ? OR s.submission_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = "WHERE " . implode(" AND ", $whereClauses);

        // Count for pagination
        $countSql = "SELECT COUNT(*) FROM outgoing o 
                     LEFT JOIN submissions s ON o.submission_id = s.id 
                     $whereSql";
        $stmtCount = $pdo->prepare($countSql);
        $stmtCount->execute($params);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $sql = "SELECT o.*, w.name as warehouse_name, u.name as processor_name, s.submission_code 
                FROM outgoing o 
                JOIN warehouses w ON o.warehouse_id = w.id 
                JOIN users u ON o.processed_by = u.id 
                LEFT JOIN submissions s ON o.submission_id = s.id
                $whereSql
                ORDER BY o.id DESC LIMIT $limit OFFSET $offset";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $outgoings = $stmt->fetchAll();

        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();

        require __DIR__ . '/../../resources/views/transactions/outgoing/index.php';
    }

    public function create() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT * FROM warehouses WHERE is_active = 1")->fetchAll();
        
        // Fetch approved submissions with requester name, date, and item summary for informative dropdown labels.
        $submissions = $pdo->query("SELECT s.id, s.submission_code, s.created_at, u.name AS submitter_name,
                GROUP_CONCAT(CONCAT(si.requested_quantity, ' ', IFNULL(m.nama, si.manual_name)) SEPARATOR ', ') AS items_summary
                FROM submissions s
                JOIN users u ON s.submitted_by = u.id
                JOIN submission_items si ON si.submission_id = s.id
                LEFT JOIN master_items m ON si.master_item_id = m.id
                WHERE s.status IN ('APPROVED', 'PARTIAL_APPROVED')
                GROUP BY s.id
                ORDER BY s.id DESC")->fetchAll();

        // Dukungan link langsung dari Detail Pengajuan: ?submission_id=X memilih & memuat item otomatis.
        $preselectSubmissionId = isset($_GET['submission_id']) ? (int)$_GET['submission_id'] : 0;
        $preselectItems = [];
        $preselectWarehouseId = 0;
        if ($preselectSubmissionId > 0) {
            $valid = false;
            foreach ($submissions as $s) {
                if ((int)$s['id'] === $preselectSubmissionId) { $valid = true; break; }
            }
            if ($valid) {
                $stmt = $pdo->prepare("SELECT si.id as submission_item_id, si.master_item_id, si.warehouse_id, m.nama, m.kode_barang, m.is_serialized, si.approved_quantity, si.remaining_quantity 
                                       FROM submission_items si 
                                       JOIN master_items m ON si.master_item_id = m.id 
                                       WHERE si.submission_id = ? AND si.remaining_quantity > 0");
                $stmt->execute([$preselectSubmissionId]);
                $preselectItems = $stmt->fetchAll();

                // Gudang pemenuhan pengajuan: jika SEMUA item STOCK memakai gudang yang sama,
                // preselect otomatis. Jika beda gudang → biarkan admin memilih per transaksi
                // (store() akan menolak item yang gudangnya tidak sesuai).
                $whSet = [];
                foreach ($preselectItems as $pi) {
                    if (!empty($pi['warehouse_id'])) $whSet[(int)$pi['warehouse_id']] = true;
                }
                if (count($whSet) === 1) {
                    $preselectWarehouseId = (int)array_key_first($whSet);
                }
            } else {
                $preselectSubmissionId = 0;
            }
        }

        require __DIR__ . '/../../resources/views/transactions/outgoing/create.php';
    }

    public function store() {
        \App\Helpers\AuthHelper::requireRole('ADMIN');
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "CSRF validation failed.";
            header("Location: " . url('/outgoing/create'));
            exit;
        }

        $warehouse_id = (int)$_POST['warehouse_id'];
        $submission_id = !empty($_POST['submission_id']) ? (int)$_POST['submission_id'] : null;
        $notes = trim($_POST['notes']);
        // Ketiga array memakai row index yang sama (item_ids[n], quantities[n], sn_list[n]).
        // Jangan mengindeks ulang dengan array_values karena baris dapat dihapus di browser.
        $item_ids = is_array($_POST['item_ids'] ?? null) ? $_POST['item_ids'] : [];
        $quantities = is_array($_POST['quantities'] ?? null) ? $_POST['quantities'] : [];
        $sn_list = is_array($_POST['sn_list'] ?? null) ? $_POST['sn_list'] : [];
        $evidenceFiles = $_FILES['evidence'] ?? [];

        $evidenceCount = 0;
        if (!empty($evidenceFiles) && isset($evidenceFiles['name']) && is_array($evidenceFiles['name'])) {
            $evidenceCount = count(array_filter($evidenceFiles['name'], static fn($name) => (string)$name !== ''));
        }
        if ($evidenceCount < 1 || $evidenceCount > 3) {
            $_SESSION['error'] = "Foto bukti Barang Keluar wajib 1–3 foto.";
            header("Location: " . url('/outgoing/create'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        
        $wh = $pdo->prepare("SELECT id FROM warehouses WHERE id = ? AND is_active = 1");
        $wh->execute([$warehouse_id]);
        if (!$wh->fetch()) {
            $_SESSION['error'] = "Gudang asal tidak valid atau tidak aktif.";
            header("Location: " . url('/outgoing/create'));
            exit;
        }

        // Evidence Barang Keluar harus berupa gambar; AttachmentHelper tetap melakukan validasi MIME + ukuran.
        if (!isset($evidenceFiles['tmp_name']) || !is_array($evidenceFiles['tmp_name'])) {
            $_SESSION['error'] = "Foto bukti Barang Keluar wajib berupa 1–3 foto."; 
            header("Location: " . url('/outgoing/create'));
            exit;
        }
        $allowedEvidenceMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        foreach ($evidenceFiles['tmp_name'] as $idx => $tmpName) {
            if (($evidenceFiles['error'][$idx] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Salah satu foto bukti gagal diunggah.";
                header("Location: " . url('/outgoing/create'));
                exit;
            }
            if (($evidenceFiles['size'][$idx] ?? 0) > \App\Helpers\AttachmentHelper::MAX_SIZE) {
                $_SESSION['error'] = "Ukuran setiap foto bukti maksimal 5MB.";
                header("Location: " . url('/outgoing/create'));
                exit;
            }
            $mime = $finfo->file($tmpName) ?: '';
            if (!in_array($mime, $allowedEvidenceMimes, true)) {
                $_SESSION['error'] = "Foto bukti hanya boleh JPG, PNG, atau WEBP.";
                header("Location: " . url('/outgoing/create'));
                exit;
            }
        }

        if (empty($item_ids)) {
            $_SESSION['error'] = "Tidak ada barang yang dipilih.";
            header("Location: " . url('/outgoing/create'));
            exit;
        }

        $stockService = new StockService();
        $user_id = AuthHelper::user()['id'];
        $transaction_code = \App\Helpers\TransactionHelper::generateCode('OUT', 'outgoing');

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO outgoing (transaction_code, warehouse_id, submission_id, processed_by, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$transaction_code, $warehouse_id, $submission_id, $user_id, $notes]);
            $outgoing_id = $pdo->lastInsertId();

            $holder_id = null;
            if ($submission_id) {
                $subQuery = $pdo->prepare("SELECT submitted_by FROM submissions WHERE id = ?");
                $subQuery->execute([$submission_id]);
                $holder_id = $subQuery->fetchColumn() ?: null;
            }

            $itemStmt = $pdo->prepare("INSERT INTO outgoing_items (outgoing_id, master_item_id, submission_item_id, quantity) VALUES (?, ?, ?, ?)");

            foreach ($item_ids as $index => $item_id) {
                if (!array_key_exists($index, $quantities)) {
                    throw new \Exception("Data kuantitas barang tidak cocok dengan baris form. Muat ulang halaman lalu coba kembali.");
                }
                $qty = (int)$quantities[$index];
                if ($qty <= 0) continue;

                $master_item_id = (int)$item_id;
                $sub_item_id_val = null;

                // Validation against submission if applicable
                if ($submission_id) {
                    $subItemStmt = $pdo->prepare("SELECT id, master_item_id, warehouse_id, remaining_quantity FROM submission_items WHERE submission_id = ? AND id = ? FOR UPDATE");
                    $subItemStmt->execute([$submission_id, $item_id]);
                    $subItem = $subItemStmt->fetch();

                    if (!$subItem || $qty > $subItem['remaining_quantity']) {
                        throw new \Exception("Kuantitas barang melebihi sisa pengajuan yang disetujui (Line ID: $item_id).");
                    }

                    // WAREHOUSE CONSISTENCY: Barang Keluar wajib berasal dari Gudang Pemenuhan
                    // yang direkam pada submission item (master_item_id + warehouse_id yang sama).
                    // Untuk pengajuan LAMA (warehouse_id NULL) tidak bisa diverifikasi — admin
                    // menentukan gudang secara eksplisit; data NULL tidak diisi paksa (recovery issue).
                    if ($subItem['warehouse_id'] !== null && (int)$subItem['warehouse_id'] !== $warehouse_id) {
                        throw new \Exception("Gudang asal tidak sesuai Gudang Pemenuhan pengajuan (Line ID: $item_id). Gudang pemenuhan: " . $subItem['warehouse_id'] . ", gudang dipilih: $warehouse_id. Proses dibatalkan — stok tidak berubah.");
                    }
                    
                    $master_item_id = $subItem['master_item_id'];
                    $sub_item_id_val = $subItem['id'];

                    // Update submission item
                    $newRemaining = $subItem['remaining_quantity'] - $qty;
                    $updSubStmt = $pdo->prepare("UPDATE submission_items SET fulfilled_quantity = fulfilled_quantity + ?, remaining_quantity = ? WHERE id = ?");
                    $updSubStmt->execute([$qty, $newRemaining, $subItem['id']]);
                }

                // Check serialized
                $mItem = $pdo->query("SELECT is_serialized FROM master_items WHERE id = " . $master_item_id)->fetch();
                if ($mItem && $mItem['is_serialized']) {
                    $rawSn = $sn_list[$index] ?? '';
                    $snArray = array_filter(array_map('trim', preg_split('/[\n,]+/', $rawSn)));
                    
                    if (count($snArray) !== $qty) {
                        throw new \Exception("Jumlah Serial Number tidak cocok dengan kuantitas untuk barang ID $item_id. Diminta: $qty, Diisi: " . count($snArray));
                    }
                    if (count($snArray) !== count(array_unique($snArray))) {
                         throw new \Exception("Ada Serial Number duplikat pada input barang ID $item_id.");
                    }

                    // STOCK SUFFICIENCY (server-side) untuk item serialized: jumlah unit AVAILABLE
                    // di gudang asal harus menutupi qty yang akan dikeluarkan — SEBELUM serial dipindah.
                    $availSnStmt = $pdo->prepare("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = ? AND current_warehouse_id = ? AND status = 'AVAILABLE'");
                    $availSnStmt->execute([$master_item_id, $warehouse_id]);
                    $availableSn = (int)$availSnStmt->fetchColumn();
                    if ($availableSn < $qty) {
                        throw new \Exception("Stok Tidak Mencukupi untuk barang serial (ID: $master_item_id). Diminta: $qty unit, tersedia: $availableSn unit. Proses dibatalkan — stok tidak berubah.");
                    }

                    foreach ($snArray as $sn) {
                        // Validate SN is AVAILABLE, in the source warehouse, and matches master_item_id
                        $chkSn = $pdo->prepare("SELECT id, status, current_warehouse_id, master_item_id FROM serial_numbers WHERE serial_number = ? FOR UPDATE");
                        $chkSn->execute([$sn]);
                        $snRow = $chkSn->fetch();

                        if (!$snRow) {
                            throw new \Exception("Serial Number $sn tidak ditemukan di sistem.");
                        }
                        if ($snRow['master_item_id'] != $master_item_id) {
                            throw new \Exception("Serial Number $sn bukan barang yang sesuai (beda master item).");
                        }
                        if ($snRow['status'] !== 'AVAILABLE') {
                            throw new \Exception("Serial Number $sn tidak berstatus AVAILABLE (status saat ini: {$snRow['status']}).");
                        }
                        if ($snRow['current_warehouse_id'] != $warehouse_id) {
                            throw new \Exception("Serial Number $sn tidak berada di gudang asal yang dipilih.");
                        }

                        // Update SN status and holder using StockService
                        $stockService->moveSerial(
                            $snRow['id'], 
                            'ISSUED', 
                            null, 
                            null, 
                            $holder_id, 
                            'outgoing', 
                            $outgoing_id, 
                            "Barang keluar - " . $transaction_code
                        );
                    }

                    $itemStmt->execute([$outgoing_id, $master_item_id, $sub_item_id_val, $qty]);

                    // STOCK POSTING untuk item serialized: stok fisik berkurang sesuai jumlah unit
                    // yang benar-benar dikeluarkan — simetris dengan incoming (+qty) & return (+1/unit).
                    // Sebelum fix ini, Barang Keluar serialized TIDAK pernah mengurangi stocks.quantity
                    // sehingga "Barang Keluar berhasil tetapi stok tidak berkurang" (P0).
                    $stockService->mutateStock($master_item_id, $warehouse_id, -$qty, 'OUTGOING', 'outgoing', $outgoing_id, "Fulfillment outgoing $transaction_code");
                } else {
                    // Bulk: validasi master item + STOCK SUFFICIENCY CHECK eksplisit (server-side,
                    // stok aktual tepat sebelum deduction). mutateStock tetap guard atomik terakhir.
                    $mItemChk = $pdo->prepare("SELECT id, nama, is_active FROM master_items WHERE id = ?");
                    $mItemChk->execute([$master_item_id]);
                    $mItemRow = $mItemChk->fetch();
                    if (!$mItemRow || (int)$mItemRow['is_active'] !== 1) {
                        throw new \Exception("Master Barang tidak valid atau nonaktif (ID: $master_item_id).");
                    }

                    $availStmt = $pdo->prepare("SELECT quantity FROM stocks WHERE master_item_id = ? AND warehouse_id = ? FOR UPDATE");
                    $availStmt->execute([$master_item_id, $warehouse_id]);
                    $available = (int)$availStmt->fetchColumn();
                    if ($available < $qty) {
                        throw new \Exception("Stok Tidak Mencukupi untuk '" . $mItemRow['nama'] . "'. Diminta: $qty Pcs, stok tersedia: $available Pcs. Proses dibatalkan — stok tidak berubah.");
                    }

                    $itemStmt->execute([$outgoing_id, $master_item_id, $sub_item_id_val, $qty]);
                    
                    $stockService->mutateStock($master_item_id, $warehouse_id, -$qty, 'OUTGOING', 'outgoing', $outgoing_id, "Fulfillment outgoing $transaction_code");
                }
            }

            // GUARD INTEGRITAS: Barang Keluar TIDAK boleh commit tanpa baris barang.
            // "Outgoing record sukses tanpa stock posting" = data integrity failure.
            $outItemCount = (int)$pdo->query("SELECT COUNT(*) FROM outgoing_items WHERE outgoing_id = " . (int)$outgoing_id)->fetchColumn();
            if ($outItemCount === 0) {
                throw new \Exception("Tidak ada baris barang valid pada transaksi ini. Barang Keluar dibatalkan — stok tidak berubah.");
            }

            // Check if submission is fully fulfilled
            if ($submission_id) {
                $remStmt = $pdo->prepare("SELECT SUM(remaining_quantity) FROM submission_items WHERE submission_id = ?");
                $remStmt->execute([$submission_id]);
                if ((int)$remStmt->fetchColumn() === 0) {
                    $pdo->prepare("UPDATE submissions SET status = 'COMPLETED' WHERE id = ?")->execute([$submission_id]);
                }
            }

            // Evidence wajib: simpan seluruh 1–3 foto sebagai attachment transaksi Barang Keluar.
            foreach ($evidenceFiles['tmp_name'] as $idx => $tmpName) {
                $file = [
                    'name' => $evidenceFiles['name'][$idx] ?? 'evidence',
                    'type' => $evidenceFiles['type'][$idx] ?? '',
                    'tmp_name' => $tmpName,
                    'error' => $evidenceFiles['error'][$idx] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $evidenceFiles['size'][$idx] ?? 0,
                ];
                $attId = \App\Helpers\AttachmentHelper::store('OUTGOING', (int)$outgoing_id, $file, (int)$user_id);
                if (!$attId) {
                    throw new \Exception("Gagal menyimpan salah satu foto bukti Barang Keluar.");
                }
            }

            // Audit Log
            $auditStmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id) VALUES (?, 'CREATE', 'outgoing', ?)");
            $auditStmt->execute([$user_id, $outgoing_id]);

            $pdo->commit();
            $_SESSION['success'] = "Barang Keluar berhasil diproses.";
        } catch (\Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Gagal memproses barang keluar: " . $e->getMessage();
            header("Location: " . url('/outgoing/create'));
            exit;
        }

        header("Location: " . url('/outgoing'));
        exit;
    }

    /**
     * Detail Barang Keluar: header transaksi + item + SN + timeline + audit.
     * Route /outgoing/show?id= (diperbaiki — sebelumnya 404).
     */
    public function show() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = "Parameter transaksi tidak valid.";
            header("Location: " . url('/outgoing'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare("SELECT o.*, w.name AS warehouse_name, u.name AS processor_name, s.submission_code,
                COALESCE(SUM(oi.quantity), 0) AS total_items
                FROM outgoing o
                JOIN warehouses w ON o.warehouse_id = w.id
                JOIN users u ON o.processed_by = u.id
                LEFT JOIN submissions s ON o.submission_id = s.id
                LEFT JOIN outgoing_items oi ON oi.outgoing_id = o.id
                WHERE o.id = ?
                GROUP BY o.id");
        $stmt->execute([$id]);
        $outgoing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$outgoing) {
            $_SESSION['error'] = "Data barang keluar tidak ditemukan.";
            header("Location: " . url('/outgoing'));
            exit;
        }

        // Item detail
        $stmt = $pdo->prepare("SELECT oi.quantity, m.kode_barang, m.nama, m.is_serialized, m.unit_id, u.name AS unit_name
                FROM outgoing_items oi
                JOIN master_items m ON oi.master_item_id = m.id
                LEFT JOIN units u ON m.unit_id = u.id
                WHERE oi.outgoing_id = ?
                ORDER BY oi.id");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // SN yang dikeluarkan (reference_type = 'outgoing')
        $stmt = $pdo->prepare("SELECT sn.serial_number, sn.status, sm.to_holder_id, hu.name AS holder_name
                FROM serial_movements sm
                JOIN serial_numbers sn ON sm.serial_number_id = sn.id
                LEFT JOIN users hu ON sm.to_holder_id = hu.id
                WHERE sm.reference_type = 'outgoing' AND sm.reference_id = ?
                ORDER BY sm.id");
        $stmt->execute([$id]);
        $serialMovements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Timeline: audit logs + serial movements
        $timeline = [];
        $auditStmt = $pdo->prepare("SELECT al.created_at, al.action, u.name AS actor, al.new_data
                FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity = 'outgoing' AND al.entity_id = ? ORDER BY al.id");
        $auditStmt->execute([$id]);
        $timeline = $auditStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($serialMovements as $sm) {
            $timeline[] = [
                'created_at' => '', // tidak tersimpan di serial_movements row ini (pakai waktu movement via row id ordering)
                'action' => 'SERIAL_MOVEMENT',
                'actor' => $sm['holder_name'] ?: '-',
                'new_data' => 'SN ' . $sm['serial_number'] . ' → status ' . $sm['status'] . ($sm['holder_name'] ? ' (pemegang: ' . $sm['holder_name'] . ')' : ''),
            ];
        }

        $attachments = \App\Helpers\AttachmentHelper::forReference('OUTGOING', $id);

        $pageTitle = 'Detail Barang Keluar';
        require __DIR__ . '/../../resources/views/transactions/outgoing/show.php';
    }
    public function uploadEvidence() {
        AuthHelper::requireRole(['ADMIN']);
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF validation failed.";
            header('Location: ' . url('/outgoing'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $pdo = Database::getInstance()->getConnection();
        $check = $pdo->prepare("SELECT id FROM outgoing WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            $_SESSION['error'] = "Transaksi Barang Keluar tidak ditemukan.";
            header('Location: ' . url('/outgoing'));
            exit;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM attachments WHERE reference_type = 'OUTGOING' AND reference_id = ?");
        $countStmt->execute([$id]);
        $currentCount = (int)$countStmt->fetchColumn();
        if ($currentCount >= 3) {
            $_SESSION['error'] = "Maksimal 3 foto bukti untuk satu transaksi Barang Keluar.";
            header('Location: ' . url('/outgoing/show?id=' . $id));
            exit;
        }

        $file = $_FILES['evidence'] ?? [];
        try {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Pilih foto bukti terlebih dahulu.');
            }
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']) ?: '';
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new \RuntimeException('Foto bukti hanya boleh JPG, PNG, atau WEBP.');
            }
            $attId = \App\Helpers\AttachmentHelper::store('OUTGOING', $id, $file, (int)AuthHelper::user()['id']);
            if (!$attId) throw new \RuntimeException('Gagal menyimpan foto bukti.');

            $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'UPLOAD_EVIDENCE', 'outgoing', ?, ?, ?)");
            $audit->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $_SESSION['success'] = "Foto bukti berhasil ditambahkan.";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . url('/outgoing/show?id=' . $id));
        exit;
    }

}
