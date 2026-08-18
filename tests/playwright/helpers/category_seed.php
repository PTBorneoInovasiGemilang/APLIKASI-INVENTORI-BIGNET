<?php
/**
 * Test helper untuk E2E (bulk action, users, dll).
 * Usage: php tests/playwright/helpers/category_seed.php <action>
 *
 * Actions:
 *  - seed         : insert 2 kategori test (PW-T*) -> echo id pertama
 *  - cleanup      : hapus semua kategori test (PW-T*)
 *  - count        : jumlah kategori test tersisa
 *  - firstuserid  : id user pertama
 *  - usedcat      : id kategori yang masih dipakai master_items (tidak boleh dihapus)
 *  - testcat      : id kategori test pertama (PW-T*)
 */
$action = $argv[1] ?? 'count';
$p = new PDO('mysql:host=127.0.0.1;dbname=db_gudang_tommy;charset=utf8mb4', 'root', '');

switch ($action) {
    case 'seed':
        $p->exec("INSERT INTO item_categories (code, name, description, is_active) VALUES ('PW-T1','PW Test A','t',1),('PW-T2','PW Test B','t',1)");
        echo $p->lastInsertId();
        break;
    case 'cleanup':
        $p->exec("DELETE FROM item_categories WHERE code LIKE 'PW-T%'");
        break;
    case 'firstuserid':
        echo $p->query("SELECT id FROM users ORDER BY id LIMIT 1")->fetchColumn();
        break;
    case 'usedcat':
        echo $p->query("SELECT item_category_id FROM master_items LIMIT 1")->fetchColumn();
        break;
    case 'testcat':
        echo $p->query("SELECT id FROM item_categories WHERE code LIKE 'PW-T%' LIMIT 1")->fetchColumn();
        break;
    case 'count':
    default:
        echo $p->query("SELECT COUNT(*) FROM item_categories WHERE code LIKE 'PW-T%'")->fetchColumn();
        break;
}
