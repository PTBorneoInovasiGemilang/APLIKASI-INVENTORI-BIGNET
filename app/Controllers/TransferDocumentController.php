<?php

namespace App\Controllers;

use App\Helpers\AuthHelper;
use App\Core\Database;
use PDO;

class TransferDocumentController {
    public function __construct() {
        AuthHelper::requireRole(['SUPERADMIN', 'ADMIN']);
    }

    public function index() {
        $id = (int)($_GET['id'] ?? 0);
        $versionNo = max(0, (int)($_GET['version'] ?? 0));
        if ($id <= 0) {
            $_SESSION['error'] = 'Transfer tidak valid.';
            header('Location: ' . url('/transfers'));
            exit;
        }

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("SELECT t.*, sw.name AS source_warehouse, dw.name AS dest_warehouse, u.name AS sender_name,
                r.receiver_name AS latest_receiver_name, r.receiver_role AS latest_receiver_role,
                r.receiver_phone AS latest_receiver_phone, r.received_at AS latest_received_at,
                r.notes AS latest_receiver_notes
            FROM transfers t
            JOIN warehouses sw ON sw.id = t.source_warehouse_id
            JOIN warehouses dw ON dw.id = t.destination_warehouse_id
            JOIN users u ON u.id = t.sent_by
            LEFT JOIN transfer_receipts r ON r.id = (
                SELECT tr2.id FROM transfer_receipts tr2
                WHERE tr2.transfer_id = t.id
                ORDER BY tr2.id DESC LIMIT 1
            )
            WHERE t.id = ?");
        $stmt->execute([$id]);
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$transfer) {
            $_SESSION['error'] = 'Transfer tidak ditemukan.';
            header('Location: ' . url('/transfers'));
            exit;
        }

        // Receipt identity belongs to transfer_receipts. Prefer the latest receipt
        // over legacy nullable fields on transfers so a receipt version is frozen
        // with the actual receiver/time of the event.
        if ($transfer['latest_receiver_name'] !== null) {
            $transfer['receiver_name'] = $transfer['latest_receiver_name'];
            $transfer['receiver_role'] = $transfer['latest_receiver_role'];
            $transfer['receiver_phone'] = $transfer['latest_receiver_phone'];
            $transfer['received_at'] = $transfer['latest_received_at'];
            $transfer['receiver_notes'] = $transfer['latest_receiver_notes'];
        }

        $this->ensureSnapshot($pdo, $transfer, $id);

        $versionStmt = $pdo->prepare('SELECT * FROM transfer_surat_jalan_versions WHERE transfer_id = ? ORDER BY version_no DESC');
        $versionStmt->execute([$id]);
        $versions = $versionStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$versions) {
            $_SESSION['error'] = 'Belum ada Surat Jalan untuk transfer ini.';
            header('Location: ' . url('/transfers/show?id=' . $id));
            exit;
        }

        if ($versionNo > 0) {
            $selectedStmt = $pdo->prepare('SELECT * FROM transfer_surat_jalan_versions WHERE transfer_id = ? AND version_no = ? LIMIT 1');
            $selectedStmt->execute([$id, $versionNo]);
            $selectedVersion = $selectedStmt->fetch(PDO::FETCH_ASSOC);
            if (!$selectedVersion) $selectedVersion = $versions[0];
        } else {
            $selectedVersion = $versions[0];
        }

        require __DIR__ . '/../../resources/views/transactions/transfers/documents.php';
    }

    private function ensureSnapshot(PDO $pdo, array $transfer, int $transferId): void {
        $itemsStmt = $pdo->prepare("SELECT ti.id, ti.quantity_sent, ti.quantity_received, m.kode_barang, m.nama, m.is_serialized
            FROM transfer_items ti
            JOIN master_items m ON m.id = ti.master_item_id
            WHERE ti.transfer_id = ? ORDER BY ti.id ASC");
        $itemsStmt->execute([$transferId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        $serialStmt = $pdo->prepare("SELECT tis.transfer_item_id, sn.serial_number, tis.status
            FROM transfer_item_serials tis
            JOIN serial_numbers sn ON sn.id = tis.serial_number_id
            WHERE tis.transfer_item_id IN (SELECT id FROM transfer_items WHERE transfer_id = ?)
            ORDER BY tis.id ASC");
        $serialStmt->execute([$transferId]);
        $serialMap = [];
        foreach ($serialStmt->fetchAll(PDO::FETCH_ASSOC) as $sn) {
            $serialMap[(int)$sn['transfer_item_id']][] = ['serial_number'=>$sn['serial_number'],'status'=>$sn['status']];
        }

        $currentItems = [];
        foreach ($items as $item) {
            $currentItems[] = [
                'kode_barang'=>$item['kode_barang'],
                'nama'=>$item['nama'],
                'is_serialized'=>(int)$item['is_serialized'],
                'quantity_sent'=>(int)$item['quantity_sent'],
                'quantity_received'=>(int)$item['quantity_received'],
                'serial_numbers'=>$serialMap[(int)$item['id']] ?? [],
            ];
        }

        $state = [
            'driver_name'=>$transfer['driver_name'] ?? null,
            'driver_phone'=>$transfer['driver_phone'] ?? null,
            'vehicle_number'=>$transfer['vehicle_number'] ?? null,
            'receiver_name'=>$transfer['receiver_name'] ?? null,
            'receiver_role'=>$transfer['receiver_role'] ?? null,
            'receiver_phone'=>$transfer['receiver_phone'] ?? null,
            'received_at'=>$transfer['received_at'] ?? null,
            'items'=>$currentItems,
        ];
        $fingerprint = hash('sha256', json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $lastStmt = $pdo->prepare('SELECT * FROM transfer_surat_jalan_versions WHERE transfer_id = ? ORDER BY version_no DESC LIMIT 1');
        $lastStmt->execute([$transferId]);
        $last = $lastStmt->fetch(PDO::FETCH_ASSOC);

        if ($last) {
            $lastItems = json_decode($last['items_json'] ?? '[]', true);
            $lastState = [
                'driver_name'=>$last['driver_name'],
                'driver_phone'=>$last['driver_phone'],
                'vehicle_number'=>$last['vehicle_number'],
                'receiver_name'=>$last['receiver_name'],
                'receiver_role'=>$last['receiver_role'],
                'receiver_phone'=>$last['receiver_phone'],
                'received_at'=>$last['received_at'] ?? null,
                'items'=>is_array($lastItems)?$lastItems:[],
            ];
            $lastFingerprint = hash('sha256', json_encode($lastState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            if ($lastFingerprint === $fingerprint) return;
        }

        $nextVersion = $last ? ((int)$last['version_no'] + 1) : 1;
        if ($nextVersion === 1) {
            $eventLabel = 'Pengiriman Awal';
            $receiverName = null; $receiverRole = null; $receiverPhone = null;
            $snapshotItems = $currentItems;
            foreach ($snapshotItems as &$snapshotItem) {
                $snapshotItem['quantity_received'] = 0;
                foreach ($snapshotItem['serial_numbers'] as &$serial) $serial['status'] = 'IN_TRANSIT';
            }
            unset($snapshotItem, $serial);
        } else {
            $receivedEvents = $nextVersion - 1;
            $eventLabel = 'Pembaruan Penerimaan #' . $receivedEvents;
            $receiverName = $transfer['receiver_name'] ?? null;
            $receiverRole = $transfer['receiver_role'] ?? null;
            $receiverPhone = $transfer['receiver_phone'] ?? null;
            $snapshotItems = $currentItems;
        }

        $documentNo = 'SJ-' . $transfer['transaction_code'] . '-' . str_pad((string)$nextVersion, 2, '0', STR_PAD_LEFT);
        $createdBy = (int)(AuthHelper::user()['id'] ?? 0);

        $insert = $pdo->prepare("INSERT INTO transfer_surat_jalan_versions
            (transfer_id, version_no, document_no, document_type, event_label, created_by, sent_at, received_at,
             sender_name, driver_name, driver_phone, vehicle_number, receiver_name, receiver_role, receiver_phone,
             source_warehouse, destination_warehouse, notes, items_json)
            VALUES (?, ?, ?, 'TRANSFER', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([
            $transferId,$nextVersion,$documentNo,$eventLabel,$createdBy>0?$createdBy:null,
            $transfer['sent_at']??null,$nextVersion>1?($transfer['received_at']??date('Y-m-d H:i:s')):null,
            $transfer['sender_name']??null,$transfer['driver_name']??null,$transfer['driver_phone']??null,
            $transfer['vehicle_number']??null,$receiverName,$receiverRole,$receiverPhone,
            $transfer['source_warehouse']??null,$transfer['destination_warehouse']??null,
            $transfer['notes']??null,json_encode($snapshotItems,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
        ]);
    }
}
