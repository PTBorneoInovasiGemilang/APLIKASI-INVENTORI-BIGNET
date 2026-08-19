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
}
