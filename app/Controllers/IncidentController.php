<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use PDO;
use Exception;

class IncidentController
{
    public function index()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);

        $db = Database::getInstance();
        
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $whereParams = [];
        $whereClause = "WHERE 1=1";

        if ($search) {
            $whereClause .= " AND (i.transaction_code LIKE :search OR u.name LIKE :search OR i.incident_type LIKE :search)";
            $whereParams[':search'] = "%$search%";
        }

        $statusFilter = $_GET['status'] ?? '';
        if ($statusFilter !== '') {
            $whereClause .= " AND i.status = :status";
            $whereParams[':status'] = $statusFilter;
        }

        // Teknisi hanya melihat miliknya (atau lokasi relevan). Admin melihat semua.
        if (AuthHelper::hasRole(['TEKNISI_NOC'])) {
            $whereClause .= " AND i.reported_by = :user_id";
            $whereParams[':user_id'] = $_SESSION['user_id'];
        }

        $countQuery = "SELECT COUNT(*) FROM incidents i 
                       LEFT JOIN users u ON i.reported_by = u.id 
                       $whereClause";
        $stmtCount = $db->prepare($countQuery);
        $stmtCount->execute($whereParams);
        $totalItems = $stmtCount->fetchColumn();
        $totalPages = ceil($totalItems / $limit);

        $query = "SELECT i.*, u.name as reporter_name, w.name as warehouse_name 
                  FROM incidents i 
                  LEFT JOIN users u ON i.reported_by = u.id 
                  LEFT JOIN warehouses w ON i.warehouse_id = w.id 
                  $whereClause 
                  ORDER BY i.created_at DESC 
                  LIMIT $limit OFFSET $offset";
                  
        $stmt = $db->prepare($query);
        $stmt->execute($whereParams);
        $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../resources/views/transactions/incidents/index.php';
    }

    public function create()
    {
        AuthHelper::requireRole(['ADMIN', 'TEKNISI_NOC']);
        $db = Database::getInstance();
        
        // Get warehouses
        $stmtW = $db->query("SELECT id, name FROM warehouses ORDER BY name ASC");
        $warehouses = $stmtW->fetchAll(PDO::FETCH_ASSOC);
        
        // Get items for selection
        $stmtI = $db->query("SELECT id, kode_barang, nama, is_serialized FROM master_items ORDER BY nama ASC");
        $items = $stmtI->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../resources/views/transactions/incidents/create.php';
    }

    public function store()
    {
        \App\Helpers\AuthHelper::denySuperadminWrite();
        AuthHelper::requireRole(['ADMIN', 'TEKNISI_NOC']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/incidents'));
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF Token Invalid");
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $incidentType = $_POST['incident_type'];
            $warehouseId = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : null;
            $notes = $_POST['notes'];
            
            // Generate Transaction Code
            $datePrefix = date('Ymd');
            $prefix = ($incidentType === 'DAMAGE') ? 'DMG' : 'LST';
            $stmtLast = $db->prepare("SELECT transaction_code FROM incidents WHERE transaction_code LIKE :prefix ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $stmtLast->execute([':prefix' => $prefix . '-' . $datePrefix . '-%']);
            $lastCode = $stmtLast->fetchColumn();
            
            $sequence = 1;
            if ($lastCode) {
                $parts = explode('-', $lastCode);
                $sequence = (int)end($parts) + 1;
            }
            $transactionCode = sprintf("%s-%s-%04d", $prefix, $datePrefix, $sequence);

            $stmtInc = $db->prepare("INSERT INTO incidents (transaction_code, incident_type, reported_by, reported_date, warehouse_id, notes, status) 
                                     VALUES (?, ?, ?, NOW(), ?, ?, 'PENDING')");
            $stmtInc->execute([
                $transactionCode, 
                $incidentType, 
                $_SESSION['user_id'], 
                $warehouseId, 
                $notes
            ]);
            $incidentId = $db->lastInsertId();

            // Handle items
            if (empty($_POST['items'])) {
                throw new Exception("Minimal satu item harus dilaporkan.");
            }

            foreach ($_POST['items'] as $index => $itemData) {
                $masterItemId = $itemData['master_item_id'];
                $qty = (int)$itemData['quantity'];
                $serialId = !empty($itemData['serial_number_id']) ? $itemData['serial_number_id'] : null;
                $lastHolder = $itemData['last_holder_name'] ?? null;
                $reason = $itemData['reason'] ?? '';
                
                // Handle evidence upload
                $evidencePath = null;
                if (isset($_FILES['items']['name'][$index]['evidence']) && $_FILES['items']['error'][$index]['evidence'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['items']['tmp_name'][$index]['evidence'];
                    $fileName = time() . '_' . basename($_FILES['items']['name'][$index]['evidence']);
                    $uploadDir = __DIR__ . '/../../public/uploads/evidence/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
                        $evidencePath = 'uploads/evidence/' . $fileName;
                    }
                }

                $stmtItem = $db->prepare("INSERT INTO incident_items (incident_id, master_item_id, serial_number_id, quantity, last_holder_name, evidence_path, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtItem->execute([
                    $incidentId, $masterItemId, $serialId, $qty, $lastHolder, $evidencePath, $reason
                ]);
                
                // Create Timeline Event
                $actionType = "REPORT_INCIDENT";
                $description = "Item dilaporkan $incidentType ($transactionCode). Alasan: $reason.";
                
                $stmtTimeline = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, ?, ?, ?, ?)");
                $stmtTimeline->execute([
                    $_SESSION['user_id'], $actionType, 'incidents', $incidentId, json_encode(['description' => $description])
                ]);
            }

            $db->commit();
            $_SESSION['success'] = "Laporan $incidentType berhasil dibuat dengan kode $transactionCode";

            // Notification ke ADMIN (business event: teknisi melaporkan incident).
            try {
                $actorName = AuthHelper::user()['name'];
                \App\Helpers\NotificationHelper::createForRoleOnce(
                    'ADMIN',
                    'Laporan ' . $incidentType . ' Baru',
                    $actorName . ' melaporkan ' . $incidentType . ' (' . $transactionCode . ').',
                    'warning',
                    '/incidents/show?id=' . $incidentId
                );
            } catch (\Exception $notifEx) {
                error_log('[BIGNET] Notification gagal (incident create #' . $incidentId . '): ' . $notifEx->getMessage());
            }

            header('Location: ' . url('/incidents/show?id=' . $incidentId));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
            if (php_sapi_name() === 'cli') {
                echo "SESSION ERROR: " . $_SESSION['error'] . "\n";
                exit;
            }
            header('Location: ' . url('/incidents/create'));
            exit;
        }
    }

    public function show()
    {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN', 'TEKNISI_NOC']);

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . url('/incidents'));
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT i.*, u.name as reporter_name, w.name as warehouse_name 
                              FROM incidents i 
                              LEFT JOIN users u ON i.reported_by = u.id 
                              LEFT JOIN warehouses w ON i.warehouse_id = w.id 
                              WHERE i.id = ?");
        $stmt->execute([$id]);
        $incident = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            header('Location: ' . url('/incidents'));
            exit;
        }

        // Get Items
        $stmtItems = $db->prepare("SELECT ii.*, m.kode_barang, m.nama as item_name, m.is_serialized, s.serial_number 
                                   FROM incident_items ii
                                   JOIN master_items m ON ii.master_item_id = m.id
                                   LEFT JOIN serial_numbers s ON ii.serial_number_id = s.id
                                   WHERE ii.incident_id = ?");
        $stmtItems->execute([$id]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        // Get Timeline
        $stmtTimeline = $db->prepare("SELECT u.name as user_name, a.action, a.new_data, a.created_at 
                                      FROM audit_logs a
                                      LEFT JOIN users u ON a.user_id = u.id
                                      WHERE a.entity = 'incidents' AND a.entity_id = ?
                                      ORDER BY a.created_at DESC");
        $stmtTimeline->execute([$id]);
        $timelineRaw = $stmtTimeline->fetchAll(PDO::FETCH_ASSOC);
        
        $timeline = [];
        foreach ($timelineRaw as $t) {
            $data = json_decode($t['new_data'], true);
            $t['description'] = $data['description'] ?? '';
            $timeline[] = $t;
        }

        // Bukti tambahan (attachments table, secure storage)
        $attachments = \App\Helpers\AttachmentHelper::forReference('INCIDENT', (int)$id);

        require __DIR__ . '/../../resources/views/transactions/incidents/show.php';
    }

    public function uploadEvidence()
    {
        AuthHelper::requireRole(['ADMIN']);
        if (($_POST['csrf_token'] ?? '') !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['error'] = "CSRF validation failed";
            header('Location: ' . url('/incidents'));
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        try {
            $attId = \App\Helpers\AttachmentHelper::store('INCIDENT', $id, $_FILES['evidence'] ?? [], (int)AuthHelper::user()['id']);
            if ($attId) {
                $pdo = \App\Core\Database::getInstance()->getConnection();
                $audit = $pdo->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent) VALUES (?, 'UPLOAD_EVIDENCE', 'incidents', ?, ?, ?)");
                $audit->execute([AuthHelper::user()['id'], $id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                $_SESSION['success'] = "Bukti berhasil diunggah.";
            } else {
                $_SESSION['error'] = "Pilih file bukti terlebih dahulu.";
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . url('/incidents/show?id=' . $id));
        exit;
    }
    
    public function process()
    {
        \App\Helpers\AuthHelper::denySuperadminWrite();
        AuthHelper::requireRole(['ADMIN']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/incidents'));
            exit;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF Token Invalid");
        }
        
        $id = $_POST['incident_id'];
        $action = $_POST['action']; // INVESTIGATING, RESOLVED, REJECTED
        $resolution = $_POST['resolution'];

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare("SELECT * FROM incidents WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $incident = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$incident || $incident['status'] === 'RESOLVED' || $incident['status'] === 'REJECTED') {
                throw new Exception("Insiden tidak valid atau sudah selesai diproses.");
            }
            
            $stmtUpdate = $db->prepare("UPDATE incidents SET status = ?, resolution = ?, updated_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$action, $resolution, $id]);
            
            // If RESOLVED, we should remove the items from stock or update serial status
            if ($action === 'RESOLVED') {
                $stmtItems = $db->prepare("SELECT * FROM incident_items WHERE incident_id = ?");
                $stmtItems->execute([$id]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($items as $item) {
                    if ($item['serial_number_id']) {
                        // Mark serial as damaged/lost (perbaikan kolom: current_warehouse_id,
                        // bukan warehouse_id yang tidak ada di tabel serial_numbers).
                        $sStatus = ($incident['incident_type'] === 'DAMAGE') ? 'DAMAGED' : 'LOST';
                        $stmtSnSel = $db->prepare("SELECT status FROM serial_numbers WHERE id = ? FOR UPDATE");
                        $stmtSnSel->execute([$item['serial_number_id']]);
                        $oldSnStatus = $stmtSnSel->fetchColumn() ?: 'AVAILABLE';

                        $stmtUpdateSerial = $db->prepare("UPDATE serial_numbers SET status = ?, current_warehouse_id = NULL, current_holder_id = NULL WHERE id = ?");
                        $stmtUpdateSerial->execute([$sStatus, $item['serial_number_id']]);
                        
                        // Serial Movement (kolom actual: from_status/to_status, bukan movement_type)
                        $stmtMove = $db->prepare("INSERT INTO serial_movements (serial_number_id, from_status, to_status, from_warehouse_id, to_warehouse_id, from_holder_id, to_holder_id, reference_type, reference_id, performed_by, notes) VALUES (?, ?, ?, ?, NULL, NULL, NULL, 'incidents', ?, ?, ?)");
                        $stmtMove->execute([$item['serial_number_id'], $oldSnStatus, $sStatus, $incident['warehouse_id'], $id, $_SESSION['user_id'], "Marked as $sStatus"]);
                    } else if ($incident['warehouse_id']) {
                        $stmtStockSel = $db->prepare("SELECT quantity FROM stocks WHERE warehouse_id = ? AND master_item_id = ? FOR UPDATE");
                        $stmtStockSel->execute([$incident['warehouse_id'], $item['master_item_id']]);
                        $currentStock = (int)$stmtStockSel->fetchColumn();

                        // No-negative-stock guard: jangan pernah mengurangi melebihi stok aktual.
                        if ($currentStock < (int)$item['quantity']) {
                            throw new Exception("Stok Tidak Mencukupi untuk resolusi insiden. Stok tersedia: $currentStock, dibutuhkan: " . (int)$item['quantity'] . ".");
                        }

                        // Bulk item deduction
                        $stmtStock = $db->prepare("UPDATE stocks SET quantity = quantity - ?, updated_at = NOW() WHERE warehouse_id = ? AND master_item_id = ?");
                        $stmtStock->execute([$item['quantity'], $incident['warehouse_id'], $item['master_item_id']]);
                        
                        // Stock Ledger
                        $stmtLedger = $db->prepare("INSERT INTO stock_ledgers (master_item_id, warehouse_id, transaction_type, reference_type, reference_id, quantity_delta, balance_before, balance_after, performed_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmtLedger->execute([
                            $item['master_item_id'], 
                            $incident['warehouse_id'], 
                            'OUT', 
                            'incidents', 
                            $id, 
                            -$item['quantity'], 
                            $currentStock,
                            $currentStock - (int)$item['quantity'],
                            $_SESSION['user_id'],
                            "Marked as " . $incident['incident_type']
                        ]);
                    }
                }
            }
            
            // Timeline
            $description = "Status updated to $action. Resolution: $resolution";
            $stmtTimeline = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, new_data) VALUES (?, ?, ?, ?, ?)");
            $stmtTimeline->execute([
                $_SESSION['user_id'], 'UPDATE_STATUS', 'incidents', $id, json_encode(['description' => $description])
            ]);
            
            $db->commit();
            $_SESSION['success'] = "Insiden berhasil diupdate menjadi $action.";
            if (php_sapi_name() === 'cli') {
                echo "SESSION SUCCESS: " . $_SESSION['success'] . "\n";
                exit;
            }
            header('Location: ' . url('/incidents/show?id=' . $id));
            exit;
            
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            if (php_sapi_name() === 'cli') {
                echo "SESSION ERROR: " . $_SESSION['error'] . "\n";
                exit;
            }
            header('Location: ' . url('/incidents/show?id=' . $_POST['incident_id']));
            exit;
        }
    }
}
