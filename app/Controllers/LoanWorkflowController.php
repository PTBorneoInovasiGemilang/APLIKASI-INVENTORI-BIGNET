<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use App\Helpers\NotificationHelper;
use App\Services\StockService;
use PDO;

class LoanWorkflowController extends LoanController
{
    private function go(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    private function csrf(string $fallback): void
    {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF validation gagal.';
            $this->go($fallback);
        }
    }

    private function audit(PDO $pdo, int $uid, string $action, int $loanId, string $note, ?array $old = null, ?array $new = null): void
    {
        $stmt = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, entity, entity_id, old_data, new_data, ip_address, user_agent)
             VALUES (?, ?, 'loans', ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $uid,
            $action,
            $loanId,
            $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            json_encode(array_merge($new ?? [], ['notes' => $note]), JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    private function bulkAvailable(PDO $pdo, int $itemId, int $warehouseId, ?int $excludeLoanId = null): int
    {
        $params = [$itemId, $warehouseId, $itemId, $warehouseId];
        $exclude = '';
        if ($excludeLoanId !== null) {
            $exclude = ' AND l.id <> ?';
            $params[] = $excludeLoanId;
        }

        $sql = "SELECT GREATEST(
                    COALESCE(s.quantity, 0) - COALESCE((
                        SELECT SUM(li.quantity - li.returned_quantity)
                        FROM loan_items li
                        JOIN loans l ON l.id = li.loan_id
                        WHERE li.master_item_id = ?
                          AND l.warehouse_id = ?
                          AND l.status IN ('APPROVED','BORROWED','PARTIAL_RETURNED')
                          AND li.quantity > li.returned_quantity
                          $exclude
                    ), 0), 0)
                FROM stocks s
                WHERE s.master_item_id = ? AND s.warehouse_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return max(0, (int)$stmt->fetchColumn());
    }

    private function serialAvailable(PDO $pdo, int $itemId, int $warehouseId): int
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM serial_numbers WHERE master_item_id = ? AND current_warehouse_id = ? AND status = 'AVAILABLE'");
        $stmt->execute([$itemId, $warehouseId]);
        return (int)$stmt->fetchColumn();
    }

    public function index(): void
    {
        $pdo = Database::getInstance()->getConnection();
        $user = AuthHelper::user();
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $where = ['1=1'];
        $params = [];
        if (!AuthHelper::hasRole(['SUPERADMIN', 'ADMIN'])) {
            $where[] = 'l.borrower_id = ?';
            $params[] = (int)$user['id'];
        }
        if ($search !== '') {
            $where[] = '(u.name LIKE ? OR w.name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status !== '') {
            $where[] = 'l.status = ?';
            $params[] = $status;
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $count = $pdo->prepare("SELECT COUNT(*) FROM loans l JOIN users u ON u.id = l.borrower_id JOIN warehouses w ON w.id = l.warehouse_id $whereSql");
        $count->execute($params);
        $totalItems = (int)$count->fetchColumn();
        $totalPages = max(1, (int)ceil($totalItems / $limit));

        $stmt = $pdo->prepare("SELECT l.*, u.name AS borrower_name, w.name AS warehouse_name
            FROM loans l JOIN users u ON u.id=l.borrower_id JOIN warehouses w ON w.id=l.warehouse_id
            $whereSql ORDER BY l.id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../../resources/views/workflow/loans/index.php';
    }

    public function create(): void
    {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        $pdo = Database::getInstance()->getConnection();
        $warehouses = $pdo->query("SELECT id,name FROM warehouses WHERE is_active=1 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $items = $pdo->query("SELECT id,nama,is_serialized FROM master_items WHERE is_active=1 ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT id, submission_code, status, created_at FROM submissions WHERE status IN ('APPROVED','PARTIAL_APPROVED') AND submitted_by=? ORDER BY id DESC");
        $stmt->execute([AuthHelper::user()['id']]);
        $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__ . '/../../resources/views/workflow/loans/create.php';
    }

    public function store(): void
    {
        AuthHelper::requireRole(['TEKNISI_NOC']);
        $this->csrf('/loans/create');
        $pdo = Database::getInstance()->getConnection();
        $uid = (int)AuthHelper::user()['id'];
        $warehouseId = (int)($_POST['warehouse_id'] ?? 0);
        $loanType = trim($_POST['loan_type'] ?? 'INTERNAL');
        $submissionId = !empty($_POST['submission_id']) ? (int)$_POST['submission_id'] : null;
        $dueDate = $_POST['due_date'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $sources = $_POST['source_types'] ?? [];
        $itemIds = $_POST['item_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];
        $manualNames = $_POST['manual_names'] ?? [];
        $manualSpecs = $_POST['manual_specs'] ?? [];
        $manualUnits = $_POST['manual_units'] ?? [];

        if ($warehouseId <= 0 || !$sources) {
            $_SESSION['error'] = 'Gudang asal dan minimal satu barang wajib diisi.';
            $this->go('/loans/create');
        }

        try {
            $pdo->beginTransaction();
            $code = \App\Helpers\TransactionHelper::generateCode('LOAN', 'loans');
            $pdo->prepare("INSERT INTO loans (transaction_code, borrower_id, warehouse_id, submission_id, loan_type, loan_date, due_date, status, notes, processed_by)
                VALUES (?, ?, ?, ?, ?, NOW(), ?, 'PENDING_APPROVAL', ?, ?)")
                ->execute([$code, $uid, $warehouseId, $submissionId, $loanType, $dueDate, $notes, $uid]);
            $loanId = (int)$pdo->lastInsertId();

            $stockStmt = $pdo->prepare("INSERT INTO loan_items (loan_id, master_item_id, requested_quantity, approved_quantity, quantity, returned_quantity) VALUES (?, ?, ?, 0, 0, 0)");
            $manualStmt = $pdo->prepare("INSERT INTO loan_manual_items (loan_id, name, master_item_id, requested_quantity, approved_quantity, quantity, returned_quantity, specification, unit) VALUES (?, ?, NULL, ?, 0, 0, 0, ?, ?)");
            $lines = 0;

            foreach ($sources as $i => $source) {
                $qty = (int)($quantities[$i] ?? 0);
                if ($qty <= 0) continue;
                if ($source === 'MANUAL') {
                    $name = trim($manualNames[$i] ?? '');
                    if ($name === '') throw new \Exception('Nama barang manual wajib diisi.');
                    $manualStmt->execute([$loanId, $name, $qty, trim($manualSpecs[$i] ?? ''), trim($manualUnits[$i] ?? '')]);
                    $lines++;
                    continue;
                }

                $itemId = (int)($itemIds[$i] ?? 0);
                $itemQ = $pdo->prepare("SELECT id,nama,is_serialized FROM master_items WHERE id=? AND is_active=1");
                $itemQ->execute([$itemId]);
                $item = $itemQ->fetch(PDO::FETCH_ASSOC);
                if (!$item) throw new \Exception('Barang dari stok tidak valid.');
                $available = (int)$item['is_serialized'] === 1
                    ? $this->serialAvailable($pdo, $itemId, $warehouseId)
                    : $this->bulkAvailable($pdo, $itemId, $warehouseId);
                if ($available < $qty) throw new \Exception("Barang {$item['nama']} yang tersedia untuk dipinjam hanya {$available}.");
                $stockStmt->execute([$loanId, $itemId, $qty]);
                $lines++;
            }

            if ($lines === 0) throw new \Exception('Tidak ada barang valid.');
            $this->audit($pdo, $uid, 'CREATE', $loanId, 'Peminjaman diajukan. Belum ada pengeluaran stok; menunggu keputusan Admin.', null, ['status'=>'PENDING_APPROVAL']);
            $pdo->commit();

            try {
                NotificationHelper::createForRoleOnce('ADMIN', 'Peminjaman Baru', AuthHelper::user()['name'].' mengajukan peminjaman baru.', 'info', '/loans/show?id='.$loanId);
            } catch (\Throwable $ignored) {}
            $_SESSION['success'] = 'Peminjaman berhasil diajukan dan menunggu keputusan Admin.';
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error'] = 'Gagal mengajukan peminjaman: '.$e->getMessage();
        }
        $this->go('/loans');
    }

    public function approve(): void
    {
        AuthHelper::requireRole(['ADMIN']);
        AuthHelper::denySuperadminWrite();
        $this->csrf('/loans');
        $pdo = Database::getInstance()->getConnection();
        $uid = (int)AuthHelper::user()['id'];
        $loanId = (int)($_POST['id'] ?? 0);
        $include = $_POST['approved_include'] ?? [];
        $approvedQty = $_POST['approved_qty'] ?? [];
        $approvedManualQty = $_POST['approved_manual_qty'] ?? [];
        $mappedMaster = $_POST['mapped_master_item'] ?? [];
        $serials = $_POST['approved_serials'] ?? [];
        $note = trim($_POST['approval_notes'] ?? '');

        try {
            $pdo->beginTransaction();
            $q = $pdo->prepare("SELECT * FROM loans WHERE id=? FOR UPDATE");
            $q->execute([$loanId]);
            $loan = $q->fetch(PDO::FETCH_ASSOC);
            if (!$loan || $loan['status'] !== 'PENDING_APPROVAL') throw new \Exception('Peminjaman sudah diproses.');

            $lines = 0;

            $itemsQ = $pdo->prepare("SELECT li.*,m.nama,m.is_serialized FROM loan_items li JOIN master_items m ON m.id=li.master_item_id WHERE li.loan_id=? ORDER BY li.id");
            $itemsQ->execute([$loanId]);
            foreach ($itemsQ->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $id = (int)$item['id'];
                if (!isset($include[$id])) {
                    $pdo->prepare("UPDATE loan_items SET approved_quantity=0, quantity=0 WHERE id=?")->execute([$id]);
                    continue;
                }
                $qty = min((int)$item['requested_quantity'], max(0, (int)($approvedQty[$id] ?? 0)));
                if ($qty < 1) continue;
                $available = (int)$item['is_serialized'] === 1
                    ? $this->serialAvailable($pdo, (int)$item['master_item_id'], (int)$loan['warehouse_id'])
                    : $this->bulkAvailable($pdo, (int)$item['master_item_id'], (int)$loan['warehouse_id'], $loanId);
                if ($available < $qty) throw new \Exception("Stok {$item['nama']} di gudang hanya {$available}.");
                $pdo->prepare("UPDATE loan_items SET approved_quantity=?, quantity=? WHERE id=?")->execute([$qty,$qty,$id]);
                $lines++;

                if ((int)$item['is_serialized'] === 1) {
                    $selected = array_values(array_filter(array_map('intval', (array)($serials[$id] ?? []))));
                    if (count($selected) !== $qty) throw new \Exception("Pilih tepat {$qty} serial untuk {$item['nama']}.");
                    $pdo->prepare("DELETE FROM loan_item_serials WHERE loan_item_id=? AND status='PENDING'")->execute([$id]);
                    $ins = $pdo->prepare("INSERT INTO loan_item_serials (loan_item_id, loan_manual_item_id, serial_number_id, status) VALUES (?,NULL,?,'PENDING')");
                    foreach ($selected as $snId) {
                        $chk = $pdo->prepare("SELECT id FROM serial_numbers WHERE id=? AND master_item_id=? AND current_warehouse_id=? AND status='AVAILABLE' FOR UPDATE");
                        $chk->execute([$snId,$item['master_item_id'],$loan['warehouse_id']]);
                        if (!$chk->fetchColumn()) throw new \Exception('Serial yang dipilih sudah tidak tersedia.');
                        $ins->execute([$id,$snId]);
                    }
                }
            }

            $manualQ = $pdo->prepare("SELECT * FROM loan_manual_items WHERE loan_id=? ORDER BY id");
            $manualQ->execute([$loanId]);
            foreach ($manualQ->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $id = (int)$item['id'];
                $key = 'm'.$id;
                if (!isset($include[$key])) {
                    $pdo->prepare("UPDATE loan_manual_items SET approved_quantity=0, quantity=0, master_item_id=NULL WHERE id=?")->execute([$id]);
                    continue;
                }

                $masterId = (int)($mappedMaster[$id] ?? 0);
                if ($masterId <= 0) throw new \Exception("Barang manual '{$item['name']}' harus dipetakan ke Master Barang sebelum disetujui.");
                $masterQ = $pdo->prepare("SELECT id,nama,is_serialized,is_active FROM master_items WHERE id=?");
                $masterQ->execute([$masterId]);
                $master = $masterQ->fetch(PDO::FETCH_ASSOC);
                if (!$master || (int)$master['is_active'] !== 1) throw new \Exception('Master Barang hasil mapping tidak valid.');

                $qty = min((int)$item['requested_quantity'], max(0, (int)($approvedManualQty[$id] ?? 0)));
                if ($qty < 1) continue;
                $available = (int)$master['is_serialized'] === 1
                    ? $this->serialAvailable($pdo, $masterId, (int)$loan['warehouse_id'])
                    : $this->bulkAvailable($pdo, $masterId, (int)$loan['warehouse_id'], $loanId);
                if ($available < $qty) throw new \Exception("Stok {$master['nama']} di gudang hanya {$available}.");

                $pdo->prepare("UPDATE loan_manual_items SET master_item_id=?, approved_quantity=?, quantity=? WHERE id=?")->execute([$masterId,$qty,$qty,$id]);
                $lines++;

                if ((int)$master['is_serialized'] === 1) {
                    $selected = array_values(array_filter(array_map('intval', (array)($serials[$key] ?? []))));
                    if (count($selected) !== $qty) throw new \Exception("Pilih tepat {$qty} serial untuk {$master['nama']}.");
                    $pdo->prepare("DELETE FROM loan_item_serials WHERE loan_manual_item_id=? AND status='PENDING'")->execute([$id]);
                    $ins = $pdo->prepare("INSERT INTO loan_item_serials (loan_item_id, loan_manual_item_id, serial_number_id, status) VALUES (NULL,?,?,'PENDING')");
                    foreach ($selected as $snId) {
                        $chk = $pdo->prepare("SELECT id FROM serial_numbers WHERE id=? AND master_item_id=? AND current_warehouse_id=? AND status='AVAILABLE' FOR UPDATE");
                        $chk->execute([$snId,$masterId,$loan['warehouse_id']]);
                        if (!$chk->fetchColumn()) throw new \Exception('Serial manual yang dipilih sudah tidak tersedia.');
                        $ins->execute([$id,$snId]);
                    }
                }
            }

            if ($lines === 0) throw new \Exception('Minimal satu barang harus disetujui.');
            $pdo->prepare("UPDATE loans SET status='APPROVED', approved_by=?, approved_at=NOW(), approval_notes=?, processed_by=? WHERE id=?")
                ->execute([$uid, $note !== '' ? $note : null, $uid, $loanId]);
            $this->audit($pdo,$uid,'APPROVE',$loanId,'Admin menentukan barang, jumlah, mapping manual, dan serial yang boleh dipinjam.',['status'=>'PENDING_APPROVAL'],['status'=>'APPROVED']);
            $pdo->commit();
            try { NotificationHelper::createForUsers([(int)$loan['borrower_id']], 'Peminjaman Disetujui', 'Admin menyetujui sebagian/seluruh permintaan peminjaman. Menunggu serah terima.', 'success', '/loans/show?id='.$loanId); } catch (\Throwable $ignored) {}
            $_SESSION['success']='Peminjaman disetujui sesuai keputusan Admin. Belum menjadi barang keluar sampai serah terima.';
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['error']='Gagal menyetujui peminjaman: '.$e->getMessage();
        }
        $this->go('/loans/show?id='.$loanId);
    }

    public function reject(): void
    {
        AuthHelper::requireRole(['ADMIN']);
        AuthHelper::denySuperadminWrite();
        $this->csrf('/loans');
        $pdo=Database::getInstance()->getConnection();
        $uid=(int)AuthHelper::user()['id'];
        $loanId=(int)($_POST['id']??0);
        $reason=trim($_POST['approval_notes']??'');
        if($reason===''){$_SESSION['error']='Alasan penolakan wajib diisi.';$this->go('/loans/show?id='.$loanId);}
        try{
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT borrower_id,status FROM loans WHERE id=? FOR UPDATE");$q->execute([$loanId]);$loan=$q->fetch(PDO::FETCH_ASSOC);
            if(!$loan||$loan['status']!=='PENDING_APPROVAL')throw new \Exception('Peminjaman sudah diproses.');
            $pdo->prepare("UPDATE loans SET status='REJECTED',approved_by=?,approved_at=NOW(),approval_notes=?,processed_by=? WHERE id=?")->execute([$uid,$reason,$uid,$loanId]);
            $this->audit($pdo,$uid,'REJECT',$loanId,$reason,['status'=>'PENDING_APPROVAL'],['status'=>'REJECTED']);
            $pdo->commit();
            try{NotificationHelper::createForUsers([(int)$loan['borrower_id']],'Peminjaman Ditolak','Peminjaman Anda ditolak oleh Admin.','danger','/loans/show?id='.$loanId);}catch(\Throwable $ignored){}
            $_SESSION['success']='Peminjaman ditolak.';
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$_SESSION['error']='Gagal menolak: '.$e->getMessage();}
        $this->go('/loans/show?id='.$loanId);
    }

    public function handover(): void
    {
        AuthHelper::requireRole(['ADMIN']);
        AuthHelper::denySuperadminWrite();
        $this->csrf('/loans');
        $pdo=Database::getInstance()->getConnection();
        $svc=new StockService();
        $uid=(int)AuthHelper::user()['id'];
        $loanId=(int)($_POST['id']??0);

        try{
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT * FROM loans WHERE id=? FOR UPDATE");$q->execute([$loanId]);$loan=$q->fetch(PDO::FETCH_ASSOC);
            if(!$loan||$loan['status']!=='APPROVED')throw new \Exception('Peminjaman belum siap diserahkan.');
            if(!empty($loan['outgoing_id']))throw new \Exception('Peminjaman ini sudah memiliki transaksi Barang Keluar.');

            $outCode=\App\Helpers\TransactionHelper::generateCode('OUT','outgoing');
            $pdo->prepare("INSERT INTO outgoing (transaction_code, warehouse_id, submission_id, purpose, loan_id, processed_by, notes, status) VALUES (?,?,NULL,'LOAN',?,?,?,'COMPLETED')")
                ->execute([$outCode,$loan['warehouse_id'],$loanId,$uid,'Pengeluaran untuk peminjaman '.$loan['transaction_code']]);
            $outgoingId=(int)$pdo->lastInsertId();

            $outItemStmt=$pdo->prepare("INSERT INTO outgoing_items (outgoing_id,master_item_id,submission_item_id,quantity) VALUES (?, ?, NULL, ?)");

            $itemsQ=$pdo->prepare("SELECT li.*,m.nama,m.is_serialized FROM loan_items li JOIN master_items m ON m.id=li.master_item_id WHERE li.loan_id=? AND li.quantity>0 ORDER BY li.id");$itemsQ->execute([$loanId]);
            foreach($itemsQ->fetchAll(PDO::FETCH_ASSOC) as $item){
                $qty=(int)$item['quantity'];
                $available=(int)$item['is_serialized']===1?$this->serialAvailable($pdo,(int)$item['master_item_id'],(int)$loan['warehouse_id']):$this->bulkAvailable($pdo,(int)$item['master_item_id'],(int)$loan['warehouse_id'],$loanId);
                if($available<$qty)throw new \Exception("Stok {$item['nama']} berubah. Tersedia {$available}, akan dipinjam {$qty}.");
                $outItemStmt->execute([$outgoingId,$item['master_item_id'],$qty]);
                if((int)$item['is_serialized']===1){
                    $sn=$pdo->prepare("SELECT serial_number_id FROM loan_item_serials WHERE loan_item_id=? AND status='PENDING' FOR UPDATE");$sn->execute([$item['id']]);$rows=$sn->fetchAll(PDO::FETCH_COLUMN);
                    if(count($rows)!==$qty)throw new \Exception("Nomor seri {$item['nama']} belum lengkap.");
                    foreach($rows as $snId){$svc->moveSerial((int)$snId,'LOANED',null,null,(int)$loan['borrower_id'],'outgoing',$outgoingId,'Barang keluar untuk peminjaman '.$outCode);}
                    $pdo->prepare("UPDATE loan_item_serials SET status='BORROWED' WHERE loan_item_id=? AND status='PENDING'")->execute([$item['id']]);
                }
            }

            $manualQ=$pdo->prepare("SELECT lmi.*,m.nama master_name,m.is_serialized FROM loan_manual_items lmi JOIN master_items m ON m.id=lmi.master_item_id WHERE lmi.loan_id=? AND lmi.quantity>0 ORDER BY lmi.id");$manualQ->execute([$loanId]);
            foreach($manualQ->fetchAll(PDO::FETCH_ASSOC) as $item){
                $qty=(int)$item['quantity'];
                $available=(int)$item['is_serialized']===1?$this->serialAvailable($pdo,(int)$item['master_item_id'],(int)$loan['warehouse_id']):$this->bulkAvailable($pdo,(int)$item['master_item_id'],(int)$loan['warehouse_id'],$loanId);
                if($available<$qty)throw new \Exception("Stok {$item['master_name']} berubah. Tersedia {$available}, akan dipinjam {$qty}.");
                $outItemStmt->execute([$outgoingId,$item['master_item_id'],$qty]);
                if((int)$item['is_serialized']===1){
                    $sn=$pdo->prepare("SELECT serial_number_id FROM loan_item_serials WHERE loan_manual_item_id=? AND status='PENDING' FOR UPDATE");$sn->execute([$item['id']]);$rows=$sn->fetchAll(PDO::FETCH_COLUMN);
                    if(count($rows)!==$qty)throw new \Exception("Nomor seri {$item['master_name']} belum lengkap.");
                    foreach($rows as $snId){$svc->moveSerial((int)$snId,'LOANED',null,null,(int)$loan['borrower_id'],'outgoing',$outgoingId,'Barang keluar untuk peminjaman '.$outCode);}
                    $pdo->prepare("UPDATE loan_item_serials SET status='BORROWED' WHERE loan_manual_item_id=? AND status='PENDING'")->execute([$item['id']]);
                }
            }

            $pdo->prepare("UPDATE loans SET status='BORROWED', outgoing_id=?, handed_over_by=?, handed_over_at=NOW(), processed_by=? WHERE id=?")
                ->execute([$outgoingId,$uid,$uid,$loanId]);
            $this->audit($pdo,$uid,'HANDOVER',$loanId,'Serah terima dibuat melalui transaksi Barang Keluar. Stok fisik tidak berkurang; availability berkurang karena custody.',['status'=>'APPROVED'],['status'=>'BORROWED','outgoing_id'=>$outgoingId]);
            $pdo->commit();
            try{NotificationHelper::createForUsers([(int)$loan['borrower_id']],'Barang Peminjaman Diserahkan','Barang telah diserahkan dan dicatat sebagai Barang Keluar untuk Peminjaman.','success','/loans/show?id='.$loanId);}catch(\Throwable $ignored){}
            $_SESSION['success']='Serah terima berhasil. Transaksi Barang Keluar telah dibuat; stok fisik tetap.';
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$_SESSION['error']='Gagal serah terima: '.$e->getMessage();}
        $this->go('/loans/show?id='.$loanId);
    }

    public function show(): void
    {
        $id=(int)($_GET['id']??0);$pdo=Database::getInstance()->getConnection();$user=AuthHelper::user();
        $q=$pdo->prepare("SELECT l.*, w.name warehouse_name, u.name borrower_name, o.transaction_code outgoing_code FROM loans l JOIN warehouses w ON w.id=l.warehouse_id JOIN users u ON u.id=l.borrower_id LEFT JOIN outgoing o ON o.id=l.outgoing_id WHERE l.id=?");$q->execute([$id]);$loan=$q->fetch(PDO::FETCH_ASSOC);
        if(!$loan){$_SESSION['error']='Peminjaman tidak ditemukan.';$this->go('/loans');}
        if($user['role']==='TEKNISI_NOC' && (int)$loan['borrower_id']!==(int)$user['id'])$this->go('/loans');

        $items=[];
        $q=$pdo->prepare("SELECT li.id,li.master_item_id,li.requested_quantity,li.approved_quantity,li.quantity,li.returned_quantity,m.nama,m.is_serialized,'STOCK' source_type FROM loan_items li JOIN master_items m ON m.id=li.master_item_id WHERE li.loan_id=? ORDER BY li.id");$q->execute([$id]);
        foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r)$items[]=$r;
        $q=$pdo->prepare("SELECT lmi.id,lmi.master_item_id,lmi.name nama,lmi.requested_quantity,lmi.approved_quantity,lmi.quantity,lmi.returned_quantity,lmi.specification,lmi.unit,m.nama mapped_name,m.is_serialized,'MANUAL' source_type FROM loan_manual_items lmi LEFT JOIN master_items m ON m.id=lmi.master_item_id WHERE lmi.loan_id=? ORDER BY lmi.id");$q->execute([$id]);
        foreach($q->fetchAll(PDO::FETCH_ASSOC) as $r){$r['is_serialized']=(int)($r['is_serialized']??0);$items[]=$r;}

        $serialQ=$pdo->prepare("SELECT lis.serial_number_id,lis.loan_item_id,lis.loan_manual_item_id,lis.status,sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON sn.id=lis.serial_number_id WHERE lis.loan_item_id=? OR lis.loan_manual_item_id=? ORDER BY sn.serial_number");
        foreach($items as &$item){$item['serial_numbers']=[];$key=(int)$item['id'];$serialQ->execute([$item['source_type']==='STOCK'?$key:0,$item['source_type']==='MANUAL'?$key:0]);$item['serial_numbers']=$serialQ->fetchAll(PDO::FETCH_ASSOC);}unset($item);

        $masterItems=$pdo->query("SELECT id,nama,is_serialized FROM master_items WHERE is_active=1 ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
        $availableSerials=[];
        if($loan['status']==='PENDING_APPROVAL' && AuthHelper::hasRole(['ADMIN'])){
            foreach($items as $item){
                if(!(int)$item['is_serialized'])continue;
                $masterId=(int)($item['master_item_id']??0);if($masterId<=0)continue;
                $q=$pdo->prepare("SELECT id,serial_number FROM serial_numbers WHERE master_item_id=? AND current_warehouse_id=? AND status='AVAILABLE' ORDER BY serial_number");$q->execute([$masterId,$loan['warehouse_id']]);
                $availableSerials[$item['source_type']==='MANUAL'?'m'.$item['id']:$item['id']]=$q->fetchAll(PDO::FETCH_ASSOC);
            }
        }

        $q=$pdo->prepare("SELECT a.*,u.name user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id WHERE a.entity='loans' AND a.entity_id=? ORDER BY a.created_at DESC,a.id DESC");$q->execute([$id]);$timeline=$q->fetchAll(PDO::FETCH_ASSOC);
        require __DIR__.'/../../resources/views/workflow/loans/show.php';
    }
}
