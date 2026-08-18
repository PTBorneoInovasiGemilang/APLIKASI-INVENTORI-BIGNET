<?php
// Output baris pertama halaman Stok Barang (semantik sama StockController::index):
//   kode_barang|warehouse_id|warehouse_name|physical|available
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');
$row = $pdo->query("SELECT s.*, m.kode_barang, m.is_serialized, w.name as warehouse_name,
    (SELECT COUNT(*) FROM serial_numbers sn
      WHERE sn.master_item_id = m.id AND sn.current_warehouse_id = s.warehouse_id
        AND sn.status = 'AVAILABLE') as available_serials
    FROM stocks s
    JOIN master_items m ON s.master_item_id = m.id
    JOIN warehouses w ON s.warehouse_id = w.id
    WHERE s.quantity > 0
    ORDER BY w.name ASC, m.nama ASC
    LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$row) { echo ''; exit; }
$physical = (int)$row['quantity'];
$available = $row['is_serialized'] ? (int)$row['available_serials'] : $physical;
echo $row['kode_barang'] . '|' . $row['warehouse_id'] . '|' . $row['warehouse_name'] . '|' . $physical . '|' . $available;
