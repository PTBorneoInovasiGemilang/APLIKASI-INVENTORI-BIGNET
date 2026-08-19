<?php

namespace App\Controllers;
use App\Helpers\AuthHelper;use App\Core\Database;use PDO;

class StockController {
 public function __construct(){AuthHelper::requireRole(['SUPERADMIN','ADMIN']);}
 public function index(){
  $pdo=Database::getInstance()->getConnection();$warehouseId=isset($_GET['warehouse_id'])?(int)$_GET['warehouse_id']:0;$categoryId=isset($_GET['category_id'])?(int)$_GET['category_id']:0;$search=$_GET['search']??'';$filter=$_GET['filter']??'';$page=max(1,(int)($_GET['page']??1));$limit=15;$offset=($page-1)*$limit;
  $where=['1=1'];$params=[];if($warehouseId>0){$where[]='s.warehouse_id=?';$params[]=$warehouseId;}if($categoryId>0){$where[]='m.item_category_id=?';$params[]=$categoryId;}if($filter==='low_stock'){$where[]='((m.is_serialized=1 AND (SELECT COUNT(*) FROM serial_numbers sn0 WHERE sn0.master_item_id=m.id AND sn0.current_warehouse_id=s.warehouse_id AND sn0.status=\'AVAILABLE\'))<=m.stok_minimum OR (m.is_serialized=0 AND (s.quantity-(SELECT COALESCE(SUM(li.quantity-li.returned_quantity),0) FROM loan_items li JOIN loans l ON l.id=li.loan_id WHERE li.master_item_id=m.id AND l.warehouse_id=s.warehouse_id AND l.status IN (\'BORROWED\',\'PARTIAL_RETURNED\')))<=m.stok_minimum))';}if($search!==''){$where[]='(m.kode_barang LIKE ? OR m.nama LIKE ?)';$params[]="%$search%";$params[]="%$search%";}$whereSql='WHERE '.implode(' AND ',$where);
  $countSql="SELECT COUNT(*) FROM stocks s JOIN master_items m ON s.master_item_id=m.id JOIN warehouses w ON s.warehouse_id=w.id $whereSql";$c=$pdo->prepare($countSql);$c->execute($params);$totalItems=(int)$c->fetchColumn();$totalPages=(int)ceil($totalItems/$limit);
  $sql="SELECT s.*,m.nama item_name,m.is_serialized,m.stok_minimum,m.is_active,w.name warehouse_name,c.name category_name,u.name unit_name,
   (SELECT COUNT(*) FROM serial_numbers sn WHERE sn.master_item_id=m.id AND sn.current_warehouse_id=s.warehouse_id) physical_serials,
   (SELECT COUNT(*) FROM serial_numbers sn WHERE sn.master_item_id=m.id AND sn.current_warehouse_id=s.warehouse_id AND sn.status='AVAILABLE') available_serials,
   (SELECT COUNT(*) FROM loan_item_serials lis JOIN loan_items li ON li.id=lis.loan_item_id JOIN loans l ON l.id=li.loan_id WHERE li.master_item_id=m.id AND l.warehouse_id=s.warehouse_id AND l.status IN ('BORROWED','PARTIAL_RETURNED') AND lis.status='BORROWED') borrowed_serials,
   (SELECT COALESCE(SUM(li.quantity-li.returned_quantity),0) FROM loan_items li JOIN loans l ON l.id=li.loan_id WHERE li.master_item_id=m.id AND l.warehouse_id=s.warehouse_id AND l.status IN ('BORROWED','PARTIAL_RETURNED')) borrowed_bulk
   FROM stocks s JOIN master_items m ON s.master_item_id=m.id JOIN warehouses w ON s.warehouse_id=w.id LEFT JOIN item_categories c ON m.item_category_id=c.id LEFT JOIN units u ON m.unit_id=u.id $whereSql ORDER BY w.name ASC,m.nama ASC LIMIT $limit OFFSET $offset";
  $stmt=$pdo->prepare($sql);$stmt->execute($params);$stocks=$stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($stocks as &$row){$row['borrowed_quantity']=(int)$row['is_serialized']?(int)$row['borrowed_serials']:(int)$row['borrowed_bulk'];$row['physical_quantity']=(int)$row['is_serialized']?(int)$row['physical_serials']+(int)$row['borrowed_serials']:(int)$row['quantity']+(int)$row['borrowed_bulk'];$row['available_quantity']=max(0,(int)$row['physical_quantity']-(int)$row['borrowed_quantity']);}$stocks=array_values($stocks);unset($row);
  $warehouses=$pdo->query("SELECT * FROM warehouses WHERE is_active=1")->fetchAll();$categories=$pdo->query("SELECT * FROM item_categories WHERE is_active=1")->fetchAll();require __DIR__.'/../../resources/views/inventory/stocks/index.php';
 }
}
