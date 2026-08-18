<?php

/**
 * ════════════════════════════════════════════════════════════════════════════
 *  RESET TEST DATA — UTILITY HANYA UNTUK ENVIRONMENT TESTING
 * ════════════════════════════════════════════════════════════════════════════
 *
 *  ⚠️  JANGAN DIJALANKAN PADA PRODUCTION.
 *
 *  Utility ini mengosongkan SELURUH data bisnis/test pada database aktif
 *  (lihat config/database.php) dan menyisakan:
 *    - tepat 1 akun SUPERADMIN existing (identitas, password hash, foto profil
 *      dipertahankan; status dipaksa APPROVED + aktif);
 *    - seluruh system settings (app_name, branding, logo/favicon, tema, dll);
 *    - schema_migrations (bookkeeping instalasi).
 *
 *  TIDAK mengubah schema: tidak ada DROP TABLE / DROP DATABASE / DROP COLUMN /
 *  ALTER. Hanya DELETE data. Urutan penghapusan dihitung dari dependency
 *  foreign key aktual (information_schema) — child dihapus sebelum parent —
 *  sehingga aman tanpa mematikan FOREIGN_KEY_CHECKS. Semua dijalankan dalam
 *  satu transaksi: bila ada error, seluruhnya di-rollback (tidak ada state
 *  setengah terhapus).
 *
 *  Penggunaan:
 *    php scripts/reset_test_data.php            → DRY-RUN: ringkasan saja, TANPA perubahan
 *    php scripts/reset_test_data.php --execute  → jalankan reset (dengan ringkasan)
 */

// ---------------------------------------------------------------------------
// Bootstrap (pola autoload sama dengan public/index.php)
// ---------------------------------------------------------------------------
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
require __DIR__ . '/../app/Core/Database.php';

$execute = in_array('--execute', $argv, true);

$dbName = require __DIR__ . '/../config/database.php';
$dbName = $dbName['database'];

echo "==================================================================\n";
echo " RESET TEST DATA (BIGNET)\n";
echo " DB TARGET : {$dbName}\n";
echo " MODE      : " . ($execute ? '** EKSEKUSI **' : 'DRY-RUN (tanpa perubahan)') . "\n";
echo "==================================================================\n";
if ($execute) {
    echo " ⚠️  UTILITY INI HANYA UNTUK ENVIRONMENT TESTING.\n";
    echo " ⚠️  JANGAN DIJALANKAN PADA PRODUCTION.\n";
}

$pdo = \App\Core\Database::getInstance()->getConnection();

// ---------------------------------------------------------------------------
// 1. Identifikasi SUPERADMIN yang dipertahankan
// ---------------------------------------------------------------------------
$saStmt = $pdo->query("SELECT id, name, email, role, account_status, is_active, photo_path FROM users WHERE role = 'SUPERADMIN' ORDER BY id ASC LIMIT 1");
$sa = $saStmt->fetch(PDO::FETCH_ASSOC);
if (!$sa) {
    fwrite(STDERR, "FATAL: tidak ditemukan akun SUPERADMIN untuk dipertahankan. Reset dibatalkan.\n");
    exit(1);
}
$saId = (int)$sa['id'];
$saEmail = $sa['email'];

echo "\nSUPERADMIN YANG DIPERTAHANKAN:\n";
echo "  ID        : {$saId}\n";
echo "  EMAIL     : {$saEmail}\n";
echo "  ROLE      : {$sa['role']}\n";
echo "  STATUS    : {$sa['account_status']} / is_active={$sa['is_active']}\n";
echo "  FOTO      : " . ($sa['photo_path'] ?? '-') . "\n";

// ---------------------------------------------------------------------------
// 2. Daftar tabel + dependency FK (source of truth: information_schema)
// ---------------------------------------------------------------------------
$allTables = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = " . $pdo->quote($dbName) . " AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
sort($allTables);

$KEEP = ['settings', 'schema_migrations', 'users']; // users dihapus khusus (sisakan superadmin)

$edges = []; // child => parent
$fkRows = $pdo->query("SELECT TABLE_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = " . $pdo->quote($dbName) . " AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
foreach ($fkRows as $f) {
    $edges[$f['TABLE_NAME']][] = $f['REFERENCED_TABLE_NAME'];
}

// ---------------------------------------------------------------------------
// 3. Topological sort (parent sebelum child) -> deletion = reverse
// ---------------------------------------------------------------------------
$nodes = array_values(array_unique(array_merge($allTables, array_keys($edges))));
$inDegree = [];
foreach ($nodes as $n) {
    $inDegree[$n] = 0;
}
foreach ($edges as $child => $parents) {
    foreach (array_unique($parents) as $p) {
        if (isset($inDegree[$p])) {
            $inDegree[$child]++;
        }
    }
}
$queue = [];
foreach ($nodes as $n) {
    if ($inDegree[$n] === 0) {
        $queue[] = $n;
    }
}
$topo = [];
while ($queue) {
    $n = array_shift($queue);
    $topo[] = $n;
    foreach ($nodes as $m) {
        if (isset($edges[$m]) && in_array($n, $edges[$m], true)) {
            $inDegree[$m]--;
            if ($inDegree[$m] === 0) {
                $queue[] = $m;
            }
        }
    }
}
if (count($topo) !== count($nodes)) {
    fwrite(STDERR, "FATAL: grafik FK memiliki cycle — tidak bisa membuat urutan penghapusan dependency-safe. Reset dibatalkan.\n");
    exit(1);
}
$deleteOrder = array_reverse($topo); // child dulu, parent belakangan

echo "\nDATA YANG AKAN DIHAPUS (urutan dependency, child → parent):\n";
foreach ($deleteOrder as $t) {
    if (in_array($t, $KEEP, true)) {
        continue;
    }
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "  - " . str_pad($t, 28) . " rows={$cnt}\n";
}

echo "\nDATA YANG DIPERTAHANKAN:\n";
echo "  - users (kecuali superadmin) — tersisa 1 SUPERADMIN (" . $saEmail . ")\n";
echo "  - settings (system configuration)\n";
echo "  - schema_migrations\n";

$otherUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE id != $saId")->fetchColumn();
echo "\n  Jumlah user selain superadmin yang akan dihapus: {$otherUsers}\n";

if (!$execute) {
    echo "\n[DRY-RUN] Tidak ada perubahan dilakukan. Jalankan dengan: php scripts/reset_test_data.php --execute\n";
    exit(0);
}

// ---------------------------------------------------------------------------
// 4. EKSEKUSI (transaksional, rollback bila error)
// ---------------------------------------------------------------------------
try {
    $pdo->beginTransaction();

    foreach ($deleteOrder as $t) {
        if ($t === 'users') {
            // Hapus semua user selain SUPERADMIN yang dipertahankan.
            $del = $pdo->prepare("DELETE FROM users WHERE id != ?");
            $del->execute([$saId]);
            continue;
        }
        if (in_array($t, $KEEP, true)) {
            continue;
        }
        $pdo->exec("DELETE FROM `$t`");
    }

    // Pastikan superadmin tetap utuh (identitas & status login normal)
    $pdo->prepare("UPDATE users SET role = 'SUPERADMIN', account_status = 'APPROVED', is_active = 1 WHERE id = ?")->execute([$saId]);

    $pdo->commit();
    echo "\n[OK] Transaksi reset commit. Seluruh data bisnis/test dikosongkan.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "\n[FAIL] Reset DIBATALKAN (rollback penuh). Penyebab: " . $e->getMessage() . "\n");
    exit(1);
}

// ---------------------------------------------------------------------------
// 5. Bersihkan file storage yang berasal dari data test (best-effort)
//    - evidence/  : bukti transaksi (data test)
//    - items/     : foto master barang (data test)
//    - profiles/  : foto profil user yang dihapus (kecuali foto superadmin)
//    Logo utama & favicon (root private_uploads) TIDAK disentuh.
// ---------------------------------------------------------------------------
$base = __DIR__ . '/../storage/private_uploads';
$keptPhoto = $sa['photo_path'] ? basename($sa['photo_path']) : '';
$removedFiles = 0;
foreach (['evidence', 'items', 'profiles'] as $sub) {
    $dir = $base . '/' . $sub;
    if (!is_dir($dir)) {
        continue;
    }
    foreach (glob($dir . '/*') ?: [] as $file) {
        if ($sub === 'profiles' && $keptPhoto !== '' && basename($file) === $keptPhoto) {
            continue; // foto superadmin dipertahankan
        }
        if (is_file($file) && @unlink($file)) {
            $removedFiles++;
        }
    }
}
echo "[OK] File storage test dihapus: {$removedFiles} file (evidence/items/profiles, kecuali foto superadmin).\n";
echo "     Logo & favicon di storage/private_uploads dipertahankan.\n";

// ---------------------------------------------------------------------------
// 6. Verifikasi count setelah reset
// ---------------------------------------------------------------------------
echo "\n=== VERIFIKASI SETELAH RESET ===\n";
$usersNow = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$saNow = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'SUPERADMIN' AND account_status = 'APPROVED' AND is_active = 1")->fetchColumn();
$adminsNow = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN'")->fetchColumn();
$teknisiNow = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'TEKNISI_NOC'")->fetchColumn();
$pendingNow = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE account_status = 'PENDING_APPROVAL' OR account_status = 'REJECTED'")->fetchColumn();
echo "  users total          : {$usersNow}  (expected 1)\n";
echo "  SUPERADMIN approved  : {$saNow}  (expected 1)\n";
echo "  ADMIN                : {$adminsNow}  (expected 0)\n";
echo "  TEKNISI              : {$teknisiNow}  (expected 0)\n";
echo "  PENDING/REJECTED     : {$pendingNow}  (expected 0)\n";

$zeroExpected = ['master_items', 'item_categories', 'units', 'warehouses', 'locations', 'stocks', 'stock_ledgers',
    'serial_numbers', 'serial_movements', 'master_item_attachments', 'incoming', 'incoming_items', 'outgoing',
    'outgoing_items', 'transfers', 'transfer_items', 'transfer_item_serials', 'returns', 'return_items', 'loans',
    'loan_items', 'loan_item_serials', 'installations', 'installation_items', 'stock_adjustments',
    'stock_adjustment_items', 'stock_opnames', 'stock_opname_items', 'submissions', 'submission_items',
    'incidents', 'incident_items', 'attachments', 'audit_logs', 'notifications'];
$allZero = true;
foreach ($zeroExpected as $t) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($cnt !== 0) {
        $allZero = false;
        echo "  [!!] {$t} masih berisi {$cnt} baris\n";
    }
}
echo "  tabel bisnis/test    : " . ($allZero ? "SEMUA 0 ✅" : "MASIH ADA SISA ❌") . "\n";
$settingsCount = (int)$pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
$migrationsCount = (int)$pdo->query("SELECT COUNT(*) FROM schema_migrations")->fetchColumn();
echo "  settings             : {$settingsCount} (dipertahankan)\n";
echo "  schema_migrations    : {$migrationsCount} (dipertahankan)\n";
echo "\nSELESAI. Database test siap: 1 SUPERADMIN + 0 data bisnis/test + settings utuh + schema utuh.\n";
