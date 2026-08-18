<?php
// Output: kode_serialized|nama_serialized|serial1|kode_bulk|nama_bulk
$pdo = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');

$kode = $pdo->query("SELECT m.kode_barang, m.nama FROM master_items m WHERE m.is_serialized = 1 ORDER BY m.id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$serial = $pdo->query("SELECT sn.serial_number FROM serial_numbers sn JOIN master_items m ON sn.master_item_id = m.id WHERE m.is_serialized = 1 ORDER BY sn.id LIMIT 1")->fetchColumn();
$bulk = $pdo->query("SELECT m.kode_barang, m.nama FROM master_items m JOIN stocks s ON s.master_item_id = m.id WHERE m.is_serialized = 0 ORDER BY m.id LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo ($kode['kode_barang'] ?? '') . '|' . ($kode['nama'] ?? '') . '|' . ($serial ?? '') . '|' . ($bulk['kode_barang'] ?? '') . '|' . ($bulk['nama'] ?? '');
