<?php

namespace App\Controllers;

use App\Core\Database;
use App\Helpers\AuthHelper;
use App\Helpers\NotificationHelper;
use App\Services\StockService;
use PDO;

class ReturnWorkflowController extends ReturnController
{
    private function go(string $path): void { header('Location: '.url($path)); exit; }
    private function audit(PDO $pdo,int $uid,string $action,int $id,string $note,?array $old=null,?array $new=null): void {
        $q=$pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,old_data,new_data,ip_address,user_agent) VALUES (?,?, 'returns',?,?,?,?,?)");
        $q->execute([$uid,$action,$id,$old?json_encode($old,JSON_UNESCAPED_UNICODE):null,json_encode(array_merge($new??[],['notes'=>$note]),JSON_UNESCAPED_UNICODE),$_SERVER['REMOTE_ADDR']??null,$_SERVER['HTTP_USER_AGENT']??null]);
    }

    public function process(): void
    {
        AuthHelper::requireRole(['ADMIN']); AuthHelper::denySuperadminWrite();
        if(!hash_equals($_SESSION['csrf_token']??'',$_POST['csrf_token']??'')){$_SESSION['error']='CSRF validation gagal.';$this->go('/returns');}
        $id=(int)($_POST['id']??0);$pdo=Database::getInstance()->getConnection();$uid=(int)AuthHelper::user()['id'];$svc=new StockService();
        try{
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT * FROM returns WHERE id=? FOR UPDATE");$q->execute([$id]);$ret=$q->fetch(PDO::FETCH_ASSOC);
            if(!$ret||$ret['status']!=='PENDING')throw new \Exception('Pengembalian sudah diproses atau tidak valid.');
            $q=$pdo->prepare("SELECT ri.*,m.is_serialized FROM return_items ri LEFT JOIN master_items m ON m.id=ri.master_item_id WHERE ri.return_id=? FOR UPDATE");$q->execute([$id]);
            foreach($q->fetchAll(PDO::FETCH_ASSOC) as $item){
                $cond=$_POST['conditions'][$item['id']]??'GOOD';$good=in_array($cond,['GOOD','BAIK','AVAILABLE'],true);
                $pdo->prepare("UPDATE return_items SET condition_status=? WHERE id=?")->execute([$cond,$item['id']]);
                $qty=(int)$item['quantity'];
                if(!empty($item['loan_manual_item_id'])){
                    $pdo->prepare("UPDATE loan_manual_items SET returned_quantity=returned_quantity+? WHERE id=?")->execute([$qty,(int)$item['loan_manual_item_id']]);
                }elseif(!empty($item['loan_item_id'])){
                    $pdo->prepare("UPDATE loan_items SET returned_quantity=returned_quantity+? WHERE id=?")->execute([$qty,(int)$item['loan_item_id']]);
                }
                if(!empty($item['serial_number_id'])){
                    $newStatus=$good?'AVAILABLE':'BROKEN';
                    $svc->moveSerial((int)$item['serial_number_id'],$newStatus,(int)$ret['warehouse_id'],null,null,'return',$id,'Pengembalian barang.', $cond);
                    if(!empty($item['loan_item_id'])){
                        $pdo->prepare("UPDATE loan_item_serials SET status='RETURNED' WHERE loan_item_id=? AND serial_number_id=?")->execute([(int)$item['loan_item_id'],(int)$item['serial_number_id']]);
                    }elseif(!empty($item['loan_manual_item_id']) && $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='loan_item_serials' AND COLUMN_NAME='loan_manual_item_id'")->fetchColumn()){
                        $pdo->prepare("UPDATE loan_item_serials SET status='RETURNED' WHERE loan_manual_item_id=? AND serial_number_id=?")->execute([(int)$item['loan_manual_item_id'],(int)$item['serial_number_id']]);
                    }
                }
            }
            $loanId=(int)$ret['reference_id'];
            if($loanId){
                $s=$pdo->prepare("SELECT (SELECT COALESCE(SUM(quantity),0) FROM loan_items WHERE loan_id=?)+(SELECT COALESCE(SUM(quantity),0) FROM loan_manual_items WHERE loan_id=?) total_qty,(SELECT COALESCE(SUM(returned_quantity),0) FROM loan_items WHERE loan_id=?)+(SELECT COALESCE(SUM(returned_quantity),0) FROM loan_manual_items WHERE loan_id=?) total_returned");
                $s->execute([$loanId,$loanId,$loanId,$loanId]);$st=$s->fetch(PDO::FETCH_ASSOC);
                $loanStatus=(int)$st['total_returned'] >= (int)$st['total_qty'] ? 'RETURNED':'PARTIAL_RETURNED';
                $pdo->prepare("UPDATE loans SET status=? WHERE id=?")->execute([$loanStatus,$loanId]);
            }
            $pdo->prepare("UPDATE returns SET status='COMPLETED',processed_by=? WHERE id=?")->execute([$uid,$id]);
            $this->audit($pdo,$uid,'RETURN_PROCESSED',$id,'Pengembalian diperiksa Admin. Barang baik kembali tersedia; barang rusak menjadi BROKEN. Stok fisik tidak dimutasi oleh pengembalian custody.',['status'=>'PENDING'],['status'=>'COMPLETED']);
            $pdo->commit();
            try{NotificationHelper::createForUsers([(int)$ret['technician_id']],'Pengembalian Diproses','Pengembalian barang Anda sudah diperiksa Admin.','success','/returns/show?id='.$id);}catch(\Throwable $ignored){}
            $_SESSION['success']='Pengembalian berhasil diproses.';
        }catch(\Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$_SESSION['error']='Gagal memproses pengembalian: '.$e->getMessage();}
        $this->go('/returns/show?id='.$id);
    }

    public function apiLoanItems(): void
    {
        header('Content-Type: application/json');
        try {
            $loanId = (int)($_GET['loan_id'] ?? $_GET['id'] ?? 0);
            if ($loanId <= 0) {
                echo json_encode([]);
                return;
            }
            $pdo = Database::getInstance()->getConnection();

            $stmt = $pdo->prepare("SELECT li.id AS loan_item_id, li.master_item_id, m.nama, m.is_serialized, (li.quantity - li.returned_quantity) AS remaining_quantity, 'STOCK' AS source_type FROM loan_items li JOIN master_items m ON li.master_item_id = m.id WHERE li.loan_id = ? AND (li.quantity - li.returned_quantity) > 0 ORDER BY li.id");
            $stmt->execute([$loanId]);
            $stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $snStmt = $pdo->prepare("SELECT sn.id, sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_item_id = ? AND lis.status IN ('PENDING','BORROWED') ORDER BY sn.serial_number");
            foreach ($stockItems as &$item) {
                $item['serials'] = [];
                if ((int)$item['is_serialized'] === 1) {
                    $snStmt->execute([(int)$item['loan_item_id']]);
                    $item['serials'] = $snStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($item);

            $manualStmt = $pdo->prepare("SELECT lmi.id AS manual_item_id, lmi.master_item_id, lmi.name AS nama, COALESCE(m.is_serialized,0) AS is_serialized, (lmi.quantity - lmi.returned_quantity) AS remaining_quantity, 'MANUAL' AS source_type FROM loan_manual_items lmi LEFT JOIN master_items m ON m.id = lmi.master_item_id WHERE lmi.loan_id = ? AND (lmi.quantity - lmi.returned_quantity) > 0 ORDER BY lmi.id");
            $manualStmt->execute([$loanId]);
            $manualItems = $manualStmt->fetchAll(PDO::FETCH_ASSOC);

            $manualSnStmt = $pdo->prepare("SELECT sn.id, sn.serial_number FROM loan_item_serials lis JOIN serial_numbers sn ON lis.serial_number_id = sn.id WHERE lis.loan_manual_item_id = ? AND lis.status IN ('PENDING','BORROWED') ORDER BY sn.serial_number");
            foreach ($manualItems as &$item) {
                $item['serials'] = [];
                if ((int)$item['is_serialized'] === 1) {
                    $manualSnStmt->execute([(int)$item['manual_item_id']]);
                    $item['serials'] = $manualSnStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            unset($item);

            echo json_encode(array_merge($stockItems, $manualItems), JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
